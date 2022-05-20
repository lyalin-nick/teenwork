<?php

namespace App\Http\Resources\Task;

use App\Models\Task;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UpdateResource extends JsonResource
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
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'result' => $this->result,
            'address' => $this->address,
            'place_id' => $this->place_id,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'amount_of_workers' => $this->amount_of_workers,
            'minimum_age' => $this->minimum_age,
            'price' => $this->price,
            'payment_type' => $this->payment_type,
            'safe_deal' => $this->safe_deal,
            'hot_work' => $this->hot_work,
            'account_verified' => $this->account_verified,
            'languages' => $this->getLanguagesAsArray(),
            'images' => $this->getImagesAsArray(),
            'video' => ($this->video && $this->video->hasVideo()) ? [
                'link' => $this->video->getLink(),
                'path' => $this->video->getFullPath()
            ] : null
        ];
    }
}
