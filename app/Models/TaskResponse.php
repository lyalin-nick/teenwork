<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property int $chat_id
 * @property int $message_id
 * @property int $is_new
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
        'task_id', 'user_id', 'text', 'is_new'
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

    public function scopeRatingOrder(Builder $query)
    {
        $query->addSelect(DB::raw('(SELECT rating FROM profiles WHERE `profiles`.`user_id`=`task_responses`.`user_id`) as rating'));
        $query->orderBy('rating', 'desc');
    }

    public function scopeNearby(Builder $query, $ulat = '53.213672', $ulng = '45.061300')
    {
        $query->addSelect(DB::raw("ACOS(SIN(PI()*(SELECT `profiles`.`lat` FROM `profiles` WHERE `profiles`.`user_id`=`task_responses`.`user_id`)/180.0)*SIN(PI()*{$ulat}/180.0)+COS(PI()*(SELECT `profiles`.`lat` FROM `profiles` WHERE `profiles`.`user_id`=`task_responses`.`user_id`)/180.0)*COS(PI()*{$ulat}/180.0)*COS(PI()*{$ulng}/180.0-PI()*(SELECT `profiles`.`lng` FROM `profiles` WHERE `profiles`.`user_id`=`task_responses`.`user_id`)/180.0))*6371 AS distance")); // формула расчета расстояния от заданных координат
        $query->orderBy('distance');
    }
}
