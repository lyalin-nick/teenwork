<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $date_of_birth
 * @property string $about
 * @property string $address
 * @property string $place_id
 * @property string $longitude
 * @property integer $number_performer_tasks
 * @property integer $number_customer_tasks
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

    public static function createProfile($data)
    {
        return self::create($data);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function getLanguagesIds()
    {
        return $this->languages()->pluck('language_id');
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
     * @return string
     */
    public function getProfileVideoLink(): string
    {
        if ($this->profileVideo) {
            return $this->profileVideo->getLink();
        }
        return '';
    }

    public function getProfileImageLink(): string
    {
        if ($this->profileImage) {
            return $this->profileImage->getLink();
        }
        return "";
    }

    public function getProfilePreviewImageLink(): string
    {
        if ($this->profileImage) {
            return $this->profileImage->getPreviewLink();
        }
        return "";
    }

    public function getPortfolio(): array
    {
        return [
            'images' => $this->getPortfolioImagesAsArray(),
            'links' => $this->getPortfolioLinksAsArray()
        ];
    }

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

    public function getPortfolioLinksAsArray()
    {
        return $this->portfolioLinks()->get(['id', 'link'])->toArray();
    }

    public function portfolioLinks()
    {
        return $this->hasMany(PortfolioLink::class);
    }

    public function portfolioImages()
    {
        return $this->hasMany(PortfolioImage::class);
    }

    public function getDateOfBirthAttribute($value): string
    {
        return date('Y-m-d', strtotime($value));
    }

    public function setLocation($address, $latitude, $longitude): void
    {
        $this->address = $address;
        $this->save();
    }

    public function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = date('Y-m-d', strtotime($value));
    }

    public function updateCategories($categories): bool
    {
        if ($categories) {
            $this->categories()->detach();
            $this->categories()->attach($categories);

            return true;
        }

        return false;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function getCategoriesIds()
    {
        return $this->categories()->pluck('id');
    }

    public function recountRating(): void
    {
        $this->rating = 5.0;
        $this->save();
    }

    public function addNumberPerformerTask(): void
    {
        $this->number_performer_tasks += 1;
        $this->save();
    }

    public function addNumberCustomerTask(): void
    {
        $this->number_customer_tasks += 1;
        $this->save();
    }

    public function addNumberReview(): void
    {
        $this->number_review += 1;
        $this->save();
    }

    /**
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
