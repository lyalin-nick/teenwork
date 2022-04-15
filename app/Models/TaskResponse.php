<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property string $text
 *
 * @property Task $task
 * @property User $user
 *
 * @mixin Builder
 */
class TaskResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'user_id', 'text'
    ];

    /**
     * @param int $task_id
     * @param int $user_id
     * @param string $text
     * @return TaskResponse|Model|string[]
     */
    public static function new($task_id, $user_id, $text)
    {
        return self::create([
            'user_id' => $user_id,
            'task_id' => $task_id,
            'text' => $text
        ]);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
