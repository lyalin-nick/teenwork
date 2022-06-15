<?php

namespace App\Events;

use App\Http\Resources\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Chat
     */
    public $chat;

    /**
     * @var Message
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $chat)
    {
        $this->message = new MessageResource($message);
        $users = $chat->users;
        $logo = [];
        if ($users)
            foreach ($users as $chat_user) {
                $logo[] = $chat_user->profile->getProfilePreviewImageLink();
            }

        $this->chat = [
            "id" => $chat->id,
            "name" => $chat->name,
            "logo" => $logo
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chat['id']);
    }

    public function broadcastAs(): string
    {
        return 'chat.message.send';
    }
}
