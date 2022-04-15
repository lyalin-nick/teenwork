<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;


class UserController extends BaseController
{
    public function view($id, Request $request)
    {
        $user = User::where('id', '=', $id)->with('profile')->first();
        if ($user) {
            $profile = $user->profile;
            $user_data = [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'photo_preview' => $profile->getProfilePreviewImageLink(),
                'photo' => $profile->getProfileImageLink(),
                'number_performer_tasks' => $profile->number_performer_tasks,
                'number_employer_tasks' => $profile->number_employer_tasks,
                'status' => $profile->status,
                'verified' => $user->isVerified(),
                'about' => $profile->about,
                'video' => $profile->getProfileVideoLink(),
                'portfolio' => $profile->getPortfolio(),//$user->isPerformer() ? $profile->getPortfolio() : null,
                'rating' => $profile->rating,
                'stars' => $user->getStars(),//$user->isPerformer() ? $user->getStars() : null,
                'last_review' => $user->getLastReview(),
                'created_at' => date('Y-m-d', strtotime($user->created_at))
            ];

            return $this->sendResponse($user_data, 'User');

        }

        return $this->sendError('User not found');
    }
}
