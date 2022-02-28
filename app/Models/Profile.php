<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(User::class, 'id', 'user_id');
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

    public function getPhotoLink()
    {
        return asset($this->photo_path . $this->photo_name . '.' . $this->photo_ext);
    }

    public function profilePhotos()
    {
        return $this->hasMany(PortfolioPhoto::class);
    }

    public function profileLinks()
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

//    public function setDateOfBirthAttribute($value)
//    {
//        $this->attributes['start_time'] = date('H:i:s', strtotime($value));
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

    public function uploadImage($image): bool
    {
        $path = 'uploads/' . strtolower(class_basename(self::class)) . '/' . $this->id;

        $img_path = $image->store($path, 'public');
        if ($img_path) {
            $path_info = pathinfo(asset('/storage/' . $img_path));
            $this->update([
                'photo_path' => 'public' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR,
                'photo_name' => $path_info['filename'],
                'photo_ext' => $path_info['extension'],
            ]);
            return true;
        } else {
            return false;
        }
    }
}
