<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantMediaStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meetingId;
    public $userId;
    public $peerId;
    public $isAudioMuted;
    public $isVideoOff;

    public function __construct($meetingId, $userId, $peerId, $isAudioMuted, $isVideoOff)
    {
        $this->meetingId = $meetingId;
        $this->userId = $userId;
        $this->peerId = $peerId;
        $this->isAudioMuted = $isAudioMuted;
        $this->isVideoOff = $isVideoOff;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('meeting.' . $this->meetingId);
    }

    public function broadcastAs()
    {
        return 'media-state-changed';
    }

    public function broadcastWith()
    {
        return [
            'meetingId' => $this->meetingId,
            'userId' => $this->userId,
            'peerId' => $this->peerId,
            'isAudioMuted' => $this->isAudioMuted,
            'isVideoOff' => $this->isVideoOff,
        ];
    }
}
