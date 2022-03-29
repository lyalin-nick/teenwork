<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersonalInformationController extends BaseController
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

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }

        if ($profile->uploadProfileImage($request->image)) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Image upload successful');
        }

        return $this->sendError('Error uploading image');
    }


    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|mimetypes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi',
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
            return $this->sendResponse(['user' => $user->getFullData()], 'Video upload successful');
        }

        return $this->sendError('Error uploading video');
    }

}
