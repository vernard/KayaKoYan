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
     * Get the user's avatar URL.
     * Uses UI Avatars service for placeholder avatars.
     */
    public function getAvatarUrlAttribute(): string
    {
        // If worker has a custom avatar, use it
        if ($this->isWorker() && $this->workerProfile?->avatar_path) {
            return asset('storage/' . $this->workerProfile->avatar_path);
        }

        // Generate avatar using UI Avatars service
        $name = urlencode($this->name);
        $background = match($this->role) {
            UserRole::Admin => 'dc2626',   // red
            UserRole::Worker => 'd97706',  // amber
            UserRole::Customer => '2563eb', // blue
            default => '6b7280',           // gray
        };

        return "https://ui-avatars.com/api/?name={$name}&background={$background}&color=fff&size=200&bold=true";
    }
}
