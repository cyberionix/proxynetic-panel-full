<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('three_proxy_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('pool_id')->nullable();
            $table->string('action', 50);
            $table->json('proxy_data')->nullable();
            $table->json('ip_list')->nullable();
            $table->unsignedInteger('proxy_count')->default(0);
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedBigInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'action']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_proxy_logs');
    }
};
