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
        Schema::create('orders', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id()->startingValue(1000);
            $table->string('order_id')->nullable();
            $table->enum('delivery_status',['NOT_DELIVERED', 'BEING_DELIVERED', 'DELIVERED','QUEUED'])->default("BEING_DELIVERED");
            $table->string('user_notes')->nullable();
            $table->string('delivery_error')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status',['CANCELLED','PENDING','PASSIVE','ACTIVE','EXPIRED'])->default("PENDING");
            $table->longText('product_info')->default('[]');
            $table->json('product_data')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->foreignId('user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
