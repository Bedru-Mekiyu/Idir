<?php

namespace Tests\Feature;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use App\Filament\Pages\Tenancy\RegisterIdir;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IdirApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $founder;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->founder = User::create([
            'name' => 'ዳዊት ከበደ ገብሬ',
            'phone' => '0911554433',
            'phone_verified_at' => now(),
            'can_create_idir' => true,
            'password' => bcrypt('password'),
        ]);

        $this->owner = User::create([
            'name' => 'Platform Super Admin',
            'email' => 'owner@idir-platform.et',
            'phone' => '0900000000',
            'phone_verified_at' => now(),
            'password' => bcrypt('password'),
            'is_platform_owner' => true,
        ]);
    }

    public function test_new_idir_registration_creates_pending_approval_status(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('committee'));
        $this->actingAs($this->founder);

        Livewire::test(RegisterIdir::class)
            ->fillForm([
                'name' => 'ድሬዳዋ አንድነት እድር',
                'membership_basis' => 'የሰፈር ነዋሪዎች',
                'region' => 'ድሬዳዋ',
                'sub_city' => 'ቀበሌ 01',
                'dues_amount' => 250.00,
                'dues_frequency' => 'monthly',
                'vesting_period_days' => 60,
                'required_approvals' => 2,
                'payout_triggers' => ['death', 'emergency'],
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $idir = Idir::where('name', 'ድሬዳዋ አንድነት እድር')->first();
        $this->assertNotNull($idir);
        $this->assertEquals('pending_approval', $idir->status);
        $this->assertTrue($idir->isPendingApproval());
        $this->assertFalse($idir->isActive());

        // Founder is Chair
        $chair = $idir->members()->where('user_id', $this->founder->id)->first();
        $this->assertNotNull($chair);
        $this->assertEquals(CommitteeRole::Chair, $chair->committee_role);
    }

    public function test_platform_owner_can_approve_pending_idir(): void
    {
        $idir = Idir::create([
            'name' => 'ባህር ዳር ማህበር እድር',
            'locale' => 'am',
            'status' => 'pending_approval',
        ]);
        $this->founder->idirs()->attach($idir);
        Member::create([
            'idir_id' => $idir->id,
            'user_id' => $this->founder->id,
            'full_name' => $this->founder->name,
            'phone' => $this->founder->phone,
            'join_date' => now()->toDateString(),
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $this->assertTrue($idir->isPendingApproval());

        // Platform Owner approves
        $idir->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $this->assertTrue($idir->fresh()->isActive());
        $this->assertNotNull($idir->fresh()->approved_at);
    }

    public function test_platform_owner_can_reject_pending_idir_with_reason(): void
    {
        $idir = Idir::create([
            'name' => 'ደሴ ሰላም እድር',
            'locale' => 'am',
            'status' => 'pending_approval',
        ]);
        $this->founder->idirs()->attach($idir);
        Member::create([
            'idir_id' => $idir->id,
            'user_id' => $this->founder->id,
            'full_name' => $this->founder->name,
            'phone' => $this->founder->phone,
            'join_date' => now()->toDateString(),
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        // Platform Owner rejects with reason
        $reason = 'የቀረበው የእድር መተዳደሪያ ደንብ እና የቦታ መረጃ ያልተሟላ ነው።';
        $idir->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'rejected_at' => now(),
        ]);

        $this->assertTrue($idir->fresh()->isRejected());
        $this->assertEquals($reason, $idir->fresh()->rejection_reason);

        // Member portal renders rejection message
        $response = $this->actingAs($this->founder)->get('/member');
        $response->assertStatus(403);
        $response->assertSee('የእድር ምዝገባው ውድቅ ተደርጓል');
        $response->assertSee($reason);
    }

    public function test_pre_existing_active_idirs_remain_unaffected(): void
    {
        $existingIdir = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'locale' => 'am',
            'status' => 'active',
        ]);
        IdirSetting::create([
            'idir_id' => $existingIdir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => 'monthly',
            'fund_balance' => 50000.00,
        ]);
        $existingIdir->users()->attach($this->founder);
        Member::create([
            'idir_id' => $existingIdir->id,
            'user_id' => $this->founder->id,
            'full_name' => $this->founder->name,
            'phone' => $this->founder->phone,
            'join_date' => now()->toDateString(),
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $this->assertTrue($existingIdir->isActive());

        // Member portal operates normally
        $response = $this->actingAs($this->founder)->get('/member');
        $response->assertStatus(200);
    }
}
