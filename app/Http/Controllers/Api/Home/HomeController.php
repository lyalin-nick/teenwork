<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use Illuminate\Http\Request;


class HomeController extends BaseController
{
    public function index($flag, Request $request)
    {
        $params = $request->all();
        $tasks = Task::search($flag, $params);

        $tasks = $tasks->paginate(20);
        $curPage = $tasks->currentPage();
        $lastPage = $tasks->lastPage();

        $tasks = $tasks->each(function ($item, $key) {
            $item->makeHidden(['user', 'images']);
            $item['user_info'] = $item->user_info;
            $item['images_links'] = $item->images_links;
            $item['status'] = $item->status_label;
        });

        return $this->sendResponse(['currentPage' => $curPage, 'lastPage' => $lastPage, 'tasks' => $tasks->toArray()], 'Success', 201);
    }

    public function map($flag, Request $request)
    {
        $params = $request->all();
        $tasks = Task::search($flag, $params, true);

        $tasks = $tasks->get();

        $tasks = $tasks->each(function ($item, $key) {
            $item->makeHidden(['user', 'images']);
            $item['user_info'] = $item->user_info;
            $item['images_links'] = $item->images_links;
            $item['status'] = $item->status_label;
        });

        return $this->sendResponse($tasks->toArray(), 'Success', 201);
    }

    public function count($flag = null, Request $request)
    {
        if ($flag) {
            $params = $request->all();
            $tasks = Task::search($flag, $params);

            return $this->sendResponse($tasks->count(), 'Result', 201);
        }
        return $this->sendResponse(['online' => Task::countOnline(), 'offline' => Task::countOffline()], 'Result', 201);
    }
}
