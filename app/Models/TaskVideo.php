<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'name', 'path', 'ext'
    ];

    /**
     * Создание модели видео
     *
     * @param $video_base64 string Видео в формате base64
     * @param $task_id integer Идентификатор задачи
     * @return bool
     */
    public static function createModel($video_base64, $task_id)
    {
        $video = self::create(['task_id' => $task_id]);

        if ($video) {
            return $video->uploadVideo($video_base64);
        }
        return false;
    }

    public function task()
    {
        $this->belongsTo(Task::class, 'task_id');
    }

    public function getPath()
    {
        if ($this->path && $this->name && $this->ext) {
            $full_path = Storage::disk('public')->path($this->path . $this->name . '.' . $this->ext);
            return is_file($full_path) ? $full_path : null;
        }
        return null;
    }

    public function getLink()
    {
        if ($this->getPath()) {
            return Storage::disk('public')->url($this->path . $this->name . '.' . $this->ext);
        }
        return null;
    }

    public function uploadVideo($video_base64): bool
    {
        $video_file = base64_decode($video_base64);

        $video_file_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($this->task_id)
            $video_file_path .= $this->task_id . DIRECTORY_SEPARATOR;

        $ext = 'mp4'; //TODO: подумать как выудить расширение картинки

        try {
            if (is_file(Storage::disk('public')->path($video_file_path . $this->id . '.' . $ext))) {
                Storage::delete($video_file_path . $this->id . '.' . $ext);
            }
            $created = Storage::disk('public')->put($video_file_path . $this->id . '.' . $ext, $video_file);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $created = false;
        }

        if ($created) {

            $this->path = $video_file_path;
            $this->name = $this->id;
            $this->ext = $ext;

            return $this->save();
        }

        return false;
    }

}
