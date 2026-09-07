<?php

namespace Tests\Feature;

use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use App\Rules\EthiopianPhone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;

    protected User $user;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idir = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'locale' => 'am',
        ]);

        IdirSetting::create([
            'idir_id' => $this->idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 0.00,
        ]);

        $this->user = User::create([
            'name' => 'ሙሉጌታ ኃይለ ማርያም',
            'email' => 'mulugeta@example.com',
            'phone' => '0911556677',
            'password' => Hash::make('password123'),
        ]);

        $this->member = Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $this->user->id,
            'full_name' => $this->user->name,
            'phone' => $this->user->phone,
            'join_date' => now()->subMonths(6)->toDateString(),
            'status' => MemberStatus::Active,
        ]);
    }

    public function test_member_can_login_with_phone_number(): void
    {
        $response = $this->post('/member/login', [
            'login' => '0911556677',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/member');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_member_login_fails_with_wrong_password(): void
    {
        $response = $this->post('/member/login', [
            'login' => '0911556677',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_member_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/member/logout');

        $response->assertRedirect('/member/login');
        $this->assertGuest();
    }

    public function test_public_quick_status_lookup_is_disabled_and_redirects_to_login(): void
    {
        // GET /lookup redirects to member login
        $response = $this->get('/lookup');
        $response->assertRedirect('/member/login');

        // GET /member/lookup redirects to member login
        $response = $this->get('/member/lookup');
        $response->assertRedirect('/member/login');

        // POST /member/lookup with phone redirects to login without revealing member details
        $response = $this->post('/member/lookup', [
            'phone' => '0911556677',
        ]);

        $response->assertRedirect('/member/login');
        $response->assertDontSee('ሙሉጌታ ኃይለ ማርያም');
        $response->assertDontSee('ሰላም የሰፈር እድር');
    }

    public function test_ethiopian_phone_validation_rule(): void
    {
        $rule = new EthiopianPhone;

        // Valid phone numbers
        $validPhones = ['0911223344', '0711223344', '+251911223344', '251911223344'];
        foreach ($validPhones as $phone) {
            $failed = false;
            $rule->validate('phone', $phone, function () use (&$failed) {
                $failed = true;
            });
            $this->assertFalse($failed, "Failed on valid phone: {$phone}");
        }

        // Invalid phone numbers
        $invalidPhones = ['12345', '0811223344', 'abcdefghij', '+1234567890'];
        foreach ($invalidPhones as $phone) {
            $failed = false;
            $rule->validate('phone', $phone, function () use (&$failed) {
                $failed = true;
            });
            $this->assertTrue($failed, "Did not fail on invalid phone: {$phone}");
        }
    }
}
