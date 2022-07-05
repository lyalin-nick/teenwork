<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\User\ShortInfoResource;
use App\Models\Task;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TaskListPreviewResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        /* @var Task $this */
        return [
            "id" => $this->id,
            "name" => $this->name,
            "price" => $this->price,
            "payment_type" => $this->payment_type,
            "description" => $this->description,
            "status" => $this->status_label,
            "safe_deal" => $this->safe_deal,
            "hot_work" => $this->hot_work,
            "images_links" => $this->getImagesAsLinks(),
        ];
    }
}
