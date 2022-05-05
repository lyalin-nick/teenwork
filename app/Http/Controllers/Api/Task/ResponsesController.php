<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponsesController extends BaseController
{
    /**
     * Получение откликов на задачу (Заказчик)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function responses(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', $id)->first();
        if ($task) {
            return $this->sendResponse($task->getResponsesInfo($request->only('sort')), 'Responses');
        }

        return $this->sendError('Task not found');
    }
}
