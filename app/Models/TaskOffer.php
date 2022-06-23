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

    public function getAcceptAttribute($value)
    {
        return $value ? (bool)$value : $value;
    }
}
