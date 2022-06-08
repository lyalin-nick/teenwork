<?php

namespace App\Http\Controllers\Api\Task;

use App\Actions\Chat\ChatCreateAction;
use App\Actions\Task\TaskStoreAction;
use App\Actions\Task\TaskUpdateAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Task\EmployerReviewRequest;
use App\Http\Requests\Api\Task\NewOfferRequest;
use App\Http\Requests\Api\Task\NewTaskRequest;
use App\Http\Requests\Api\Task\UpdateTaskRequest;
use App\Http\Resources\Task\UpdateResource;
use App\Http\Resources\Task\ViewResource;
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
     * Получение данных о задаче для редактирования (Заказчик)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $user = \Auth::user();
        $task = $user->tasks()->where('id', '=', $id)->first();

        if ($task) {
            return $this->sendResponse(new UpdateResource($task), 'Task info', 201);
        }

        return $this->sendError('Task not found');
    }

    /**
     * Обновление существующей задачи (Заказчик)
     *
     * @param UpdateTaskRequest $request
     * @param int $id
     * @param TaskUpdateAction $updateAction
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, int $id, TaskUpdateAction $updateAction): JsonResponse
    {
        $user = \Auth::user();
        $task = $user->tasks()->where('id', $id)->first();

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
     * Удаление задачи (Заказчик)
     *
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $user = Auth::user();
        $task = $user->tasks()->where('id', $id)->first();
        if ($task) {
            return ($task->delete()) ? $this->sendResponse([], 'Task delete', 201) : $this->sendError('Task deleting error', [], 500);
        }

        return $this->sendError('Task not found');
    }

    /**
     * Отправка офера исполнителю (Заказчик)
     *
     * @param NewOfferRequest $request
     * @param int $id
     * @param ChatCreateAction $chatCreateAction
     * @return JsonResponse
     */
    public function offer(NewOfferRequest $request, int $id, ChatCreateAction $chatCreateAction): JsonResponse
    {
        $task = Task::where('id', '=', $id)->first();
        if (!$task) {
            return $this->sendError("Task #{$id} not found");
        }

        $employer = Auth::user();
        $performer = User::where('id', $request->get('user_id'))->first();

        if ($performer) {
            $offer = TaskOffer::new($id, $request->get('user_id'), $request->get('text'));

            if ($offer) {
                $chat = $chatCreateAction($employer, $performer, $task, $offer->text);

                return $this->sendResponse(['chat_id' => $chat->id], 'Offer create', 201);
            }
        }
        return $this->sendError('Error creating', [], 501);
    }


    /**
     * Отправка отзыва на исполнителей
     *
     * @param $id
     * @param EmployerReviewRequest $request
     * @return JsonResponse
     */
    public function review(EmployerReviewRequest $request, $id): JsonResponse
    {
        $reviewer = Auth::user();
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }

        if ($task->user_id !== $reviewer->id) {
            return $this->sendError('You`re an idiot?');
        }

        foreach ($request->users as $user_id)
            $review = Review::new($id, $user_id, $reviewer->id, $request->rating, $request->text);

        $users_without_review = []; //TODO: доделать проверку существования отзывов на всех исполнителей данной задачи

        return $this->sendResponse($users_without_review, 'Create successful', 201);
    }
}
