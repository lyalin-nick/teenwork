<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property integer $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $date_of_birth
 * @property string $about
 * @property string $address
 * @property string $place_id
 * @property integer $number_performer_tasks
 * @property integer $number_employer_tasks
 * @property float $rating
 * @property integer $number_review
 * @property boolean $push_notification
 * @property boolean $email_notification
 * @property boolean $invisible
 *
 * @property User $user
 * @property Language[] $languages
 * @property ProfileImage $profileImage
 * @property ProfileVideo $profileVideo
 * @property PortfolioImage[] $portfolioImages
 * @property PortfolioLink[] $portfolioLinks
 *
 * @mixin Builder
 */
class Profile extends Model
{
    use HasFactory, ImageTrait;

    protected $configImages = [
        '_mini' => [
            'width' => 128,
            'height' => 128
        ]
    ];

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'date_of_birth', 'about',
        'address', 'place_id'
    ];

    /**
     * Создание модели портфолио пользователя
     * @param $data
     * @return Profile|Model
     */
    public static function createProfile($data)
    {
        return self::create($data);
    }

    protected static function booted()
    {
        static::deleted(function (self $profile) {
            $profile->categories()->detach(); //удалим прилинкованные категории

            if ($profile->profileVideo)
                $profile->profileVideo->delete();//удалим прикрепленное видео

            if ($profile->profileImage)
                $profile->profileImage->delete();//удалим прикрепленное фото

            if ($profile->portfolioImages)//удалим прикрепленные фото
                foreach ($profile->portfolioImages as $image_model)
                    $image_model->delete();

            if ($profile->portfolioLinks)//удалим прикрепленные фото
                foreach ($profile->portfolioLinks as $link_model)
                    $link_model->delete();

        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Прилинковка выбранных языков к профилю
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

    /**
     * Получение массива IDшников предпочитаемых языков
     * @return array
     */
    public function getLanguagesIds(): array
    {
        $languages = $this->languages;
        return ($languages) ? Arr::pluck($languages, 'id') : [];
    }

    public function profileImage()
    {
        return $this->hasOne(ProfileImage::class);
    }

    public function profileVideo()
    {
        return $this->hasOne(ProfileVideo::class);
    }

    /**
     * Получить ссылку на видео
     *
     * @return string|null
     */
    public function getProfileVideoLink()
    {
        if ($this->profileVideo) {
            return $this->profileVideo->getLink();
        }
        return null;
    }

    /**
     * Получить ссылку на фото профиля
     * @return string
     */
    public function getProfileImageLink(): string
    {
        if ($this->profileImage) {
            return $this->profileImage->getLink();
        }
        return "";
    }

    /**
     * Получить ссылку на превью фото профиля
     * @return string
     */
    public function getProfilePreviewImageLink(): string
    {
        if ($this->profileImage) {
            return $this->profileImage->getPreviewLink();
        }
        return "";
    }

    /**
     * Получение данных портфолио
     * @return array
     */
    public function getPortfolio(): array
    {
        return [
            'images' => $this->getPortfolioImagesAsArray(),
            'links' => $this->getPortfolioLinksAsArray()
        ];
    }

    /**
     * Получение массива данных о фото в портфолио
     * @return array
     */
    public function getPortfolioImagesAsArray(): array
    {
        $images = [];
        $models = $this->portfolioImages;

        if ($models)
            foreach ($models as $model) {
                $images[] = [
                    'id' => $model->id,
                    'image' => $model->getLink(),
                    'image_preview' => $model->getPreviewLink()
                ];
            }

        return $images;
    }

    /**
     * Получение данных о ссылках в портфолио
     * @return array
     */
    public function getPortfolioLinksAsArray()
    {
        return $this->portfolioLinks()->get(['id', 'link'])->toArray();
    }

    public function portfolioLinks()
    {
        return $this->hasMany(PortfolioLink::class);
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

    public function portfolioImages()
    {
        return $this->hasMany(PortfolioImage::class);
    }

    /**
     * Аксессор
     * @param $value
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }

    /**
     * Аксессор на получение даты рождения
     * @param $value
     * @return string
     */
    public function getDateOfBirthAttribute($value): string
    {
        return date('Y-m-d', strtotime($value));
    }

    /**
     * Обновление адреса профиля
     * @param $address
     * @param $place_id
     * @return bool
     */
    public function setLocation($address, $place_id): bool
    {
        $this->address = $address;
        $this->place_id = $place_id;
        return $this->save();
    }

    /**
     * Мутатор на поле даты рождения
     * @param $value
     */
    public function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = date('Y-m-d', strtotime($value));
    }

    /**
     * Прилинковка предпочитаемых категорий профиля
     * @param $categories
     * @return bool
     */
    public function updateCategories($categories): bool
    {
        if ($categories) {
            $this->categories()->detach();
            $this->categories()->attach($categories);

            return true;
        }

        return false;
    }

    /**
     * Получение ID предпочитаемых категорий профиля
     * @return array
     */
    public function getCategoriesIds()
    {
        $categories = $this->categories;
        return ($categories) ? Arr::pluck($categories, 'id') : [];
    }

    /**
     * Пересчет рейтинга профиля
     */
    public function recountRating(): void
    {
        $this->rating = 5.0;
        $this->save();
    }

    /**
     * Добавить количество выполненных задач
     */
    public function addNumberPerformerTask(): void
    {
        $this->number_performer_tasks += 1;
        $this->save();
    }

    /**
     * Добавить количество созданных задач
     */
    public function addNumberEmployerTask(): void
    {
        $this->number_employer_tasks += 1;
        $this->save();
    }

    /**
     * Добавить количество отзывов
     */
    public function addNumberReview(): void
    {
        $this->number_review += 1;
        $this->save();
    }

    /**
     * Загрузка фото профиля
     * @param $image object|string
     * @return bool
     */
    public function uploadProfileImage($image)
    {
        $profile_image = $this->profileImage;
        if (!$profile_image) {
            $profile_image = ProfileImage::create(['profile_id' => $this->id]);
        }

        return is_string($image) ? $profile_image->copyImage($image, $this->id) : $profile_image->uploadImage($image, $this->id);
    }

    /**
     * Загрузка видео профиля
     * @param $video object|string
     * @return bool
     */
    public function uploadProfileVideo($video): bool
    {
        $profile_video = $this->profileVideo;
        if (!$profile_video) {
            $profile_video = ProfileVideo::create(['profile_id' => $this->id]);
        }

        return is_string($video) ? $profile_video->copyVideo($video, $this->id) : $profile_video->uploadVideo($video, $this->id);
    }
}
