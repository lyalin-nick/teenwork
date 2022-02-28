<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $address
 * @property string $latitude
 * @property string $longitude
 *
 * @mixin Builder
 */
class TaskAddress extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'address', 'place_id', 'latitude', 'longitude'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'id', 'task_id');
    }
}
