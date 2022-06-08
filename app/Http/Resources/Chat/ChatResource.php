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
        $logo = [];
        $users = $this->users()->where('id', '!=', $user->id)->get();
        if ($users)
            foreach ($users as $chat_user) {
                $logo[] = $chat_user->profile->getProfilePreviewImageLink();
            }

        return [
            "id" => $this->id,
            "name" => $this->name,
            "logo" => $logo,
            "last_message" => $this->last_message,
            "last_message_user_id" => $this->last_message_user_id,
            "last_message_created_at" => $this->last_message_user_id,
            "unread_messages_count" => $this->unread_messages_count,
        ];
    }
}
