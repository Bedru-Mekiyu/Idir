<?php

namespace Tests\Feature;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserHierarchyAndRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $platformOwner;

    protected Idir $idirA;

    protected Idir $idirB;

    protected User $chairUserA;

    protected Member $chairMemberA;

    protected User $secretaryUserA;

    protected Member $secretaryMemberA;

    protected User $regularUserA;

    protected Member $regularMemberA;

    protected User $chairUserB;

    protected Member $chairMemberB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Platform Owner
        $this->platformOwner = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'owner@platform.et',
            'is_platform_owner' => true,
        ]);

        // 2. Idir A
        $this->idirA = Idir::create([
            'name' => 'ሰላም እድር',
            'status' => 'active',
        ]);
        IdirSetting::create([
            'idir_id' => $this->idirA->id,
            'dues_amount' => 200.00,
            'fund_balance' => 1000.00,
        ]);

        $this->chairUserA = User::factory()->create(['name' => 'Chair Abebe', 'email' => 'chairA@idir.et']);
        $this->idirA->users()->attach($this->chairUserA);
        $this->chairMemberA = Member::create([
            'idir_id' => $this->idirA->id,
            'user_id' => $this->chairUserA->id,
            'full_name' => 'Chair Abebe',
            'phone' => '0911000001',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $this->secretaryUserA = User::factory()->create(['name' => 'Secretary Mary', 'email' => 'secA@idir.et']);
        $this->idirA->users()->attach($this->secretaryUserA);
        $this->secretaryMemberA = Member::create([
            'idir_id' => $this->idirA->id,
            'user_id' => $this->secretaryUserA->id,
            'full_name' => 'Secretary Mary',
            'phone' => '0911000002',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Secretary,
        ]);

        $this->regularUserA = User::factory()->create(['name' => 'Regular Kebede', 'email' => 'regA@idir.et']);
        $this->regularMemberA = Member::create([
            'idir_id' => $this->idirA->id,
            'user_id' => $this->regularUserA->id,
            'full_name' => 'Regular Kebede',
            'phone' => '0911000003',
            'join_date' => '2024-02-01',
            'status' => MemberStatus::Active,
            'committee_role' => null,
        ]);

        // 3. Idir B
        $this->idirB = Idir::create([
            'name' => 'አቢሲኒያ እድር',
            'status' => 'active',
        ]);
        IdirSetting::create([
            'idir_id' => $this->idirB->id,
            'dues_amount' => 300.00,
            'fund_balance' => 500.00,
        ]);

        $this->chairUserB = User::factory()->create(['name' => 'Chair Dawit', 'email' => 'chairB@idir.et']);
        $this->idirB->users()->attach($this->chairUserB);
        $this->chairMemberB = Member::create([
            'idir_id' => $this->idirB->id,
            'user_id' => $this->chairUserB->id,
            'full_name' => 'Chair Dawit',
            'phone' => '0922000001',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);
    }

    public function test_platform_owner_cannot_update_or_delete_or_change_role_of_members(): void
    {
        $this->assertFalse(Gate::forUser($this->platformOwner)->allows('update', $this->regularMemberA));
        $this->assertFalse(Gate::forUser($this->platformOwner)->allows('delete', $this->regularMemberA));
        $this->assertFalse(Gate::forUser($this->platformOwner)->allows('changeRole', $this->regularMemberA));
        $this->assertFalse(Gate::forUser($this->platformOwner)->allows('handoverChair', $this->regularMemberA));
        $this->assertFalse(Gate::forUser($this->platformOwner)->allows('deactivate', $this->regularMemberA));
        $this->assertFalse(Gate::forUser($this->platformOwner)->allows('create', Member::class));
    }

    public function test_platform_owner_can_manage_idir_status_only(): void
    {
        $pendingIdir = Idir::create([
            'name' => 'አዲስ እድር',
            'status' => 'pending_approval',
        ]);

        // Platform owner approves idir
        $pendingIdir->update(['status' => 'active', 'approved_at' => now()]);
        $this->assertTrue($pendingIdir->fresh()->isActive());

        // Platform owner suspends idir
        $pendingIdir->update(['status' => 'suspended']);
        $this->assertTrue($pendingIdir->fresh()->isSuspended());

        // Platform owner reactivates idir
        $pendingIdir->update(['status' => 'active']);
        $this->assertTrue($pendingIdir->fresh()->isActive());
    }

    public function test_chair_can_change_member_roles(): void
    {
        $this->assertTrue(Gate::forUser($this->chairUserA)->allows('changeRole', $this->regularMemberA));

        // Promote to Treasurer
        $this->regularMemberA->update(['committee_role' => CommitteeRole::Treasurer]);
        $this->assertEquals(CommitteeRole::Treasurer, $this->regularMemberA->fresh()->committee_role);

        // Promote to Secretary
        $this->regularMemberA->update(['committee_role' => CommitteeRole::Secretary]);
        $this->assertEquals(CommitteeRole::Secretary, $this->regularMemberA->fresh()->committee_role);

        // Demote back to Regular Member
        $this->regularMemberA->update(['committee_role' => null]);
        $this->assertNull($this->regularMemberA->fresh()->committee_role);
    }

    public function test_non_chair_committee_members_cannot_change_roles(): void
    {
        $this->assertFalse(Gate::forUser($this->secretaryUserA)->allows('changeRole', $this->regularMemberA));
        $this->assertFalse(Gate::forUser($this->regularUserA)->allows('changeRole', $this->secretaryMemberA));
    }

    public function test_chair_can_deactivate_member_preserving_ledger_and_history(): void
    {
        // Add historical contribution
        Contribution::create([
            'idir_id' => $this->idirA->id,
            'member_id' => $this->regularMemberA->id,
            'amount' => 200.00,
            'method' => 'cash',
            'type' => 'cash',
            'period_covered' => '2026-06',
        ]);

        $this->assertTrue(Gate::forUser($this->chairUserA)->allows('deactivate', $this->regularMemberA));

        // Deactivate member
        $this->regularMemberA->update([
            'status' => MemberStatus::Inactive,
            'committee_role' => null,
            'exclusion_reason' => 'ወደ ሌላ ከተማ በመዛወራቸው በፈቃዳቸው የወጡ',
            'excluded_at' => now(),
        ]);

        $freshMember = $this->regularMemberA->fresh();
        $this->assertEquals(MemberStatus::Inactive, $freshMember->status);
        $this->assertNull($freshMember->committee_role);

        // Verify contribution history is preserved
        $this->assertEquals(1, $freshMember->contributions()->count());
        $this->assertEquals(200.00, $freshMember->contributions()->sum('amount'));

        // Reactivate member
        $this->assertTrue(Gate::forUser($this->chairUserA)->allows('reactivate', $freshMember));
        $freshMember->update([
            'status' => MemberStatus::Active,
            'exclusion_reason' => null,
            'excluded_at' => null,
        ]);
        $this->assertEquals(MemberStatus::Active, $freshMember->fresh()->status);
    }

    public function test_chair_can_handoff_chairmanship(): void
    {
        $this->assertTrue(Gate::forUser($this->chairUserA)->allows('handoverChair', $this->secretaryMemberA));

        // Transfer chair role to Secretary Mary, Abebe becomes regular member
        $this->secretaryMemberA->update(['committee_role' => CommitteeRole::Chair]);
        $this->chairMemberA->update(['committee_role' => null]);

        $this->secretaryUserA->unsetRelation('member');
        $this->chairUserA->unsetRelation('member');

        $this->assertEquals(CommitteeRole::Chair, $this->secretaryMemberA->fresh()->committee_role);
        $this->assertNull($this->chairMemberA->fresh()->committee_role);

        // New chair Mary can now manage roles
        $this->assertTrue(Gate::forUser($this->secretaryUserA)->allows('changeRole', $this->chairMemberA));
        // Old chair Abebe can no longer manage roles
        $this->assertFalse(Gate::forUser($this->chairUserA)->allows('changeRole', $this->secretaryMemberA));
    }

    public function test_tenant_isolation_prevents_cross_idir_management(): void
    {
        // Chair of Idir A cannot change role or deactivate members in Idir B
        $this->assertFalse(Gate::forUser($this->chairUserA)->allows('changeRole', $this->chairMemberB));
        $this->assertFalse(Gate::forUser($this->chairUserA)->allows('handoverChair', $this->chairMemberB));
        $this->assertFalse(Gate::forUser($this->chairUserA)->allows('deactivate', $this->chairMemberB));
        $this->assertFalse(Gate::forUser($this->chairUserA)->allows('update', $this->chairMemberB));
    }
}
