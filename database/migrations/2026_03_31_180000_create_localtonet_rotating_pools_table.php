<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('localtonet_rotating_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['quota', 'unlimited'])->default('quota');
            $table->json('tunnel_ids')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localtonet_rotating_pools');
    }
};
