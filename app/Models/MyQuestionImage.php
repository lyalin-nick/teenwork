<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $my_question_id
 * @property string $path
 * @property string $name
 * @property string $ext
 * @property MyQuestion $myQuestion
 *
 * @mixin Builder
 */
class MyQuestionImage extends Model
{
    use HasFactory, ImageTrait;

    protected $configImages = [
        '_mini' => [
            'width' => 128,
            'height' => 128
        ]
    ];

    protected $fillable = [
        'my_question_id', 'path', 'name', 'ext'
    ];

    public static function createModels(array $images, $my_question_id): bool
    {
        if ($images) {
            $models = [];
            foreach ($images as $image) {

                $model = self::new($image, $my_question_id);
                if ($model) {
                    $models[] = $model;
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
    public static function new($image, $my_question_id): bool
    {
        if (empty($image) || empty($my_question_id))
            return false;

        $model = self::create([
            'my_question_id' => $my_question_id
        ]);

        return is_string($image) ?
            $model->save() && $model->copyImage($image, $my_question_id) :
            $model->save() && $model->uploadImage($image, $my_question_id);
    }

    protected static function booted()
    {
        static::deleted(function (self $my_question_image) {
            $my_question_image->checkExistImageAndDelete();
        });
    }

    public function myQuestion()
    {
        return $this->belongsTo(MyQuestion::class);
    }
}
