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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $users = User::where('id', '!=', Auth::id())->get();
    return view('dashboard', compact('users'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Broadcast::routes();

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/save-peer-id', [PeerController::class, 'save']);

    Route::get('/messages/{userId}', [ChatController::class, 'getMessages']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    Route::post('/messages/read/{userId}', [ChatController::class, 'markAsRead']);

    // MEETING ROUTES
    Route::get('/meeting/create', [MeetingController::class, 'create'])->name('meeting.create');
    Route::get('/meeting/{roomId}', [MeetingController::class, 'join'])->name('meeting.join');
    Route::get('/meeting/participants/{meetingId}', [MeetingController::class, 'getParticipants']);
    Route::post('/meeting/leave', [MeetingController::class, 'leave']);
    Route::post('/meeting/end/{meetingId}', [MeetingController::class, 'endMeeting']);
    Route::post('/meeting/toggle-audio/{meetingId}/{userId}', [MeetingController::class, 'toggleAudio']);
    Route::post('/meeting/toggle-video/{meetingId}/{userId}', [MeetingController::class, 'toggleVideo']);
    Route::post('/meeting/kick/{meetingId}/{userId}', [MeetingController::class, 'kickParticipant']);
    Route::post('/meeting/update-status/{meetingId}', [MeetingController::class, 'updateStatus']);
    Route::post('/meeting/signal', [MeetingController::class, 'sendSignal']);
    Route::get('/meeting/signals/{meetingId}/{peerId}', [MeetingController::class, 'getSignals']);

    Route::post('/set-offline', function () {
        if (Auth::check()) {
            DB::table('users')
                ->where('id', Auth::id())
                ->update(['is_online' => false]);
        }
        return response()->json(['success' => true]);
    });
});

Route::get('/peer-test', function () {
    return view('peer-test');
})->middleware('auth');

require __DIR__ . '/auth.php';
