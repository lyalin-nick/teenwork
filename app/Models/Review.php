<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $task_id
 * @property int $employer_id
 * @property int $performer_id
 * @property int $rating
 * @property string $text
 * @property string $date
 * @property string $updated_at
 * @property string $created_at
 * @property Task $task
 * @property User $employer
 * @property User $performer
 *
 * @mixin Builder
 */
class Review extends Model
{
    use HasFactory;

    public $fillable = [
        'task_id', 'employer_id', 'performer_id', 'rating', 'text'
    ];


    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performer_id');
    }

    public static function createReview($params)
    {
        $review = self::create($params);
    }

    public function getEmployerInfoAttribute()
    {
        $employer = $this->employer;
        return $employer->getShortInfo();
    }

    public function getTaskInfoAttribute()
    {
        $task = $this->task;
        return $task->only('id', 'name');
    }

    public static function search($request)
    {
        $user = $request->user();
        $params = $request->all();
        $reviews = $user->reviews()->orderBy('created_at', 'desc');

        if (isset($params['dates'])) {
            $reviews->whereIn('date', $params['dates']);
        }

        $reviews = $reviews->simplePaginate(20);

        $reviews = $reviews->each(function ($item) {
            $item['employer_info'] = $item->employer_info;
            $item['task_info'] = $item->task_info;
            $item->makeHidden(['task_id', 'employer_id', 'performer_id', 'task', 'employer', 'performer', 'created_at', 'updated_at']);
        });

        return $reviews;
    }

    protected static function booted()
    {
        static::creating(function (self $review) {
            if (self::where(['task_id' => $review->task_id, 'employer_id' => $review->employer_id, 'performer_id' => $review->performer_id])->first()) {
                throw new \Exception("Такой отзыв уже создан");
            }
            $review->date = date('Y-m-d');
        });
        static::created(function (self $review) {
            $review->performer->recountRating($review->rating);
        });

        static::deleted(function (self $review) {
            $review->performer->recountRating($review->rating);
        });
    }
}
