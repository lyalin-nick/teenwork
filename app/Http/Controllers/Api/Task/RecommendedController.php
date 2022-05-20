<?php

namespace App\Http\Controllers\Api\Task;

use App\Actions\Task\RecommendedSearchAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\User\ShortInfoResource;
use App\Models\Profile;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendedController extends BaseController
{

    /**
     * Получение рекомендуемых пользователей (Заказчик)
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function recommended(int $id, Request $request, RecommendedSearchAction $searchAction): JsonResponse
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
                'users' => ShortInfoResource::collection($profiles)
            ], 'Users');
        }

        return $this->sendError('Task not found');
    }

}
