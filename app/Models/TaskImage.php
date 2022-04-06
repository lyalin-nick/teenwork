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
        'task_id', 'path', 'name', 'ext', 'alt', 'pos'
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
                    $model = self::create([
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

    /**
     * Создание моделей прикрепленных фото к задаче и загрузка фото
     * @param array $images
     * @param $task_id
     * @return bool
     */
    public static function updateModels(array $images, $task_id): bool
    {
        if ($images) {
            $exists_models = TaskImage::where('task_id', $task_id)->get();

            $updated_models = [];

            foreach ($images as $i => $image_path) {
                if (Storage::disk('public')->exists($image_path)) {
                    if (isset($exists_models[$i])) {
                        $model = $exists_models[$i];
                        if ($model->copyImage($image_path, $task_id)) {
                            $updated_models[$i] = $model;
                        }
                    } else {
                        $model = self::create(['task_id' => $task_id]);

                        if ($model->save() && $model->copyImage($image_path, $task_id)) {
                            $updated_models[$i] = $model;
                        }
                    }
                }
            }

            foreach ($exists_models as $pos => $exists_model) {
                if (!isset($updated_models[$pos])) {
                    $exists_model->delete();
                }
            }
            return count($images) === count($updated_models);
        }

        return false;
    }


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
