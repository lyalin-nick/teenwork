<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Task\ResponseRequest;
use App\Http\Requests\Api\Task\ReviewRequest;
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
     * @param ResponseRequest $request
     * @return JsonResponse
     */
    public function response(int $id, ResponseRequest $request): JsonResponse
    {
        $task = Task::where('id', '=', $id)->first();
        if (!$task) {
            return $this->sendError('Task not found');
        }

        $user = Auth::user();
        if ($task->user_id === $user->id) {
            return $this->sendError('Its your task.');
        }

        $new_response = TaskResponse::new($id, $user->id, $request->text);
        if ($new_response) {
            return $this->sendResponse([], 'Response create', 201);
        }

        return $this->sendError('Error', [], 501);
    }

    /**
     * Отправка отзыва на заказчика
     *
     * @param int $id
     * @param ReviewRequest $request
     * @return JsonResponse
     */
    public function review(int $id, ReviewRequest $request): JsonResponse
    {
        $reviewer = Auth::user();
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }

        if ($task->user_id == !$reviewer->id) {
            return $this->sendError('You`re an idiot?');
        }

        $review = Review::new($id, $task->user_id, $reviewer->id, $request->rating, $request->text);

        if (!$review) {
            return $this->sendError('Error creating', [], 502);
        }
        return $this->sendResponse([], 'Create successful', 201);
    }
}
