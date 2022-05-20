<?php

namespace App\Http\Resources\Profile;

use App\Models\User;
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
        /* @var User $this */
        $profile = $this->profile;
        return [
            'id' => $this->id,
            'full_name' => $profile->full_name,
            'photo_preview' => $profile->getProfilePreviewImageLink(),
            'photo' => $profile->getProfileImageLink(),
            'number_performer_tasks' => $profile->number_performer_tasks,
            'number_employer_tasks' => $profile->number_employer_tasks,
            'status' => $profile->status,
            'verified' => $this->isVerified(),
            'about' => $profile->about,
            'video' => $profile->getProfileVideoLink(),
            'portfolio' => $profile->getPortfolio(),//$this->isPerformer() ? $profile->getPortfolio() : null,
            'rating' => $profile->rating,
            'stars' => $this->getStars(),//$this->isPerformer() ? $this->getStars() : null,
            'last_review' => $this->getLastReview(),
            'created_at' => date('Y-m-d', strtotime($this->created_at))
        ];
    }
}
