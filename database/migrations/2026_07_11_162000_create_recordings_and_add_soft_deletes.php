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
        // Add soft deletes to meetings and meeting_participants
        Schema::table('meetings', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Create recordings table
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->unsignedBigInteger('user_id'); // User who recorded/uploaded it
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });

        // Create recording_participants table
        Schema::create('recording_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recording_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('recording_id')->references('id')->on('recordings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Create recording_download_requests table
        Schema::create('recording_download_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recording_id');
            $table->unsignedBigInteger('user_id'); // Participant requesting download
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->foreign('recording_id')->references('id')->on('recordings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_download_requests');
        Schema::dropIfExists('recording_participants');
        Schema::dropIfExists('recordings');

        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
