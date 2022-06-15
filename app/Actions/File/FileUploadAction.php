<?php

namespace App\Actions\File;

use Illuminate\Support\Facades\Storage;

class FileUploadAction
{

    /**
     * @param $file
     * @param $folder
     * @return false|string
     */
    public function __invoke($file, $folder = 'tmp')
    {
        return Storage::disk('public')->putFile($folder, $file);
    }
}
