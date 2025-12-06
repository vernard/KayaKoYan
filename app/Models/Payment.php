<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'reference_number',
        'proof_path',
        'notes',
        'status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function isVerified(): bool
    {
        return $this->status === PaymentStatus::Verified;
    }

    public function isRejected(): bool
    {
        return $this->status === PaymentStatus::Rejected;
    }

    public function verify(): void
    {
        $this->update([
            'status' => PaymentStatus::Verified,
            'verified_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => PaymentStatus::Rejected,
        ]);
    }

    public function getProofUrlAttribute(): ?string
    {
        if (!$this->proof_path) {
            return null;
        }

        return asset('storage/' . $this->proof_path);
    }
}
