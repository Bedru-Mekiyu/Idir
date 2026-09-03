<?php

namespace Tests\Feature;

use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformOwnerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $chairUser;
    protected Idir $idir;
    protected Member $chairMember;

    protected function setUp(): void
    {
        parent::setUp();

        // Platform Owner
        $this->owner = User::create([
            'name' => 'የፕላትፎርም ባለቤት (Super Admin)',
            'email' => 'owner@idir-platform.et',
            'phone' => '0900000000',
            'password' => bcrypt('password'),
            'is_platform_owner' => true,
        ]);

        // Tenant Idir
        $this->idir = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'locale' => 'am',
            'status' => 'active',
        ]);
        IdirSetting::create([
            'idir_id' => $this->idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => 'monthly',
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 15000.00,
        ]);

        // Committee Chair User
        $this->chairUser = User::create([
            'name' => 'አበበ ተሰማ',
            'email' => 'chair@idir.et',
            'phone' => '0911223344',
            'password' => bcrypt('password'),
            'is_platform_owner' => false,
        ]);
        $this->idir->users()->attach($this->chairUser);

        $this->chairMember = Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $this->chairUser->id,
            'full_name' => $this->chairUser->name,
            'phone' => $this->chairUser->phone,
            'join_date' => '2024-01-01',
            'status' => \App\Enums\MemberStatus::Active,
            'committee_role' => \App\Enums\CommitteeRole::Chair,
        ]);
    }

    public function test_platform_owner_can_access_admin_panel(): void
    {
        $response = $this->actingAs($this->owner)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('የእድር ፕላትፎርም ባለቤት');
    }

    public function test_regular_committee_user_is_blocked_from_admin_panel(): void
    {
        $response = $this->actingAs($this->chairUser)->get('/admin');

        // Filament blocks users where canAccessPanel() is false
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
    }

    public function test_platform_owner_can_suspend_and_reactivate_idir(): void
    {
        $this->assertEquals('active', $this->idir->status);

        // Suspend
        $this->idir->update(['status' => 'suspended']);
        $this->assertTrue($this->idir->fresh()->isSuspended());
        $this->assertFalse($this->idir->fresh()->isActive());

        // Suspended idir blocks member portal
        $response = $this->actingAs($this->chairUser)->get('/member');
        $response->assertStatus(403);
        $response->assertSee('ይህ እድር በጊዜያዊነት ታግዷል');

        // Reactivate
        $this->idir->update(['status' => 'active']);
        $this->assertTrue($this->idir->fresh()->isActive());

        $responseRestored = $this->actingAs($this->chairUser)->get('/member');
        $responseRestored->assertStatus(200);
    }
}
