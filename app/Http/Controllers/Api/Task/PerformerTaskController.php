<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\TaskResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerformerTaskController extends BaseController
{
    /**
     * Отправка отклика (Исполнитель)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function response($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $task = Task::where('id', '=', $id)->first();
        if (!$task) {
            return $this->sendError('Task not found');
        }

        $user = $request->user();
        if ($task->user_id === $user->id) {
            return $this->sendError('Its your task.');
        }

        $new_response = TaskResponse::new($task->id, $user->id, $request->get('text'));
        if ($new_response) {
            return $this->sendResponse([], 'Response create', 201);
        }

        return $this->sendError('Error', [], 501);
    }
}
