<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $category_id
 * @property integer $user_id
 * @property string $name
 * @property string $description
 * @property string $result
 * @property string $start_date
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

    const
        STATUS_WAIT = 1,
        STATUS_PROGRESS = 2,
        STATUS_FAIL = 3,
        STATUS_EXPIRE = 4,
        STATUS_COMPLETE = 5;

    protected $fillable = [
        'category_id', 'name',
        'description', 'result',
        'start_date', 'start_time', 'amount_of_workers',
        'minimum_age', 'price', 'payment_type',
        'safe_deal', 'hot_work', 'account_verified',
        'status', 'views_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Мутатор на поле start_date
     *
     * @param $value
     */
    public function setStartDateAttribute($value): void
    {
        $this->attributes['start_date'] = date('Y-m-d', strtotime($value));
    }

    /**
     * Аксессор поля start_date
     *
     * @param $value
     * @return string
     */
    public function getStartTimeAttribute($value): string
    {
        return date('H:i A', strtotime($value));
    }

    /**
     * Аксессор поля views_number
     *
     * @param $value
     * @return int
     */
    public function getViewsNumberAttribute($value): int
    {
        return intval($value);
    }

    /**
     * Получение полной информации о задаче
     *
     * @return array
     */
    public function getFullInfo(): array
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
            'images' => $this->getAllImages(),
            'video' => $this->getVideoLink(),
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'amount_of_workers' => $this->amount_of_workers,
            'minimum_age' => $this->minimum_age,
            'price' => $this->price,
            'payment_type' => $this->payment_type,
            'safe_deal' => $this->safe_deal,
            'hot_work' => $this->hot_work,
            'account_verified' => $this->account_verified,
            'status' => $this->getStatusLabel(),
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
            'views_number' => $this->views_number,
        ];

        return $task;
    }

    /**
     * Получение краткой информации о пользователе создавший задачу
     *
     * @return array
     */
    public function getUserInfo(): array
    {
        $user = $this->user;
        $profile = $user->profile;
        $info = [
            'id' => $user->id,
            'name' => $profile->first_name . ' ' . $profile->last_name,
            'photo' => $profile->getProfileImageLink(),
            'rating' => $profile->rating,
        ];

        return $info;
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

    public function getAllImages(): array
    {
        $images = $this->images;

        $task_images = [];

        if ($images) {
            foreach ($images as $image)
                $task_images[] = $image->getImageLink();
        }

        return $task_images;
    }

    public function getVideoLink()
    {
        return ($this->video) ? $this->video->getLink() : null;
    }

    public function getStatusLabel(): string
    {
        $labels = self::getStatusLabels();

        return isset($labels[$this->status]) ? $labels[$this->status] : 'undefined';
    }

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

    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = date('H:i:s', strtotime($value));
    }

    public function createTaskAddresses($addresses): bool
    {
        if ($addresses) {
            $addresses_models = [];
            foreach ($addresses as $address) {
                $address['task_id'] = $this->id;
                $addresses_models[] = TaskAddress::create($address);
            }
        }

        return count($addresses) === count($addresses_models);
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

    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

    public function addresses()
    {
        return $this->hasMany(TaskAddress::class);
    }

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function video()
    {
        return $this->hasOne(TaskVideo::class);
    }

}
