<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    /**
     * Установка роли Заказчик
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function roleEmployer(Request $request)
    {
        $user = $request->user();
        $user->role = User::ROLE_EMPLOYER;
        if ($user->save()) {
            return $this->sendResponse(['role' => User::ROLE_EMPLOYER], 'Role change success');
        }

        return $this->sendError('Error! Role don`t change!');
    }

    /**
     * Установка роли "Исполнитель" доступно только для заказчика
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function rolePerformer(Request $request)
    {
        $user = $request->user();
        $user->role = User::ROLE_PERFORMER;
        if ($user->save()) {
            return $this->sendResponse(['role' => User::ROLE_PERFORMER], 'Role change success');
        }

        return $this->sendError('Error! Role don`t change!');
    }

}
