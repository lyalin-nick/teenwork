<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property int $task_id
 * @property Task $task
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'user_id'];

    public $timestamps = false;

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
