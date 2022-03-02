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

    protected $fillable = [
        'name', 'task_id', 'name', 'alt', 'path', 'pos', 'ext'
    ];

    public static function createModels(array $images, $task_id)
    {
        if ($images) {
            $models = [];
            foreach ($images as $image) {
                $model = self::create([
                    'task_id' => $task_id
                ]);
                $models[] = $model;

                if ($model)
                    $model->uploadImageFromBase64($image, $task_id);
            }
        }

        return count($images) === count($models);
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
