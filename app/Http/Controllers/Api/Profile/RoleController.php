<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;

class RoleController extends BaseController
{
    /**
     * Установка роли Заказчик
     *
     * @return JsonResponse
     */
    public function roleEmployer(): JsonResponse
    {
        $user = Auth::user();
        $user->role = User::ROLE_EMPLOYER;
        if ($user->save()) {
            return $this->sendResponse(['role' => User::ROLE_EMPLOYER], 'Role change success');
        }

        return $this->sendError('Error! Role don`t change!');
    }

    /**
     * Установка роли "Исполнитель" доступно только для заказчика
     *
     * @return JsonResponse
     */
    public function rolePerformer()
    {
        $user = Auth::user();
        $user->role = User::ROLE_PERFORMER;
        if ($user->save()) {
            return $this->sendResponse(['role' => User::ROLE_PERFORMER], 'Role change success');
        }

        return $this->sendError('Error! Role don`t change!');
    }

}
