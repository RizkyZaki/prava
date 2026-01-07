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
        Schema::create('monthly_payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('salary_id')->constrained()->onDelete('cascade')->comment('Salary configuration used');
            $table->integer('year')->comment('Payroll year');
            $table->integer('month')->comment('Payroll month (1-12)');

            // Salary components (snapshot from master salary)
            $table->decimal('base_salary', 15, 2)->comment('Base salary from master');
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('meal_allowance', 15, 2)->default(0);
            $table->decimal('position_allowance', 15, 2)->default(0);
            $table->decimal('other_allowance', 15, 2)->default(0);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->comment('Gross salary (base + allowances)');

            // Attendance summary
            $table->integer('total_days_present')->default(0)->comment('Total days present');
            $table->integer('total_days_late')->default(0)->comment('Total days late');
            $table->integer('total_days_absent')->default(0)->comment('Total days absent');
            $table->integer('total_work_minutes')->default(0)->comment('Total work minutes');

            // Deductions
            $table->decimal('total_deductions', 15, 2)->default(0)->comment('Total salary deductions');
            $table->decimal('late_deductions', 15, 2)->default(0)->comment('Deductions for late');
            $table->decimal('early_leave_deductions', 15, 2)->default(0)->comment('Deductions for early leave');
            $table->decimal('absent_deductions', 15, 2)->default(0)->comment('Deductions for absent');
            $table->decimal('other_deductions', 15, 2)->default(0)->comment('Other deductions');

            // Net salary
            $table->decimal('net_salary', 15, 2)->comment('Net salary (gross - deductions)');

            // Status
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid'])->default('draft');
            $table->date('payment_date')->nullable()->comment('Actual payment date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'year', 'month']);
            $table->index('status');
            $table->unique(['user_id', 'year', 'month'], 'unique_user_payroll_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_payrolls');
    }
};
