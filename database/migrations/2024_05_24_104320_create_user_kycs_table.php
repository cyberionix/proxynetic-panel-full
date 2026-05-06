<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_kycs', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->foreignId('user_id');
            $table->enum('status', ["WAITING_FOR_DOCS", "WAITING_FOR_CONFIRM", "NOT_CONFIRMED", "CONFIRMED"])->nullable();
            $table->string('card_front_side')->nullable();
            $table->string('card_back_side')->nullable();
            $table->string('selfie')->nullable();
            $table->timestamp('verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_kycs');
    }
};
