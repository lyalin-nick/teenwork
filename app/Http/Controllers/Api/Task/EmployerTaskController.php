<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Models\Review;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\TaskOffer;
use App\Models\TaskVideo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployerTaskController extends BaseController
{

    /**
     * Создание задачи (Заказчик)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'result' => 'required|string',
            'images' => 'array|max:10',
            'images.*' => 'string',
            'video' => 'nullable|string',
            'address' => 'required|string|max:255',
            'place_id' => 'required|string',
            'dates' => 'required|array',
            'dates.*' => "string",
            'start_time' => 'required|string',
            'amount_of_workers' => 'required|integer',
            'minimum_age' => 'required|integer',
            'languages' => 'array',
            'languages.*' => 'integer',
            'price' => 'required|integer',
            'payment_type' => 'required|string',
            'safe_deal' => 'required|boolean',
            'hot_work' => 'required|boolean',
            'account_verified' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $dates = Arr::sort($request->dates);

        $response_task = null;
        foreach ($dates as $date) {

            $task_attributes = $request->only('category_id', 'name', 'description', 'result', 'address', 'place_id', 'start_time', 'amount_of_workers',
                'minimum_age', 'price', 'payment_type', 'safe_deal', 'hot_work', 'account_verified');

            $task_attributes['start_date'] = $date;
            $user = $request->user();
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

            return $this->sendResponse(['task' => $response_task->getFullInfo(), 'employers' => $response_task->getRecommendedPerformers()], 'Task create');
        }

        return $this->sendError('Task doesnt created', [], 500);

    }

    /**
     * Получение данных о задаче для редактирования (Заказчик)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', $id)->first();

        if ($task) {
            $task_arr = [
                'id' => $task->id,
                'category_id' => $task->category_id,
                'name' => $task->name,
                'description' => $task->description,
                'result' => $task->result,
                'address' => $task->address,
                'place_id' => $task->place_id,
                'start_date' => $task->start_date,
                'start_time' => $task->start_time,
                'amount_of_workers' => $task->amount_of_workers,
                'minimum_age' => $task->minimum_age,
                'price' => $task->price,
                'payment_type' => $task->payment_type,
                'safe_deal' => $task->safe_deal,
                'hot_work' => $task->hot_work,
                'account_verified' => $task->account_verified,
                'languages' => $task->getLanguagesAsArray(),
                'images' => $task->getImagesAsArray(),
                'video' => ($task->video && $task->video->hasVideo()) ? [
                    'link' => $task->video->getLink(),
                    'path' => $task->video->getFullPath()
                ] : null
            ];

            return $this->sendResponse($task_arr, 'Task info', 201);
        }

        return $this->sendError('Task not found');
    }

    /**
     * Обновление существующей задачи (Заказчик)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', $id)->first();

        if ($task) {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'result' => 'required|string',
                'images' => 'array|max:10',
                'images.*' => 'string',
                'video' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'place_id' => 'nullable|string',
                'start_date' => 'required|string',
                'start_time' => 'required|string',
                'amount_of_workers' => 'required|integer',
                'minimum_age' => 'required|integer',
                'languages' => 'array',
                'languages.*' => 'integer',
                'price' => 'required|integer',
                'payment_type' => 'required|string',
                'safe_deal' => 'required|boolean',
                'hot_work' => 'required|boolean',
                'account_verified' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), Response::HTTP_BAD_REQUEST);
            }


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

                return $this->sendResponse(['task' => $task->getFullInfo(), 'employers' => $task->getRecommendedPerformers()], 'Task create');
            }
            return $this->sendError('Task update error', [], 502);

        }

        return $this->sendError('Task not found');
    }

    /**
     * Удаление задачи (Заказчик)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', $id)->first();
        if ($task) {
            return ($task->delete()) ? $this->sendResponse([], 'Task delete', 201) : $this->sendError('Task deleting error', [], 500);
        }

        return $this->sendError('Task not found');
    }

    /**
     * Получение рекомендуемых пользователей (Заказчик)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function recommended(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', $id)->first();
        if ($task) {
            return $this->sendResponse($task->getRecommendedPerformers(), 'Users');
        }

        return $this->sendError('Users not found');
    }

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
            return $this->sendResponse($task->responses_info, 'Responses');
        }

        return $this->sendError('Task not found');
    }

    /**
     * Отправка офера исполнителю (Заказчик)
     * TODO:недоделан
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function offer($id, Request $request): JsonResponse
    {
        if (!Task::where('id', '=', $id)->first()) {
            return $this->sendError("Task #{$id} not found");
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();

        $offer = TaskOffer::new($id, $user->id, $request->get('text'));

        if ($offer) {
            return $this->sendResponse([], 'Offer create', 201);
        }

        return $this->sendError('Error creating', [], 501);
    }


    /**
     * Отправка отзыва на исполнителей
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function review($id, Request $request)
    {
        $reviewer = $request->user();
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }

        if ($task->user_id == !$reviewer->id) {
            return $this->sendError('You`re an idiot?');
        }

        $validator = Validator::make($request->all(), [
            'users' => 'required|array',
            'users.*' => 'integer',
            'rating' => 'required|integer',
            'text' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        foreach ($request->users as $user_id)
            $review = Review::new($id, $user_id, $reviewer->id, $request->rating, $request->text);

        $users_without_review = []; //TODO: доделать проверку существования отзывов на всех исполнителей данной задачи

        return $this->sendResponse($users_without_review, 'Create successful', 201);
    }
}
