<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends BaseController
{
    /**
     * Добавить задачу в избранное
     *
     * @param int $taskId
     * @param Request $request
     * @return JsonResponse
     */
    public function add($taskId, Request $request)
    {
        $user = $request->user();

        $user->favorites()->where('task_id', '=', $taskId)->delete();

        $user->favorites()->create(['task_id' => $taskId]);

        return $this->sendResponse($user->getFavoritesId(), 'Success');
    }

    /**
     * Все задачи в избранном
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request)
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->with(['user.profile', 'task'])
            ->get();

        $tasks = [];
        foreach ($favorites as $favorite) {
            $task = $favorite->task;
            $tasks[] = [
                'id' => $task->id,
                'name' => $task->name,
                'price' => $task->price,
                'description' => $task->description,
                'hot_work' => $task->hot_work,
                'safe_deal' => $task->safe_deal,
                'start_date' => $task->start_date,
                'user_info' => $task->user_info,
                'images_links' => $task->images_links,
                'status' => $task->status_label,
            ];
        }

        return $this->sendResponse($tasks, 'Success');
    }

    /**
     * Удаление задачи из избранного
     *
     * @param int $taskId
     * @param Request $request
     * @return JsonResponse
     */
    public function remove($taskId, Request $request)
    {
        $user = $request->user();
        $user->favorites()->where('task_id', '=', $taskId)->delete();

        return $this->sendResponse($user->getFavoritesId(), 'Success');
    }
}
