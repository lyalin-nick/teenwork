<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Chat
     */
    public $chat;
    public $user_addressees_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($chat_data, $user_addressee_id)
    {
        $this->chat = $chat_data;
        $this->user_addressees_id = $user_addressee_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chatlist.' . $this->user_addressees_id);
    }

    public function broadcastAs(): string
    {
        return 'chatlist.update';
    }
}
