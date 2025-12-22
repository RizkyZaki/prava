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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('fingerprint_id')->nullable()->comment('ID from fingerprint/face recognition device');
            $table->dateTime('check_in')->nullable()->comment('Check-in time (jam masuk)');
            $table->dateTime('check_out')->nullable()->comment('Check-out time (jam pulang)');
            $table->date('attendance_date')->comment('Date of attendance');
            $table->enum('status', ['present', 'late', 'half_day', 'absent', 'leave', 'holiday'])->default('present');
            $table->text('notes')->nullable()->comment('Additional notes or remarks');
            $table->integer('work_duration')->nullable()->comment('Work duration in minutes');
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'attendance_date']);
            $table->index('attendance_date');
            $table->index('status');

            // Unique constraint: one attendance record per user per day
            $table->unique(['user_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
