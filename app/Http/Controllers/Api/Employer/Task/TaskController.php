<?php

namespace App\Http\Controllers\Api\Employer\Task;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'result' => 'required|string',
            'images' => 'array|max:10',
            'images.*' => 'integer',
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
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $task = Task::create($request->all());

        if ($task) {
            $user = $request->user();
            if ($user) {
                $task->user_id = $request->user()->id;
                $task->save();
                $user->checkEmptyRole(User::ROLE_EMPLOYER);
            }
            if ($request->addresses) $task->createTaskAddresses($request->addresses);
            $task->linkToLanguages($request->get('languages'));
            if ($request->images) $task->updateTaskImages($request->images);

            return $this->sendResponse(['task' => $task->id], 'Task create');
        }

        return $this->sendError('Task doesnt created');

    }


    public function images(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2024',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        return $this->uploadImages($request->images, TaskImage::class);
    }
}
