<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends BaseController
{
    public function pushNotification(Request $request)
    {
        $user = $request->user();
        $user->push_notification = !$user->push_notification;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }

        return $this->sendError('Error! Setting don`t update!');
    }

    public function emailNotification(Request $request)
    {
        $user = $request->user();
        $user->email_notification = !$user->email_notification;
        if ($user->save()) {
            return $this->sendResponse(['user' => $user->getFullData()], 'Setting change success');
        }
        return $this->sendError('Error! Setting don`t update!');
    }

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
