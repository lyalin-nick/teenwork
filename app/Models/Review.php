<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected static function booted()
    {
        static::created(function (self $review) {
            $performer = $review->performer;
            $performer_profile = $performer->profile;
            $rating_sum = $performer->reviews()->sum('rating');
            $reviews_sum = $performer->reviews()->count();
            if ($rating_sum > 0 && $reviews_sum > 0) {
                $performer_profile->rating = (float)$rating_sum / $reviews_sum;
            } else {
                $performer_profile->rating = (float)$review->rating;
            }
            $performer_profile->save();
        });

        static::deleted(function (self $review) {
            $performer = $review->performer;
            $performer_profile = $performer->profile;
            $rating_sum = $performer->reviews()->count('rating');
            $reviews_sum = $performer->reviews()->count();
            if ($rating_sum > 0 && $reviews_sum > 0) {
                $performer_profile->rating = (float)$rating_sum / $reviews_sum;
            } else {
                $performer_profile->rating = (float)$review->rating;
            }
            $performer_profile->save();
        });
    }
}
