<?php

namespace App\Models;

use App\Http\Resources\Review\ViewResource;
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
 * @property Profile $profile
 * @property Task[] $tasks
 * @property Review[] $reviews
 * @property Favorite[] $favorites
 * @property Chat[] $chats
 *
 * @mixin HasApiTokens
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const STATUS_WAIT = 'wait';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BANNED = 'banned';
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

    public static function getAdminUser()
    {
        $user = User::where('id', 0)->first();
        if (!$user) {
            $user = new User();
            $user->id = 0;
            $user->phone = '+1';
            $user->status = self::STATUS_ACTIVE;
            $user->save();
            if ($user) {
                $profile = $user->profile;
                $profile->first_name = 'Technical support';
                $profile->save();
            }
        }
        return User::where('id', 0)->first();
    }

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
     * @return User|\Illuminate\Database\Eloquent\Model|object|null
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

    /**
     * @return HasMany
     */
    public function myQuestions()
    {
        return $this->hasMany(MyQuestion::class);
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

    public function getTaskList($status = null)
    {
        $tasks = [];

        if ($this->isEmployer()) {
            $tasks = $this->tasks()->taskList($status)
                ->paginate(20);

            $curPage = $tasks->currentPage();
            $lastPage = $tasks->lastPage();

            $tasks = $tasks->each(function ($item, $key) {
                $item->makeHidden(['images']);
                $item['images_links'] = $item->images_links;
                $item['status'] = $item->status_label;
            });

        } else { //TODO: доделать список задач исполнителя
            $responses = $this->responses()->with(['task' => function ($query) {
                $query->with('images');
            }])->paginate(20);

            $curPage = $responses->currentPage();
            $lastPage = $responses->lastPage();

            $tasks = [];
            foreach ($responses as $response) {
                $task = $response->task;
                $tasks[] = [
                    'id' => $task->id,
                    'name' => $task->name,
                    'price' => $task->price,
                    'description' => $task->description,
                    'status' => $task->status_label,
                    'safe_deal' => $task->safe_deal,
                    'images_links' => $task->images_links,
                ];
            }
        }

        return ['currentPage' => $curPage, 'lastPage' => $lastPage, 'tasks' => $tasks];
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

    public function isBanned(): bool
    {
        return $this->status === self::STATUS_BANNED;
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
            $profile->rating = round((float)$rating_sum / $reviews_sum, 1);
        } else {
            $profile->rating = round((float)$review_rating, 1);
        }
        $profile->save();
        $this->refreshStatus();
    }

    private function refreshStatus()
    {
        $profile = $this->profile;
        if ($profile->number_performer_tasks >= 20 && $profile->rating >= 4.0) {
            $profile->status = Profile::STATUS_PRO;
        }
        if ($profile->number_performer_tasks >= 25 && $profile->rating >= 4.5) {
            $profile->status = Profile::STATUS_EXPERT;
        }
        $profile->save();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'id');
    }

    public function getStars($params = null): array
    {
        $reviews = $this->reviews()->get();
        if (isset($params['dates'])) {
            $reviews = $this->reviews()->whereIn('date', $params['dates'])->get();
        }
        $stars = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviews as $review) {
            $stars[$review->rating]++;
        }
        return $stars;

//        $reviews = Review::query()->select(
//            \DB::raw("(SELECT COUNT(rating) FROM `reviews` WHERE `reviews`.`user_id`={$this->id} AND `reviews`.`rating` = 1) as star1"),
//            \DB::raw("(SELECT COUNT(rating) FROM `reviews` WHERE `reviews`.`user_id`={$this->id} AND `reviews`.`rating` = 2) as star2"),
//            \DB::raw("(SELECT COUNT(rating) FROM `reviews` WHERE `reviews`.`user_id`={$this->id} AND `reviews`.`rating` = 3) as star3"),
//            \DB::raw("(SELECT COUNT(rating) FROM `reviews` WHERE `reviews`.`user_id`={$this->id} AND `reviews`.`rating` = 4) as star4"),
//            \DB::raw("(SELECT COUNT(rating) FROM `reviews` WHERE `reviews`.`user_id`={$this->id} AND `reviews`.`rating` = 5) as star5"),
//        )->first();
//        return [
//            5 => $reviews->star5,
//            4 => $reviews->star4,
//            3 => $reviews->star3,
//            2 => $reviews->star2,
//            1 => $reviews->star1,
//        ];
    }

    public function getCountChats(): array
    {
        $count[Chat::STATUS_CURRENT] = $this->chats()->where(['status' => Chat::STATUS_CURRENT])->count();
        $count[Chat::STATUS_HISTORY] = $this->chats()->where(['status' => Chat::STATUS_HISTORY])->count();
        $count[Chat::STATUS_SUPPORT] = $this->chats()->where(['status' => Chat::STATUS_SUPPORT])->count();
        $count['all'] = $count[Chat::STATUS_CURRENT] + $count[Chat::STATUS_HISTORY] + $count[Chat::STATUS_SUPPORT];

        return $count;
    }

    public function getLastReview()
    {
        $review = $this->reviews()->orderBy('id', 'DESC')->first();
        if (!$review) {
            return [];
        }

        return new ViewResource($review);
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class);
    }
}
