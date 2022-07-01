<?php

namespace App\Http\Resources\Chat;

use App\Models\Chat;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = \Auth::user();
        /* @var Chat $this */

        $logo = $this->getChatLogo($user->id);

        return [
            "id" => $this->id,
            "name" => $this->name,
            "status" => $this->status,
            "logo" => $logo,
            "last_message" => ($this->lastMessage) ? $this->lastMessage->text : null,
            "last_message_user_id" => ($this->lastMessage) ? $this->lastMessage->user_id : null,
            "last_message_created_at" => ($this->lastMessage) ? $this->lastMessage->created_at : null,
            "unread_messages_count" => $this->unread_messages_count,
        ];
    }
}
