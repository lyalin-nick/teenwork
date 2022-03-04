<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

/**
 * @property int $id
 * @property string $name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property bool $phone_verified
 * @property string $password
 * @property string $verify_token
 * @property Carbon $verify_token_expire
 * @property Carbon $email_verified_at
 * @property string $phone_verify_token
 * @property Carbon $phone_verify_token_expire
 * @property Carbon $phone_verified_at
 * @property string $role
 * @property string $status
 * @mixin HasApiTokens
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const STATUS_WAIT = 'wait';
    public const STATUS_ACTIVE = 'active';

    public const ROLE_PERFORMER = 'performer';
    public const ROLE_EMPLOYER = 'employer';

    const SECONDS_TO_EXPIRE = 300;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'last_name', 'email', 'phone', 'password', 'status', 'role',
//        'verify_token', 'email_verified_at', 'verify_token_expire',
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
        'phone_verified_at' => 'datetime',
        'phone_verify_token_expire' => 'datetime',
    ];

    public static function register(string $name, string $email, string $password): self
    {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'verify_token' => Str::uuid(),
            'role' => null,
            'status' => self::STATUS_WAIT,
        ]);
    }

    public static function new($name, $email): self
    {
        return static::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt(Str::random()),
            'role' => null,
            'status' => self::STATUS_ACTIVE,
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
    public static function findByEmail($identifier)
    {
        return self::where('email', $identifier)->first();
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

    public function verify(): void
    {
        $this->email_verified_at = ($this->email_verified_at) ?: Carbon::now();
        $this->status = self::STATUS_ACTIVE;
        $this->password = $this->verify_token;
        $this->verify_token = null;
        $this->verify_token_expire = null;
        $this->saveOrFail();
    }

    public function verifyPhone(): void
    {
        $this->phone_verified_at = ($this->phone_verified_at) ?: Carbon::now();
        $this->status = self::STATUS_ACTIVE;
        $this->password = $this->verify_token;
        $this->phone_verify_token = null;
        $this->phone_verify_token_expire = null;
        $this->saveOrFail();
    }

    /**
     *
     * @param $code
     * @throws Throwable
     */
    public function setEmailVerificationData($code): void
    {
        $this->verify_token = bcrypt($code);
        $this->verify_token_expire = Carbon::now()->addSeconds(self::SECONDS_TO_EXPIRE);
        $this->saveOrFail();
    }

    public function setPhoneVerificationData($code)
    {
        $this->phone_verify_token = bcrypt($code);
        $this->phone_verify_token_expire = Carbon::now()->addSeconds(self::SECONDS_TO_EXPIRE);
        $this->saveOrFail();
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

    public function hasFilledProfile(): bool
    {
        return !empty($this->name) && !empty($this->last_name) && $this->isPhoneVerified();
    }

    /**
     * @return bool
     */
    public function isPhoneVerified(): bool
    {
        return $this->phone_verified;
    }

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

    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

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
            'photo_preview' => $profile->getPhotoPreviewLink(),
            'photo' => $profile->getPhotoLink(),
            'video' => $profile->getProfileVideoLink(),
            'address' => $profile->address,
            'address_id' => $profile->place_id,
            'languages' => $profile->getLanguagesIds(),
            'categories' => $profile->getCategoriesIds(),
            'number_performer_tasks' => $profile->number_performer_tasks,
            'number_customer_tasks' => $profile->number_customer_tasks,
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
