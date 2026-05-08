<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn("products","is_auto_generated")) {
            Schema::table("products", function (Blueprint $table) {
                $table->boolean("is_auto_generated")->default(false)->after("is_link_only")->index();
                $table->json("auto_meta")->nullable()->after("is_auto_generated");
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn("products","is_auto_generated")) {
            Schema::table("products", function (Blueprint $table) {
                $table->dropColumn(["is_auto_generated","auto_meta"]);
            });
        }
    }
};
