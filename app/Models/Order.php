<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'email', 'phone', 'shipping_address_id',
        'shipping_address_snapshot', 'subtotal_in_paise', 'shipping_in_paise',
        'tax_in_paise', 'total_in_paise', 'status', 'payment_gateway',
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'paid_at', 'notes',
    ];

    protected $casts = [
        'shipping_address_snapshot' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function markAsPaid(string $razorpayPaymentId, string $razorpaySignature): void
    {
        $this->update([
            'status' => 'paid',
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature,
            'paid_at' => now(),
        ]);
    }
}
