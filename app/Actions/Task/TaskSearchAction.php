<?php

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

class TaskSearchAction
{
    /**
     * @param string $flag
     * @param $params
     * @return Builder
     */
    public function __invoke(string $flag, $params): Builder
    {
        $tasks = Task::home($flag);

        if (isset($params['search']))
            $tasks->hasPhrase($params['search']);

        if (isset($params['categories']))
            $tasks->categoriesIn($params['categories']);

        if (isset($params['languages']))
            $tasks->languagesIn($params['languages']);

        if (isset($params['place_id'])) {
            $tasks->address($params['place_id']);
        }

        if (isset($params['days'])) {
            $tasks->startDays($params['days']);
        }

        if (isset($params['price']))
            $tasks->priceFrom($params['price']);

        if (isset($params['safe_deal']) && filter_var($params['safe_deal'], FILTER_VALIDATE_BOOLEAN))
            $tasks->flagSafeDeal($params['safe_deal']);

        if (isset($params['hot_work']) && filter_var($params['hot_work'], FILTER_VALIDATE_BOOLEAN))
            $tasks->flagHotWork($params['hot_work']);

        if (isset($params['answers']) && filter_var($params['answers'], FILTER_VALIDATE_BOOLEAN))
            $tasks->notResponse($params['answers']);


        $params['sort'] = $params['sort'] ?? 'default';
        switch ($params['sort']) {
            case "price":
                $tasks->priceOrder();
                break;
            case "-price":
                $tasks->priceOrder('asc');
                break;
            case "rating":
                $tasks->ratingDesc();
                break;
            case "nearby":
                if (isset($params['ulat']) && $params['ulng'])
                    $tasks->nearby($params['ulat'], $params['ulng']);
                else
                    $tasks->nearby();
                break;
            default:
                $tasks->orderBy('start_date');
                break;
        }
        $tasks->orderBy('hot_work', 'desc');

        return $tasks;
    }
}
