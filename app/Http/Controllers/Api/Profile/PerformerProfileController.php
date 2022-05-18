<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Profile\ProfileUpdateRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;

class PerformerProfileController extends BaseController
{
    /**
     * Первоначальное заполнение профиля
     *
     * @param ProfileUpdateRequest $request
     * @return JsonResponse
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = Auth::user();
        $user->checkEmptyRole(User::ROLE_PERFORMER);
        $profile = $user->profile;
//        if (!$profile) {
//            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
//        }

        $updated = $profile->performerProfile(
            $request->only('first_name', 'last_name', 'date_of_birth', 'about', 'address', 'place_id'),
            $request->get('languages'), $request->categories, $request->image, $request->video,
            $request->portfolio_images, $request->portfolio_links
        );

        if ($updated) {
            return $this->sendResponse(['user' => new UserResource($user)], 'Profile update');
        }

        return $this->sendError('Profile doesnt updated', [], 500);
    }
}
