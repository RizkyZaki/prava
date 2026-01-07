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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Schedule name (e.g., "Shift Pagi", "Default")');
            $table->time('start_time')->default('08:00:00')->comment('Work start time (jam masuk)');
            $table->time('end_time')->default('16:00:00')->comment('Work end time (jam pulang)');
            $table->time('break_start')->nullable()->comment('Break start time (jam istirahat mulai)');
            $table->time('break_end')->nullable()->comment('Break end time (jam istirahat selesai)');
            $table->integer('late_tolerance_minutes')->default(15)->comment('Late tolerance in minutes');
            $table->integer('early_leave_tolerance_minutes')->default(15)->comment('Early leave tolerance in minutes');
            $table->decimal('daily_work_hours', 5, 2)->default(8.00)->comment('Expected daily work hours');
            $table->decimal('hourly_deduction_rate', 10, 2)->default(0)->comment('Deduction per hour late/early (% of daily salary)');
            $table->boolean('is_default')->default(false)->comment('Is this the default schedule?');
            $table->boolean('is_active')->default(true)->comment('Is this schedule active?');
            $table->text('description')->nullable()->comment('Schedule description');
            $table->timestamps();

            // Index for default schedule lookup
            $table->index('is_default');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
