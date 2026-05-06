<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pproxy_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_base_url')->default('https://dashboard.plainproxies.com');
            $table->string('api_key')->nullable();
            $table->string('server_domain')->default('tr.saglamproxy.com');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pproxy_settings');
    }
};
