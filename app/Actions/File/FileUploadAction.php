<?php

namespace App\Actions\File;

use Illuminate\Support\Facades\Storage;

class FileUploadAction
{

    public function __invoke($file): bool
    {
        return Storage::disk('public')->putFile('tmp', $file);
    }
}
