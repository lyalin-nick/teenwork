<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    /**
     * Получение информации о пользователе
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return $this->sendResponse($user->getFullData(), 'Success');
    }

    /**
     * Обновление персональных данных
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function baseInfo(Request $request)
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

    /**
     * Обновление фото профиля
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function image(Request $request)
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
            $user->refresh();
            return $this->sendResponse(['user' => $user->getFullData()], 'Image upload successful');
        }

        return $this->sendError('Error uploading image');
    }

    /**
     * Обновление видео профиля
     *
     * @param Request $request
     * @return
     */
    public function video(Request $request)
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
            $user->refresh();
            return $this->sendResponse(['user' => $user->getFullData()], 'Video upload successful');
        }

        return $this->sendError('Error uploading video');
    }

    /**
     * Получение данных портфолио (Только для исполнителя)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function portfolio(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return $this->sendResponse($profile->getPortfolio(), 'Portfolio info');
    }

    /**
     * Обновление категорий (Только для исполнителя)
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Обновление адреса (Только для исполнителя)
     *
     * @param Request $request
     * @return JsonResponse/
     */
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




    /*================================ Зона отчуждения ====================================*/
    /**
     * Не используется
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function about(Request $request)
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
}
