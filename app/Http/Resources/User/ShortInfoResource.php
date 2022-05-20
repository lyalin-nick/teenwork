<?php

namespace App\Http\Resources\User;

use App\Models\Profile;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ShortInfoResource extends JsonResource
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
        if ($this->resource instanceof Profile) {
            $user = $this->user;
            $profile = $this;
        } else {
            $user = $this;
            $profile = $this->profile;
        }

        return [
            'id' => $user->id,
            'name' => $profile->full_name,
            'photo' => $profile->getProfilePreviewImageLink(),
            'rating' => $profile->rating,
            'status' => $profile->status,
            'number_reviews' => $profile->number_review,
        ];
    }
}
