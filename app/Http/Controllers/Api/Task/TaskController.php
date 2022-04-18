<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\TaskReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends BaseController
{
    /**
     * Получение списка собственных задач (авторизованный)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tasks(Request $request)
    {
        $user = $request->user();

        $tasks = $user->getTaskList();

        return $this->sendResponse($tasks, 'Tasks not found');
    }

    /**
     * Данные для показа одной задачи
     *
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

    /**
     * Отправка репорта
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function report($id, Request $request)
    {
        $reporter = $request->user();
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('User not found');
        }

        if ($task->user_id == !$reporter->id) {
            return $this->sendError('You`re an idiot?');
        }

        $validator = Validator::make($request->all(), [
            'title_id' => 'required|integer',
            'title' => 'string',
            'text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $report = TaskReport::new($task->id, $reporter->id, $request->title_id, $request->title, $request->text);
        if (!$report) {
            return $this->sendError('Error report creating', [], 502);
        }
        return $this->sendResponse(['report_id' => $report->id], 'Report was create', 201);


    }
}
