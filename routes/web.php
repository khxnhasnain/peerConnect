<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PeerController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\RecordingController;

// ============================================================
// PUBLIC & GUEST ROUTES
// ============================================================
Route::get('/', function () {
    return view('welcome');
});

Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// ============================================================
// CORE DASHBOARD (VERIFIED USERS)
// ============================================================
Route::get('/dashboard', function () {
    $users = User::where('id', '!=', Auth::id())->get();
    return view('dashboard', compact('users'));
})->middleware(['auth', 'verified'])->name('dashboard');

// ============================================================
// AUTHENTICATED APPLICATION ENGINE
// ============================================================
Route::middleware('auth')->group(function () {

    // Realtime Websocket Channels Auth
    Broadcast::routes();

    // User Profile Layer
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // Global Peer Tracking Engine
    Route::post('/save-peer-id', [PeerController::class, 'save'])->name('peer.save');

    // Chat Management Layer
    Route::controller(ChatController::class)->group(function () {
        Route::get('/messages/{userId}', 'getMessages')->name('chat.messages');
        Route::post('/messages', 'sendMessage')->name('chat.send');
        Route::post('/messages/read/{userId}', 'markAsRead')->name('chat.read');
    });

    // Cleaned & Grouped Video Meeting Architecture
    Route::prefix('meeting')->controller(MeetingController::class)->group(function () {
        Route::post('/create', 'create')->name('meeting.create');
        Route::get('/status/{roomId}', 'checkStatus')->name('meeting.status');
        Route::get('/participants/{meetingId}', 'getParticipants')->name('meeting.participants');
        Route::get('/chat/{meetingId}', 'getChatMessages')->name('meeting.chat.messages');
        Route::get('/signals/{meetingId}/{peerId}', 'getSignals')->name('meeting.get-signals');
        Route::post('/leave', 'leave')->name('meeting.leave');
        Route::post('/end/{meetingId}', 'endMeeting')->name('meeting.end');
        Route::post('/update-status/{meetingId}', 'updateStatus')->name('meeting.update-status');
        Route::post('/signal', 'sendSignal')->name('meeting.signal');

        // Host Moderation Handlers
        Route::post('/toggle-audio/{meetingId}/{userId}', 'toggleAudio')->name('meeting.remote-mute');
        Route::post('/toggle-video/{meetingId}/{userId}', 'toggleVideo')->name('meeting.remote-camera');
        Route::post('/kick/{meetingId}/{userId}', 'kickParticipant')->name('meeting.kick');
        Route::post('/make-admin/{meetingId}/{userId}', 'makeAdmin')->name('meeting.make-admin');
        Route::post('/raise-hand-event', 'raiseHand')->name('meeting.raise-hand-event');
        Route::post('/chat/broadcast', 'broadcastChat')->name('meeting.chat.broadcast');
        Route::get('/sync/{meetingId}/{peerId}', 'syncMeeting')->name('meeting.sync');
        Route::get('/{roomId}', 'join')->name('meeting.join');
    });

    // Recording Management Layer
    Route::controller(RecordingController::class)->group(function () {
        Route::get('/recordings', 'index')->name('recordings.index');
        Route::get('/recordings/sync-requests', 'syncRequests')->name('recordings.sync-requests');
        Route::post('/meeting/recording/upload', 'upload')->name('recording.upload');
        Route::get('/recordings/{id}/play', 'play')->name('recordings.play');
        Route::get('/recordings/{id}/download', 'download')->name('recordings.download');
        Route::post('/recordings/{id}/request-download', 'requestDownload')->name('recordings.request-download');
        Route::post('/recordings/download-requests/{requestId}/action', 'handleRequest')->name('recordings.handle-request');
        Route::delete('/recordings/{id}', 'destroy')->name('recordings.destroy');
    });

    // Presence / Lifecycle Toggles
    Route::post('/set-offline', function () {
        if (Auth::check()) {
            DB::table('users')
                ->where('id', Auth::id())
                ->update(['is_online' => false]);
        }
        return response()->json(['success' => true]);
    })->name('user.set-offline');
});

// ============================================================
// HAND RAISE ROUTES
// ============================================================
Route::post('/meeting/raise-hand', [MeetingController::class, 'raiseHand'])->middleware('auth')->name('meeting.raise-hand');

// ============================================================
// DEVELOPMENT / TESTING SANDBOX
// ============================================================
Route::get('/peer-test', function () {
    return view('peer-test');
})->middleware('auth')->name('peer.test');

require __DIR__ . '/auth.php';
