<?php

namespace App\Models;

use App\Models\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
class PortfolioPhoto extends Model
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

                if ($model->save() && $model->uploadImageFromUri($image, $profile_id)) {
                    $models[] = $model;
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

    public function getPhotoPreviewLink(): string
    {
        if ($this->path && $this->name && $this->ext) {
            if (is_file(Storage::disk('public')->path($this->path . $this->name . '.' . $this->ext)) && !is_file(Storage::disk('public')->path($this->path . $this->name . '_mini.' . $this->ext)))
                $this->createMiniature($this->path, $this->name, $this->ext);

            if (is_file(Storage::disk('public')->path($this->path . $this->name . '_mini.' . $this->ext)))
                return Storage::disk('public')->url($this->path . $this->name . '_mini.' . $this->ext);
        }
        return "";
    }

    public function getPhotoLink(): string
    {
        if ($this->path && $this->name && $this->ext) {
            if (is_file(Storage::disk('public')->path($this->path . $this->name . '.' . $this->ext)))
                return Storage::disk('public')->url($this->path . $this->name . '.' . $this->ext);
        }
        return "";
    }

}
