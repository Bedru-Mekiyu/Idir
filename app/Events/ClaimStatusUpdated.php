<?php

namespace App\Events;

use App\Models\Claim;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaimStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Claim $claim) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('idir.'.$this->claim->idir_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->claim->id,
            'member_name' => $this->claim->member->full_name,
            'trigger' => $this->claim->triggerType->label_am ?? 'ጥያቄ',
            'status' => $this->claim->status->value,
            'requested_amount' => (float) $this->claim->requested_amount,
            'updated_at' => $this->claim->updated_at->toIso8601String(),
        ];
    }
}
