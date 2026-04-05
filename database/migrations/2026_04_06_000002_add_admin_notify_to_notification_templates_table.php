<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->boolean('admin_sms_enabled')->default(false)->after('mail_enabled');
            $table->boolean('admin_mail_enabled')->default(false)->after('admin_sms_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn(['admin_sms_enabled', 'admin_mail_enabled']);
        });
    }
};
