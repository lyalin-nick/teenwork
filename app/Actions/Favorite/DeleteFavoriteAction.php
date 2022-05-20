<?php

namespace App\Actions\Favorite;

use App\Models\User;

class DeleteFavoriteAction
{

    public function __invoke(User $user, $identify): bool
    {
        if ($user->isPerformer())
            $user->favoriteTasks()->where('task_id', '=', $identify)->delete();

        $user->favoritePerformers()->where('performer_id', '=', $identify)->delete();

        return true;
    }
}
