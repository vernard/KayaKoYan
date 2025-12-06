<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'bio',
        'phone',
        'location',
        'avatar_path',
        'gcash_number',
        'gcash_name',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WorkerProfile $profile) {
            if (empty($profile->slug)) {
                $profile->slug = static::generateUniqueSlug($profile->user->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return route('worker.profile', $this->slug);
    }

    public function hasGcash(): bool
    {
        return !empty($this->gcash_number) && !empty($this->gcash_name);
    }

    public function hasBank(): bool
    {
        return !empty($this->bank_name) && !empty($this->bank_account_number) && !empty($this->bank_account_name);
    }

    public function hasPaymentMethods(): bool
    {
        return $this->hasGcash() || $this->hasBank();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
