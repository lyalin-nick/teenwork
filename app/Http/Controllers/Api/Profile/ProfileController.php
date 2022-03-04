<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    public function index(Request $request)
    {
        $user = $request->user();

        return $user->getFullData();
    }

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

        return $this->sendResponse(['user' => $user->getFullData()], 'Profile update');
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

        return $this->sendResponse(['user' => $user->getFullData()], 'Profile update');
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

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }

        if ($profile->uploadImageFromBase64($request->image)) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Image upload successful');
        }

        return $this->sendError('Error uploading image');
    }


    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }

        if ($profile->uploadProfileVideo($request->video)) {
            return $this->sendResponse(['video' => $profile->getProfileVideoLink()], 'Video upload successful');
        }

        return $this->sendError('Error uploading image');
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

    public function setRolePerformer(Request $request)
    {
        $user = $request->user();
        $user->role = User::ROLE_PERFORMER;
        if ($user->save()) {
            return $this->sendResponse(['role' => User::ROLE_PERFORMER], 'Role change success');
        }

        return $this->sendError('Error! Role don`t change!');
    }

    public function setPushNotification(Request $request)
    {
        $user = $request->user();
        $user->push_notification = !$user->push_notification;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }

        return $this->sendError('Error! Setting don`t update!');
    }

    public function setEmailNotification(Request $request)
    {
        $user = $request->user();
        $user->email_notification = !$user->email_notification;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }
        return $this->sendError('Error! Setting don`t update!');
    }

    public function setInvisible(Request $request)
    {
        $user = $request->user();
        $user->invisible = !$user->invisible;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }
        return $this->sendError('Error! Setting don`t update!');
    }
}
