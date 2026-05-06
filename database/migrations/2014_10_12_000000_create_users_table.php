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
        Schema::create('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id()->startingValue(1000);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('password');

            $table->string('image')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamp('not_tc_citizen_at')->nullable();
            $table->string('identity_number', 40)->nullable();
            $table->timestamp('identity_number_verified_at')->nullable();
            $table->boolean('is_force_kyc')->default(0);
            $table->decimal('balance')->default(0.00);

            $table->tinyInteger('accept_sms')->default(1);
            $table->tinyInteger('accept_email')->default(1);

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            $table->timestamp('last_seen_at')->nullable();

            $table->foreignId('user_group_id')->nullable();
            $table->foreignId('invoice_address_id')->nullable();
            $table->string('parasut_id')->nullable();
            $table->boolean('is_banned')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
