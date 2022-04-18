<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PerformerProfileController extends BaseController
{
    /**
     * Первоначальное заполнение профиля
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|string|max:255',
            'about' => 'string',
            'address' => 'required|string|max:255',
            'place_id' => 'required|string',
            'languages' => 'array',
            'languages.*' => 'integer',
            'categories' => 'required|array',
            'categories.*' => 'integer',
            'image' => 'nullable|string',
            'video' => 'nullable|string',
            'portfolio_images' => 'array',
            'portfolio_images.*' => 'array',
            'portfolio_links' => 'array',
            'portfolio_links.*' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        $user->checkEmptyRole(User::ROLE_PERFORMER);
        $profile = $user->profile;
        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }

        $updated = $profile->performerProfile(
            $request->only('first_name', 'last_name', 'date_of_birth', 'about', 'address', 'place_id'),
            $request->get('languages'), $request->categories, $request->image, $request->video,
            $request->portfolio_images, $request->portfolio_links
        );

        if ($updated) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Profile update');
        }

        return $this->sendError('Profile doesnt updated', [], 500);
    }
}
