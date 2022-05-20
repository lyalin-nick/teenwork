<?php

namespace App\Actions\Favorite;

use App\Http\Resources\Task\PreviewResource;
use App\Http\Resources\User\ShortInfoResource;
use App\Models\User;

class ListFavoriteAction
{

    /**
     * Получить список избранных
     *
     * @param User $user
     * @return array
     */
    public function __invoke(User $user): array
    {
        if ($user->isPerformer()) {
            $favorites = $user->favoriteTasks()
                ->with(['user.profile', 'task'])
                ->paginate(20);

            $curPage = $favorites->currentPage();
            $lastPage = $favorites->lastPage();

            $tasks = [];
            foreach ($favorites as $favorite) {
                $task = $favorite->task;
                if (!$task) {
                    $favorite->delete();
                } else {
                    $tasks[] = $task;
                }
            }

            return ['currentPage' => $curPage, 'lastPage' => $lastPage, 'tasks' => PreviewResource::collection($tasks)];
        }

        $favorites = $user->favoritePerformers()
            ->with(['performer.profile'])
            ->paginate(20);

        $curPage = $favorites->currentPage();
        $lastPage = $favorites->lastPage();

        $performers = [];
        foreach ($favorites as $favorite) {
            $performer = $favorite->performer;
            if (!$performer) {
                $favorite->delete();
            }
            $performers[] = $performer;
        }

        return ['currentPage' => $curPage, 'lastPage' => $lastPage, 'performers' => ShortInfoResource::collection($performers)];
    }
}
