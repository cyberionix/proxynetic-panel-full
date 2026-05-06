<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('three_proxy_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('server_ip');
            $table->integer('port')->default(7000);
            $table->string('auth_username');
            $table->string('auth_password');
            $table->longText('ip_list')->nullable();
            $table->integer('http_port')->default(8888);
            $table->integer('socks_port')->default(9999);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_proxy_pools');
    }
};
