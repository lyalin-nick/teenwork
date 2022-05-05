<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendedController extends BaseController
{

    /**
     * Получение рекомендуемых пользователей (Заказчик)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function recommended(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', '=', $id)->first();
        if ($task) {
            return $this->sendResponse($task->getRecommendedPerformers($request->only('sort')), 'Users');
        }

        return $this->sendError('Task not found');
    }

}
