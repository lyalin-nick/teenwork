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
     * @param string $video_path
     * @param integer $task_id
     */
    public static function updateModel(string $video_path, int $task_id)
    {
        if (Storage::disk('public')->exists($video_path)) {
            $video_model = self::where('task_id', $task_id)->first();
            if (!$video_model) {
                $video_model = self::create(['task_id' => $task_id]);
            }

            if ($video_model) {
                $video_model->copyVideo($video_path, $task_id);
            }
        }
    }

    protected static function booted()
    {
        static::deleted(function (self $task_video) {
            $task_video->checkExistVideoAndDelete();
        });
    }

    public function cleanUp(): void
    {
        $this->checkExistVideoAndDelete();
        $this->update(['path' => null, 'name' => null, 'ext' => null]);
    }

    public function task()
    {
        $this->belongsTo(Task::class, 'task_id');
    }

    /**
     * Получение ссылки на видео
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->getVideoLink();
    }

    /*
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
    */
}
