<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
class TaskImage extends Model
{
    use HasFactory;
    use ImageTrait;

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

    public static function createModels(array $images, $task_id): bool
    {

        if ($images) {
            $models = [];
            foreach ($images as $image) {
                $model = new TaskImage([
                    'task_id' => $task_id
                ]);

                if ($model->save() && $model->uploadImageFromBase64($image, $task_id)) {
                    $models[] = $model;
                }
            }
            return count($images) === count($models);
        }

        return false;
    }

    public function getNewFullPath($new_path)
    {
        return $this->path . $new_path . $this->name . '.' . $this->ext;
    }

    public function profile()
    {
        $this->belongsTo(Task::class, 'id', 'task_id');
    }
}
