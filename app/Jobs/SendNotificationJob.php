<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
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
use Illuminate\Support\Facades\Log;

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
        $idir = $member ? $member->idir : \App\Models\Idir::find($this->idirId);

        if (!$idir) return;

        $pref = NotificationPreference::where('idir_id', $this->idirId)
            ->where('event_type', $this->type->value)
            ->first();

        // Default templates if no custom preference exists
        $template = $pref?->template_am ?? "ውድ :member_name፣ ከ :idir_name ማሳወቂያ ተልኳል።";

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
        }

        // 2. Send Telegram if enabled
        if (($pref?->telegram_enabled ?? false) && $member?->telegram_chat_id) {
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
        }
    }
}
