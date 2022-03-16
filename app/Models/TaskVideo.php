<?php

namespace App\Models;

use App\Models\Traits\VideoTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer $task_id
 * @property string $name
 * @property string $path
 * @property string $ext
 *
 * @mixin Builder
 */
class TaskVideo extends Model
{
    use HasFactory, VideoTrait;

    protected $fillable = [
        'task_id', 'name', 'path', 'ext'
    ];

    /**
     * Создание модели видео
     *
     * @param $video_path string Путь до видео
     * @param $task_id integer Идентификатор задачи
     * @return bool
     */
    public static function createModel($video_path, $task_id)
    {
        if (Storage::disk('public')->exists($video_path)) {
            $video = self::create(['task_id' => $task_id]);

            if ($video) {
                return $video->moveVideo($video_path, $task_id);
            }
        }
        return false;
    }

    public function task()
    {
        $this->belongsTo(Task::class, 'task_id');
    }

    public function getLink()
    {
        return $this->getVideoLink();
    }

    public function uploadVideo($video_uri): bool
    {
        return $this->uploadVideoFromUri($video_uri, $this->task_id);
    }

}
