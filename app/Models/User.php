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
        'email', 'phone', 'password', 'status', 'role', 'verify_token', 'verified_at', 'verify_token_expire',
//       'name', 'last_name', 'verify_token', 'email_verified_at', 'verify_token_expire',
//        'phone_verify_token', 'phone_verified_at', 'phone_verify_token_expire'
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
        'reset_token_expire' => 'datetime',
        //'phone_verified_at' => 'datetime',
        //'phone_verify_token_expire' => 'datetime',
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

    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Route notifications for the Nexmo channel.
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

    public function isPerformer(): bool
    {
        return $this->role === self::ROLE_PERFORMER || $this->role === null;
    }

    public function isEmployer(): bool
    {
        return $this->role === self::ROLE_EMPLOYER || $this->role === null;
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return !empty($this->verified_at);
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

    /**
     * Связь с моделями авторизованных через соцсети
     *
     * @return HasMany
     */
    public function networks()
    {
        return $this->hasMany(Network::class, 'user_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id', 'id');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    /**
     * Аксессор
     *
     * @param $value
     * @return bool
     */
    public function getPushNotificationAttribute($value)
    {
        return (boolean)$value;
    }

    /**
     * Аксессор
     *
     * @param $value
     * @return bool
     */
    public function getEmailNotificationAttribute($value)
    {
        return (boolean)$value;
    }

    /**
     * Аксессор
     *
     * @param $value
     * @return bool
     */
    public function getInvisibleAttribute($value)
    {
        return (boolean)$value;
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
            'push_notification' => $this->push_notification,
            'email_notification' => $this->email_notification,
            'invisible' => $this->invisible,
            'created_at' => date('Y-m-d', strtotime($this->created_at))
        ];

        return $user_data;
    }
}
