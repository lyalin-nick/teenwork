<?php

namespace App\Actions\Task;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RecommendedSearchAction
{

    public function __invoke($task, $params = null): Builder
    {
        $task_languages = $task->getLanguagesAsArray();
        $category_id = $task->category_id;
        $profiles = Profile::query()->select('profiles.*')
            ->where('user_id', '!=', $task->user_id)
            ->whereHas('user', function ($query) {
                $query->where('role', '=', User::ROLE_PERFORMER);
            })
            ->whereHas('languages', function ($lang_query) use ($task_languages) {
                $lang_query->whereIn('id', $task_languages);
            })
            ->whereHas('categories', function ($cat_query) use ($category_id) {
                $cat_query->where('id', $category_id);
            })
            ->with('user');

        $params['sort'] = $params['sort'] ?? 'nearby';

        switch ($params['sort']) {
            case 'rating':
                $profiles->orderBy('profiles.rating', 'desc');
                break;
            case 'nearby':
                $profiles->nearby($task->lat, $task->lng);
                break;
        }

        return $profiles;
    }
}
