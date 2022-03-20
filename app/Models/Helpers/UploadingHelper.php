<?php

namespace App\Models\Helpers;


use Illuminate\Support\Facades\Storage;

class UploadingHelper
{
    public static function uploadFiles($files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = Storage::disk('public')->putFile('tmp', $file);
        }

        return $paths;
    }

    public static function uploadFile($file): string
    {
        $path = Storage::disk('public')->putFile('tmp', $file);

        return $path;
    }
}
