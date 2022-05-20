<?php

namespace App\Http\Resources\Review;

use App\Http\Resources\User\ShortInfoResource;
use App\Models\Review;
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
        /* @var Review $this */
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'text' => $this->text,
            'date' => $this->date,
            'reviewer_info' => new ShortInfoResource($this->reviewer),
            'task_info' => ($this->task) ? $this->task->only('id', 'name') : ['id' => null, 'name' => 'Task has been deleted']
        ];
    }
}
