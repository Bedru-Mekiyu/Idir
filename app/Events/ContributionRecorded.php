<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContributionRecorded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Contribution $contribution) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('idir.'.$this->contribution->idir_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->contribution->id,
            'member_name' => $this->contribution->member->full_name,
            'amount' => (float) $this->contribution->amount,
            'period' => $this->contribution->period_covered,
            'method' => $this->contribution->method->value,
            'is_correction' => $this->contribution->is_correction,
            'created_at' => $this->contribution->created_at->toIso8601String(),
        ];
    }
}
