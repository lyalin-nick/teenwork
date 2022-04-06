<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property integer $category_id
 * @property integer $user_id
 * @property string $name
 * @property string $description
 * @property string $result
 * @property string $address
 * @property string $place_id
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
 * @property string $expired_at
 *
 * @property TaskImage[] $images
 * @property TaskVideo $video
 * @property Language[] $languages
 * @property User $user
 * @property Category $category
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
        'address', 'place_id',
        'start_date', 'start_time', 'amount_of_workers',
        'minimum_age', 'price', 'payment_type',
        'safe_deal', 'hot_work', 'account_verified',
        'status', 'views_number', 'expired_at'
    ];

    /**
     * Поиск активных задач
     *
     * @param string $flag
     * @param array $params
     * @param false $getAll
     * @return array
     */
    public static function search($flag, $params, $getAll = false)
    {
        $tasks = Task::query()
            ->with('user')
            ->with('images')
            ->where('tasks.expired_at', '>', date('Y-m-d H:i:s'))
            ->join('users as u', 'u.id', '=', 'tasks.user_id')
            ->join('profiles as p', 'u.id', '=', 'p.user_id')
            ->join('categories as c', 'tasks.category_id', '=', 'c.id')
            ->whereIn('c.flag', Category::getFlagsConstants($flag))
            ->select([
                'tasks.id',
                'tasks.user_id',
                'tasks.name',
                'tasks.price',
                'tasks.description',
                'tasks.place_id',
                'tasks.hot_work',
                'tasks.safe_deal',
                'tasks.created_at',
                'tasks.start_date',
                'p.rating',
                'c.icon_name as icon_name',
            ]);

        if (isset($params['categories']))
            $tasks->whereIn('tasks.category_id', $params['categories']);

        if (isset($params['languages']))
            $tasks->join('language_task as l', 'tasks.id', '=', 'l.task_id')
                ->whereIn('l.language_id', $params['languages']);

        if (isset($params['place_id']))
            $tasks->where('tasks.place_id', $params['place_id']);

        if (isset($params['days'])) {
            foreach ($params['days'] as $i => $day) {
                $params['days'][$i] = date('Y-m-d', strtotime($day));
            }
            $tasks->whereIn('tasks.start_date', $params['days']);
        }
        if (isset($params['price']))
            $tasks->where('tasks.price', '>=', $params['price']);

        if (isset($params['safe_deal']))
            $tasks->where('tasks.safe_deal', '=', filter_var($params['safe_deal'], FILTER_VALIDATE_BOOLEAN));

        if (isset($params['hot_work']))
            $tasks->where('tasks.hot_work', '=', filter_var($params['hot_work'], FILTER_VALIDATE_BOOLEAN));


        $params['sort'] = isset($params['sort']) ? $params['sort'] : 'default';
        switch ($params['sort']) {
            case "price":
                $tasks->orderBy('tasks.price', 'desc');
                break;
            case "rating":
                $tasks->orderBy('p.rating', 'desc');
                break;
            default:
                $tasks->orderBy('start_date');
                $tasks->orderBy('hot_work', 'desc');
                break;
        }

        $tasks = ($getAll) ? $tasks->get() : $tasks->simplePaginate(20);


        $tasks = $tasks->each(function ($item, $key) {
            $item->makeHidden(['user', 'images']);
            $item['user_info'] = $item->user_info;
            $item['images_links'] = $item->images_links;
            $item['status'] = $item->status_label;
        });

        return $tasks->toArray();
    }

    /**
     * Кол-во задач online/offline
     *
     * @return int
     */
    public static function countOnline()
    {
        $tasks = Task::query()
            ->where('tasks.expired_at', '>', date('Y-m-d H:i:s'))
            ->join('categories as c', 'tasks.category_id', '=', 'c.id')
            ->whereIn('c.flag', Category::getFlagsConstants('online'));

        return $tasks->count();
    }

    public static function countOffline()
    {
        $tasks = Task::query()
            ->where('tasks.expired_at', '>', date('Y-m-d H:i:s'))
            ->join('categories as c', 'tasks.category_id', '=', 'c.id')
            ->whereIn('c.flag', Category::getFlagsConstants('offline'));

        return $tasks->count();
    }

    protected static function booted()
    {
        static::creating(function ($task) {
            $task->expired_at = date('Y-m-d H:i:s', strtotime($task->start_date . ' ' . $task->start_time));
        });
        static::updating(function ($task) {
            $task->expired_at = date('Y-m-d H:i:s', strtotime($task->start_date . ' ' . $task->start_time));
        });
        static::created(function ($task) {
            $profile = $task->profile;
            if ($profile)
                $profile->addNumberEmployerTask();
        });

        static::deleted(function (self $task) {
            $task->languages()->detach(); //удалим прилинкованные языки

            if ($task->video)
                $task->video->delete();//удалим прикрепленное видео

            if ($task->images)//удалим прикрепленные фото
                foreach ($task->images as $image_model)
                    $image_model->delete();
        });
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

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
     * Мутатор на поле start_date
     *
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute($value): string
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    /**
     * Мутатор на поле user_data
     *
     * @param $value
     */
    public function getUserInfoAttribute(): array
    {
        return $this->user->getShortInfo();
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
     * Аксессор поля start_date
     *
     * @param $value
     * @return string
     */
    public function getSafeDealAttribute($value): bool
    {
        return (boolean)$value;
    }

    /**
     * Аксессор поля start_date
     *
     * @param $value
     * @return string
     */
    public function getHotWorkAttribute($value): bool
    {
        return (boolean)$value;
    }

    /**
     * Аксессор поля start_date
     *
     * @param $value
     * @return string
     */
    public function getAccountVerifiedAttribute($value): bool
    {
        return (boolean)$value;
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
     * Аксессор поля status_label
     *
     * @param $value
     * @return int
     */
    public function getStatusLabelAttribute($value): string
    {
        $labels = self::getStatusLabels();

        return isset($labels[$this->status]) ? $labels[$this->status] : 'undefined';
    }

    /**
     * Получение массива всех статусов задачи
     *
     * @return string[]
     */
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

    /**
     * Аксессор поля video_link
     *
     * @param $value
     * @return int
     */
    public function getVideoLinkAttribute()
    {
        return ($this->video) ? $this->video->getLink() : null;
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
            'user' => $this->user_info,
            'category' => $this->category->getFullPathName(),
            'name' => $this->name,
            'description' => $this->description,
            'result' => $this->result,
            'languages' => $this->getLanguagesAsString(),
            'address' => $this->address,
            'place_id' => $this->place_id,
            'images' => $this->images_links,
            'video' => $this->video_link,
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
     * Получение выбранных к задаче предпочитаемых языков в виде строки
     *
     * @return string
     */
    public function getLanguagesAsString(): string
    {
        $languages = $this->languages;

        return ($languages) ? implode(', ', Arr::pluck($languages, 'name')) : "";
    }

    /**
     * Получение статуса задачи в виде строки
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        $labels = self::getStatusLabels();

        return isset($labels[$this->status]) ? $labels[$this->status] : 'undefined';
    }

    /**
     * Получение выбранных к задаче предпочитаемых языков в виде массива
     *
     * @return string
     */
    public function getLanguagesAsArray(): array
    {
        $languages = $this->languages;

        return ($languages) ? Arr::pluck($languages, 'id') : [];
    }

    /**
     * Получение массива ссылок прикрепленных к задаче фото
     *
     * @return array
     */
    public function getImagesAsLinks(): array
    {
        $images = $this->images;

        $task_images = [];

        if ($images) {
            foreach ($images as $image)
                $task_images[] = ['image' => $image->getImageLink(), 'preview' => $image->getImageLink('_mini')];
        }

        return $task_images;
    }

    /**
     * Аксессор поля images_links
     *
     * @return array
     */
    public function getImagesLinksAttribute(): array
    {
        return $this->getImagesAsArray();
    }

    /**
     * Получение массива ссылок прикрепленных к задаче фото
     *
     * @return array
     */
    public function getImagesAsArray(): array
    {
        $images = $this->images;

        $task_images = [];

        if ($images) {
            foreach ($images as $image)
                $task_images[] = [
                    'link' => $image->getImageLink(),
                    'path' => $image->getFullPath()
                ];
        }

        return $task_images;
    }

    public function cleanImages(): void
    {
        foreach ($this->images as $image) {
            $image->delete();
        }
    }

    public function cleanVideo(): void
    {
        if ($this->video) {
            $this->video->cleanUp();
        }
    }

    /**
     * Получение ссылки на видео прикрепленного к задаче
     *
     * @return null
     */
    public function getVideoLink()
    {
        return ($this->video) ? $this->video->getLink() : null;
    }

    /**
     * Мутатор на поле start_time
     *
     * @param $value
     */
    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = date('H:i:s', strtotime($value));
    }

    /**
     * Прилинковка предпочитаемых языков выбранных к задаче
     *
     * @param $languages
     */
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

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function video()
    {
        return $this->hasOne(TaskVideo::class);
    }

}
