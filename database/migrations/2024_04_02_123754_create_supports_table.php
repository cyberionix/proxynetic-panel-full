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
        Schema::create('supports', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id()->startingValue(1000);
            $table->string("subject");
            $table->string("department");
            $table->enum("priority", ["LOW", "MEDIUM", "HIGH"]);
            $table->enum("status", ["WAITING_FOR_AN_ANSWER", "ANSWERED", "RESOLVED"])->default("WAITING_FOR_AN_ANSWER");
            $table->boolean("is_locked")->default(0);
            $table->foreignId("user_id");
            $table->foreignId("order_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
};
