<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Task\ReportRequest;
use App\Models\Task;
use App\Models\TaskReport;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $user = Auth::user();

        $tasks = $user->getTaskList();

        return $this->sendResponse($tasks, 'Tasks');
    }

    /**
     * Данные для показа одной задачи
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function view($id)
    {
        $task = Task::where('id', '=', $id)->first();

        if (!$task) {
            return $this->sendError('Task not found');
        }
        $task_info = $task->getFullInfo();

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $task_info['favorite'] = $user->checkFavorite($id);
        }

        $task->addViews();

        return $this->sendResponse($task_info, 'Task was found');
    }

    /**
     * Отправка репорта
     *
     * @param $id
     * @param ReportRequest $request
     * @return JsonResponse
     */
    public function report(ReportRequest $request, $id)
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
