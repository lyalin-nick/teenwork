<?php

namespace App\Http\Controllers\Api\Performer\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Helpers\UploadingHelper;
use App\Models\PortfolioLink;
use App\Models\PortfolioImage;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
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
            'image' => 'string',
            'video' => 'string',
            'portfolio_photos' => 'array',
            'portfolio_photos.*' => 'string',
            'portfolio_links' => 'array',
            'portfolio_links.*' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        $profile = $user->profile;
        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }

        $updated = $profile->update($request->only('first_name', 'last_name', 'date_of_birth', 'about', 'address', 'place_id'));

        if ($updated) {
            if ($request->get('languages')) {
                $profile->linkToLanguages($request->get('languages'));
            }

            if ($request->categories) {
                $profile->updateCategories($request->categories);
            }

            if ($request->image) {
                $profile->uploadProfileImage($request->image);
            }

            if ($request->video) {
                $profile->uploadProfileVideo($request->image);
            }

            if ($request->portfolio_photos) {
                $result = PortfolioImage::createModels($request->portfolio_photos, $profile->id);

                if (!$result)
                    return $this->sendError('Photos upload error!', [], 511);

            }
            if ($request->portfolio_links) {
                $result = PortfolioLink::createModels($request->portfolio_links, $profile->id);

                if (!$result)
                    return $this->sendError('Links upload error!', [], 512);
            }

            return $this->sendResponse(['user' => $user->getFullData()], 'Profile update');
        }

        return $this->sendError('Profile doesnt updated', [], 500);
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $path = UploadingHelper::uploadFile($request->image);

        return $this->sendResponse($path, 'Uploading success');
    }

    public function uploadImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $paths = UploadingHelper::uploadFiles($request->images);

        return $this->sendResponse($paths, 'Uploading success');
    }


    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|mimetypes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $path = UploadingHelper::uploadFile($request->video);

        return $this->sendResponse($path, 'Uploading success');
    }


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
        if (!$profile->updateCategories($request->categories)) {
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
