<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
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
class ProfileImage extends Model
{
    use HasFactory, ImageTrait;

    protected $configImages = [
        '_mini' => [
            'width' => 128,
            'height' => 128
        ]
    ];

    protected $fillable = [
        'profile_id', 'path', 'name', 'ext'
    ];

    protected static function booted()
    {
        static::deleted(function (self $profile_image) {
            $profile_image->checkExistImageAndDelete();
        });
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    /**
     * Получить ссылку на фото профиля
     * @return string
     */
    public function getLink(): string
    {
        return $this->getImageLink();
    }

    /**
     * Получить ссылку на превью фото профиля
     * @return string
     */
    public function getPreviewLink(): string
    {
        return $this->getImageLink('_mini');
    }
}
