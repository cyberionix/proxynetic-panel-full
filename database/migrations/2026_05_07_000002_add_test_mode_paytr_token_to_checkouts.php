<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('checkouts', 'test_mode')) {
            Schema::table('checkouts', function (Blueprint $table) {
                $table->boolean('test_mode')->default(false)->after('channel');
            });
        }
        if (!Schema::hasColumn('checkouts', 'paytr_token')) {
            Schema::table('checkouts', function (Blueprint $table) {
                $table->string('paytr_token', 191)->nullable()->after('test_mode');
            });
        }
    }

    public function down(): void
    {
        Schema::table('checkouts', function (Blueprint $table) {
            if (Schema::hasColumn('checkouts', 'test_mode'))   $table->dropColumn('test_mode');
            if (Schema::hasColumn('checkouts', 'paytr_token')) $table->dropColumn('paytr_token');
        });
    }
};
