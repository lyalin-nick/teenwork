<?php

namespace App\Http\Controllers\Api\Employer\Task;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\TaskVideo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
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
            "addresses" => 'required|array',
            'addresses.*' => 'array',
            "dates" => 'required|array',
            "dates.*" => "string",
            "start_time" => 'required|string',
            "amount_of_workers" => 'required|integer',
            "minimum_age" => 'required|integer',
            "languages" => "array",
            "languages.*" => "integer",
            "price" => "required|integer",
            "payment_type" => "required|string",
            "safe_deal" => "required|boolean",
            "hot_work" => "required|boolean",
            "account_verified" => "required|boolean"
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $dates = $request->dates;

        $response_task = null;
        foreach ($dates as $date) {

            $task_attributes = $request->only('category_id', 'name', 'description', 'result', 'start_time', 'amount_of_workers',
                'minimum_age', 'price', 'payment_type', 'safe_deal', 'hot_work', 'account_verified');

            $task_attributes['start_date'] = $date;

            $task = Task::create($task_attributes);

            if ($task) {

                $user = $request->user();
                if ($user) {
                    $task->user_id = $request->user()->id;
                    $task->save();
                    $user->checkEmptyRole(User::ROLE_EMPLOYER);
                }

                $task->linkToLanguages($request->get('languages'));

                if ($request->addresses) {
                    $result = $task->createTaskAddresses($request->addresses);

                    if (!$result)
                        return $this->sendError('Addresses create error!', [], 512);
                }

                if ($request->images) {
                    $result = TaskImage::createModels($request->images, $task->id);

                    if (!$result)
                        return $this->sendError('Images upload error!', [], 513);
                }

                if ($request->video) {
                    $result = TaskVideo::createModel($request->video, $task->id);

                    if (!$result)
                        return $this->sendError('Video upload error!', [], 514);
                }

                $response_task = (!$response_task || ($response_task && strtotime($response_task->start_date) > strtotime($task->start_date))) ? $task : $response_task;
            }
        }
        if ($response_task) {
            if ($request->images)
                Storage::disk('public')->delete($request->images);
            if ($request->video) {
                Storage::disk('public')->delete($request->video);
            }

            return $this->sendResponse(['task' => $response_task->getFullInfo()], 'Task create');
        }
        return $this->sendError('Task doesnt created', [], 500);

    }


    public function delete($id, Request $request)
    {
        $user = $request->user();
        $task = $user->tasks()->where('id', $id)->first();
        if ($task) {
            return ($task->delete()) ? $this->sendResponse([], 'Task delete', 201) : $this->sendError('Task deleting error', [], 500);
        }

        return $this->sendError('Task not found');
    }

}
