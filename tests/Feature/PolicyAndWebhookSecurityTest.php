<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\CommitteeRole;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Models\Claim;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\PayoutTriggerType;
use App\Models\User;
use App\Policies\ClaimPolicy;
use App\Policies\ContributionPolicy;
use App\Policies\IdirSettingPolicy;
use App\Policies\MemberPolicy;
use App\Services\FaydaOidcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyAndWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;
    protected User $chairUser;
    protected Member $chairMember;
    protected User $regularUser;
    protected Member $regularMember;
    protected PayoutTriggerType $trigger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idir = Idir::create(['name' => 'ሰላም እድር', 'locale' => 'am']);
        IdirSetting::create([
            'idir_id' => $this->idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 10000.00,
        ]);

        $this->chairUser = User::create([
            'name' => 'አበበ ተሰማ (ሰብሳቢ)',
            'email' => 'chair@idir.et',
            'phone' => '0911000001',
            'password' => bcrypt('password'),
        ]);
        $this->chairMember = Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $this->chairUser->id,
            'full_name' => $this->chairUser->name,
            'phone' => $this->chairUser->phone,
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $this->regularUser = User::create([
            'name' => 'ተፈራ ደስታ (ተራ አባል)',
            'email' => 'regular@idir.et',
            'phone' => '0911000002',
            'password' => bcrypt('password'),
        ]);
        $this->regularMember = Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $this->regularUser->id,
            'full_name' => $this->regularUser->name,
            'phone' => $this->regularUser->phone,
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => null,
        ]);

        $this->trigger = PayoutTriggerType::create([
            'idir_id' => $this->idir->id,
            'name' => 'death',
            'label_am' => 'ሞት',
            'default_payout_amount' => 8000.00,
            'is_active' => true,
        ]);
    }

    public function test_policies_restrict_sensitive_actions_to_committee(): void
    {
        $memberPolicy = new MemberPolicy();
        $contributionPolicy = new ContributionPolicy();
        $claimPolicy = new ClaimPolicy();
        $settingPolicy = new IdirSettingPolicy();

        $claim = Claim::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->regularMember->id,
            'payout_trigger_type_id' => $this->trigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የቀብር እርዳታ',
            'status' => ClaimStatus::Pending,
        ]);

        // Member Policy: Chair can create/exclude, regular member cannot
        $this->assertTrue($memberPolicy->create($this->chairUser));
        $this->assertFalse($memberPolicy->create($this->regularUser));

        // Contribution Policy: Chair can record cash contribution, regular member cannot
        $this->assertTrue($contributionPolicy->create($this->chairUser));
        $this->assertFalse($contributionPolicy->create($this->regularUser));

        // Claim Policy: Chair can approve/reject, regular member cannot
        $this->assertTrue($claimPolicy->approve($this->chairUser, $claim));
        $this->assertFalse($claimPolicy->approve($this->regularUser, $claim));

        // Setting Policy: Only chair can update settings
        $this->assertTrue($settingPolicy->update($this->chairUser, $this->idir->settings));
        $this->assertFalse($settingPolicy->update($this->regularUser, $this->idir->settings));
    }

    public function test_chapa_webhook_rejects_invalid_signature(): void
    {
        config(['services.chapa.webhook_secret' => 'super-secret-key']);

        $payload = json_encode(['tx_ref' => 'TEST-TX-123']);

        $response = $this->call(
            'POST',
            '/api/chapa/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CHAPA_SIGNATURE' => 'invalid-hash-signature',
            ],
            $payload
        );

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Invalid webhook signature']);
    }

    public function test_fayda_oidc_service_generates_auth_url_and_links_member(): void
    {
        $fayda = app(FaydaOidcService::class);
        $url = $fayda->getAuthorizationUrl('state123', 'nonce123');

        $this->assertStringContainsString('https://esignet.ida.fayda.et/authorize', $url);
        $this->assertStringContainsString('response_type=code', $url);

        // Test linking
        $success = $fayda->verifyAndLinkMember($this->regularMember, 'auth_code_sample');
        $this->assertTrue($success);
        $this->assertTrue($this->regularMember->fresh()->fayda_verified);
        $this->assertNotNull($this->regularMember->fresh()->fayda_id);
    }
}
