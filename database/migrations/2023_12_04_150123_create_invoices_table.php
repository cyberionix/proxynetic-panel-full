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
        Schema::create('invoices', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ["PENDING","PAID","CANCELLED"]);
            $table->decimal('total_price')->nullable();
            $table->decimal('total_vat')->nullable();
            $table->decimal('total_price_with_vat')->nullable();
            $table->text('notes')->nullable();
            $table->text('invoice_address')->nullable();

            $table->foreignId("user_id");
            $table->string('parasut_id')->nullable();
            $table->longText('e_document_info')->default('[]');
            $table->string('invoice_pdf')->nullable();
            $table->dateTime('formalized_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
