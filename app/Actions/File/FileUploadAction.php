<?php

namespace App\Actions\File;

use Illuminate\Support\Facades\Storage;

class FileUploadAction
{

    /**
     * @param $file
     * @return false|string
     */
    public function __invoke($file)
    {
        return Storage::disk('public')->putFile('tmp', $file);
    }
}
