<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    public function setBaseInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|string|max:255',
            'about' => 'string',
            "languages" => "array",
            "languages.*" => "integer",
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }


        $user = $request->user();
        $profile = $user->profile;
        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }
        $profile->update($request->only('first_name', 'last_name', 'date_of_birth', 'about'));
        $profile->linkToLanguages($request->get('languages'));

        return $this->sendResponse([], 'Profile update');
    }

    public function setAbout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'about' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;
        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }
        $profile->update($request->only('about'));

        return $this->sendResponse([], 'Profile update');
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
        if (!$profile->refreshCategories($request->categories)){
            $this->sendError([], 'Error updating');
        }

        return $this->sendResponse([], 'Profile update');
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

        return $this->sendResponse([], 'Profile update');
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2024',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }

        if ($profile->uploadImage($request->image)) {
            return $this->sendResponse([], 'Image upload successful');
        }

        return $this->sendError([], 'Error uploading image');
    }

    public function setRoleEmployer(Request $request)
    {
        $user = $request->user();
        $user->role = User::ROLE_EMPLOYER;
        if ($user->save()) {
            return $this->sendResponse([], 'Role change success');
        }
    }

    public function setRolePerformer(Request $request)
    {
        $user = $request->user();
        $user->role = User::ROLE_PERFORMER;
        if ($user->save()) {
            return $this->sendResponse([], 'Role change success');
        }
    }

    public function setPushNotification(Request $request)
    {
        $user = $request->user();
        $user->push_notification = !$user->push_notification;
        if ($user->save()) {
            return $this->sendResponse([], 'Setting change success');
        }
    }

    public function setEmailNotification(Request $request)
    {
        $user = $request->user();
        $user->email_notification = !$user->email_notification;
        if ($user->save()) {
            return $this->sendResponse([], 'Setting change success');
        }
    }

    public function setInvisible(Request $request)
    {
        $user = $request->user();
        $user->invisible = !$user->invisible;
        if ($user->save()) {
            return $this->sendResponse([], 'Setting change success');
        }
    }
}
