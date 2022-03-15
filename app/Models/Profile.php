<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $date_of_birth
 * @property string $about
 * @property string $photo_path
 * @property string $photo_name
 * @property string $photo_ext
 * @property string $address
 * @property string $latitude
 * @property string $longitude
 * @property integer $number_performer_tasks
 * @property integer $number_customer_tasks
 * @property float $rating
 * @property integer $number_review
 * @property boolean $push_notification
 * @property boolean $email_notification
 * @property boolean $invisible
 */
class Profile extends Model
{
    use HasFactory;
    use ImageTrait;

    protected $configImages = [
        '_mini' => [
            'width' => 128,
            'height' => 128
        ]
    ];

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'date_of_birth', 'about',
        'photo_path', 'photo_name', 'photo_ext',
        'address', 'place_id', 'latitude', 'longitude'
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
        return $this->languages()->pluck('id');
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

    public function getPhotoLink()
    {
        if ($this->photo_path && $this->photo_name && $this->photo_ext) {
            if (is_file(Storage::disk('public')->path($this->photo_path . $this->photo_name . '.' . $this->photo_ext)))
                return Storage::disk('public')->url($this->photo_path . $this->photo_name . '.' . $this->photo_ext);
        }
        return "";
    }

    public function getPhotoPreviewLink()
    {
        if ($this->photo_path && $this->photo_name && $this->photo_ext) {
            return Storage::disk('public')->url($this->photo_path . $this->photo_name . '_mini.' . $this->photo_ext);
        }
        return "";
    }

    public function getPortfolio(): array
    {
        return [
            'photos' => $this->getPortfolioPhotosAsArray(),
            'links' => $this->getPortfolioLinksAsArray()
        ];
    }

    public function portfolioPhotos()
    {
        return $this->hasMany(PortfolioPhoto::class);
    }

    public function getPortfolioPhotosAsArray()
    {
        $photos = [];
        $models = $this->portfolioPhotos;

        if ($models)
            foreach ($models as $model) {
                $photos[] = [
                    'id' => $model->id,
                    'photo' => $model->getPhotoLink(),
                    'photo_preview' => $model->getPhotoPreviewLink()
                ];
            }

        return $photos;
    }

    public function getPortfolioLinksAsArray()
    {
        return $this->portfolioLinks()->select('id', 'link')->get();
    }

    public function portfolioLinks()
    {
        return $this->hasMany(PortfolioLink::class);
    }

    public function getDateOfBirthAttribute($value)
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

//    public function setPlaceIdAttribute($value)
//    {
//        if (empty($value)) {
//            $address = $this->attributes['address'];
//            $this->attributes['place_id'] = GoogleMap::getPlaceId($address);
//        }
//        $this->attributes['place_id'] = $value;
//    }

    public function refreshCategories($categories): bool
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

    public function uploadImageFromUri($image_uri, $parent_id = null)
    {
        $image_uri_info = pathinfo($image_uri);

        $img_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $img_path .= $parent_id . DIRECTORY_SEPARATOR;

        $ext = $image_uri_info['extension'];

        try {
            if ($this->hasImage()) {
                $this->deleteImage();
            }
            $created = Storage::disk('public')->put($img_path . $this->id . '.' . $ext, file_get_contents($image_uri));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $created = false;
        }

        if ($created) {

            $this->photo_path = $img_path;
            $this->photo_name = $this->id;
            $this->photo_ext = $ext;

            return $this->createMiniature($img_path, $this->id, $ext) && $this->save();
        }
        return false;
    }

    public function uploadProfileVideo($video_uri)
    {
        $profile_video = $this->profileVideo;
        if (!$profile_video) {
            $profile_video = ProfileVideo::create(['profile_id' => $this->id]);
        }

        return $profile_video->uploadVideo($video_uri);
    }

    public function hasImage(): bool
    {
        $profile_photo_path = $this->getFullPath();

        return !empty($profile_photo_path) && is_file(Storage::disk('public')->path($profile_photo_path));
    }

    public function getImageLink(): string
    {
        return asset(Storage::url($this->getFullPath()));
    }

    public function getFullPath(): string
    {
        return $this->photo_path . $this->photo_name . '.' . $this->photo_ext;
    }

    public function deleteImage(): bool
    {
        return Storage::delete(Storage::disk('public')->path($this->getFullPath()));
    }
}
