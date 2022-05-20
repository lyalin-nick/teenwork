<?php

namespace App\Actions\Task;

use App\Http\Resources\Response\ViewResource;

class ResponsesSearchAction
{
    public function __invoke($task, $params)
    {
        $responses = $task->responses()
            ->select('*')
            ->with('user');

        $params['sort'] = $params['sort'] ?? 'rating';

        switch ($params['sort']) {
            case 'rating':
                $responses->ratingOrder();
                break;
            case 'nearby':
                $responses->nearby($task->lat, $task->lng);
                break;
        }

        return ViewResource::collection($responses->get());
    }
}
