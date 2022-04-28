<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $email
 * @property string $phone
 * @property bool $phone_verified
 * @property string $password
 * @property Carbon $verified_at
 * @property string $verify_token
 * @property Carbon $verify_token_expire
 * @property string $reset_token
 * @property Carbon $reset_token_expire
 * @property string $role
 * @property string $status
 * @property Task[] $tasks
 * @property Review[] $reviews
 * @property Favorite[] $favorites
 *
 * @mixin HasApiTokens
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const STATUS_WAIT = 'wait';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RESET = 'reset';

    public const ROLE_PERFORMER = 'performer';
    public const ROLE_EMPLOYER = 'employer';

    const SECONDS_TO_EXPIRE = 300;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email', 'phone', 'password', 'status', 'role', 'verify_token', 'verified_at', 'verify_token_expire'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified_at' => 'datetime',
        'verify_token_expire' => 'datetime',
        'reset_token_expire' => 'datetime'
    ];

    public static function register(string $phone, string $password, string $verify_code)
    {
        return static::create([
            'phone' => $phone,
            'password' => bcrypt($password),
            'verify_token' => bcrypt($verify_code),
            'verify_token_expire' => Carbon::now()->addSeconds(self::SECONDS_TO_EXPIRE),
            'role' => null,
            'status' => self::STATUS_WAIT,
        ]);
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public static function findByPhone($identifier)
    {
        return self::where('phone', $identifier)->first();
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public static function findByNetwork($provider, $socialite_user)
    {
        $user = self::query()->whereHas('networks', function (Builder $query) use ($provider, $socialite_user) {
            $query->where('network', $provider)->where('network_user_id', $socialite_user->id);
        })->first(); //ищем пользователя по модели Network

        if (!$user) { // если не нашли, то ищем с такой же почтой
            $user = self::findByEmail($socialite_user->email);
            if ($user) {
                $user->networks()->create([
                    'network' => $provider,
                    'network_user_id' => $socialite_user->id,
                ]);
            }
        }

        if (!$user) { // если не нашли по почте, то регистрируем
            $user = self::registerFromNetwork($provider, $socialite_user);
        }

        return $user;
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public static function findByEmail($identifier)
    {
        return self::where('email', $identifier)->first();
    }

    public static function registerFromNetwork($provider, $socialite_user)
    {
        $user = static::create([
            'email' => $socialite_user->email,
            'role' => null,
            'status' => self::STATUS_ACTIVE,
        ]);

        $user->networks()->create([
            'network' => $provider,
            'network_user_id' => $socialite_user->id,
        ]);

        return $user;
    }

    /**
     * Связь с моделями авторизованных через соцсети
     *
     * @return HasMany
     */
    public function networks()
    {
        return $this->hasMany(Network::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function myQuestions()
    {
        return $this->hasMany(MyQuestion::class);
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public static function findResetByPhone($identifier)
    {
        return self::where('phone', $identifier)->where('status', self::STATUS_RESET)->first();
    }

    public static function rolesList(): array
    {
        return [
            self::ROLE_PERFORMER => 'Performer',
            self::ROLE_EMPLOYER => 'Employer'
        ];
    }

    /**
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($user) {
            Profile::createProfile(['user_id' => $user->id]);
        });
    }

    public function verify(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->verify_token = null;
        $this->verify_token_expire = null;
        $this->verified_at = ($this->verified_at) ?: Carbon::now();
        $this->saveOrFail();
    }

    public function setPassword($password): void
    {
        $this->password = bcrypt($password);
        $this->status = self::STATUS_ACTIVE;
        $this->saveOrFail();
    }

    public function setResetToken($verify_code): void
    {
        $this->reset_token = bcrypt($verify_code);
        $this->reset_token_expire = Carbon::now()->addSeconds(self::SECONDS_TO_EXPIRE);
        $this->status = self::STATUS_RESET;
        $this->saveOrFail();
    }

    public function removeResetToken(): void
    {
        $this->password = null;
        $this->reset_token = null;
        $this->reset_token_expire = null;
        $this->status = self::STATUS_RESET;
        $this->saveOrFail();
    }

    public function setActive(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->saveOrFail();
    }

    /**
     * Обновление пустой роли
     *
     * @param $role
     */
    public function checkEmptyRole($role): void
    {
        if ($this->role === null) {
            $this->role = $role;
            $this->save();
        }
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    /**
     * Идентификатор для отправки смс
     *
     * @param $notification
     * @return string
     */
    public function routeNotificationForNexmo($notification)
    {
        return $this->phone;
    }

    /**
     * Маршрутизация уведомлений для почтового канала.
     *
     * @param Notification $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

    /**
     *
     * @param $value
     * @return bool
     */
    public function getShortInfo(): array
    {
        $profile = $this->profile;
        return [
            'id' => $this->id,
            'name' => $profile->full_name,
            'photo' => $profile->getProfilePreviewImageLink(),
            'rating' => $profile->rating,
            'status' => $profile->status,
        ];
    }

    public function getTaskList()
    {
        $tasks = [];

        if ($this->isEmployer()) {
            $tasks = $this->tasks()
                ->with('images')
                ->select([
                    'tasks.id',
                    'tasks.name',
                    'tasks.price',
                    'tasks.description',
                    'tasks.status',
                    'tasks.safe_deal',
                ])
                ->orderBy('status')
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get();

            $tasks = $tasks->each(function ($item, $key) {
                $item->makeHidden(['images']);
                $item['images_links'] = $item->images_links;
                $item['status'] = $item->status_label;
            });

        } else {
            $responses = $this->responses()->with(['task' => function ($query) {
                $query->with('images');
            }])->get();
            $tasks = [];
            foreach ($responses as $response) {
                $tasks[] = [
                    'id' => $response->task->id,
                    'name' => $response->task->name,
                    'price' => $response->task->price,
                    'description' => $response->task->description,
                    'status' => $response->task->status_label,
                    'safe_deal' => $response->task->safe_deal,
                    'images_links' => $response->task->images_links,
                ];
            }
        }

        return $tasks;
    }

    /**
     * Роль заказчик
     *
     * @return bool
     */
    public function isEmployer(): bool
    {
        return $this->role === self::ROLE_EMPLOYER || $this->role === null;
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id', 'id');
    }

    public function responses()
    {
        return $this->hasMany(TaskResponse::class, 'user_id', 'id');
    }

    /**
     * Подтвержденный профиль
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return !empty($this->verified_at);
    }

    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Получить данные о пользователе
     *
     * @return array
     */
    public function getFullData(): array
    {
        $profile = $this->profile;

        $user_data = [
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'date_of_birth' => $profile->date_of_birth,
            'about' => $profile->about,
            'status' => $profile->status,
            'photo_preview' => $profile->getProfilePreviewImageLink(),
            'photo' => $profile->getProfileImageLink(),
            'video' => $profile->getProfileVideoLink(),
            'address' => $profile->address,
            'address_id' => $profile->place_id,
            'languages' => $profile->getLanguagesIds(),
            'categories' => $profile->getCategoriesIds(),
            'number_performer_tasks' => $profile->number_performer_tasks,
            'number_employer_tasks' => $profile->number_employer_tasks,
            'rating' => $profile->rating,
            'number_review' => $profile->number_review,
            'push_notification' => $profile->push_notification,
            'email_notification' => $profile->email_notification,
            'invisible' => $profile->invisible,
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            //'favorites' => $this->getFavoritesId()
        ];

        return $user_data;
    }

    public function getFavoritesId(): array
    {
        if ($this->isPerformer())
            return $this->favoriteTasks()->pluck('task_id')->toArray();

        return $this->favoritePerformers()->pluck('performer_id')->toArray();
    }

    /**
     * Роль исполнитель
     *
     * @return bool
     */
    public function isPerformer(): bool
    {
        return $this->role === self::ROLE_PERFORMER || $this->role === null;
    }

    public function favoriteTasks()
    {
        return $this->hasMany(FavoriteTask::class);
    }

    public function favoritePerformers()
    {
        return $this->hasMany(FavoritePerformer::class);
    }

    public function checkFavorite($identifier)
    {
        if ($this->isPerformer())
            return $this->favoriteTasks()->where('task_id', '=', $identifier)->exists();

        return $this->favoritePerformers()->where('performer_id', '=', $identifier)->exists();
    }

    public function recountRating($review_rating)
    {
        $profile = $this->profile;
        $rating_sum = $this->reviews()->sum('rating');
        $reviews_sum = $this->reviews()->count();
        if ($rating_sum > 0 && $reviews_sum > 0) {
            $profile->rating = (float)$rating_sum / $reviews_sum;
        } else {
            $profile->rating = (float)$review_rating;
        }
        $profile->save();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'performer_id', 'id');
    }

    public function getStars(): array
    {
        return [
            5 => $this->reviews()->where('rating', '=', 5)->count(),
            4 => $this->reviews()->where('rating', '=', 4)->count(),
            3 => $this->reviews()->where('rating', '=', 3)->count(),
            2 => $this->reviews()->where('rating', '=', 2)->count(),
            1 => $this->reviews()->where('rating', '=', 1)->count(),
        ];
    }

    public function getLastReview(): array
    {
        $review = $this->reviews()->orderBy('id', 'DESC')->first();
        if (!$review) {
            return [];
        }

        return [
            'user' => $review->getEmployerInfoAttribute(),
            'rating' => $review->rating,
            'text' => $review->text
        ];
    }

    public function getFavorites()
    {
        if ($this->isPerformer()) {
            $favorites = $this->favoriteTasks()
                ->with(['user.profile', 'task'])
                ->paginate(20);

            $curPage = $favorites->currentPage();
            $lastPage = $favorites->lastPage();

            $tasks = [];
            foreach ($favorites as $favorite) {
                $task = $favorite->task;
                $tasks[] = [
                    'id' => $task->id,
                    'name' => $task->name,
                    'price' => $task->price,
                    'description' => $task->description,
                    'hot_work' => $task->hot_work,
                    'safe_deal' => $task->safe_deal,
                    'start_date' => $task->start_date,
                    'user_info' => $task->user_info,
                    'images_links' => $task->images_links,
                    'status' => $task->status_label,
                    'created_at' => $task->created_at,
                ];
            }

            return ['currentPage' => $curPage, 'lastPage' => $lastPage, 'tasks' => $tasks];
        }

        $favorites = $this->favoritePerformers()
            ->with(['performer.profile'])
            ->paginate(20);

        $curPage = $favorites->currentPage();
        $lastPage = $favorites->lastPage();

        $performers = [];
        foreach ($favorites as $favorite) {
            $performer = $favorite->performer;
            $performers[] = $performer->getShortInfo();
        }

        return ['currentPage' => $curPage, 'lastPage' => $lastPage, 'performers' => $performers];
    }

    public function addFavorite($identify)
    {
        if ($this->isPerformer()) {
            $this->favoriteTasks()->where('task_id', '=', $identify)->delete();

            $this->favoriteTasks()->create(['task_id' => $identify]);
        }

        $this->favoritePerformers()->where('performer_id', '=', $identify)->delete();

        $this->favoritePerformers()->create(['performer_id' => $identify]);
    }

    public function removeFavorite($identify)
    {
        if ($this->isPerformer())
            $this->favoriteTasks()->where('task_id', '=', $identify)->delete();

        $this->favoritePerformers()->where('performer_id', '=', $identify)->delete();
    }

}
