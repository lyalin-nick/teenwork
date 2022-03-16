<?php

namespace App\Models\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait VideoTrait
{

    public function copyVideo($path, $parent_id)
    {
        $video_uri_info = pathinfo(Storage::disk('public')->path($path));
        $ext = $video_uri_info['extension'];

        $video_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($parent_id)
            $video_path .= $parent_id . DIRECTORY_SEPARATOR;

        try {
            if ($this->hasVideo()) {
                $this->deleteVideo();
            }
            $created = Storage::disk('public')->copy($path, $video_path . $this->id . '.' . $ext);
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

    /**
     * @return string|null
     */
    public function getVideoLink(): ?string
    {
        return $this->hasVideo() ? asset(Storage::url($this->getFullPath())) : null;
    }

    public function getFullPath(): string
    {
        return $this->path . $this->name . "." . $this->ext;
    }

    public function hasVideo(): bool
    {
        $video_path = $this->getFullPath();

        return !empty($video_path) && is_file(Storage::disk('public')->path($video_path));
    }

    public function deleteVideo(): bool
    {
        return Storage::disk('public')->delete($this->getFullPath());
    }
}
