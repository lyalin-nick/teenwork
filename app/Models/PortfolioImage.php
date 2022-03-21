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
            foreach ($images as $image_arr) {
                $model = self::create([
                    'profile_id' => $profile_id,
                    'description' => $image_arr['description']
                ]);

                if (is_string($image_arr['image'])) {
                    if ($model->save() && $model->copyImage($image_arr['image'], $profile_id)) {
                        $models[] = $model;
                    }
                } else {
                    if ($model->save() && $model->uploadImage($image_arr['image'], $profile_id)) {
                        $models[] = $model;
                    }
                }
            }
            return count($images) === count($models);
        }

        return false;
    }

    public static function createModel($image, $profile_id, $description): bool
    {
        $model = self::create([
            'profile_id' => $profile_id,
            'description' => $description
        ]);

        return is_string($image) ? $model->save() && $model->copyImage($image, $profile_id) : $model->save() && $model->uploadImage($image, $profile_id);

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
