<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->string('media_type')->nullable()->after('body'); // image, video, audio, document
            $table->string('media_url')->nullable()->after('media_type');
            $table->string('media_mime')->nullable()->after('media_url');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'media_url', 'media_mime']);
        });
    }
};
