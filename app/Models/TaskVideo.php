<?php

namespace App\Models;

use App\Models\Traits\VideoTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer $id
 * @property integer $task_id
 * @property string $name
 * @property string $path
 * @property string $ext
 *
 * @property Task $task
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
                return $video->copyVideo($video_path, $task_id);
            }
        }
        return false;
    }



    protected static function booted()
    {
        static::deleted(function (self $task_video) {
            $task_video->checkExistVideoAndDelete();
        });
    }

    public function task()
    {
        $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Загрузка видео к задаче
     * @param $video_uri
     * @return bool
     */
//    public function uploadVideo($video_uri): bool
//    {
//        return $this->uploadVideoFromUri($video_uri, $this->task_id);
//    }
    /**
     * Получение ссылки на видео
     * @return string|null
     */
    public function getLink()
    {
        return $this->getVideoLink();
    }

}
