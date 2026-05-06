<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->boolean('skip_if_admin_replied')->default(false)->after('delay_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->dropColumn('skip_if_admin_replied');
        });
    }
};
