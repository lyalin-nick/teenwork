<?php

namespace App\Http\Resources\Task;

use App\Http\Resources\User\ShortInfoResource;
use App\Models\Task;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PreviewResource extends JsonResource
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
            "place_id" => $this->place_id,
            "hot_work" => $this->hot_work,
            "safe_deal" => $this->safe_deal,
            "created_at" => $this->created_at,
            "start_date" => $this->start_date,
            "lat" => $this->lat,
            "lng" => $this->lng,
            "icon_name" => $this->icon_name,
            "user_info" => new ShortInfoResource($this->user),
            "images_links" => $this->getImagesAsLinks(),
            "status" => $this->status_label,
            $this->mergeWhen(isset($this->distance), [
                "distance" => round($this->distance, 2) . ' km'
            ])
        ];
    }
}
