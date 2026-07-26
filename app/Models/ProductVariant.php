<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'name', 'price_override_in_paise', 'stock_quantity', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_override_in_paise' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceInPaise(): int
    {
        return $this->price_override_in_paise ?? $this->product->price_in_paise;
    }
}
