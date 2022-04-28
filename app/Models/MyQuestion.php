<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property string $question
 * @property string $status
 * @property User $user
 * @property MyQuestionImage[] $myQuestionImages
 *
 * @method Builder questionList($user_id)
 *
 * @mixin Builder
 */
class MyQuestion extends Model
{
    use HasFactory;

    const
        STATUS_WAIT = 'wait',
        STATUS_PROCESS = 'process',
        STATUS_CLOSE = 'close';

    protected $fillable = ['user_id', 'subject', 'question', 'status'];

    public static function getStatuses()
    {
        return [
            self::STATUS_WAIT => 'Waiting',
            self::STATUS_PROCESS => 'In process',
            self::STATUS_CLOSE => 'Closed',
        ];
    }

    public static function new($user_id, $subject, $question, $images = null)
    {
        $question = self::create([
            'user_id' => $user_id,
            'subject' => $subject,
            'question' => $question,
            'status' => self::STATUS_WAIT
        ]);

        if ($images) {
            MyQuestionImage::createModels($images, $question->id);
        }

        return $question;
    }

    public function scopeQuestionList(Builder $query, $user_id)
    {
        $query->where('user_id', '=', $user_id)
            ->select('id', 'subject', 'question', 'status', 'created_at')
            ->orderBy('created_at', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function myQuestionImages()
    {
        return $this->hasMany(MyQuestionImage::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }
}
