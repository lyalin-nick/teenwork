<?php

namespace App\Http\Controllers\Api\Task;

use App\Actions\Task\ResponsesSearchAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Task\RespondedUsersResource;
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
            $responses_with_user = $searchAction($task, $request->only('sort'));

            return $this->sendResponse(['users' => RespondedUsersResource::collection($responses_with_user)], 'Responses');
        }

        return $this->sendError('Task not found');
    }
}
