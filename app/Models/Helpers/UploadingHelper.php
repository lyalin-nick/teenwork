<?php

namespace App\Models\Helpers;


use Illuminate\Support\Facades\Storage;

class UploadingHelper
{
    /**
     * Загрузка файлов
     * @param $files
     * @return array
     */
    public static function uploadFiles($files): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = Storage::disk('public')->putFile('tmp', $file);
        }

        return $paths;
    }

    /**
     * Загрузка одного файла
     * @param $file
     * @return string
     */
    public static function uploadFile($file): string
    {
        $path = Storage::disk('public')->putFile('tmp', $file);

        return $path;
    }
}
