<?php

namespace Tests\Feature;

use App\Enums\ChapaStatus;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Jobs\ProcessPaymentWebhookJob;
use App\Jobs\SendNotificationJob;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\NotificationEvent;
use App\Models\NotificationPreference;
use App\Services\AfroMessageService;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\LedgerService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QueuedJobsTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;

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

        $this->member = Member::create([
            'idir_id' => $this->idir->id,
            'full_name' => 'አበበ ተሰማ',
            'phone' => '0911223344',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
        ]);
    }

    public function test_process_payment_webhook_job_updates_ledger(): void
    {
        $contribution = Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 500.00,
            'method' => PaymentMethod::Chapa,
            'period_covered' => '2026-08',
            'chapa_tx_ref' => 'TEST-TX-REF-12345',
            'chapa_status' => ChapaStatus::Pending,
        ]);

        $job = Http::fake([
            'api.chapa.co/v1/transaction/verify/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'success',
                    'tx_ref' => 'TEST-TX-REF-12345',
                    'currency' => 'ETB',
                    'amount' => 100,
                ]
            ], 200),
        ]);
        
        $job = new ProcessPaymentWebhookJob('chapa', 'TEST-TX-REF-12345');
        $job->handle(app(PaymentGatewayManager::class), app(LedgerService::class));

        // Contribution is verified and fund balance updated
        $this->assertEquals(ChapaStatus::Verified, $contribution->fresh()->chapa_status);
        $this->assertEquals(500.00, $this->idir->settings->fresh()->fund_balance);
    }

    public function test_send_notification_job_renders_template_and_logs_event(): void
    {
        NotificationPreference::create([
            'idir_id' => $this->idir->id,
            'event_type' => NotificationType::PaymentConfirmation,
            'sms_enabled' => true,
            'template_am' => 'ክፍያ ተረጋግጧል፦ ውድ :member_name፣ ለ:period ወር የተከፈለው :amount ብር ገቢ ሆኗል። :idir_name',
        ]);

        $job = new SendNotificationJob(
            $this->idir->id,
            $this->member->id,
            NotificationType::PaymentConfirmation,
            [
                'amount' => 200.00,
                'period' => '2026-08',
            ]
        );

        $job->handle(app(AfroMessageService::class), app(TelegramService::class));

        $this->assertDatabaseHas('notification_events', [
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'type' => NotificationType::PaymentConfirmation->value,
            'channel' => NotificationChannel::Sms->value,
            'status' => NotificationStatus::Sent->value,
        ]);

        $event = NotificationEvent::latest()->first();
        $this->assertStringContainsString('አበበ ተሰማ', $event->message_content);
        $this->assertStringContainsString('200.00', $event->message_content);
        $this->assertStringContainsString('2026-08', $event->message_content);
    }
}

