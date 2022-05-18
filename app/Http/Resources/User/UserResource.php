<?php

namespace App\Http\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
        /* @var $this User */
        $profile = $this->profile;
        return [
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'date_of_birth' => $profile->date_of_birth,
            'about' => $profile->about,
            'status' => $profile->status,
            'photo_preview' => $profile->getProfilePreviewImageLink(),
            'photo' => $profile->getProfileImageLink(),
            'video' => $profile->getProfileVideoLink(),
            'address' => $profile->address,
            'address_id' => $profile->place_id,
            'languages' => $profile->getLanguagesIds(),
            'categories' => $profile->getCategoriesIds(),
            'number_performer_tasks' => $profile->number_performer_tasks,
            'number_employer_tasks' => $profile->number_employer_tasks,
            'rating' => $profile->rating,
            'number_review' => $profile->number_review,
            'push_notification' => $profile->push_notification,
            'email_notification' => $profile->email_notification,
            'invisible' => $profile->invisible,
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'banned' => $this->isBanned(),
        ];
    }
}
