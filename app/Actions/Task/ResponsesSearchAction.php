<?php

namespace App\Actions\Task;

use App\Http\Resources\Task\RespondedUsersResource;

class ResponsesSearchAction
{
    public function __invoke($task, $params)
    {
        $responses = $task->responses()
            ->select('*')
            ->with(['user', 'user.taskOffers' => function($query) use ($task){
                $query->where('task_id', '=', $task->id);
            }]);

        $params['sort'] = $params['sort'] ?? 'rating';

        switch ($params['sort']) {
            case 'rating':
                $responses->ratingOrder();
                break;
            case 'nearby':
                $responses->nearby($task->lat, $task->lng);
                break;
        }

        return RespondedUsersResource::collection($responses->get());
    }
}
