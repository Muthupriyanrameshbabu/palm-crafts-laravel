<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $bags = Category::create(['name' => 'Bags', 'slug' => 'bags', 'sort_order' => 1]);
        $baskets = Category::create(['name' => 'Baskets', 'slug' => 'baskets', 'sort_order' => 2]);
        $boxes = Category::create(['name' => 'Boxes', 'slug' => 'boxes', 'sort_order' => 3]);
        $decor = Category::create(['name' => 'Decor', 'slug' => 'decor', 'sort_order' => 4]);

        $tote = Product::create([
            'category_id' => $bags->id,
            'name' => 'The Pattamadai Tote',
            'slug' => 'pattamadai-tote',
            'origin' => 'Pattamadai village, Tamil Nadu',
            'material' => 'Dry palm-leaf, natural leather handles, brass pivots',
            'dimensions' => '14" W x 11" H x 5" D',
            'craft_time' => '3 days of active hand-weaving',
            'description' => 'A premium double-woven bag constructed with wild harvested palm fronds. Accented with brass hardware and high-grade, naturally tanned leather. Ideal for everyday use.',
            'price_in_paise' => 1540000, // ₹15,400
            'stock_quantity' => 12,
            'is_active' => true,
            'is_featured' => true,
        ]);

        ProductVariant::create([
            'product_id' => $tote->id,
            'sku' => 'PC-TOTE-NAT',
            'name' => 'Natural Tan',
            'stock_quantity' => 7,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $tote->id,
            'sku' => 'PC-TOTE-ESP',
            'name' => 'Espresso Brown',
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $baskets->id,
            'name' => 'Chettinad Storage Basket',
            'slug' => 'chettinad-storage-basket',
            'origin' => 'Chettinad region, Tamil Nadu',
            'material' => 'Sundried palm-leaf strips, natural dye',
            'dimensions' => '12" diameter x 10" H',
            'craft_time' => '2 days of active hand-weaving',
            'description' => 'A sturdy, diagonal-rib woven basket in the traditional Chettinad style, ideal for linens, produce, or decorative storage.',
            'price_in_paise' => 890000, // ₹8,900
            'stock_quantity' => 18,
            'is_active' => true,
            'is_featured' => true,
        ]);

        Product::create([
            'category_id' => $boxes->id,
            'name' => 'Artisan Trinket Box',
            'slug' => 'artisan-trinket-box',
            'origin' => 'Pattamadai village, Tamil Nadu',
            'material' => 'Fine-strip palm-leaf, brass clasp',
            'dimensions' => '6" W x 4" H x 4" D',
            'craft_time' => '1 day of active hand-weaving',
            'description' => 'A compact keepsake box with a hinged brass clasp, woven in the ultra-fine Pattamadai mat-weaving style traditionally reserved for royal courts.',
            'price_in_paise' => 420000, // ₹4,200
            'stock_quantity' => 25,
            'is_active' => true,
            'is_featured' => true,
        ]);

        Product::create([
            'category_id' => $decor->id,
            'name' => 'Etched Brass Kolam Plate',
            'slug' => 'etched-brass-kolam-plate',
            'origin' => 'Thanjavur, Tamil Nadu',
            'material' => 'Hand-etched brass',
            'dimensions' => '10" diameter',
            'craft_time' => '4 days of etching and polishing',
            'description' => 'A wall or table display plate featuring a traditional Kolam geometric pattern, hand-etched by Thanjavur metalsmiths.',
            'price_in_paise' => 620000, // ₹6,200
            'stock_quantity' => 9,
            'is_active' => true,
            'is_featured' => true,
        ]);
    }
}
