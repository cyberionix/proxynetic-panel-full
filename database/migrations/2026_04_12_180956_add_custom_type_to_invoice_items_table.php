<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoice_items MODIFY COLUMN type ENUM('NEW','RENEW','UPGRADE','BALANCE','ADDITIONAL_QUOTA','ADDITIONAL_QUOTA_DURATION','TP_EXTRA_DURATION','TP_SERVICE_ACTION','PPROXY_ADDITIONAL_QUOTA','CUSTOM') DEFAULT 'NEW'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoice_items MODIFY COLUMN type ENUM('NEW','RENEW','UPGRADE','BALANCE','ADDITIONAL_QUOTA','ADDITIONAL_QUOTA_DURATION','TP_EXTRA_DURATION','TP_SERVICE_ACTION','PPROXY_ADDITIONAL_QUOTA') DEFAULT 'NEW'");
    }
};
