<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->boolean('is_audio_muted')->default(false);
            $table->boolean('is_video_off')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropColumn(['is_audio_muted', 'is_video_off']);
        });
    }
};
