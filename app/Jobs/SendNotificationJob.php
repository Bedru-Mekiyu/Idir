<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\Idir;
use App\Models\Member;
use App\Models\NotificationEvent;
use App\Models\NotificationPreference;
use App\Services\AfroMessageService;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public function __construct(
        public int $idirId,
        public ?int $memberId,
        public NotificationType $type,
        public array $placeholders = []
    ) {}

    public function handle(AfroMessageService $sms, TelegramService $telegram): void
    {
        $member = $this->memberId ? Member::with('idir')->find($this->memberId) : null;
        $idir = $member ? $member->idir : Idir::find($this->idirId);

        if (! $idir) {
            return;
        }

        $pref = NotificationPreference::where('idir_id', $this->idirId)
            ->where('event_type', $this->type->value)
            ->first();

        // Use the tenant's custom template when set, otherwise a per-type default.
        $template = $pref?->template_am ?: $this->defaultTemplate();

        // Merge default placeholders
        $vars = array_merge([
            ':member_name' => $member?->full_name ?? 'አባል',
            ':idir_name' => $idir->name,
            ':amount' => number_format($this->placeholders['amount'] ?? 0, 2),
            ':period' => $this->placeholders['period'] ?? now()->format('Y-m'),
            ':trigger' => $this->placeholders['trigger'] ?? 'ጥያቄ',
            ':reason' => $this->placeholders['reason'] ?? '',
            ':message' => $this->placeholders['message'] ?? '',
        ], $this->placeholders);

        $messageContent = str_replace(array_keys($vars), array_values($vars), $template);

        $failures = [];

        // 1. Send SMS if enabled
        if (($pref?->sms_enabled ?? true) && $member?->phone) {
            $smsResult = $sms->sendSms($member->phone, $messageContent);
            $isSuccess = ($smsResult['acknowledge'] ?? '') === 'success';

            NotificationEvent::create([
                'idir_id' => $this->idirId,
                'member_id' => $this->memberId,
                'type' => $this->type,
                'channel' => NotificationChannel::Sms,
                'status' => $isSuccess ? NotificationStatus::Sent : NotificationStatus::Failed,
                'message_content' => $messageContent,
                'error_detail' => $isSuccess ? null : json_encode($smsResult),
                'sent_at' => now(),
            ]);

            // A gateway/transport failure with valid credentials is retryable;
            // a permanent misconfiguration ("configured" === false) is not.
            if (! $isSuccess && ($smsResult['configured'] ?? true) !== false) {
                $failures[] = 'sms: '.json_encode($smsResult);
            }
        }

        // 2. Send Telegram if enabled
        if (($pref?->telegram_enabled ?? true) && $member?->telegram_chat_id) {
            $tgResult = $telegram->sendMessage($member->telegram_chat_id, $messageContent);
            $isSuccess = ($tgResult['ok'] ?? false) === true;

            NotificationEvent::create([
                'idir_id' => $this->idirId,
                'member_id' => $this->memberId,
                'type' => $this->type,
                'channel' => NotificationChannel::Telegram,
                'status' => $isSuccess ? NotificationStatus::Sent : NotificationStatus::Failed,
                'message_content' => $messageContent,
                'error_detail' => $isSuccess ? null : json_encode($tgResult),
                'sent_at' => now(),
            ]);

            if (! $isSuccess && ($tgResult['configured'] ?? true) !== false) {
                $failures[] = 'telegram: '.json_encode($tgResult);
            }
        }

        // Throw so the queue retries per $tries/$backoff and, once exhausted,
        // records the job in failed_jobs (visible in Horizon). A permanent
        // misconfiguration is recorded as a failed event above but not retried.
        if ($failures !== []) {
            throw new \RuntimeException('Notification delivery failed and will be retried: '.implode(' | ', $failures));
        }
    }

    /**
     * Sensible default Amharic message templates per notification type, used
     * when the tenant has not configured a custom template for the event.
     */
    protected function defaultTemplate(): string
    {
        return match ($this->type) {
            NotificationType::DueReminder => 'ውድ :member_name፣ ለ:period ወር የ:idir_name መዋጮ :amount ብር ክፍያ ጊዜው ደርሷል። እባክዎ በጊዜው ይክፈሉ።',
            NotificationType::LateWarning => 'ውድ :member_name፣ ለ:period ወር የ:idir_name መዋጮ :amount ብር እስካሁን አልተከፈለም። እባክዎ በአስቸኳይ ይክፈሉ።',
            NotificationType::PaymentConfirmation => 'ውድ :member_name፣ ለ:period ወር የከፈሉት :amount ብር ተረጋግጧል። እናመሰግናለን። (:idir_name)',
            NotificationType::ClaimFiled => 'ውድ :member_name፣ በ:idir_name አዲስ የክፍያ ጥያቄ ቀርቧል። እባክዎ ገምግመው ውሳኔ ይስጡ።',
            NotificationType::ClaimApproved => 'ውድ :member_name፣ ያቀረቡት የክፍያ ጥያቄ በ:idir_name ጸድቋል። የተፈቀደ መጠን፦ :amount ብር።',
            NotificationType::ClaimRejected => 'ውድ :member_name፣ ያቀረቡት የክፍያ ጥያቄ በ:idir_name ተቀባይነት አላገኘም። ምክንያት፦ :reason',
            NotificationType::DisbursementMade => 'ውድ :member_name፣ ከ:idir_name :amount ብር ክፍያ ተፈጽሞልዎታል።',
            NotificationType::ExclusionWarning => 'ውድ :member_name፣ ከ:idir_name ያለብዎ የመዋጮ እዳ ስላልተከፈለ ማስጠንቀቂያ ተሰጥቷል። በጊዜው ካልከፈሉ ከአባልነት ሊሰናበቱ ይችላሉ።',
            NotificationType::GeneralAnnouncement => 'ከ:idir_name ማስታወቂያ፦ :message',
            NotificationType::NewMemberWelcome => 'እንኳን ወደ :idir_name በደህና መጡ፣ ውድ :member_name! አባልነትዎ በተሳካ ሁኔታ ተመዝግቧል።',
            NotificationType::IdirApproved => 'እንኳን ደስ አለዎት ውድ :member_name! የ:idir_name እድር ምዝገባ ጸድቋል። አሁን አገልግሎት መጀመር ይችላሉ።',
            NotificationType::IdirRejected => 'ውድ :member_name፣ የ:idir_name እድር ምዝገባ ጥያቄ ተቀባይነት አላገኘም። ምክንያት፦ :reason',
        };
    }
}
