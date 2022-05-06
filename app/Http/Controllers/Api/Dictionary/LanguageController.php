<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\Language;
use Illuminate\Http\JsonResponse;

class LanguageController extends BaseController
{
    /**
     * Получение списка языков
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = Language::getAllLanguagesAsArray();

        return $this->sendResponse($data, '');
    }
}
