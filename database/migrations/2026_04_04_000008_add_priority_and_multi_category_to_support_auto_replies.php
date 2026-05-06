<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->boolean('is_priority')->default(false)->after('skip_if_admin_replied');
            $table->json('trigger_product_category_ids')->nullable()->after('trigger_department');
        });

        // Migrate existing single category to array
        $rows = DB::table('support_auto_replies')->whereNotNull('trigger_product_category_id')->get();
        foreach ($rows as $row) {
            DB::table('support_auto_replies')
                ->where('id', $row->id)
                ->update(['trigger_product_category_ids' => json_encode([(int) $row->trigger_product_category_id])]);
        }

        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->dropColumn('trigger_product_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->unsignedBigInteger('trigger_product_category_id')->nullable()->after('trigger_department');
        });

        Schema::table('support_auto_replies', function (Blueprint $table) {
            $table->dropColumn(['is_priority', 'trigger_product_category_ids']);
        });
    }
};
