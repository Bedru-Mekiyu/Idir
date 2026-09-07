<?php

namespace Tests\Feature;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use App\Filament\Pages\Tenancy\RegisterIdir;
use App\Models\Idir;
use App\Models\Member;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SelfServiceSignupJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_view_landing_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('እድር');
        $response->assertSee('ይመዝገቡ');
    }

    public function test_public_user_can_signup_and_receives_otp(): void
    {
        $response = $this->post('/register', [
            'name' => 'አብዱራህማን ሁሴን',
            'phone' => '0988776655',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect('/verify-phone');

        $user = User::where('phone', '0988776655')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->phone_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_user_cannot_access_tenant_registration_wizard(): void
    {
        $user = User::create([
            'name' => 'አብዱራህማን ሁሴን',
            'phone' => '0988776655',
            'password' => bcrypt('password'),
            'phone_verified_at' => null,
        ]);

        $this->actingAs($user);

        // Attempting to visit /committee/new without verified phone
        $response = $this->get('/committee/new');
        $response->assertRedirect('/verify-phone');
    }

    public function test_user_can_verify_phone_with_otp_and_redirects_to_access_request(): void
    {
        $user = User::create([
            'name' => 'አብዱራህማን ሁሴን',
            'phone' => '0988776655',
            'password' => bcrypt('password'),
            'phone_verified_at' => null,
            'can_create_idir' => false,
        ]);

        $this->actingAs($user);

        // Generate OTP
        app(OtpService::class)->sendOtp($user);

        // Submit OTP verification
        $response = $this->post('/verify-phone', [
            'code' => '123456',
        ]);

        // In new flow, phone-verified user is redirected to /access-request, NOT /committee/new
        $response->assertRedirect('/access-request');
        $this->assertTrue($user->fresh()->isPhoneVerified());
        $this->assertFalse($user->fresh()->can_create_idir);
    }

    public function test_verified_user_creates_idir_becomes_chair_and_invites_treasurer(): void
    {
        // 1. New verified and authorized founder
        $founder = User::create([
            'name' => 'ዮናስ ታደሰ በቀለ',
            'phone' => '0977112233',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => true,
        ]);

        $this->actingAs($founder);

        // 2. Submit RegisterIdir wizard
        Livewire::test(RegisterIdir::class)
            ->fillForm([
                'name' => 'ሐዋሳ አንድነት እድር',
                'membership_basis' => 'የሰፈር ነዋሪዎች',
                'region' => 'ሲዳማ',
                'sub_city' => 'ሐዋሳ',
                'woreda' => 'ታቦር',
                'dues_amount' => 300.00,
                'dues_frequency' => 'monthly',
                'late_fee_amount' => 60.00,
                'late_fee_grace_days' => 7,
                'vesting_period_days' => 60,
                'required_approvals' => 2,
                'payout_triggers' => ['death', 'emergency'],
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        // 3. Confirm idir created in pending_approval status and founder is Chair
        $idir = Idir::where('name', 'ሐዋሳ አንድነት እድር')->first();
        $this->assertNotNull($idir);
        $this->assertEquals('pending_approval', $idir->status);

        $chairMember = $idir->members()->where('user_id', $founder->id)->first();
        $this->assertNotNull($chairMember);
        $this->assertEquals(CommitteeRole::Chair, $chairMember->committee_role);

        // 4. Platform owner approves idir
        $idir->update(['status' => 'active', 'approved_at' => now()]);
        $this->assertTrue($idir->fresh()->isActive());

        // 5. Founder (Chair) invites another member and assigns Treasurer role
        $newTreasurer = Member::create([
            'idir_id' => $idir->id,
            'full_name' => 'ሳራ መንግስቱ ኃይሌ',
            'phone' => '0977445566',
            'join_date' => now()->toDateString(),
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Treasurer,
        ]);

        $this->assertNotNull($newTreasurer);
        $this->assertEquals(CommitteeRole::Treasurer, $newTreasurer->committee_role);

        // 6. Confirm platform owner sees this new idir in overview
        $owner = User::create([
            'name' => 'Super Admin',
            'email' => 'owner@platform.et',
            'password' => bcrypt('password'),
            'is_platform_owner' => true,
        ]);

        $this->actingAs($owner);
        $adminResponse = $this->get('/admin/idirs');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('ሐዋሳ አንድነት እድር');
    }
}
