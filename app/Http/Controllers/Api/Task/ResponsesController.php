<?php

namespace App\Http\Controllers\Api\Task;

use App\Actions\Task\ResponsesSearchAction;
use App\Http\Controllers\Api\BaseController;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponsesController extends BaseController
{
    /**
     * Получение откликов на задачу (Заказчик)
     *
     * @param $id
     * @param Request $request
     * @param ResponsesSearchAction $searchAction
     * @return JsonResponse
     */
    public function responses($id, Request $request, ResponsesSearchAction $searchAction): JsonResponse
    {
        $user = Auth::user();
        $task = $user->tasks()->where('id', $id)->first();
        if ($task) {
            return $this->sendResponse($searchAction($task, $request->only('sort')), 'Responses');
        }

        return $this->sendError('Task not found');
    }
}
