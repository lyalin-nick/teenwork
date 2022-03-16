<?php

namespace App\Http\Controllers\Api\Employer\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    /**
     * Установка роли "Исполнитель" доступно только для заказчика
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setRolePerformer(Request $request)
    {
        $user = $request->user();
        $user->role = User::ROLE_PERFORMER;
        if ($user->save()) {
            return $this->sendResponse(['role' => User::ROLE_PERFORMER], 'Role change success');
        }

        return $this->sendError('Error! Role don`t change!');
    }
}
