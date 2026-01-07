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
        Schema::create('permitted_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('absence_type', ['izin', 'sakit', 'remote'])->comment('Tipe ketidakhadiran');
            $table->date('start_date')->comment('Tanggal mulai');
            $table->date('end_date')->comment('Tanggal selesai');
            $table->text('reason')->comment('Alasan');
            $table->string('attachment')->nullable()->comment('Bukti (surat/foto) untuk izin & sakit');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Status approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->comment('User yang approve/reject');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval/rejection');
            $table->text('rejection_reason')->nullable()->comment('Alasan penolakan');
            $table->timestamps();

            $table->index(['user_id', 'start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permitted_absences');
    }
};
