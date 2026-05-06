<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoice_items MODIFY COLUMN type ENUM('NEW','RENEW','UPGRADE','BALANCE','ADDITIONAL_QUOTA','ADDITIONAL_QUOTA_DURATION','TP_EXTRA_DURATION','TP_SERVICE_ACTION','PPROXY_ADDITIONAL_QUOTA') DEFAULT 'NEW'");
        } elseif ($driver === 'sqlite') {
            $columns = '"deleted_at" datetime, "created_at" datetime, "updated_at" datetime, "id" integer primary key autoincrement not null, "type" varchar check ("type" in (\'NEW\',\'RENEW\',\'UPGRADE\',\'BALANCE\',\'ADDITIONAL_QUOTA\',\'ADDITIONAL_QUOTA_DURATION\',\'TP_EXTRA_DURATION\',\'TP_SERVICE_ACTION\',\'PPROXY_ADDITIONAL_QUOTA\')) not null default \'NEW\', "name" varchar not null, "total_price" numeric not null default \'0\', "vat_percent" integer not null default \'0\', "total_price_with_vat" numeric not null default \'0\', "additional_services" text, "product_id" integer, "price_id" integer, "order_id" integer, "order_detail_id" integer, "invoice_id" integer not null';
            DB::statement('CREATE TABLE "invoice_items_new" (' . $columns . ')');
            DB::statement('INSERT INTO invoice_items_new SELECT * FROM invoice_items');
            DB::statement('DROP TABLE invoice_items');
            DB::statement('ALTER TABLE invoice_items_new RENAME TO invoice_items');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE invoice_items MODIFY COLUMN type ENUM('NEW','RENEW','UPGRADE','BALANCE','ADDITIONAL_QUOTA','ADDITIONAL_QUOTA_DURATION','TP_EXTRA_DURATION','TP_SERVICE_ACTION') DEFAULT 'NEW'");
        }
    }
};
