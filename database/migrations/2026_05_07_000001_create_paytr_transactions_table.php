<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('paytr_transactions')) {
            return;
        }

        Schema::create('paytr_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference_uuid')->unique();
            $table->foreignId('checkout_id')->nullable()->index();
            $table->foreignId('invoice_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('merchant_oid', 80)->nullable()->index();
            $table->string('type', 40)->default('iframe_token_request');
            $table->string('status', 40)->default('pending');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 8)->default('TL');
            $table->boolean('test_mode')->default(true)->index();
            $table->string('paytr_status', 20)->nullable();
            $table->decimal('paytr_total_amount', 12, 2)->nullable();
            $table->string('paytr_payment_type', 20)->nullable();
            $table->unsignedTinyInteger('paytr_installment')->nullable();
            $table->string('paytr_failed_reason_code', 20)->nullable();
            $table->text('paytr_failed_reason_msg')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paytr_transactions');
    }
};
