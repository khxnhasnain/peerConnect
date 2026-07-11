<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingChatMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $meetingId;
    public $userId;
    public $senderName;
    public $message;
    public $messageId;
    public $timestamp;

    public function __construct($meetingId, $userId, $senderName, $message, $messageId = null)
    {
        $this->meetingId = $meetingId;
        $this->userId = $userId;
        $this->senderName = $senderName;
        $this->message = $message;
        $this->messageId = $messageId;
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn()
    {
        return new Channel('meeting.' . $this->meetingId);
    }

    public function broadcastAs()
    {
        return 'chat-message';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->messageId,
            'user_id' => $this->userId,
            'sender_name' => $this->senderName,
            'message' => $this->message,
            'timestamp' => $this->timestamp
        ];
    }
}
