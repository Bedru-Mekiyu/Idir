<?php

namespace App\Models;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Idir extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'membership_basis',
        'region',
        'sub_city',
        'woreda',
        'locale',
        'status', // 'active', 'pending_approval', 'suspended', 'rejected'
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function founder(): ?Member
    {
        return $this->members()->where('committee_role', CommitteeRole::Chair)->first();
    }

    public function settings(): HasOne
    {
        return $this->hasOne(IdirSetting::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(Member::class)->where('status', MemberStatus::Active);
    }

    public function committeeMembers(): HasMany
    {
        return $this->hasMany(Member::class)->whereNotNull('committee_role');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function contributionRules(): HasMany
    {
        return $this->hasMany(ContributionRule::class);
    }

    public function payoutTriggerTypes(): HasMany
    {
        return $this->hasMany(PayoutTriggerType::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function notificationEvents(): HasMany
    {
        return $this->hasMany(NotificationEvent::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }
}
