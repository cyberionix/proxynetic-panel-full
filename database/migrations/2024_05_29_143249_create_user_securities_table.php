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
        Schema::create('user_securities', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->foreignId("user_id");
            $table->boolean('is_cant_vpn')->default(0);
            $table->boolean('is_limit_payment_methods')->default(0);
            $table->json('payment_methods')->nullable();
            $table->boolean('is_no_support')->default(0);
            $table->boolean('is_limited_support')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_securities');
    }
};
