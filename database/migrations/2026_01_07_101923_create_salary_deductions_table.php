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
        Schema::create('salary_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained()->onDelete('cascade')->comment('Related attendance record');
            $table->foreignId('salary_id')->constrained()->onDelete('cascade')->comment('Salary configuration used');
            $table->date('deduction_date')->comment('Date of deduction');
            $table->enum('deduction_type', ['late', 'early_leave', 'absent', 'no_check_in', 'no_check_out', 'short_hours', 'manual'])->comment('Type of deduction');
            $table->decimal('deduction_amount', 15, 2)->comment('Deduction amount in Rupiah');
            $table->integer('minutes_late')->default(0)->comment('Minutes late (if applicable)');
            $table->integer('minutes_early')->default(0)->comment('Minutes early leave (if applicable)');
            $table->integer('hours_short')->default(0)->comment('Hours short of required work time');
            $table->text('reason')->nullable()->comment('Deduction reason/description');
            $table->text('calculation_details')->nullable()->comment('JSON of calculation breakdown');
            $table->boolean('is_approved')->default(false)->comment('Is deduction approved?');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'deduction_date']);
            $table->index('deduction_date');
            $table->index('deduction_type');
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_deductions');
    }
};
