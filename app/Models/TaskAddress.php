<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $task_id
 * @property string $address
 * @property string $place_id
 *
 * @mixin Builder
 */
class TaskAddress extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'address', 'place_id'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
