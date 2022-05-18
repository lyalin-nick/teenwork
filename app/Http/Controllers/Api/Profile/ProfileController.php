<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Helper\UploadFile\ImageRequest;
use App\Http\Requests\Api\Helper\UploadFile\VideoRequest;
use App\Http\Requests\Api\Profile\AddressRequest;
use App\Http\Requests\Api\Profile\CategoriesRequest;
use App\Http\Requests\Api\Profile\ProfileBaseInfoRequest;
use App\Http\Resources\User\UserResource;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    /**
     * Получение информации о пользователе
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        return $this->sendResponse(new UserResource($user), 'Success');
    }

    /**
     * Обновление персональных данных
     *
     * @param ProfileBaseInfoRequest $request
     * @return JsonResponse
     */
    public function baseInfo(ProfileBaseInfoRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;
//        if (!$profile) {
//            $profile = Profile::createProfile(['user_id' => Auth::user()->id]);
//        }
        $profile->update($request->only('first_name', 'last_name', 'date_of_birth', 'about'));
        $profile->linkToLanguages($request->get('languages'));

        return $this->sendResponse(['user' => new UserResource($user)], 'Profile update');
    }

    /**
     * Обновление фото профиля
     *
     * @param ImageRequest $request
     * @return JsonResponse
     */
    public function image(ImageRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

//        if (!$profile) {
//            $profile = Profile::createProfile(['user_id' => Auth::user()->id]);
//        }

        if ($profile->uploadProfileImage($request->image)) {
            $user->refresh();
            return $this->sendResponse(['user' => new UserResource($user)], 'Image upload successful');
        }

        return $this->sendError('Error uploading image');
    }

    /**
     * Обновление видео профиля
     *
     * @param VideoRequest $request
     * @return JsonResponse
     */
    public function video(VideoRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        if ($profile->uploadProfileVideo($request->video)) {
            $user->refresh();
            return $this->sendResponse(['user' => new UserResource($user)], 'Video upload successful');
        }

        return $this->sendError('Error uploading video');
    }

    /**
     * Получение данных портфолио (Только для исполнителя)
     *
     * @return JsonResponse
     */
    public function portfolio(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        return $this->sendResponse($profile->getPortfolio(), 'Portfolio info');
    }

    /**
     * Обновление категорий (Только для исполнителя)
     *
     * @param CategoriesRequest $request
     * @return JsonResponse
     */
    public function categories(CategoriesRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile->updateCategories($request->categories)) {
            return $this->sendError('Error updating');
        }

        return $this->sendResponse(['user' => new UserResource($user)], 'Profile update');
    }

    /**
     * Обновление адреса (Только для исполнителя)
     *
     * @param AddressRequest $request
     * @return JsonResponse
     */
    public function address(AddressRequest $request): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;
//        if (!$profile) {
//            $profile = Profile::createProfile(['user_id' => Auth::user()->id]);
//        }

        if ($profile->setLocation($request->get('address'), $request->get('place_id'))) {
            return $this->sendResponse(['user' => new UserResource($user)], 'Profile update successful');
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
    public function about(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'about' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = Auth::user();
        $profile = $user->profile;
//        if (!$profile) {
//            $profile = Profile::createProfile(['user_id' => Auth::user()->id]);
//        }
        $profile->update($request->only('about'));

        return $this->sendResponse(['user' => new UserResource($user)], 'Profile update');
    }
}
