<?php

namespace App\Models\Traits;

use App\Models\ImageFilters\MiniatureFilter;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait ImageTrait
{

    public function uploadImageFromUri($image_uri, $parent_id = null)
    {
        $image_uri_info = pathinfo($image_uri);

        $img_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $img_path .= $parent_id . DIRECTORY_SEPARATOR;

        $ext = $image_uri_info['extension'];

        try {
            if (is_file(Storage::disk('public')->path($img_path . $this->id . '.' . $ext))) {
                Storage::delete($img_path . $this->id . '.' . $ext);
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

            return $this->createMiniature($this->path, $this->name, $this->ext) && $this->save();
        }
        return false;
    }

    public function createMiniature($path, $name, $ext): bool
    {
        $image_path = $path . $name . '.' . $ext;

        try {

            foreach ($this->configImages as $suffix => $config) {
                $img = Image::make(Storage::disk('public')->path($image_path));

                if (is_file(Storage::disk('public')->path($path . $this->id . $suffix . '.' . $ext))) {
                    Storage::delete($path . $this->id . '.' . $ext);
                }

                $img->filter(new MiniatureFilter($config['width'], $config['height']));

                $img->save(Storage::disk('public')->path($path . $name . $suffix . '.' . $ext));
            }

            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }


    public function getImageLink()
    {
        return asset(Storage::url($this->getFullPath()));
    }

    public function getFullPath()
    {
        return $this->path . $this->name . '.' . $this->ext;
    }

    /////// NOT USED
    /*

    public function uploadImageFromBase64($image_base64, $parent_id = null)
    {
        $image = base64_decode($image_base64);

        $img_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $img_path .= $parent_id . DIRECTORY_SEPARATOR;

        $ext = 'jpg'; //TODO: подумать как выудить расширение картинки

        try {
            if (is_file(Storage::disk('public')->path($img_path . $this->id . '.' . $ext))) {
                Storage::delete($img_path . $this->id . '.' . $ext);
            }
            $created = Storage::disk('public')->put($img_path . $this->id . '.' . $ext, $image);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $created = false;
        }

        if ($created) {

            $this->path = $img_path;
            $this->name = $this->id;
            $this->ext = $ext;

            return $this->createMiniature($this->path, $this->name, $this->ext) && $this->save();
        }
        return false;
    }

     */
}
