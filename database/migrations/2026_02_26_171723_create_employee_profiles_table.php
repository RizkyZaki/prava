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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('national_id_number', 32)->nullable(); // nik
            $table->date('birth_date')->nullable(); // tanggal_lahir
            $table->date('hire_date')->nullable(); // tanggal_masuk
            $table->string('bjb_bank_account_number', 64)->nullable(); // rekening_bjb
            $table->string('tax_identification_number', 32)->nullable(); // npwp
            $table->string('position_title', 64)->nullable(); // jabatan
            $table->string('address', 255)->nullable();
            $table->string('phone_number', 32)->nullable(); // no_hp
            $table->string('personal_email', 128)->nullable(); // email_pribadi
            $table->string('marital_status', 32)->nullable(); // status_pernikahan
            $table->string('last_education', 64)->nullable(); // pendidikan_terakhir
            $table->string('profile_photo')->nullable(); // foto_profil
            $table->string('id_card_attachment')->nullable(); // attachment_ktp
            $table->string('tax_attachment')->nullable(); // attachment_npwp
            $table->string('employment_contract_attachment')->nullable(); // attachment_kontrak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
