<?php

namespace App\Models\Traits;

use App\Models\ImageFilters\MiniatureFilter;
use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait ImageTrait
{

    public function copyImage($temp_path, $parent_id)
    {
        $img_path = $this->createPath($parent_id);

        $created = $this->copyToNewPath($temp_path, $img_path);

        if ($created) {
            $path_info = pathinfo(Storage::disk('public')->path($created));

            $this->path = $img_path;
            $this->name = $path_info['filename'];
            $this->ext = $path_info['extension'];

            return $this->save() && $this->createMiniature($this->path, $this->name, $this->ext);
        }
        return false;
    }

    public function createPath($parent_id = null): string
    {
        $file_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $file_path .= $parent_id . DIRECTORY_SEPARATOR;

        return $file_path;
    }

    protected function copyToNewPath($temp_img_path, $img_path)
    {
        try {
            $this->checkExistImageAndDelete();

            return Storage::disk('public')->putFile($img_path, new File(Storage::disk('public')->path($temp_img_path)));
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    public function checkExistImageAndDelete(): void
    {
        if ($this->hasImage()) {
            $this->deleteImage();
            $this->deleteResizedImages();
        }
    }

    public function hasImage(): bool
    {
        $profile_photo_path = $this->getFullPath();

        return !empty($profile_photo_path) && is_file(Storage::disk('public')->path($profile_photo_path));
    }

    public function getFullPath($suffix = ''): string
    {
        return $this->path . $this->name . "{$suffix}." . $this->ext;
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
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    public function uploadImage($image, $parent_id)
    {
        $img_path = $this->createPath($parent_id);

        $created = $this->createImage($image, $img_path);

        if ($created) {
            $path_info = pathinfo(Storage::disk('public')->path($created));

            $this->path = $img_path;
            $this->name = $path_info['filename'];
            $this->ext = $path_info['extension'];

            return $this->save() && $this->createMiniature($this->path, $this->name, $this->ext);
        }
        return false;
    }

    protected function createImage($image, $img_path)
    {
        try {
            $this->checkExistImageAndDelete();

            return Storage::disk('public')->putFile($img_path, $image);
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
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

    public function getImageLink(): string
    {
        return asset(Storage::url($this->getFullPath()));
    }
}
