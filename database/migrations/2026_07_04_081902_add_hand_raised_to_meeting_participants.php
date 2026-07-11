<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->boolean('hand_raised')->default(false)->after('is_video_off');
        });
    }

    public function down()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropColumn('hand_raised');
        });
    }
};
