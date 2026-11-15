<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per payment attempt (customer may cancel and retry)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['bkash', 'cod'])->default('bkash');
            $table->string('bkash_payment_id', 100)->nullable()->unique();  // paymentID from create
            $table->string('trx_id', 50)->nullable()->unique();             // trxID from execute
            $table->string('payer_account', 20)->nullable();                // customerMsisdn (masked)
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['initiated', 'completed', 'failed', 'cancelled', 'refunded'])->default('initiated');
            $table->json('raw_response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
