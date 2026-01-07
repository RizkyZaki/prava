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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_schedule_id')->nullable()->constrained()->onDelete('set null')->comment('Work schedule for this user');
            $table->decimal('base_salary', 15, 2)->comment('Base monthly salary (gaji pokok)');
            $table->decimal('transport_allowance', 15, 2)->default(0)->comment('Transport allowance (uang transport)');
            $table->decimal('meal_allowance', 15, 2)->default(0)->comment('Meal allowance (uang makan)');
            $table->decimal('position_allowance', 15, 2)->default(0)->comment('Position allowance (tunjangan jabatan)');
            $table->decimal('other_allowance', 15, 2)->default(0)->comment('Other allowances (tunjangan lain)');
            $table->decimal('total_allowances', 15, 2)->default(0)->comment('Total allowances');
            $table->decimal('gross_salary', 15, 2)->comment('Gross salary (total before deductions)');
            $table->boolean('enable_late_deduction')->default(true)->comment('Enable deduction for late arrival');
            $table->boolean('enable_early_leave_deduction')->default(true)->comment('Enable deduction for early leave');
            $table->boolean('enable_absent_deduction')->default(true)->comment('Enable deduction for absent');
            $table->date('effective_from')->comment('Effective start date');
            $table->date('effective_to')->nullable()->comment('Effective end date (null = ongoing)');
            $table->boolean('is_active')->default(true)->comment('Is this salary configuration active?');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index('effective_from');
            $table->unique(['user_id', 'effective_from'], 'unique_user_salary_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
