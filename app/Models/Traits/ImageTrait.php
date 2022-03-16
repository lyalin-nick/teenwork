<?php

namespace App\Models\Traits;

use App\Models\ImageFilters\MiniatureFilter;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait ImageTrait
{

    public function existsImage($path)
    {
        return Storage::disk('public')->exists($path);
    }

    public function copyImage($path, $parent_id)
    {
        $image_uri_info = pathinfo(Storage::disk('public')->path($path));
        $ext = $image_uri_info['extension'];

        $img_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $img_path .= $parent_id . DIRECTORY_SEPARATOR;

        try {
            if ($this->hasImage()) {
                $this->deleteImage();
                $this->deleteResizedImages();
            }
            $created = Storage::disk('public')->copy($path, $img_path . $this->id . '.' . $ext);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $created = false;
        }

        if ($created) {
            $this->path = $img_path;
            $this->name = $this->id;
            $this->ext = $ext;

            return $this->save() && $this->createMiniature($img_path, $this->id, $ext);
        }
        return false;
    }

    public function uploadImageFromUri($image_uri, $parent_id = null)
    {
        $image_uri_info = pathinfo($image_uri);

        $img_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $img_path .= $parent_id . DIRECTORY_SEPARATOR;

        $ext = $image_uri_info['extension'];

        try {
            if ($this->hasImage()) {
                $this->deleteImage();
                $this->deleteResizedImages();
            }

            $created = Storage::disk('public')->put($img_path . $this->id . '.' . $ext, file_get_contents($image_uri));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $created = false;
        }

        if ($created) {

            $this->path = $img_path;
            $this->name = $this->id;
            $this->ext = $ext;

            return $this->createMiniature($img_path, $this->id, $ext) && $this->save();
        }
        return false;
    }

    public function createMiniature($path, $name, $ext): bool
    {
        try {
            $image_path = $path . $name . '.' . $ext;

            foreach ($this->configImages as $suffix => $config) {
                $img = Image::make(Storage::disk('public')->path($image_path));

                $img->filter(new MiniatureFilter($config['width'], $config['height']));

                $img->save(Storage::disk('public')->path($path . $name . $suffix . '.' . $ext));
            }

            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }


    public function getImageLink(): string
    {
        return asset(Storage::url($this->getFullPath()));
    }

    public function getFullPath($suffix = ''): string
    {
        return $this->path . $this->name . "{$suffix}." . $this->ext;
    }

    public function hasImage(): bool
    {
        $profile_photo_path = $this->getFullPath();

        return !empty($profile_photo_path) && is_file(Storage::disk('public')->path($profile_photo_path));
    }

    public function deleteImage(): bool
    {
        return Storage::disk('public')->delete($this->getFullPath());
    }

    public function deleteResizedImages(): void
    {
        if ($this->configImages) {
            foreach ($this->configImages as $suffix => $params) {
                $full_path = $this->getFullPath($suffix);
                if (is_file(Storage::disk('public')->path($full_path)))
                    Storage::disk('public')->delete($full_path);
            }
        }
    }
}
