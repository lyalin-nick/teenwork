<?php

namespace App\Models\Helpers;


use Illuminate\Support\Facades\Storage;

class UploadingHelper
{
    public static function uploadFiles($files)
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = Storage::disk('public')->putFile('tmp', $file);
        }

        return $paths;
    }

    public static function uploadFile($file)
    {
        $path = Storage::disk('public')->putFile('tmp', $file);

        return $path;
    }
}
