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
        $profiles = Profile::query()
            ->select('profiles.*')
            ->addSelect(\DB::raw("(SELECT id FROM task_offers WHERE task_id={$task->id} AND user_id=profiles.user_id) as offer_id"))
            ->addSelect(\DB::raw("(SELECT chat_id FROM task_offers WHERE task_id={$task->id} AND user_id=profiles.user_id) as offer_chat_id"))
            ->addSelect(\DB::raw("(SELECT id FROM chats WHERE type='task' AND identifier={$task->id} AND (SELECT COUNT(*) FROM chat_user WHERE chats.id=chat_user.chat_id)=2 AND (SELECT COUNT(*) FROM chat_user WHERE chats.id=chat_user.chat_id AND chat_user.user_id IN (profiles.user_id, {$task->user_id}))=2) as chat_id"))
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
