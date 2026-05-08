<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('baskets', 'session_id')) {
            Schema::table('baskets', function (Blueprint $table) {
                $table->string('session_id', 100)->nullable()->index()->after('user_id');
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn('baskets', 'session_id')) {
            Schema::table('baskets', function (Blueprint $table) {
                $table->dropColumn('session_id');
            });
        }
    }
};
