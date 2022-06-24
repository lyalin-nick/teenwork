<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $task_id
 * @property integer $user_id
 * @property integer $message_id
 * @property string $text
 * @property boolean $accept
 *
 * @property Task $task
 * @property Message $message
 * @property Chat $chat
 * @property User $user
 */
class TaskOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'user_id', 'text'
    ];

    public static function new($task_id, $user_id, $text = '')
    {
        return self::create([
            'task_id' => $task_id,
            'user_id' => $user_id,
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

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function getAcceptAttribute($value)
    {
        return $value === null ? $value : (bool)$value;
    }
}
