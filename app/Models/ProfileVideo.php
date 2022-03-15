<?php

namespace App\Models;

use App\Models\Traits\VideoTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $profile_id
 * @property string $path
 * @property string $name
 * @property string $ext
 */
class ProfileVideo extends Model
{
    use HasFactory, VideoTrait;

    protected $fillable = [
        'profile_id', 'path', 'name', 'ext'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function getLink()
    {
        return $this->getVideoLink();
    }

    public function uploadVideo($video_uri): bool
    {
        return $this->uploadVideoFromUri($video_uri, $this->profile_id);
    }
}
