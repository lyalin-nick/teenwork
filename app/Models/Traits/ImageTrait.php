<?php

namespace App\Models\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait ImageTrait
{

    public function uploadImageFromBase64($image_base64, $parent_id = null)
    {
        $image = base64_decode($image_base64);

        $img_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $img_path .= $parent_id . DIRECTORY_SEPARATOR;

        $ext = 'jpg'; //TODO: подумать как выудить расширение картинки

        try {
            if (is_file($img_path . $this->id . '.' . $ext)) {
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

            return $this->save();
        }
        return false;
    }


    public function getImageLink()
    {
        return asset(Storage::url($this->getFullPath()));
    }

    public function getFullPath()
    {
        return $this->path . $this->name . '.' . $this->ext;
    }
}
