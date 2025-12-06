<?php

namespace App\Models;

use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'price',
        'type',
        'digital_file_path',
        'digital_file_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'type' => ListingType::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            if (empty($listing->slug)) {
                $listing->slug = static::generateUniqueSlug($listing->title, $listing->user_id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, int $userId): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('user_id', $userId)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getPrimaryImageAttribute(): ?ListingImage
    {
        return $this->images->first();
    }

    public function isDigitalProduct(): bool
    {
        return $this->type === ListingType::DigitalProduct;
    }

    public function isService(): bool
    {
        return $this->type === ListingType::Service;
    }

    public function hasDigitalFile(): bool
    {
        return !empty($this->digital_file_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeServices($query)
    {
        return $query->where('type', ListingType::Service);
    }

    public function scopeDigitalProducts($query)
    {
        return $query->where('type', ListingType::DigitalProduct);
    }
}
