<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_pending_auto_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id');
            $table->unsignedBigInteger('support_id');
            $table->timestamp('send_at');
            $table->boolean('sent')->default(false);
            $table->timestamps();

            $table->index(['sent', 'send_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_pending_auto_replies');
    }
};
