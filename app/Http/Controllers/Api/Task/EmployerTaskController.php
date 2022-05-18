<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Task\EmployerReviewRequest;
use App\Http\Requests\Api\Task\NewOfferRequest;
use App\Http\Requests\Api\Task\NewTaskRequest;
use App\Http\Requests\Api\Task\UpdateTaskRequest;
use App\Http\Resources\Task\UpdateResource;
use App\Http\Resources\Task\ViewResource;
use App\Models\Review;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\TaskOffer;
use App\Models\TaskVideo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployerTaskController extends BaseController
{

    /**
     * Создание задачи (Заказчик)
     *
     * @param NewTaskRequest $request
     * @return JsonResponse
     */
    public function store(NewTaskRequest $request): JsonResponse
    {
        $dates = Arr::sort($request->dates);

        $response_task = null;
        foreach ($dates as $date) {

            $task_attributes = $request->only('category_id', 'name', 'description', 'result', 'address', 'place_id', 'start_time', 'amount_of_workers',
                'minimum_age', 'price', 'payment_type', 'safe_deal', 'hot_work', 'account_verified');

            $task_attributes['start_date'] = $date;
            $user = Auth::user();
            $user->checkEmptyRole(User::ROLE_EMPLOYER);
            $task_attributes['user_id'] = $user->id;

            $task = Task::new($task_attributes, $request->get('languages'), $request->images, $request->video);

            if (!$response_task && $task) {
                $response_task = $task;
            }
        }
        if ($response_task) {
            if ($request->images)
                Storage::disk('public')->delete($request->images);
            if ($request->video) {
                Storage::disk('public')->delete($request->video);
            }

            return $this->sendResponse(['task' => new ViewResource($response_task), 'employers' => $response_task->getRecommendedPerformers()], 'Task create');
        }

        return $this->sendError('Task doesnt created', [], 500);

    }

    /**
     * Получение данных о задаче для редактирования (Заказчик)
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
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
     * @param $id
     * @param UpdateTaskRequest $request
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, $id): JsonResponse
    {
        $user = \Auth::user();
        $task = $user->tasks()->where('id', $id)->first();

        if ($task) {
            if ($task->update($request->all())) {

                $task->linkToLanguages($request->get('languages'));

                if ($request->images) {
                    TaskImage::updateModels($request->images, $task->id);
                } else {
                    $task->cleanImages();
                }

                if ($request->video) {
                    TaskVideo::updateModel($request->video, $task->id);
                } else {
                    $task->cleanVideo();
                }

                if ($request->images)
                    Storage::disk('public')->delete($request->images);
                if ($request->video) {
                    Storage::disk('public')->delete($request->video);
                }

                $task->refresh();

                return $this->sendResponse(['task' => new ViewResource($task), 'employers' => $task->getRecommendedPerformers()], 'Task create');
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
     * TODO:недоделан
     *
     * @param $id
     * @param NewOfferRequest $request
     * @return JsonResponse
     */
    public function offer(NewOfferRequest $request, $id): JsonResponse
    {
        if (!Task::where('id', '=', $id)->first()) {
            return $this->sendError("Task #{$id} not found");
        }

        $user = Auth::user();

        $offer = TaskOffer::new($id, $user->id, $request->get('text'));

        if ($offer) {
            return $this->sendResponse([], 'Offer create', 201);
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
    public function review(EmployerReviewRequest $request, $id)
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
