<?php

namespace Tests\Feature;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\NotificationPreference;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpirePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;
    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idir = Idir::create([
            'name' => 'ሙከራ ሰላም እድር',
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

        $this->member = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'ተፈራ አበበ',
            'phone' => '0911000001',
            'join_date' => now()->subMonths(6)->toDateString(),
            'status' => MemberStatus::Active,
        ]);
    }

    public function test_expire_pending_payments_command_marks_old_payments_expired(): void
    {
        // 1. Create an old pending payment (30 hours old)
        $oldPending = Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Chapa,
            'type' => ContributionType::Cash,
            'period_covered' => '2026-08',
            'chapa_tx_ref' => 'OLD-REF-12345',
            'chapa_status' => ChapaStatus::Pending,
        ]);
        \Illuminate\Support\Facades\DB::table('contributions')
            ->where('id', $oldPending->id)
            ->update(['created_at' => now()->subHours(30)]);

        // 2. Create a fresh pending payment (2 hours old)
        $freshPending = Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Chapa,
            'type' => ContributionType::Cash,
            'period_covered' => '2026-08',
            'chapa_tx_ref' => 'FRESH-REF-67890',
            'chapa_status' => ChapaStatus::Pending,
            'created_at' => now()->subHours(2),
        ]);

        // Run the scheduled artisan command (Fix #6)
        $this->artisan('idir:expire-pending-payments')
            ->assertSuccessful();

        // Old pending is now expired
        $this->assertEquals(ChapaStatus::Expired, $oldPending->fresh()->chapa_status);

        // Fresh pending remains pending
        $this->assertEquals(ChapaStatus::Pending, $freshPending->fresh()->chapa_status);
    }

    public function test_unique_constraint_on_notification_preferences(): void
    {
        // First preference for due_reminder
        NotificationPreference::create([
            'idir_id' => $this->idir->id,
            'event_type' => NotificationType::DueReminder,
            'sms_enabled' => true,
            'template_am' => 'የመዋጮ ማስታወሻ',
        ]);

        // Second preference for SAME idir and SAME event_type must throw QueryException (Fix #5)
        $this->expectException(QueryException::class);

        NotificationPreference::create([
            'idir_id' => $this->idir->id,
            'event_type' => NotificationType::DueReminder,
            'sms_enabled' => false,
            'template_am' => 'ሁለተኛ ማስታወሻ',
        ]);
    }
}
