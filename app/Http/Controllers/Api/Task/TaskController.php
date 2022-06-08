<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Task\ReportRequest;
use App\Http\Resources\Task\ViewResource;
use App\Models\Task;
use App\Models\TaskReport;
use Auth;
use Illuminate\Http\JsonResponse;

class TaskController extends BaseController
{
    /**
     * Получение списка собственных задач (авторизованный)
     *
     * @return JsonResponse
     */
    public function tasks(): JsonResponse
    {
        $user = Auth::user();

        $tasks = $user->getTaskList();

        return $this->sendResponse($tasks, 'Tasks');
    }

    /**
     * Данные для показа одной задачи
     *
     * @param $id
     * @return JsonResponse
     */
    public function view($id): JsonResponse
    {
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }

        $task->addViews();

        return $this->sendResponse(new ViewResource($task), 'Task was found');
    }

    /**
     * Отправка репорта
     *
     * @param $id
     * @param ReportRequest $request
     * @return JsonResponse
     */
    public function report(ReportRequest $request, $id): JsonResponse
    {
        $reporter = Auth::user();
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('User not found');
        }

        if ($task->user_id !== $reporter->id) {
            return $this->sendError('You`re an idiot?');
        }

        $report = TaskReport::new($task->id, $reporter->id, $request->title_id, $request->title, $request->text);
        if (!$report) {
            return $this->sendError('Error report creating', [], 502);
        }
        return $this->sendResponse(['report_id' => $report->id], 'Report was create', 201);
    }
}
