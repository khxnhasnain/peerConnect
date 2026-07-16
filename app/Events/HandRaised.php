<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HandRaised implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meetingId;
    public $userId;
    public $peerId;
    public $raised;

    public function __construct($meetingId, $userId, $peerId, $raised)
    {
        $this->meetingId = $meetingId;
        $this->userId = $userId;
        $this->peerId = $peerId;
        $this->raised = $raised;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('meeting.' . $this->meetingId);
    }

    public function broadcastAs()
    {
        return 'user-raised-hand';
    }

    public function broadcastWith()
    {
        return [
            'meetingId' => $this->meetingId,
            'userId' => $this->userId,
            'peerId' => $this->peerId,
            'raised' => $this->raised,
        ];
    }
}
