<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $reporter_id
 * @property int $task_id
 * @property int $title_id
 * @property string $title
 * @property string $text
 * @property Task $task
 * @property User $reporter
 */
class TaskReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id', 'task_id', 'title_id', 'title', 'text'
    ];

    /**
     * @param $reporter_id
     * @param $task_id
     * @param $title_id
     * @param $title
     * @param $text
     * @return mixed
     */
    static function new($reporter_id, $task_id, $title_id, $title, $text)
    {
        return self::create([
            'reporter_id' => $reporter_id,
            'task_id' => $task_id,
            'title_id' => $title_id,
            'title' => $title,
            'text' => $text,
        ]);
    }

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    public function reporter()
    {
        return $this->hasOne(User::class, 'id', 'reporter_id');
    }
}
