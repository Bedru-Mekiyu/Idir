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
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'password',
        'profile_photo_path',
        'is_platform_owner',
        'can_create_idir',
    ];

    protected $hidden = [
        'password',
        'profile_photo_path',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_owner' => 'boolean',
            'can_create_idir' => 'boolean',
        ];
    }

    public function isPhoneVerified(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function canCreateIdir(): bool
    {
        if ($this->is_platform_owner) {
            return true;
        }

        return (bool) $this->can_create_idir;
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(AccessRequest::class);
    }

    public function latestAccessRequest(): HasOne
    {
        return $this->hasOne(AccessRequest::class)->latestOfMany();
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

    /**
     * For phone-based login in committee panels. Filament's default provider
     * retrieves by email only, which leaves signup-only (phone-only) users
     * without any committee login path.
     */
    public static function findByLoginIdentifier(string $identifier): ?static
    {
        $normalized = $identifier;
        if (str_starts_with($normalized, '09') || str_starts_with($normalized, '07')) {
            $normalized = '09'.substr($normalized, 1);
        }
        if (str_starts_with($normalized, '+2519')) {
            $normalized = '09'.substr($normalized, 4);
        }
        if (str_starts_with($normalized, '+2517')) {
            $normalized = '07'.substr($normalized, 4);
        }

        return static::where('email', $identifier)
            ->orWhere('phone', $normalized)
            ->first();
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->idirs;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->idirs()->whereKey($tenant->getKey())->exists();
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return Storage::url($this->profile_photo_path);
        }
        
        // Return a default SVG placeholder string directly if no photo is set
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" /></svg>');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

}

