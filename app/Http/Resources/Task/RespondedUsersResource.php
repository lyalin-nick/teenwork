<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\User\ShortInfoResource;
use App\Models\TaskResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class RespondedUsersResource extends JsonResource
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
        /* @var TaskResponse $this */
        return [
            "id" => $this->id,
            "text" => $this->text,
            "is_new" => $this->is_new,
            "user_info" => new ShortInfoResource($this->user),
            $this->mergeWhen(isset($this->distance), [
                "distance" => round($this->distance, 2) . ' km'
            ]),
            "offer" => (isset($this->user->taskOffers[0])) ? ['id' => $this->user->taskOffers[0]->id] : null,
            "chat" => ($this->chat_id !== null) ?
                ['id' => $this->chat_id] :
                ((isset($this->user->taskOffers[0])) ? ['id' => $this->user->taskOffers[0]->chat_id] : null),
        ];
    }
}
