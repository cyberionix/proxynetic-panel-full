<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up():void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('vat_percent')->default(0);
            $table->text('properties')->nullable();
            $table->json('attrs')->nullable();
            $table->boolean('is_active')->default(1);
            $table->longText('delivery_items')->default("[]");
            $table->string('delivery_type')->default('STACK');
            $table->integer('delivery_count')->default(1);
            $table->string('isp_image')->nullable();
            $table->string('parasut_id')->nullable();
            $table->foreignId('category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down():void
    {
        Schema::dropIfExists('products');
    }
};
