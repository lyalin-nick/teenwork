<?php

namespace App\Actions\Task;

use App\Http\Requests\Api\Task\NewTaskRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class TaskStoreAction
{
    /**
     * Создает задачи и возвращает первую задачу близкую к текущему дню
     * @param NewTaskRequest $request
     * @return Task|Model|null
     */
    public function __invoke(NewTaskRequest $request)
    {
        $dates = Arr::sort($request->dates);

        $response_task = null;
        foreach ($dates as $date) {

            $task_attributes = $request->only('category_id', 'name', 'description', 'result', 'address', 'place_id', 'start_time', 'amount_of_workers',
                'minimum_age', 'price', 'payment_type', 'safe_deal', 'hot_work', 'account_verified');

            $task_attributes['start_date'] = $date;
            $user = Auth::user();
            $user->checkEmptyRole(User::ROLE_EMPLOYER);
            $task_attributes['user_id'] = $user->id;

            $task = Task::new($task_attributes, $request->get('languages'), $request->images, $request->video);

            if (!$response_task && $task) {
                $response_task = $task;
            }
        }

        return $response_task;
    }
}
