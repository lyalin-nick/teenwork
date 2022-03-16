<?php

namespace App\Models\Traits;

use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait VideoTrait
{

    public function copyVideo($temp_path, $parent_id): bool
    {
        $video_path = $this->createPath($parent_id);

        $created = $this->copyToNewPath($temp_path, $video_path);

        if ($created) {
            $path_info = pathinfo(Storage::disk('public')->path($created));

            $this->path = $video_path;
            $this->name = $path_info['filename'];
            $this->ext = $path_info['extension'];

            return $this->save();
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
            $this->checkExistVideoAndDelete();

            return Storage::disk('public')->putFile($img_path, new File(Storage::disk('public')->path($temp_img_path)));
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    protected function checkExistVideoAndDelete(): void
    {
        if ($this->hasVideo())
            $this->deleteVideo();
    }

    public function hasVideo(): bool
    {
        $video_path = $this->getFullPath();

        return !empty($video_path) && is_file(Storage::disk('public')->path($video_path));
    }

    public function getFullPath(): string
    {
        return $this->path . $this->name . "." . $this->ext;
    }

    public function deleteVideo(): bool
    {
        return Storage::disk('public')->delete($this->getFullPath());
    }

    public function uploadVideo($video, $parent_id)
    {
        $video_path = $this->createPath($parent_id);

        $created = $this->createVideo($video, $video_path);

        if ($created) {
            $path_info = pathinfo(Storage::disk('public')->path($created));

            $this->path = $video_path;
            $this->name = $path_info['filename'];
            $this->ext = $path_info['extension'];

            return $this->save();
        }
        return false;
    }

    protected function createVideo($video, $video_path)
    {
        try {
            $this->checkExistVideoAndDelete();

            return Storage::disk('public')->putFile($video_path, $video);
        } catch (Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return false;
        }
    }

    public function getVideoLink(): ?string
    {
        return $this->hasVideo() ? asset(Storage::url($this->getFullPath())) : null;
    }

    /*
        public function uploadVideoFromUri($video_uri, $parent_id): bool
        {
            $video_uri_info = pathinfo($video_uri);

            $video_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
            if ($parent_id)
                $video_path .= $parent_id . DIRECTORY_SEPARATOR;

            $ext = $video_uri_info['extension'];

            try {
                if ($this->hasVideo()) {
                    $this->deleteVideo();
                }
                $created = Storage::disk('public')->put($video_path . $this->id . '.' . $ext, file_get_contents($video_uri));
            } catch (Exception $e) {
                Log::error($e->getMessage(), $e->getTrace());
                $created = false;
            }

            if ($created) {

                $this->path = $video_path;
                $this->name = $this->id;
                $this->ext = $ext;

                return $this->save();
            }

            return false;
        }
    */

}
