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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->string('receipt');
            $table->string('subject');
            $table->longText('body');
            $table->string('service')->nullable();
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->enum('status',['PENDING','SUCCESS','ERROR'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_activity_logs');
    }
};
