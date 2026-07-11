<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\MeetingParticipant;

// Channel authorization for hand raise events
Broadcast::channel('meeting.{meetingId}', function ($user, $meetingId) {
    // Check if user is a participant in this meeting
    $participant = MeetingParticipant::where('meeting_id', $meetingId)
        ->where('user_id', $user->id)
        ->exists();

    \Illuminate\Support\Facades\Log::info('Channel auth:', [
        'user_id' => $user->id,
        'meeting_id' => $meetingId,
        'participant' => $participant
    ]);

    return $participant;
});
