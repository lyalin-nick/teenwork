<?php

namespace App\Http\Controllers\Api\Home;

use App\Actions\Task\TaskSearchAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Task\PreviewResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class HomeController extends BaseController
{
    /**
     * Получение списка задач для главного экрана
     *
     * @param $flag
     * @param Request $request
     * @param TaskSearchAction $searchAction
     * @return JsonResponse
     */
    public function index($flag, Request $request, TaskSearchAction $searchAction): JsonResponse
    {
        $params = $request->all();
        $tasks = $searchAction($flag, $params);

        $tasks = $tasks->paginate(20);
        $curPage = $tasks->currentPage();
        $lastPage = $tasks->lastPage();

        return $this->sendResponse([
            'currentPage' => $curPage,
            'lastPage' => $lastPage,
            'tasks' => PreviewResource::collection($tasks->items())
        ], 'Success', 201);
    }

    /**
     * Получение задач для карты
     *
     * @param $flag
     * @param Request $request
     * @param TaskSearchAction $searchAction
     * @return JsonResponse
     */
    public function map($flag, Request $request, TaskSearchAction $searchAction): JsonResponse
    {
        $params = $request->all();

        $params['sort'] = 'nearby';
        $params['ulat'] = $params['ulat'] ?? '39.782897';
        $params['ulng'] = $params['ulng'] ?? '-101.377715';

        $tasks = $searchAction($flag, $params);

        $tasks = $tasks->get();
        $curPage = 1;//$tasks->currentPage();
        $lastPage = 1;//$tasks->lastPage();

        return $this->sendResponse([
            'currentPage' => $curPage,
            'lastPage' => $lastPage,
            'tasks' => PreviewResource::collection($tasks)
        ], 'Success', 201);
    }

    /**
     * Получение кол-ва онлайн/офлайн задач
     *
     * @param TaskSearchAction $searchAction
     * @param Request $request
     * @param string|null $flag
     * @return JsonResponse
     */
    public function count(TaskSearchAction $searchAction, Request $request, string $flag = null): JsonResponse
    {
        if ($flag) {
            $params = $request->all();
            $tasks = $searchAction($flag, $params);

            return $this->sendResponse($tasks->get()->count(), 'Result', 201);
        }
        return $this->sendResponse(['online' => Task::countOnline(), 'offline' => Task::countOffline()], 'Result', 201);
    }
}
