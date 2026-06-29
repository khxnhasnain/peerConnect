<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meeting_id;
    public $message;

    public function __construct($meeting_id, $message = 'Meeting ended by host')
    {
        $this->meeting_id = $meeting_id;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('meeting.' . $this->meeting_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
        ];
    }
}
