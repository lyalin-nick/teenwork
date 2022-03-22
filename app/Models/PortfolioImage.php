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
 * @property Profile $profile
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

    /**
     * Создание моделей фото портфолио и загрузка фото
     *
     * @param array $images
     * @param $profile_id
     * @return bool
     */
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

    /**
     * Создание одной модели фото портфолио и загрузка фото
     * @param $image
     * @param $profile_id
     * @param $description
     * @return bool
     */
    public static function createModel($image, $profile_id, $description): bool
    {
        $model = self::create([
            'profile_id' => $profile_id,
            'description' => $description
        ]);

        return is_string($image) ? $model->save() && $model->copyImage($image, $profile_id) : $model->save() && $model->uploadImage($image, $profile_id);

    }

    protected static function booted()
    {
        static::deleted(function (self $portfolio_image) {
            $portfolio_image->checkExistImageAndDelete();
        });
    }

    public function profile()
    {
        $this->belongsTo(Profile::class, 'profile_id');
    }

    /**
     * Получить ссылку на фото
     * @return string
     */
    public function getLink(): string
    {
        return $this->getImageLink();
    }

    /**
     * Получение ссылки на превью фото
     * @return string
     */
    public function getPreviewLink(): string
    {
        return $this->getImageLink('_mini');
    }
}
