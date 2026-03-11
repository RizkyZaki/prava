<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('sender_type', ['customer', 'admin', 'ai', 'system']);
            $table->foreignId('sender_id')->nullable()->comment('User ID if admin')->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->string('whatsapp_message_id', 100)->nullable()->index();
            $table->timestamps();

            $table->index('whatsapp_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
