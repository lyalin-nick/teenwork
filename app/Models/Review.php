<?php

namespace App\Models;

use App\Http\Resources\User\ShortInfoResource;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property int $reviewer_id
 * @property int $rating
 * @property string $text
 * @property string $date
 * @property string $updated_at
 * @property string $created_at
 * @property array $reviewer_info
 * @property array $task_info
 * @property Task $task
 * @property User $user
 * @property User $reviewer
 * @mixin Builder
 */
class Review extends Model
{
    use HasFactory;

    public $fillable = [
        'task_id', 'user_id', 'reviewer_id', 'rating', 'text'
    ];

    public static function new($task_id, $user_id, $reviewer_id, $rating, $text)
    {
        return self::create([
            'task_id' => $task_id,
            'user_id' => $user_id,
            'reviewer_id' => $reviewer_id,
            'rating' => $rating,
            'text' => $text
        ]);
    }

    public static function search($user_id, $dates = null)
    {
        $reviews = self::atUser($user_id)->orderBy('created_at', 'desc');

        if ($dates) {
            $reviews->whereIn('date', $dates);
        }

        $reviews = $reviews->paginate(20);
        $curPage = $reviews->currentPage();
        $lastPage = $reviews->lastPage();

        $reviews = $reviews->each(function ($item) {
            $item['reviewer_info'] = $item->reviewer_info;
            $item['task_info'] = $item->task_info;
            $item->makeHidden(['task_id', 'user_id', 'reviewer_id', 'task', 'user', 'reviewer', 'created_at', 'updated_at']);
        });

        return ['currentPage' => $curPage, 'lastPage' => $lastPage, 'reviews' => $reviews];
    }

    protected static function booted()
    {
        static::creating(function (self $review) {
            if (self::where(['task_id' => $review->task_id, 'user_id' => $review->user_id, 'reviewer_id' => $review->reviewer_id])->first()) {
                throw new Exception("Такой отзыв уже создан");
            }
            $review->date = date('Y-m-d');
        });
        static::created(function (self $review) {
            $review->user->recountRating($review->rating);
        });

        static::deleted(function (self $review) {
            $review->user->recountRating($review->rating);
        });
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function getReviewerInfoAttribute()
    {
        $employer = $this->reviewer;
        return new ShortInfoResource($employer);
    }

    public function getTaskInfoAttribute()
    {
        $task = $this->task;

        return ($task) ? $task->only('id', 'name') : ['id' => null, 'name' => 'Task has been deleted'];
    }

    public function scopeAtUser(Builder $query, $user_id)
    {
        $query->where('user_id', '=', $user_id);
    }
}
