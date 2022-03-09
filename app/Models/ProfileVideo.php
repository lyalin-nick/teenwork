<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer $profile_id
 * @property string $path
 * @property string $name
 * @property string $ext
 */
class ProfileVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id', 'path', 'name', 'ext'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function getPath()
    {
        if ($this->path && $this->name && $this->ext) {
            return Storage::disk('public')->path($this->path . $this->name . '.' . $this->ext);
        }
        return null;
    }

    public function getLink()
    {
        if ($this->path && $this->name && $this->ext) {
            return Storage::disk('public')->url($this->path . $this->name . '.' . $this->ext);
        }
        return null;
    }

    public function uploadVideo($video_base64): bool
    {

        $image = base64_decode($video_base64);

        $img_path = strtolower(class_basename($this)) . DIRECTORY_SEPARATOR;
        if ($this->profile_id)
            $img_path .= $this->profile_id . DIRECTORY_SEPARATOR;

        $ext = 'mp4'; //TODO: подумать как выудить расширение картинки

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

            return $this->save();
        }

        return false;
    }
}
