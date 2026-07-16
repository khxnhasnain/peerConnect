<?php

use App\Models\User;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\Recording;
use App\Models\RecordingParticipant;
use App\Models\RecordingDownloadRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->host = User::factory()->create();
    $this->participant = User::factory()->create();
    $this->outsider = User::factory()->create();

    $this->meeting = Meeting::create([
        'room_id' => 'ABCD12',
        'created_by' => $this->host->id,
        'meeting_name' => 'Test Meeting',
    ]);

    // Add host as participant
    MeetingParticipant::create([
        'meeting_id' => $this->meeting->id,
        'user_id' => $this->host->id,
        'peer_id' => 'peer-host',
    ]);

    // Add participant
    MeetingParticipant::create([
        'meeting_id' => $this->meeting->id,
        'user_id' => $this->participant->id,
        'peer_id' => 'peer-participant',
    ]);
});

it('prevents guests from accessing recordings dashboard', function () {
    $this->get(route('recordings.index'))
        ->assertRedirect(route('login'));
});

it('allows participants to upload recordings', function () {
    $file = UploadedFile::fake()->create('video.webm', 1000, 'video/webm');

    $response = $this->actingAs($this->participant)
        ->postJson(route('recording.upload'), [
            'meeting_id' => $this->meeting->id,
            'video' => $file,
        ]);

    $response->assertStatus(200);
    $response->assertJsonPath('success', true);

    $recording = Recording::first();
    expect($recording)->not->toBeNull();
    expect($recording->user_id)->toBe($this->participant->id);

    // Verify file exists on local storage
    Storage::disk('local')->assertExists($recording->file_path);

    // Verify recording participants includes host and participant
    $participants = RecordingParticipant::where('recording_id', $recording->id)->pluck('user_id')->toArray();
    expect($participants)->toContain($this->host->id);
    expect($participants)->toContain($this->participant->id);
    expect($participants)->not->toContain($this->outsider->id);
});

it('prevents outsiders from uploading recordings', function () {
    $file = UploadedFile::fake()->create('video.webm', 1000, 'video/webm');

    $response = $this->actingAs($this->outsider)
        ->postJson(route('recording.upload'), [
            'meeting_id' => $this->meeting->id,
            'video' => $file,
        ]);

    $response->assertStatus(403);
});

it('allows only host and participants to play a recording', function () {
    // Host uploads a recording
    $file = UploadedFile::fake()->create('video.webm', 1000, 'video/webm');
    $this->actingAs($this->host)
        ->post(route('recording.upload'), [
            'meeting_id' => $this->meeting->id,
            'video' => $file,
        ]);

    $recording = Recording::first();

    // Host can play
    $this->actingAs($this->host)
        ->get(route('recordings.play', $recording->id))
        ->assertStatus(200);

    // Participant cannot play
    $this->actingAs($this->participant)
        ->get(route('recordings.play', $recording->id))
        ->assertStatus(403);

    // Outsider cannot play
    $this->actingAs($this->outsider)
        ->get(route('recordings.play', $recording->id))
        ->assertStatus(403);
});

it('restricts downloads for participants and allows for host', function () {
    // Host uploads a recording
    $file = UploadedFile::fake()->create('video.webm', 1000, 'video/webm');
    $this->actingAs($this->host)
        ->post(route('recording.upload'), [
            'meeting_id' => $this->meeting->id,
            'video' => $file,
        ]);

    $recording = Recording::first();

    // Host can download directly
    $this->actingAs($this->host)
        ->get(route('recordings.download', $recording->id))
        ->assertStatus(200);

    // Participant cannot download directly without approval
    $this->actingAs($this->participant)
        ->get(route('recordings.download', $recording->id))
        ->assertStatus(403);

    // Participant requests download
    $this->actingAs($this->participant)
        ->post(route('recordings.request-download', $recording->id))
        ->assertRedirect();

    $request = RecordingDownloadRequest::first();
    expect($request)->not->toBeNull();
    expect($request->status)->toBe('pending');

    // Host approves request
    $this->actingAs($this->host)
        ->post(route('recordings.handle-request', $request->id), ['action' => 'approve'])
        ->assertRedirect();

    expect($request->refresh()->status)->toBe('approved');

    // Participant can now download
    $this->actingAs($this->participant)
        ->get(route('recordings.download', $recording->id))
        ->assertStatus(200);
});
