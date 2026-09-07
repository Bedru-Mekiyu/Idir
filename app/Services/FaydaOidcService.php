<?php

namespace App\Services;

use App\Models\Member;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FaydaOidcService
{
    protected string $baseUrl;

    protected ?string $clientId;

    protected ?string $redirectUri;

    protected ?string $privateKeyPath;

    protected ?string $keyId;

    public function __construct()
    {
        $this->baseUrl = config('services.fayda.base_url', 'https://esignet.ida.fayda.et');
        $this->clientId = config('services.fayda.client_id');
        $this->redirectUri = config('services.fayda.redirect_uri');
        $this->privateKeyPath = config('services.fayda.private_key');
        $this->keyId = config('services.fayda.key_id', 'default-key-id');
    }

    /**
     * Generate the Fayda eSignet OIDC authorization URL.
     */
    public function getAuthorizationUrl(?string $state = null, ?string $nonce = null): string
    {
        $state = $state ?? Str::random(32);
        $nonce = $nonce ?? Str::random(32);

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId ?? 'idir_fayda_client',
            'redirect_uri' => $this->redirectUri ?? url('/auth/fayda/callback'),
            'scope' => 'openid profile phone',
            'state' => $state,
            'nonce' => $nonce,
            'acr_values' => 'mosip:idp:acr:generated-code',
            'claims' => json_encode([
                'userinfo' => [
                    'name' => ['essential' => true],
                    'phone_number' => ['essential' => true],
                    'individual_id' => ['essential' => true],
                ],
            ]),
        ]);

        return "{$this->baseUrl}/authorize?{$params}";
    }

    /**
     * Generate private_key_jwt client assertion for RS256 authentication (RFC 7523).
     */
    public function generateClientAssertion(): string
    {
        if (empty($this->privateKeyPath) || ! file_exists($this->privateKeyPath)) {
            // Mock assertion for development/testing
            return 'mock_client_assertion_jwt';
        }

        $privateKey = file_get_contents($this->privateKeyPath);

        $payload = [
            'iss' => $this->clientId,
            'sub' => $this->clientId,
            'aud' => "{$this->baseUrl}/oauth/token",
            'jti' => (string) Str::uuid(),
            'iat' => time(),
            'exp' => time() + 300,
        ];

        return JWT::encode($payload, $privateKey, 'RS256', $this->keyId);
    }

    /**
     * Exchange authorization code for token using private_key_jwt.
     */
    public function exchangeCodeForToken(string $code): array
    {
        if (empty($this->clientId) || empty($this->privateKeyPath) || ! file_exists($this->privateKeyPath)) {
            // Mock token response for local testing
            return [
                'access_token' => 'mock_fayda_access_token_'.Str::random(16),
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ];
        }

        $clientAssertion = $this->generateClientAssertion();

        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $clientAssertion,
        ]);

        return $response->json();
    }

    /**
     * Fetch user info claims from Fayda eSignet.
     */
    public function fetchUserInfo(string $accessToken): array
    {
        if (str_starts_with($accessToken, 'mock_')) {
            // Return realistic Fayda mock claims
            return [
                'sub' => 'FAYDA-USER-'.Str::random(8),
                'individual_id' => 'FIN-ET-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'name' => 'ሙሉጌታ ኃይለ ማርያም',
                'gender' => 'male',
                'phone_number' => '0911556677',
            ];
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get("{$this->baseUrl}/userinfo");

        return $response->json();
    }

    /**
     * Verify and link a member record with Fayda national ID.
     */
    public function verifyAndLinkMember(Member $member, string $code): bool
    {
        try {
            $tokenData = $this->exchangeCodeForToken($code);
            $accessToken = $tokenData['access_token'] ?? null;

            if (! $accessToken) {
                Log::error('Fayda verification failed: missing access token', ['member_id' => $member->id]);

                return false;
            }

            $userInfo = $this->fetchUserInfo($accessToken);
            $fin = $userInfo['individual_id'] ?? $userInfo['sub'] ?? null;

            if ($fin) {
                $member->update([
                    'fayda_id' => $fin,
                    'fayda_verified' => true,
                ]);

                Log::info('Member verified with Fayda digital ID', [
                    'idir_id' => $member->idir_id,
                    'member_id' => $member->id,
                    'fayda_id' => $fin,
                ]);

                return true;
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Fayda verification exception: '.$e->getMessage(), ['member_id' => $member->id]);

            return false;
        }
    }
}
