<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Guest checkout: customer details live on the order
            $table->string('customer_name', 120);
            $table->string('customer_phone', 20)->index();     // 01XXXXXXXXX (normalized)
            $table->string('customer_email')->nullable();
            $table->foreignId('delivery_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('district', 80);
            $table->text('address');
            $table->text('note')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('payment_method', ['bkash', 'cod'])->default('bkash');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid')->index();
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->char('locale', 2)->default('bn');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_check CHECK (total = subtotal + delivery_charge)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
