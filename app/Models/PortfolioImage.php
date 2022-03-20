<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $profile_id
 * @property string $path
 * @property string $name
 * @property string $ext
 * @property string $description
 *
 * @mixin Builder
 */
class PortfolioImage extends Model
{
    use HasFactory, ImageTrait;

    protected $configImages = [
        '_mini' => [
            'width' => 128,
            'height' => 128
        ]
    ];

    protected $fillable = [
        'profile_id', 'path', 'name', 'ext', 'description'
    ];

    public static function createModels(array $images, $profile_id): bool
    {
        if ($images) {
            $models = [];
            foreach ($images as $image) {
                $model = new self([
                    'profile_id' => $profile_id
                ]);
                if (is_string($image)) {
                    if ($model->save() && $model->copyImage($image, $profile_id)) {
                        $models[] = $model;
                    }
                } else {
                    if ($model->save() && $model->uploadImage($image, $profile_id)) {
                        $models[] = $model;
                    }
                }
            }
            return count($images) === count($models);
        }

        return false;
    }

    public function profile()
    {
        $this->belongsTo(Profile::class, 'profile_id');
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
