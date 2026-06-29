<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'receiver']);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->message->receiver_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'content'     => $this->message->content,
            'sender_id'   => $this->message->sender_id,
            'sender_name' => $this->message->sender->name, // <-- IMPORTANT: This gives the sender's name
            'receiver_id' => $this->message->receiver_id,
            'created_at'  => $this->message->created_at->format('h:i A'),
        ];
    }
}
