<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Models\Review;
use App\Models\Task;
use App\Models\TaskResponse;
use Auth;
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
    public function response($id, Request $request): JsonResponse
    {
        $task = Task::where('id', '=', $id)->first();
        if (!$task) {
            return $this->sendError('Task not found');
        }

        $user = Auth::user();
        if ($task->user_id === $user->id) {
            return $this->sendError('Its your task.');
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $new_response = TaskResponse::new($id, $user->id, $request->get('text'));
        if ($new_response) {
            return $this->sendResponse([], 'Response create', 201);
        }

        return $this->sendError('Error', [], 501);
    }

    /**
     * Отправка отзыва на заказчика
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function review($id, Request $request)
    {
        $reviewer = Auth::user();
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }

        if ($task->user_id == !$reviewer->id) {
            return $this->sendError('You`re an idiot?');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer',
            'text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $review = Review::new($id, $task->user_id, $reviewer->id, $request->rating, $request->text);

        if (!$review) {
            return $this->sendError('Error creating', [], 502);
        }
        return $this->sendResponse([], 'Create successful', 201);
    }
}
