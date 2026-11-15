<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name_en', 200);
            $table->string('name_bn', 200);
            $table->string('slug', 220)->unique();
            $table->string('sku', 50)->nullable()->unique();
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->string('packet_size_en', 50);              // e.g. "10 g", "50 seeds"
            $table->string('packet_size_bn', 50);              // e.g. "১০ গ্রাম", "৫০টি বীজ"
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedTinyInteger('germination_rate')->nullable();   // percent, 0-100
            $table->string('sowing_season_en', 100)->nullable();
            $table->string('sowing_season_bn', 100)->nullable();
            $table->unsignedSmallInteger('days_to_harvest')->nullable();
            $table->string('image')->nullable();               // main image path
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index(['is_featured', 'is_active']);
            $table->index('stock');
        });

        // Blueprint has no CHECK constraints (SQLite, used by tests, can't add them later)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_price_check CHECK (price >= 0)');
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_sale_price_check CHECK (sale_price IS NULL OR (sale_price >= 0 AND sale_price < price))');
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_germination_check CHECK (germination_rate IS NULL OR germination_rate <= 100)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
