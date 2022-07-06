<?php

namespace App\Models;

use App\Http\Resources\User\ShortInfoResource;
use App\Services\Google\GoogleMapService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @property integer $id
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
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $expired_at
 * @property array $responses_info
 * @property string $video_link
 * @property string $images_links
 * @property string $user_info
 * @property double $lat
 * @property double $lng
 * @property integer $chat_id
 *
 * @property TaskImage[] $images
 * @property TaskVideo $video
 * @property Language[] $languages
 * @property User $user
 * @property Category $category
 * @property TaskResponse[] $responses
 * @property Chat $chat
 * @property User[] $acceptedTaskOfferUsers
 *
 * @method Builder home($flag)
 * @method Builder notExpired()
 * @method Builder categoryFlag($flag)
 * @method Builder categoriesIn($categories)
 * @method Builder languagesIn($categories)
 * @method Builder address($place_id)
 * @method Builder startDays($days)
 * @method Builder priceFrom($price)
 * @method Builder flagSafeDeal($safe_deal)
 * @method Builder flagHotWork($hot_work)
 * @method Builder priceDesc()
 * @method Builder ratingDesc()
 * @method Builder nearby($ulat, $ulng)
 * @method Builder hasPhrase($searchPhrase)
 *
 * @mixin Builder
 */
class Task extends Model
{
    use HasFactory;

    const
        STATUS_WAIT = 'wait',
        STATUS_PROGRESS = 'progress',
        STATUS_FAIL = 'fail',
        STATUS_EXPIRE = 'expire',
        STATUS_COMPLETE = 'complete';

    protected $fillable = [
        'user_id', 'category_id', 'name',
        'description', 'result',
        'address', 'place_id',
        'start_date', 'start_time', 'amount_of_workers',
        'minimum_age', 'price', 'payment_type',
        'safe_deal', 'hot_work', 'account_verified',
        'status', 'views_number', 'expired_at'
    ];

    protected $casts = [
        'safe_deal' => 'boolean',
        'hot_work' => 'boolean',
        'account_verified' => 'boolean',
        'views_number' => 'integer',
    ];

    public static function new($task_data, $languages, $images, $video)
    {
        $task = self::create($task_data);
        if ($task) {

            $task->linkToLanguages($languages);
            if ($images) {
                TaskImage::createModels($images, $task->id);
            }
            if ($video) {
                TaskVideo::updateModel($video, $task->id);
            }

            return $task;
        }
        return null;
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

    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

    public function taskOffers()
    {
        return $this->hasMany(TaskOffer::class);
    }

    public function acceptedTaskOffers()
    {
        return $this->hasMany(TaskOffer::class)->where('accept', '=', true);
    }

    public function acceptedTaskOfferUsers()
    {
        return $this->hasManyThrough(User::class, TaskOffer::class,'task_id', 'id', 'id', 'user_id')
            ->where('accept', '=', true);
    }

    /**
     * Кол-во задач online/offline
     *
     */
    public static function countOnline()
    {
        $tasks = Task::query()->notExpired()->categoryFlag('online');

        return $tasks->count();
    }

    public static function countOffline()
    {
        $tasks = Task::query()->notExpired()->categoryFlag('offline');

        return $tasks->count();
    }

    public function scopeHome(Builder $query, $flag)
    {
        $query->notExpired()
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
                'tasks.lat as lat',
                'tasks.lng as lng',
            ])
            ->categoryFlag($flag)
            ->with('user')
            ->with('images');
    }

    public function scopeTaskList(Builder $query, $status = null)
    {
        $query->with('images')
            ->select([
                'tasks.id',
                'tasks.name',
                'tasks.price',
                'tasks.description',
                'tasks.status',
                'tasks.safe_deal',
            ])
            ->orderBy('created_at', 'desc');
        if ($status) {
            $query->where('status', '=', $status);
        }
    }

    /**
     * Выполняем поиск по названию или описанию задачи, либо по имени заказчика
     *
     * @param Builder $query
     * @param $searchPhrase
     * @return void
     */
    public function scopeHasPhrase(Builder $query, $searchPhrase)
    {
        //$searchPhraseModify = self::getModifyPhrase($searchPhrase);

        $query->join('profiles', 'tasks.user_id', '=', 'profiles.user_id')
            ->addSelect(DB::raw("MATCH(`tasks`.`name`, `tasks`.`description`) AGAINST('{$searchPhrase}') as score"))
            ->whereRaw(DB::raw("MATCH(`tasks`.`name`, `tasks`.`description`) AGAINST('{$searchPhrase}') OR CONCAT(`profiles`.`first_name`,' ',`profiles`.`last_name`) LIKE '%{$searchPhrase}%'"))
            ->orderBy('score', 'desc');

    }

    private static function getModifyPhrase($searchPhrase)
    {
        $arr = explode(" ", $searchPhrase);
        foreach ($arr as $i => $word) {
            $arr[$i] = "+" . $word . "*";
        }
        return implode(" ", $arr);
    }

    public function scopeCategoriesIn(Builder $query, $categories)
    {
        $query->whereIn('tasks.category_id', $categories);
    }

    public function scopeLanguagesIn(Builder $query, $languages)
    {
        $query->join('language_task as l', 'tasks.id', '=', 'l.task_id')
            ->whereIn('l.language_id', $languages);
    }

    public function scopeNotExpired(Builder $query)
    {
        $query->where('tasks.expired_at', '>', date('Y-m-d H:i:s'));
    }

    public function scopeCategoryFlag(Builder $query, $flag)
    {
        $query->join('categories as c', 'tasks.category_id', '=', 'c.id')
            ->whereIn('c.flag', Category::getFlagsConstants($flag))
            ->addSelect('c.icon_name as icon_name');
    }

    public function scopeAddress(Builder $query, $place_id)
    {
        $query->where('tasks.place_id', '=', $place_id);
    }

    public function scopeStartDays(Builder $query, $days)
    {
        foreach ($days as $i => $day) {
            $params['days'][$i] = date('Y-m-d', strtotime($day));
        }
        $query->whereIn('tasks.start_date', $days);
    }

    public function scopePriceFrom(Builder $query, $price)
    {
        $query->where('tasks.price', '>=', $price);
    }

    public function scopeFlagSafeDeal(Builder $query, $safe_deal)
    {
        $query->where('tasks.safe_deal', '=', filter_var($safe_deal, FILTER_VALIDATE_BOOLEAN));
    }

    public function scopeFlagHotWork(Builder $query, $hot_work)
    {
        $query->where('tasks.hot_work', '=', filter_var($hot_work, FILTER_VALIDATE_BOOLEAN));
    }


    public function scopeNotResponse(Builder $query)
    {
        $query->whereDoesntHave('responses');
    }

    public function scopePriceOrder(Builder $query, $direction = 'desc')
    {
        $query->orderBy('tasks.price', $direction);
    }

    public function scopeRatingDesc(Builder $query)
    {
        $query->join('profiles as p', 'tasks.user_id', '=', 'p.user_id')
            ->addSelect('p.rating')
            ->orderBy('p.rating', 'desc');
    }

    public function scopeNearby(Builder $query, $ulat = '53.213672', $ulng = '45.061300')
    {
        $query->addSelect(DB::raw("ACOS(SIN(PI()*lat/180.0)*SIN(PI()*{$ulat}/180.0)+COS(PI()*lat/180.0)*COS(PI()*{$ulat}/180.0)*COS(PI()*{$ulng}/180.0-PI()*lng/180.0))*6371 AS distance")); // формула расчета расстояния от заданных координат
        $query->orderBy('distance');
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
     * @return ShortInfoResource
     */
    public function getUserInfoAttribute(): ShortInfoResource
    {
        return new ShortInfoResource($this->user);
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
     * @param $value
     * @return string
     */
    public function getResponsesInfo($params = null): array
    {
        $responses = $this->responses()
            ->select('*')
            ->with('user');
        if (isset($params['sort'])) {
            switch ($params['sort']) {
                case 'rating':
                    $responses->ratingOrder();
                    break;
                case 'nearby':
                    $responses->nearby($this->lat, $this->lng);
                    break;
            }
        }
        $responses = $responses->get();

        if ($responses) {
            $responses = $responses->filter(function ($item, $key) {
                $item['user_info'] = new ShortInfoResource($item->user);
                $item->makeHidden('task_id', 'user_id', 'created_at', 'updated_at', 'user');
                return $item->user->isPerformer();
            });
            return $responses->toArray();
        }
        return [];
    }

    public function responses()
    {
        return $this->hasMany(TaskResponse::class);
    }

//    public function report()
//    {
//        return $this->belongsTo(TaskReport::class, 'task_id', 'id');
//    }

    /**
     * Аксессор поля status_label
     *
     * @param $value
     * @return int
     */
    public function getStatusLabelAttribute($value): string
    {
        $labels = self::getStatusLabels();

        return $labels[$this->status] ?? 'undefined';
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
     */
    public function getVideoLinkAttribute()
    {
        return ($this->video) ? $this->video->getLink() : null;
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
        if ($this->images) {
            foreach ($this->images as $image) {
                $image->delete();
            }
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

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function video()
    {
        return $this->hasOne(TaskVideo::class);
    }


    public function chat()
    {
        return $this->belongsTo(Chat::class);
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

    public function addViews(): void
    {
        $this->views_number += 1;
        $this->save();
    }

}
