<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortInfoResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        /* @var User $this */
        $profile = $this->profile;
        return [
            'id' => $this->id,
            'name' => $profile->full_name,
            'photo' => $profile->getProfilePreviewImageLink(),
            'rating' => $profile->rating,
            'status' => $profile->status,
            'number_reviews' => $profile->number_review,
        ];
    }
}
