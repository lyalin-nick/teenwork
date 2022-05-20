<?php

namespace App\Actions\Task;

use App\Http\Requests\Api\Task\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\TaskVideo;
use Illuminate\Support\Facades\Storage;

class TaskUpdateAction
{
    /**
     * @param $task
     * @param UpdateTaskRequest $request
     * @return false|Task
     */
    public function __invoke($task, UpdateTaskRequest $request)
    {
        if ($task->update($request->all())) {

            $task->linkToLanguages($request->get('languages'));

            if ($request->images) {
                TaskImage::updateModels($request->images, $task->id);
            } else {
                $task->cleanImages();
            }

            if ($request->video) {
                TaskVideo::updateModel($request->video, $task->id);
            } else {
                $task->cleanVideo();
            }

            if ($request->images)
                Storage::disk('public')->delete($request->images);
            if ($request->video) {
                Storage::disk('public')->delete($request->video);
            }

            $task->refresh();

            return $task;
        }
        return false;
    }
}
