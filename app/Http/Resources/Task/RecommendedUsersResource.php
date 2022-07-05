<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\User\ShortInfoResource;
use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class  RecommendedUsersResource extends JsonResource
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
        } else {
            $user = $this;
            $profile = $this->profile;
        }

        return [
            'user_info' => new ShortInfoResource($user),
            'offer' => (isset($profile->offer_id)) ? ['id' => $profile->offer_id] : null,
            'chat' => isset($profile->offer_chat_id) ? ['id' => $profile->offer_chat_id] : ((isset($profile->chat_id) ? ['id' => $profile->chat_id] : null)),
            $this->mergeWhen(isset($this->distance), [
                "distance" => round($this->distance, 2) . ' km'
            ])
        ];
    }
}
