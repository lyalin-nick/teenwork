<?php

namespace App\Actions\Favorite;

use App\Models\Task;
use App\Models\User;

class AddFavoriteAction
{

    public function __invoke(User $user, $identify): bool
    {
        if ($user->isPerformer()) {
            $task = Task::where('id', $identify)->first();
            if (!$task) {
                return false;
            }
            $user->favoriteTasks()->where('task_id', '=', $identify)->delete();

            $link = $user->favoriteTasks()->create(['task_id' => $identify]);
        } else {
            $performer = User::where('id', $identify)->first();
            if (!$performer) {
                return false;
            }

            $user->favoritePerformers()->where('performer_id', '=', $identify)->delete();

            $link = $user->favoritePerformers()->create(['performer_id' => $identify]);
        }
        return !empty($link);
    }
}
