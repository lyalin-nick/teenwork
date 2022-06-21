<?php

namespace App\Http\Resources\Chat;

use App\Models\Message;
use Illuminate\Http\Resources\Json\JsonResource;
use Auth;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = Auth::user();
        /* @var Message $this */
        $sender = $this->sender;

        $status = $this->messageStatuses()->where('user_id', $user->id)->first();
        return [
            'id' => $this->id,
            'text' => $this->text,
            'images' => $this->images,
            'reading' => ($status !== null) ? $status->reading : true,
            'user_info' => [
                'id' => $sender->id,
                'name' => $sender->profile->full_name,
                'photo' => $sender->profile->getProfilePreviewImageLink(),
            ],
        ];
    }
}
