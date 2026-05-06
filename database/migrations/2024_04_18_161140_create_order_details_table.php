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
        Schema::create('order_details', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->foreignId('order_id')->nullable();
            $table->boolean("is_active")->default(0);
            $table->json("additional_services")->nullable();
            $table->json('price_data')->nullable();
            $table->foreignId('price_id')->nullable();
            $table->foreignId('checkout_id')->nullable();
            $table->boolean('is_hidden')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
