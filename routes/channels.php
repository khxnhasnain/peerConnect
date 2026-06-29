<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('meeting.{meetingId}', function ($user, $meetingId) {
    return \App\Models\MeetingParticipant::where('meeting_id', $meetingId)
        ->where('user_id', $user->id)
        ->exists();
});
