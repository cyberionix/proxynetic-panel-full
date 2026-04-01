<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->enum('type',['CREDIT_CARD','TRANSFER','BALANCE']);
            $table->enum('status',['NEW','WAITING_APPROVAL','3DS_REDIRECTED','COMPLETED','FAILED','CANCELLED'])->default('NEW');
            $table->string('reference_code')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('uuid_value')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('remote_order_number')->nullable();
            $table->string('channel')->nullable();
            $table->json('extra_params')->nullable();
            $table->foreignId('basket_id')->nullable();
            $table->foreignId('invoice_id')->nullable();
            $table->foreignId('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
