<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'password',
        'is_platform_owner',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_owner' => 'boolean',
        ];
    }

    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function idirs(): BelongsToMany
    {
        return $this->belongsToMany(Idir::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return (bool) $this->is_platform_owner;
        }

        return true;
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->idirs;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->idirs()->whereKey($tenant->getKey())->exists();
    }
}
