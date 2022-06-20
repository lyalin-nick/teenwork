<?php

namespace App\Actions\File;

use Illuminate\Support\Facades\Storage;

class FileUploadAction
{

    /**
     * @param $file
     * @param string $folder
     * @return false|string
     */
    public function __invoke($file, string $folder = 'tmp')
    {
        return Storage::disk('public')->putFile($folder, $file);
    }
}
