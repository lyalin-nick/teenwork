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

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function getLink(): string
    {
        return $this->getImageLink();
    }

    public function getPreviewLink(): string
    {
        return $this->getImageLink('_mini');
    }
}
