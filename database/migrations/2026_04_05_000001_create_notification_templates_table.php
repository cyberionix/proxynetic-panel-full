<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('category', 50);
            $table->string('title');
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('mail_enabled')->default(true);
            $table->text('sms_content')->nullable();
            $table->string('mail_subject')->nullable();
            $table->longText('mail_content')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
