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
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->integer('enhancer')->comment('User/employee ID');
            $table->integer('company')->comment('Company/OPD ID');
            $table->integer('position')->comment('Position ID');
            $table->string('fingerprint', 30)->comment('Fingerprint ID from device');
            $table->dateTime('time')->comment('Check-in/Check-out time');
            $table->decimal('piece', 3, 2)->default(0);
            $table->bigInteger('price')->default(0);
            $table->integer('late')->default(0)->comment('Late minutes');
            $table->integer('earlier')->default(0)->comment('Early leave minutes');
            $table->enum('type', ['0', '1'])->comment('0=check-in, 1=check-out');
            $table->enum('category', ['0', '1'])->default('0');
            $table->string('coordinate', 255)->nullable()->comment('GPS coordinates');
            $table->string('biometric', 80)->nullable()->comment('Biometric data');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            // Indexes for performance
            $table->index('company');
            $table->index('enhancer');
            $table->index('position');
            $table->index('fingerprint');
            $table->index('time');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
