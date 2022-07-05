<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\User\ShortInfoResource;
use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformersMapResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->resource instanceof Profile) {
            $user = $this->user;
            $profile = $this;

            return [
                'user_info' => new ShortInfoResource($user),
                'lat' => $profile->lat,
                'lng' => $profile->lng,
                'offer' => (isset($profile->offer_id)) ? ['id' => $profile->offer_id] : null,
                'chat' => isset($profile->offer_chat_id) ? ['id' => $profile->offer_chat_id] : ((isset($profile->chat_id) ? ['id' => $profile->chat_id] : null)),
                $this->mergeWhen(isset($this->distance), [
                    "distance" => round($this->distance, 2) . ' km'
                ])
            ];

        } else {
            return [
                "response" => [
                    "id" => $this->id,
                    "text" => $this->text,
                    "is_new" => $this->is_new,
                ],
                "user_info" => new ShortInfoResource($this->user),
                $this->mergeWhen(isset($this->distance), [
                    "distance" => round($this->distance, 2) . ' km'
                ]),
                'lat' => $this->user->profile->lat,
                'lng' => $this->user->profile->lng,
                "offer" => (isset($this->user->taskOffers[0])) ? ['id' => $this->user->taskOffers[0]->id] : null,
                "chat" => ($this->chat_id !== null) ?
                    ['id' => $this->chat_id] :
                    ((isset($this->user->taskOffers[0])) ? ['id' => $this->user->taskOffers[0]->chat_id] : null),
            ];
        }
    }
}
