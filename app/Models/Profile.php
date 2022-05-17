<?php

namespace App\Models;

use App\Models\Helpers\GoogleMap;
use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
 * @property string $status
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

    const
        STATUS_PRO = 'professional',
        STATUS_EXPERT = 'expert';

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

    public static function getStatuses(): array
    {
        return [
            null => 'No status',
            self::STATUS_PRO => self::STATUS_PRO,
            self::STATUS_EXPERT => self::STATUS_EXPERT,
        ];
    }

    protected static function booted()
    {
        static::updating(function ($profile) {
            if ($profile->isDirty('place_id')) { // обновление координат, если был изменен адрес
                $coords = GoogleMap::getCoordinates($profile->place_id);
                $coords = $coords ?: ['lat' => 53.213672, 'lng' => 45.061300];
                $profile->lat = $coords['lat'];
                $profile->lng = $coords['lng'];
            }
        });

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

    /**
     * @param array|null $profile_data
     * @param array|null $languages
     * @param array|null $categories
     * @param string|null $image
     * @param string|null $video
     * @param array|null $portfolio_images
     * @param array|null $portfolio_links
     * @return self $this
     */
    public function performerProfile($profile_data, $languages, $categories, $image, $video, $portfolio_images, $portfolio_links)
    {
        $this->update($profile_data);

        if ($languages) {
            $this->linkToLanguages($languages);
        }

        if ($categories) {
            $this->updateCategories($categories);
        }

        if ($image) {
            $this->uploadProfileImage($image);
        }

        if ($video) {
            $this->uploadProfileVideo($video);
        }

        if ($portfolio_images) {
            $result = PortfolioImage::createModels($portfolio_images, $this->id);
        }

        if ($portfolio_links) {
            $result = PortfolioLink::createModels($portfolio_links, $this->id);
        }

        return $this;
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

    public function user()
    {
        return $this->belongsTo(User::class);
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
                    'image_preview' => $model->getPreviewLink(),
                    'description' => $model->description
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
    public function getRatingAttribute($value)
    {
        return round($value, 2);
    }

    /**
     * Мутатор
     *
     * @param $value
     */
    public function setRatingAttribute($value)
    {
        $this->attributes['rating'] = round($value, 2);
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
        return $this->hasMany(PortfolioImage::class, 'profile_id', 'id');
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
     * Получение ID предпочитаемых категорий профиля
     * @return array
     */
    public function getCategoriesIds()
    {
        $categories = $this->categories;
        return ($categories) ? Arr::pluck($categories, 'id') : [];
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
     * Смотрим есть ли категория задачи у профиля
     *
     * @param Builder $query
     * @param $category_id\
     */
    public function scopeCategoryMatches(Builder $query, $category_id)
    {
        $query->addSelect(DB::raw("(SELECT COUNT(*) FROM `category_profile` WHERE `profiles`.`id`=`category_profile`.`profile_id` AND `category_profile`.`category_id`={$category_id}) AS `category_matches`"));
//        $query->orderBy('category_matches', 'desc');
    }

    /**
     * Считаем сколько языков совпадает
     *
     * @param Builder $query
     * @param array $languages
     */
    public function scopeLanguagesMatches(Builder $query, $languages = [Language::ENGLISH_LANGUAGE])
    {
        $languages_arr = implode(',', $languages);
        $query->addSelect(DB::raw("(SELECT COUNT(*) FROM `language_profile` WHERE `profiles`.`id`=`language_profile`.`profile_id` AND `language_profile`.`language_id` IN ({$languages_arr})) AS languages_matches"));
//        $query->orderBy('languages_matches', 'desc');
    }

    public function scopeNearby(Builder $query, $ulat = '53.213672', $ulng = '45.061300')
    {
        $query->addSelect(DB::raw("ACOS(SIN(PI()*lat/180.0)*SIN(PI()*{$ulat}/180.0)+COS(PI()*lat/180.0)*COS(PI()*{$ulat}/180.0)*COS(PI()*{$ulng}/180.0-PI()*lng/180.0))*6371 AS distance")); // формула расчета расстояния от заданных координат
        $query->orderBy('distance');
    }
}
