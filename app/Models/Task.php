<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer $category_id
 * @property integer $user_id
 * @property string $name
 * @property string $description
 * @property string $result
 * @property string $start_time
 * @property integer $amount_of_workers
 * @property integer $minimum_age
 * @property integer $price
 * @property string $payment_type
 * @property int $safe_deal
 * @property int $hot_work
 * @property int $account_verified
 * @property int $views_number
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin Builder
 */
class Task extends Model
{
    use HasFactory;

    const STATUS_WAIT = 1,
        STATUS_PROGRESS = 2,
        STATUS_FAIL = 3,
        STATUS_EXPIRE = 4,
        STATUS_COMPLETE = 5;

    protected $fillable = [
        'category_id', 'name',
        'description', 'result',
        'start_time', 'amount_of_workers',
        'minimum_age', 'price', 'payment_type',
        'safe_deal', 'hot_work', 'account_verified'
    ];

    public static function getStatusLabels(): array
    {
        $labels = [
            self::STATUS_WAIT => "Waiting for response",
            self::STATUS_PROGRESS => "In progress",
            self::STATUS_FAIL => "Fail",
            self::STATUS_EXPIRE => "Expire",
            self::STATUS_COMPLETE => "Completed"
        ];

        return $labels;
    }


    public function getStatusLabel(): string
    {
        $labels = self::getStatusLabels();

        return isset($labels[$this->status]) ? $labels[$this->status] : 'undefined';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getStartTimeAttribute($value)
    {
        return date('H:i A', strtotime($value));
    }

    public function getFullInfo()
    {
        $task = [
            'id' => $this->id,
            'user' => $this->getUserInfo(),
            'category' => $this->category->getFullPathName(),
            'name' => $this->name,
            'description' => $this->description,
            'result' => $this->result,
            'languages' => $this->getAllLanguages(),
            'addresses' => $this->getAllAddresses(),
            'photos' => $this->getAllImages(),
            'start_time' => $this->start_time,
            'amount_of_workers' => $this->amount_of_workers,
            'minimum_age' => $this->minimum_age,
            'price' => $this->price,
            'payment_type' => $this->payment_type,
            'safe_deal' => $this->safe_deal,
            'hot_work' => $this->hot_work,
            'account_verified' => $this->account_verified,
            'status' => $this->getStatusLabel(),
            'created_at' => $this->created_at,
            'views_number' => $this->views_number,
        ];

        return $task;
    }

    public function getUserInfo(): array
    {
        $user = $this->user;
        $portfolio = $user->portfolio;
        $info = [
            'id' => $user->id,
            'name' => $portfolio->first_name . ' ' . $portfolio->last_name,
            'photo' => $portfolio->getPhotoLink(),
            'rating' => $portfolio->rating,
        ];

        return $info;
    }

    public function getAllImages(): array
    {
        $images = $this->images;

        $task_images = [];

        if ($images) {
            foreach ($images as $image)
                $task_images[] = $image->getLink();
        }

        return $task_images;
    }

    public function getAllLanguages(): string
    {
        $languages = $this->languages;

        $task_languages = [];

        if ($languages) {
            foreach ($languages as $language)
                $task_languages[] = $language->name;
        }

        return implode(', ', $task_languages);
    }

    public function getAllAddresses(): string
    {
        $addresses = $this->addresses;

        $task_addresses = [];

        if ($addresses) {
            foreach ($addresses as $address)
                $task_addresses[] = $address->name;
        }

        return implode(', ', $task_addresses);
    }

    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = date('H:i:s', strtotime($value));
    }

    public function createTaskAddresses($addresses)
    {
        if ($addresses) {
            foreach ($addresses as $address) {
                $address['task_id'] = $this->id;
                $adds = TaskAddress::create($address);
            }
        }
    }

    public function linkToLanguages($languages)
    {
        $language_models = Language::where('id', Language::ENGLISH_LANGUAGE)->get();

        if ($languages) {
            $language_models = Language::whereIn('id', $languages)->get();
        }

        if ($language_models) {
            $this->languages()->detach();
            $this->languages()->attach($language_models);
        }
    }

    public function addresses()
    {
        return $this->hasMany(TaskAddress::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function updateTaskImages($images)
    {
        if ($images) {
            foreach ($images as $pos => $image_id) {
                $image = TaskImage::find($image_id);
                if ($image) {
                    $image->task_id = $this->id;
                    $image->pos = $pos;
                    $is_moved = Storage::move($image->getFullPath(), $image->getNewFullPath($this->id . '/'));
                    if ($is_moved) {
                        $image->path .= $this->id . '/';
                        $image->save();
                    }
                }
            }
        }
    }
}
