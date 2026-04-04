<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pproxyu_pool', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->unsignedInteger('port');
            $table->string('username');
            $table->string('password');
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pproxyu_pool');
    }
};
