<?php

namespace App\Http\Controllers\Api\Task;

use App\Actions\Chat\TaskChatCreateAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Task\ResponseRequest;
use App\Http\Requests\Api\Task\ReviewRequest;
use App\Models\Review;
use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\TaskResponse;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformerTaskController extends BaseController
{
    /**
     * Отправка отклика (Исполнитель)
     *
     * @param $id
     * @param ResponseRequest $request
     * @param TaskChatCreateAction $chatCreateAction
     * @return JsonResponse
     */
    public function response($id, ResponseRequest $request, TaskChatCreateAction $chatCreateAction): JsonResponse
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
            $chat = $chatCreateAction($user, $task->user, $task, $new_response);

            if ($chat)
                return $this->sendResponse(['chat_id' => $chat->id], 'Response create', 201);
        }

        return $this->sendError('Error', [], 501);
    }

    /**
     * Отправка отзыва на заказчика
     *
     * @param $id
     * @param ReviewRequest $request
     * @return JsonResponse
     */
    public function review($id, ReviewRequest $request): JsonResponse
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

    /**
     *
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function offer($id, Request $request): JsonResponse
    {
        $performer = Auth::user();
        $taskOffer = TaskOffer::where('task_id', '=', $id)->where('user_id', '=', $performer->id)->first();

        if (!$taskOffer) {
            return $this->sendError('Offer not found');
        }

        $taskOffer->accept = $request->accept;

        if (!$taskOffer->save()) {
            return $this->sendError('Error creating', [], 502);
        }

        return $this->sendResponse(['chat' => $taskOffer->chat_id], 'Create successful', 201);
    }
}
