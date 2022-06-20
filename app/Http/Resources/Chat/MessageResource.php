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
        $sender = $this->sender;

        return [
            'id' => $this->id,
            'text' => $this->text,
            'images' => $this->images,
            $this->mergeWhen(!empty($this->messageStatuses->first()), [
                'reading' => $this->messageStatuses->first()->reading
            ]),
            'user_info' => [
                'id' => $sender->id,
                'name' => $sender->profile->full_name,
                'photo' => $sender->profile->getProfilePreviewImageLink(),
            ],
        ];
    }
}
