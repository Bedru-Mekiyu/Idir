<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    protected AfroMessageService $afroMessage;

    public function __construct(AfroMessageService $afroMessage)
    {
        $this->afroMessage = $afroMessage;
    }

    /**
     * Generate, store, and send 6-digit OTP code to the user's phone.
     */
    public function sendOtp(User $user): string
    {
        // Generate secure 6-digit code
        $otp = app()->environment('testing', 'local') ? '123456' : (string) random_int(100000, 999999);

        // Store in cache for 10 minutes
        $cacheKey = "phone_otp_{$user->id}";
        Cache::put($cacheKey, $otp, now()->addMinutes(10));

        $message = "የእድር ፕላትፎርም የማረጋገጫ ኮድዎ: {$otp} ነው። ይህን ኮድ ለማንም አያጋሩ። (Your Idir Platform verification code is: {$otp})";

        Log::info("OTP generated for User {$user->id} ({$user->phone}): {$otp}");

        // Send via AfroMessage SMS
        $this->afroMessage->sendSms($user->phone, $message);

        return $otp;
    }

    /**
     * Verify the provided OTP code.
     */
    public function verifyOtp(User $user, string $code): bool
    {
        $cacheKey = "phone_otp_{$user->id}";
        $cachedOtp = Cache::get($cacheKey);

        // Allow test code in test/local environments if cache expired or in automated test
        $isValid = ($cachedOtp && $cachedOtp === trim($code)) ||
            (app()->environment('testing', 'local') && trim($code) === '123456');

        if ($isValid) {
            $user->markPhoneAsVerified();
            Cache::forget($cacheKey);
            Log::info("Phone verified successfully for User {$user->id} ({$user->phone})");
            return true;
        }

        return false;
    }
}
