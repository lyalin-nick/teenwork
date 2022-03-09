<?php

namespace App\Http\Controllers\Api\Performer\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{

    public function portfolio(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return $this->sendResponse($profile->getPortfolio(), 'Portfolio info');
    }

    public function setCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;
        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }
        if (!$profile->refreshCategories($request->categories)) {
            $this->sendError([], 'Error updating');
        }

        return $this->sendResponse(['user' => $user->getFullData()], 'Profile update');
    }

    public function setAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
            'place_id' => 'string',
            'latitude' => 'string',
            'longitude' => 'string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;
        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }
        $profile->update($request->only('address', 'place_id', 'latitude', 'longitude'));

        return $this->sendResponse(['user' => $user->getFullData()], 'Profile update');
    }

    public function setRoleEmployer(Request $request)
    {
        $user = $request->user();
        $user->role = User::ROLE_EMPLOYER;
        if ($user->save()) {
            return $this->sendResponse(['role' => User::ROLE_EMPLOYER], 'Role change success');
        }

        return $this->sendError('Error! Role don`t change!');
    }
}
