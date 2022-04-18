<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $reporter_id
 * @property int $user_id
 * @property int $title_id
 * @property string $title
 * @property string $text
 * @property User $user
 * @property User $reporter
 */
class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id', 'user_id', 'title_id', 'title', 'text'
    ];

    /**
     * Создание новой модели
     *
     * @param $reporter_id
     * @param $user_id
     * @param $title_id
     * @param $title
     * @param $text
     * @return mixed
     */
    static function new($reporter_id, $user_id, $title_id, $title, $text)
    {
        return self::create([
            'reporter_id' => $reporter_id,
            'user_id' => $user_id,
            'title_id' => $title_id,
            'title' => $title,
            'text' => $text,
        ]);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function reporter()
    {
        return $this->hasOne(User::class, 'id', 'reporter_id');
    }
}
