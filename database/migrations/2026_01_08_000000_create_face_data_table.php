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
        Schema::create('face_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('face_image')->comment('Path ke file wajah di storage');
            $table->json('face_embedding')->nullable()->comment('Face embedding untuk matching (optional, untuk future ML)');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Status face data');
            $table->timestamp('registered_at')->useCurrent()->comment('Waktu pendaftaran wajah');
            $table->timestamps();

            $table->unique('user_id')->comment('Satu user hanya bisa punya satu face data');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_data');
    }
};
