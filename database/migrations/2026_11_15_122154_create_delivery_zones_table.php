<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 100);
            $table->string('name_bn', 100);
            $table->decimal('charge', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE delivery_zones ADD CONSTRAINT delivery_zones_charge_check CHECK (charge >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
