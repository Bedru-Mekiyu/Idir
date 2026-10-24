<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationType;
use App\Jobs\SendNotificationJob;
use App\Mail\PaymentConfirmationMail;
use App\Models\Idir;
use App\Models\Member;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\Sms\SmsGatewayManager;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_email_notification_if_enabled()
    {
        Mail::fake();

        $user = User::create(['name' => 'Test', 'email' => 'test@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        NotificationPreference::create([
            'idir_id' => $idir->id,
            'event_type' => NotificationType::PaymentConfirmation,
            'email_enabled' => true,
            'sms_enabled' => false,
            'telegram_enabled' => false,
            'in_app_enabled' => false,
            'template_am' => 'test',
        ]);

        $job = new SendNotificationJob($idir->id, $member->id, NotificationType::PaymentConfirmation, ['amount' => 100, 'period' => '2023-01']);
        $job->handle(app(SmsGatewayManager::class), app(TelegramService::class));

        Mail::assertQueued(PaymentConfirmationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}
