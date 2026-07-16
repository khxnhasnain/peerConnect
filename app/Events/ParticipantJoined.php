<?php

namespace App\Events;

use App\Models\MeetingParticipant;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $participant;

    public function __construct(MeetingParticipant $participant)
    {
        $this->participant = $participant->load('user');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('meeting.' . $this->participant->meeting_id);
    }

    public function broadcastWith()
    {
        return [
            'participant' => [
                'id'         => $this->participant->id,
                'user_id'    => $this->participant->user_id,
                'user_name'  => $this->participant->user->name,
                'peer_id'    => $this->participant->user ? $this->participant->user->peer_id : $this->participant->peer_id,
            ]
        ];
    }
}
