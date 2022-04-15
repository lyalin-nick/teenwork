<?php

namespace App\Http\Controllers\Api\Performer\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\PortfolioImage;
use App\Models\PortfolioLink;
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

            if ($request->portfolio_images) {

                $result = PortfolioImage::createModels($request->portfolio_images, $profile->id);

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


    public function portfolio(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return $this->sendResponse($profile->getPortfolio(), 'Portfolio info');
    }

    public function categories(Request $request)
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
            return $this->sendError('Error updating');
        }

        return $this->sendResponse(['user' => $user->getFullData()], 'Profile update');
    }

    public function address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
            'place_id' => 'string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $profile = $user->profile;
        if (!$profile) {
            $profile = Profile::createProfile(['user_id' => $request->user()->id]);
        }

        if ($profile->setLocation($request->get('address'), $request->get('place_id'))) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Profile update successful');
        }
        return $this->sendError('Profile update error', [], 501);
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
