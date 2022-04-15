<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class HomeController extends BaseController
{
    /**
     * @param string $flag
     * @param Request $request
     * @return JsonResponse
     */
    public function index(string $flag, Request $request): JsonResponse
    {
        $params = $request->all();
        $tasks = Task::search($flag, $params);

        $tasks = $tasks->paginate(20);
        $curPage = $tasks->currentPage();
        $lastPage = $tasks->lastPage();

        $tasks = $tasks->each(function ($item, $key) {
            $item->makeHidden(['user', 'user_id', 'rating', 'images']);
            $item['user_info'] = $item->user_info;
            $item['images_links'] = $item->images_links;
            $item['status'] = $item->status_label;
        });

        return $this->sendResponse(['currentPage' => $curPage, 'lastPage' => $lastPage, 'tasks' => $tasks->toArray()], 'Success', 201);
    }

    /**
     * @param string $flag
     * @param Request $request
     * @return JsonResponse
     */
    public function map($flag, Request $request): JsonResponse
    {
        $params = $request->all();

        $params['sort'] = 'nearby';
        $params['ulat'] = $params['ulat'] ?? '39.782897';
        $params['ulng'] = $params['ulng'] ?? '-101.377715';

        $tasks = Task::search($flag, $params);

        $tasks = $tasks->get();
        $curPage = 1;//$tasks->currentPage();
        $lastPage = 1;//$tasks->lastPage();

        $tasks = $tasks->each(function ($item, $key) {
            $item->makeHidden(['user', 'user_id', 'rating', 'images']);
            $item['user_info'] = $item->user_info;
            $item['images_links'] = $item->images_links;
            $item['status'] = $item->status_label;
        });

        return $this->sendResponse(['currentPage' => $curPage, 'lastPage' => $lastPage, 'tasks' => $tasks->toArray()], 'Success', 201);
    }

    /**
     * @param Request $request
     * @param string|null $flag
     * @return JsonResponse
     */
    public function count(Request $request, string $flag = null): JsonResponse
    {
        if ($flag) {
            $params = $request->all();
            $tasks = Task::search($flag, $params);

            return $this->sendResponse($tasks->get()->count(), 'Result', 201);
        }
        return $this->sendResponse(['online' => Task::countOnline(), 'offline' => Task::countOffline()], 'Result', 201);
    }
}
