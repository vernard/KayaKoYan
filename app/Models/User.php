<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        if ($panel->getId() === 'worker') {
            return $this->isWorker();
        }

        return false;
    }

    public function workerProfile(): HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function workerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'worker_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isWorker(): bool
    {
        return $this->role === UserRole::Worker;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function scopeWorkers($query)
    {
        return $query->where('role', UserRole::Worker);
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', UserRole::Customer);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::Admin);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Get the user's avatar URL (600px version).
     * Uses UI Avatars service for placeholder avatars.
     */
    public function getAvatarUrlAttribute(): string
    {
        // Check user's own avatar first
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }

        // If worker has a custom avatar in their profile, use it
        if ($this->isWorker() && $this->workerProfile?->avatar_path) {
            return asset('storage/' . $this->workerProfile->avatar_path);
        }

        // Generate avatar using UI Avatars service with full initials
        return $this->generatePlaceholderAvatar(600);
    }

    /**
     * Get the user's small avatar URL (200px version for chat heads).
     * Uses UI Avatars service for placeholder avatars.
     */
    public function getAvatarUrlSmallAttribute(): string
    {
        // Check user's own avatar first
        if ($this->avatar_path) {
            // Derive small path from main path
            $pathInfo = pathinfo($this->avatar_path);
            $smallPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_200.' . ($pathInfo['extension'] ?? 'jpg');
            return asset('storage/' . $smallPath);
        }

        // If worker has a custom avatar in their profile, use it
        if ($this->isWorker() && $this->workerProfile?->avatar_path) {
            $pathInfo = pathinfo($this->workerProfile->avatar_path);
            $smallPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_200.' . ($pathInfo['extension'] ?? 'jpg');
            return asset('storage/' . $smallPath);
        }

        // Generate avatar using UI Avatars service with full initials
        return $this->generatePlaceholderAvatar(200);
    }

    /**
     * Get the user's initials (e.g., "JD" for "John Doe").
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            }
        }

        // Limit to 2 characters
        return mb_substr($initials, 0, 2);
    }

    /**
     * Generate a placeholder avatar URL using UI Avatars service.
     */
    protected function generatePlaceholderAvatar(int $size = 200): string
    {
        $initials = urlencode($this->initials);
        $background = match($this->role) {
            UserRole::Admin => 'dc2626',   // red
            UserRole::Worker => 'd97706',  // amber
            UserRole::Customer => '2563eb', // blue
            default => '6b7280',           // gray
        };

        return "https://ui-avatars.com/api/?name={$initials}&background={$background}&color=fff&size={$size}&bold=true";
    }
}
