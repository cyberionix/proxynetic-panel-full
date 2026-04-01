<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('three_proxy_pools', function (Blueprint $table) {
            $table->string('server_ip')->default('')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('three_proxy_pools', function (Blueprint $table) {
            $table->dropColumn('server_ip');
        });
    }
};
