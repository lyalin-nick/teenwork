<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SettingController extends BaseController
{
    /**
     * PUSH-уведомления
     *
     * @return JsonResponse
     */
    public function pushNotification(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;
        $profile->push_notification = !$profile->push_notification;
        if ($profile->save()) {
            $user->refresh();
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }

        return $this->sendError('Error! Setting don`t update!');
    }

    /**
     * Email-уведомления
     *
     * @return JsonResponse
     */
    public function emailNotification(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;
        $profile->email_notification = !$profile->email_notification;
        if ($profile->save()) {
            $user->refresh();
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }
        return $this->sendError('Error! Setting don`t update!');
    }

    /**
     * Мод невидимка
     *
     * @return JsonResponse
     */
    public function invisible(): JsonResponse
    {
        $user = Auth::user();
        $profile = $user->profile;
        $profile->invisible = !$profile->invisible;
        if ($profile->save()) {
            $user->refresh();
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }
        return $this->sendError('Error! Setting don`t update!');
    }
}
