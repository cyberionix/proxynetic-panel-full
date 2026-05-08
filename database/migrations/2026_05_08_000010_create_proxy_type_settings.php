<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable("proxy_type_settings")) return;
        Schema::create("proxy_type_settings", function (Blueprint $table) {
            $table->id();
            $table->string("type_code", 32)->unique();
            $table->string("display_name");
            $table->unsignedInteger("max_quantity")->default(50);
            $table->string("quantity_unit", 16)->default("PROXY");
            $table->string("delivery_type", 32)->default("LOCALTONET");
            $table->json("delivery_items_template")->nullable();
            $table->unsignedBigInteger("category_id")->nullable();
            $table->json("default_attrs")->nullable();
            $table->text("default_properties")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index("type_code");
        });
    }
    public function down(): void { Schema::dropIfExists("proxy_type_settings"); }
};
