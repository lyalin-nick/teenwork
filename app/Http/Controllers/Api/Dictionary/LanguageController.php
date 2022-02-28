<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\Category;
use App\Models\Language;

class LanguageController extends BaseController
{
    public function index()
    {
        $data = Language::getAllLanguagesAsArray();

        return $this->sendResponse($data, '');
    }
}
