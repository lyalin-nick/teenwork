<?php

namespace App\Http\Resources\Chat;

use App\Models\Message;
use Illuminate\Http\Resources\Json\JsonResource;

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
        /* @var Message $this */
        return [
            'id' => $this->id,
            'text' => $this->text,
            'img' => $this->img,
            "user_info" => [
                'id' => $this->user->id,
                'name' => $this->user->profile->full_name,
                'photo' => $this->user->profile->getProfilePreviewImageLink(),
            ],
        ];
    }
}
