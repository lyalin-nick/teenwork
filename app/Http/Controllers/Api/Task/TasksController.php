<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TasksController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = $user->getTaskList();

        return $this->sendResponse($tasks, 'Tasks not found');
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function view($id, Request $request)
    {
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }
        $task_info = $task->getFullInfo();

        $task->addViews();

        return $this->sendResponse($task_info, 'Task was found');
    }
}
