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

        return $this->sendResponse($tasks, 'Success', 201);
    }

    public function count()
    {
        return $this->sendResponse(['online' => Task::countOnline(), 'offline' => Task::countOffline()], 'Result', 201);
    }
}
