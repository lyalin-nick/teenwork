<?php

namespace App\Http\Controllers\Api\Task;

use App\Actions\Chat\TaskChatCreateAction;
use App\Actions\Task\RecommendedSearchAction;
use App\Actions\Task\ResponsesSearchAction;
use App\Actions\Task\TaskStoreAction;
use App\Actions\Task\TaskUpdateAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Task\EmployerReviewRequest;
use App\Http\Requests\Api\Task\NewOfferRequest;
use App\Http\Requests\Api\Task\NewTaskRequest;
use App\Http\Requests\Api\Task\UpdateTaskRequest;
use App\Http\Resources\Task\PerformersMapResource;
use App\Http\Resources\Task\UpdateResource;
use App\Http\Resources\Task\ViewResource;
use App\Http\Resources\User\ShortInfoResource;
use App\Models\Review;
use App\Models\Task;
use App\Models\TaskOffer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployerTaskController extends BaseController
{

    /**
     * Создание задачи (Заказчик)
     *
     * @param NewTaskRequest $request
     * @param TaskStoreAction $storeAction
     * @return JsonResponse
     */
    public function store(NewTaskRequest $request, TaskStoreAction $storeAction): JsonResponse
    {
        $task_with_first_date = $storeAction($request);

        if ($task_with_first_date) {
            if ($request->images)
                Storage::disk('public')->delete($request->images);
            if ($request->video) {
                Storage::disk('public')->delete($request->video);
            }

            return $this->sendResponse(['task' => new ViewResource($task_with_first_date)], 'Task create');
        }

        return $this->sendError('Task doesnt created', [], 500);
    }

    /**
     * Удаление задачи (Заказчик)
     *
     * @param $taskId
     * @return JsonResponse
     */
    public function delete($taskId): JsonResponse
    {
        $user = Auth::user();
        $task = $user->tasks()->where('id', $taskId)->first();
        if ($task) {
            return ($task->delete()) ? $this->sendResponse([], 'Task delete', 201) : $this->sendError('Task deleting error', [], 500);
        }

        return $this->sendError('Task not found');
    }

    /**
     * Получение данных о задаче для редактирования (Заказчик)
     *
     * @param $taskId
     * @return JsonResponse
     */
    public function edit($taskId): JsonResponse
    {
        $user = \Auth::user();
        $task = $user->tasks()->where('id', '=', $taskId)->first();

        if ($task) {
            return $this->sendResponse(new UpdateResource($task), 'Task info', 201);
        }

        return $this->sendError('Task not found');
    }

    /**
     * Обновление существующей задачи (Заказчик)
     *
     * @param $taskId
     * @param UpdateTaskRequest $request
     * @param TaskUpdateAction $updateAction
     * @return JsonResponse
     */
    public function update($taskId, UpdateTaskRequest $request, TaskUpdateAction $updateAction): JsonResponse
    {
        $user = Auth::user();
        $task = $user->tasks()->where('id', $taskId)->first();

        if ($task) {
            $updated_task = $updateAction($task, $request);
            if ($updated_task) {
                return $this->sendResponse(['task' => new ViewResource($updated_task)], 'Task create');
            }

            return $this->sendError('Task update error', [], 502);
        }

        return $this->sendError('Task not found');
    }

    /**
     * Отправка оффера исполнителю (Заказчик)
     *
     * @param $taskId
     * @param NewOfferRequest $request
     * @param TaskChatCreateAction $chatCreateAction
     * @return JsonResponse
     */
    public function offer($taskId, NewOfferRequest $request, TaskChatCreateAction $chatCreateAction): JsonResponse
    {
        $task = Task::where('id', '=', $taskId)->first();
        if (!$task) {
            return $this->sendError("Task #{$taskId} not found");
        }

        $employer = Auth::user();
        $performer = User::where('id', $request->user_id)->first();

        if (!$performer) {
            return $this->sendError('Error! Performer not found', [], 501);
        }

        if (TaskOffer::where('task_id', '=', $taskId)->where('user_id', '=', $request->user_id)->first() !== null) {
            return $this->sendError('Offer created already', [], 402);
        }

        if ($task->acceptedTaskOfferUsers()->count() >= $task->amount_of_workers) {
            return $this->sendError('Task team is full', [], 402);
        }

        $offer = TaskOffer::new($taskId, $request->user_id, $request->text);

        if ($offer) {
            $chat = $chatCreateAction($employer, $performer, $task, $offer);

            return $this->sendResponse(['chat_id' => $chat->id], 'Offer create', 201);
        }

        return $this->sendError('Offer doesnt create', [], 501);
    }


    /**
     * Отправка отзыва на исполнителей
     *
     * @param $taskId
     * @param EmployerReviewRequest $request
     * @return JsonResponse
     */
    public function review($taskId, EmployerReviewRequest $request): JsonResponse
    {
        $reviewer = Auth::user();
        $task = Task::where('id', '=', $taskId)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }

        if ($task->user_id !== $reviewer->id) {
            return $this->sendError('You`re an idiot?');
        }

        foreach ($request->users as $user_id)
            $review = Review::new($taskId, $user_id, $reviewer->id, $request->rating, $request->text);

        $users_without_review = []; //TODO: доделать проверку существования отзывов на всех исполнителей данной задачи

        return $this->sendResponse($users_without_review, 'Create successful', 201);
    }

    /**
     * Получение рекомендованных и откликнувшихся исполнителей для отображения на карте
     *
     * @param $taskId
     * @param ResponsesSearchAction $responsesSearchAction
     * @param RecommendedSearchAction $recommendedSearchAction
     * @return JsonResponse
     */
    public function performersOnMap($taskId, ResponsesSearchAction $responsesSearchAction, RecommendedSearchAction $recommendedSearchAction): JsonResponse
    {
        $user = Auth::user();
        $task = Task::where('id', $taskId)->where('user_id', $user->id)->first();
        if (!$task) {
            return $this->sendError('Task not found');
        }
        $responses = $responsesSearchAction($task);
        $responses_user_ids = [];
        foreach ($responses as $response) {
            $responses_user_ids[] = $response->user->id;
        }
        $recommendedSearchAction = $recommendedSearchAction($task, null, $responses_user_ids);

        return $this->sendResponse(['users' => PerformersMapResource::collection($responses->merge($recommendedSearchAction->get()))], 'Users');
    }

    /**
     * Завершение задачи
     *
     * @param $taskId
     * @return JsonResponse
     */
    public function finish($taskId): JsonResponse
    {
        $user = Auth::user();
        $task = Task::where('id', $taskId)->where('user_id', $user->id)->first();
        if (!$task) {
            return $this->sendError('Task not found');
        }

        $task->status = Task::STATUS_COMPLETE;
        if (!$task->save()) {
            return $this->sendError('Error task updated', [], 405);
        }
        $task->refresh();

        $users = $task->acceptedTaskOfferUsers;
        foreach ($users as $i => $user) {
            if ($user->reviews()->where('task_id', '=', $task->id)->exists()) {
                $users->forget($i);
            }
        }

        return $this->sendResponse(['task' => new ViewResource($task)], 'Task finished');
    }

    /**
     * Завершение задачи
     *
     * @param $taskId
     * @return JsonResponse
     */
    public function performersWithoutReview($taskId): JsonResponse
    {
        $user = Auth::user();
        $task = Task::where('id', $taskId)->where('user_id', $user->id)->first();
        if (!$task) {
            return $this->sendError('Task not found');
        }

        $users = $task->acceptedTaskOfferUsers;
        foreach ($users as $i => $user) {
            if ($user->reviews()->where('task_id', '=', $task->id)->exists()) {
                $users->forget($i);
            }
        }

        return $this->sendResponse(['users' => ShortInfoResource::collection($users)], 'Task finished');
    }
}
