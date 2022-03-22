<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer $id
 * @property integer $task_id
 * @property string $path
 * @property string $name
 * @property string $ext
 * @property string $alt
 * @property string $pos
 *
 * @property Task $task
 *
 * @mixin Builder
 */
class TaskImage extends Model
{
    use HasFactory, ImageTrait;

    protected $configImages = [
        '_mini' => [
            'width' => 128,
            'height' => 128
        ]
    ];

    protected $fillable = [
        'name', 'task_id', 'name', 'alt', 'path', 'pos', 'ext'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Создание моделей прикрепленных фото к задаче и загрузка фото
     * @param array $images
     * @param $task_id
     * @return bool
     */
    public static function createModels(array $images, $task_id): bool
    {

        if ($images) {
            $models = [];
            foreach ($images as $image_path) {
                if (Storage::disk('public')->exists($image_path)) {
                    $model = new TaskImage([
                        'task_id' => $task_id
                    ]);

                    if ($model->save() && $model->copyImage($image_path, $task_id)) {
                        $models[] = $model;
                    }
                }
            }
            return count($images) === count($models);
        }

        return false;
    }

    /*
    public function getNewFullPath($new_path)
    {
        return $this->path . $new_path . $this->name . '.' . $this->ext;
    }
    */

    protected static function booted()
    {
        static::deleted(function (self $task_image) {
            $task_image->checkExistImageAndDelete();
        });
    }

    public function task()
    {
        $this->belongsTo(Task::class, 'task_id');
    }

}
