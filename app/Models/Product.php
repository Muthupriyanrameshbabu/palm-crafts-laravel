<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'origin', 'material', 'dimensions',
        'craft_time', 'description', 'price_in_paise', 'stock_quantity',
        'is_active', 'is_featured', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price_in_paise' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true);
    }

    /**
     * Rupee-formatted price for display, derived from the integer paise value.
     * Never store or calculate money as float — this accessor is display-only.
     */
    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '₹' . number_format($this->price_in_paise / 100, 2),
        );
    }

    public function inStock(): bool
    {
        if ($this->variants()->exists()) {
            return $this->variants()->where('is_active', true)->where('stock_quantity', '>', 0)->exists();
        }

        return $this->stock_quantity > 0;
    }
}
