<?php

namespace App\Observers;

use App\Models\Chat;
use App\Models\Task;
use App\Services\Google\GoogleMapService;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     *
     * @param Task $task
     * @return void
     */
    public function created(Task $task)
    {
        $profile = $task->profile;
        if ($profile)
            $profile->addNumberEmployerTask();
    }

    /**
     * Handle the Task "saving" event.
     *
     * @param Task $task
     * @return void
     */
    public function saving(Task $task)
    {
        $task->expired_at = date('Y-m-d H:i:s', strtotime($task->start_date . ' ' . $task->start_time));

        if ($task->place_id) {
            $service = new GoogleMapService();
            $coords = $service->coords($task->place_id);
            $coords = $coords ?: ['lat' => 53.213672, 'lng' => 45.061300];
            $task->lat = $coords['lat'];
            $task->lng = $coords['lng'];
        }
    }

    /**
     * Handle the Task "deleted" event.
     *
     * @param Task $task
     * @return void
     */
    public function deleted(Task $task)
    {
        $task->languages()->detach();

        $task->cleanVideo();
        $task->cleanImages();

        $chats = Chat::where('type', '=', Chat::TYPE_TASK)->where('identifier', '=', $task->id)
            ->update(['status' => Chat::STATUS_HISTORY]);
    }
}
