<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'company_id', 'location_id', 'role', 'alternative_email', 'is_active', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_BUSINESS_ADMIN = 'business_admin';
    public const ROLE_MODERATOR = 'moderator';

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isBusinessAdmin(): bool
    {
        return $this->role === self::ROLE_BUSINESS_ADMIN;
    }

    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    /**
     * Moderators (cashier/delivery staff) are POS-only — everyone else can
     * use the inventory back office (Business Admins are further restricted
     * from Users management at the route level, not here).
     */
    public function canAccessInventoryApp(): bool
    {
        return ! $this->isModerator();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    /**
     * The location this user is locked to selling from in POS. Only set for
     * moderators — once assigned (by the moderator's own first pick, or by
     * an admin), a moderator can no longer change it themselves. See POS's
     * LocationSessionController and TeamController.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Every company this user has been granted access to. `company_id`
     * (via company()) is the single "currently active" one — most users
     * have exactly one membership here matching it, but an account can be
     * granted more than one company from the Users page.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id', 'id', 'company_id');
    }

    /**
     * Send account-recovery notifications (password reset codes) to the
     * alternative email when one is set, falling back to the primary login
     * email otherwise.
     */
    public function routeNotificationForMail($notification = null): string
    {
        return $this->alternative_email ?: $this->email;
    }
}
