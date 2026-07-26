<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // human-friendly, e.g. PC-20260718-0001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('phone', 20);

            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->json('shipping_address_snapshot'); // frozen copy in case address is later edited/deleted

            $table->unsignedBigInteger('subtotal_in_paise');
            $table->unsignedBigInteger('shipping_in_paise')->default(0);
            $table->unsignedBigInteger('tax_in_paise')->default(0);
            $table->unsignedBigInteger('total_in_paise');

            $table->enum('status', [
                'pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded',
            ])->default('pending_payment');

            $table->string('payment_gateway')->default('razorpay');
            $table->string('razorpay_order_id')->nullable()->index();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_signature')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name'); // snapshot — survives product deletion/renaming
            $table->string('variant_name')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_in_paise');
            $table->unsignedBigInteger('line_total_in_paise');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
