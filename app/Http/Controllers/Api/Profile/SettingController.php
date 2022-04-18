<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    /**
     * PUSH-уведомления
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pushNotification(Request $request)
    {
        $user = $request->user();
        $user->push_notification = !$user->push_notification;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }

        return $this->sendError('Error! Setting don`t update!');
    }

    /**
     * Email-уведомления
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function emailNotification(Request $request)
    {
        $user = $request->user();
        $user->email_notification = !$user->email_notification;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }
        return $this->sendError('Error! Setting don`t update!');
    }

    /**
     * Мод невидимка
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function invisible(Request $request)
    {
        $user = $request->user();
        $user->invisible = !$user->invisible;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }
        return $this->sendError('Error! Setting don`t update!');
    }
}
