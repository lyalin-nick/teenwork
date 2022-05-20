<?php

namespace App\Http\Resources\Response;

use App\Http\Resources\User\ShortInfoResource;
use App\Models\TaskResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        /* @var TaskResponse $this */
        return [
            "id" => $this->id,
            "chat_id" => $this->chat_id,
            "text" => $this->text,
            "is_new" => $this->is_new,
            "user_info" => new ShortInfoResource($this->user),
            $this->mergeWhen(isset($this->distance), [
                "distance" => round($this->distance, 2) . ' km'
            ])
        ];
    }
}
