<?php

namespace App\Http\Controllers\Api\Task;

use App\Actions\Task\RecommendedSearchAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Task\RecommendedUsersResource;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendedController extends BaseController
{

    /**
     * Получение рекомендуемых пользователей (Заказчик)
     *
     * @param $id
     * @param Request $request
     * @param RecommendedSearchAction $searchAction
     * @return JsonResponse
     */
    public function recommended($id, Request $request, RecommendedSearchAction $searchAction): JsonResponse
    {
        $user = Auth::user();
        $task = $user->tasks()->where('id', '=', $id)->first();
        if ($task) {
            $profiles = $searchAction($task, $request->only('sort'));
            $paginate = $profiles->paginate(20);

            $curPage = $paginate->currentPage();
            $lastPage = $paginate->lastPage();
            $profiles = $paginate->items();

            return $this->sendResponse([
                'currentPage' => $curPage,
                'lastPage' => $lastPage,
                'users' => RecommendedUsersResource::collection($profiles)
            ], 'Users');
        }

        return $this->sendError('Task not found');
    }

}
