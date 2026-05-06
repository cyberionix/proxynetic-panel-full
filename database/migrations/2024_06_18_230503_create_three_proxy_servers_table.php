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
        Schema::create('three_proxy_servers', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->string('ip_address');
            $table->string('port')->default('80');
            $table->string('api_key')->nullable();
            $table->tinyInteger('is_active');
            $table->dateTime('last_checked_at')->nullable();
            $table->tinyInteger('is_live')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('three_proxy_servers');
    }
};
