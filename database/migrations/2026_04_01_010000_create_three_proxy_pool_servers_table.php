<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('three_proxy_pool_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('three_proxy_pools')->cascadeOnDelete();
            $table->string('server_ip');
            $table->integer('port')->default(7000);
            $table->string('auth_username');
            $table->string('auth_password');
            $table->longText('ip_list')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_proxy_pool_servers');
    }
};
