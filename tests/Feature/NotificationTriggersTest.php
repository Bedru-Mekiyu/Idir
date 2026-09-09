<?php

namespace Tests\Feature;

use App\Enums\CommitteeRole;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Jobs\SendNotificationJob;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\NotificationEvent;
use App\Models\PayoutTriggerType;
use App\Models\User;
use App\Services\AfroMessageService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationTriggersTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;

    protected Member $chair;

    protected User $memberUser;

    protected Member $member;

    protected PayoutTriggerType $trigger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idir = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'locale' => 'am',
            'status' => 'active',
        ]);

        IdirSetting::create([
            'idir_id' => $this->idir->id,
            'dues_amount' => 2500.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 0.00,
        ]);

        $this->chair = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'ሰብሳቢ አበበ',
            'phone' => '0911000010',
            'join_date' => '2025-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $this->memberUser = User::create([
            'name' => 'መደበኛ አባል',
            'email' => 'member@example.com',
            'phone' => '0911000020',
            'password' => bcrypt('password'),
        ]);

        $this->member = Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $this->memberUser->id,
            'full_name' => $this->memberUser->name,
            'phone' => $this->memberUser->phone,
            'join_date' => '2025-01-01',
            'status' => MemberStatus::Active,
        ]);

        $this->trigger = PayoutTriggerType::create([
            'idir_id' => $this->idir->id,
            'name' => 'death',
            'label_am' => 'ሞት',
            'default_payout_amount' => 8000.00,
            'is_active' => true,
        ]);
    }

    public function test_member_claim_submission_dispatches_claim_filed_to_committee(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->memberUser)->post('/member/claims', [
            'payout_trigger_type_id' => $this->trigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የቀብር እርዳታ ጥያቄ ማብራሪያ',
        ]);

        $response->assertRedirect('/member');

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) {
            return $job->type === NotificationType::ClaimFiled
                && $job->memberId === $this->chair->id;
        });
    }

    public function test_check_arrears_command_dispatches_late_warning(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 15, 10, 0, 0));
        Queue::fake();

        $this->artisan('idir:check-arrears')->assertSuccessful();

        $this->assertEquals(MemberStatus::InArrears, $this->member->fresh()->status);

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) {
            return $job->type === NotificationType::LateWarning
                && $job->memberId === $this->member->id;
        });

        Carbon::setTestNow();
    }

    public function test_send_due_reminders_command_dispatches_due_reminder(): void
    {
        Queue::fake();

        $this->artisan('idir:send-due-reminders')->assertSuccessful();

        Queue::assertPushed(SendNotificationJob::class, function (SendNotificationJob $job) {
            return $job->type === NotificationType::DueReminder
                && $job->memberId === $this->member->id;
        });
    }

    public function test_payment_confirmation_message_formats_birr_currency(): void
    {
        config(['services.afromessage.token' => 'test-token-xyz']);

        Http::fake([
            'api.afromessage.com/*' => Http::response(['acknowledge' => 'success'], 200),
        ]);

        $job = new SendNotificationJob(
            $this->idir->id,
            $this->member->id,
            NotificationType::PaymentConfirmation,
            ['amount' => 2500, 'period' => '2026-03'],
        );

        $job->handle(app(AfroMessageService::class), app(TelegramService::class));

        $event = NotificationEvent::latest()->first();

        // Proper Ethiopian Birr formatting: "2,500.00 ብር" — not a raw float or ".00000".
        $this->assertStringContainsString('2,500.00 ብር', $event->message_content);
        $this->assertStringNotContainsString('2500', $event->message_content);
    }

    public function test_send_notification_job_throws_to_retry_on_gateway_failure(): void
    {
        config(['services.afromessage.token' => 'invalid-token']);

        Http::fake([
            'api.afromessage.com/*' => Http::response(['acknowledge' => 'error', 'response' => ['errors' => ['invalid api token']]], 401),
        ]);

        $job = new SendNotificationJob(
            $this->idir->id,
            $this->member->id,
            NotificationType::PaymentConfirmation,
            ['amount' => 100, 'period' => '2026-03'],
        );

        try {
            $job->handle(app(AfroMessageService::class), app(TelegramService::class));
            $this->fail('Expected the job to throw so the queue retries the delivery.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('will be retried', $e->getMessage());
        }

        // The failed attempt is still recorded honestly as failed (not sent).
        $this->assertDatabaseHas('notification_events', [
            'member_id' => $this->member->id,
            'channel' => NotificationChannel::Sms->value,
            'status' => NotificationStatus::Failed->value,
        ]);
    }

    public function test_every_notification_type_has_a_default_template(): void
    {
        foreach (NotificationType::cases() as $type) {
            $job = new SendNotificationJob($this->idir->id, $this->member->id, $type, []);

            $method = new \ReflectionMethod($job, 'defaultTemplate');
            $method->setAccessible(true);
            $template = $method->invoke($job);

            $this->assertNotEmpty($template, "Missing default template for {$type->value}");
        }
    }
}
