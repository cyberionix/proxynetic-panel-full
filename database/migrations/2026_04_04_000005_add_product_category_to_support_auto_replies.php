<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->unsignedBigInteger('trigger_product_category_id')->nullable()->after('trigger_department');
        });
    }

    public function down(): void
    {
        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->dropColumn('trigger_product_category_id');
        });
    }
};
