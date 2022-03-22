<?php

namespace App\Models;

use App\Models\Traits\VideoTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $profile_id
 * @property string $path
 * @property string $name
 * @property string $ext
 *
 * @property Profile $profile
 *
 * @mixin Builder
 */
class ProfileVideo extends Model
{
    use HasFactory, VideoTrait;

    protected $fillable = [
        'profile_id', 'path', 'name', 'ext'
    ];

    protected static function booted()
    {
        static::deleted(function (self $profile_video) {
            $profile_video->checkExistVideoAndDelete();
        });
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

//    public function upload($video): bool
//    {
//        return $this->uploadVideo($video, $this->profile_id);
//    }

    /**
     * Получение ссылки на видео профиля
     * @return string|null
     */
    public function getLink()
    {
        return $this->getVideoLink();
    }
}
