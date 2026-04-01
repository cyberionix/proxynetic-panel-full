<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->tinyInteger('duration')->nullable();
            $table->enum('duration_unit', ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY', 'ONE_TIME']);
            $table->decimal('price')->nullable();
            $table->json('upgradeable_price_ids')->nullable();
            $table->foreignId('currency_id');
            $table->foreignId('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
