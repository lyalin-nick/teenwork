<?php

namespace App\Http\Controllers\Api\Employer\Task;

use App\Http\Controllers\Api\BaseController;
use App\Jobs\ProcessDeleteFiles;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\TaskVideo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TaskController extends BaseController
{

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

        $dates = $request->dates;

        $response_task = null;
        foreach ($dates as $date) {

            $task_attributes = $request->only('category_id', 'name', 'description', 'result', 'address', 'place_id', 'start_time', 'amount_of_workers',
                'minimum_age', 'price', 'payment_type', 'safe_deal', 'hot_work', 'account_verified');

            $task_attributes['start_date'] = $date;

            $task = Task::create($task_attributes);

            if ($task) {

                $user = $request->user();
                if ($user) {
                    $task->user_id = $user->id;
                    $task->save();
                    $user->checkEmptyRole(User::ROLE_EMPLOYER);
                }

                $task->linkToLanguages($request->get('languages'));

                if ($request->images) {
                    $result = TaskImage::createModels($request->images, $task->id);

                    if (!$result)
                        return $this->sendError('Images upload error!', [], 513);
                }

                if ($request->video) {
                    $result = TaskVideo::updateModel($request->video, $task->id);

                    if (!$result)
                        return $this->sendError('Video upload error!', [], 514);
                }

                $response_task = (!$response_task || ($response_task && strtotime($response_task->start_date) > strtotime($task->start_date))) ? $task : $response_task;
            }
        }
        if ($response_task) {
            if ($request->images)
                ProcessDeleteFiles::dispatchAfterResponse($request->images);
            if ($request->video) {
                ProcessDeleteFiles::dispatchAfterResponse($request->video);
            }

            return $this->sendResponse(['task' => $response_task->getFullInfo()], 'Task create');
        }
        return $this->sendError('Task doesnt created', [], 500);

    }

    public function edit($id, Request $request): JsonResponse
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

    public function update($id, Request $request): JsonResponse
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
                'address' => 'required|string|max:255',
                'place_id' => 'required|string',
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
                    $result = TaskImage::updateModels($request->images, $task->id);

                    if (!$result)
                        return $this->sendError('Images upload error!', [], 513);
                } else {
                    $task->cleanImages();
                }

                if ($request->video) {
                    $result = TaskVideo::updateModel($request->video, $task->id);

                    if (!$result)
                        return $this->sendError('Video upload error!', [], 514);
                } else {
                    $task->cleanVideo();
                }

                if ($request->images)
                    ProcessDeleteFiles::dispatchAfterResponse($request->images);
                if ($request->video) {
                    ProcessDeleteFiles::dispatchAfterResponse($request->video);
                }

                $task->refresh();

                return $this->sendResponse(['task' => $task->getFullInfo()], 'Task create');
            }
            return $this->sendError('Task update error', [], 502);

        }


        return $this->sendError('Task not found');
    }

    public function delete($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', $id)->first();
        if ($task) {
            return ($task->delete()) ? $this->sendResponse([], 'Task delete', 201) : $this->sendError('Task deleting error', [], 500);
        }

        return $this->sendError('Task not found');
    }

}
