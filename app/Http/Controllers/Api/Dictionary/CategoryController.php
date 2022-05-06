<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends BaseController
{
    /**
     * Получение списка категорий
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = Category::getAllCategoriesAsArray();

        return $this->sendResponse($data, '');
    }

    /**
     * Получение задач по флагу
     *
     * @param string $flag
     * @return JsonResponse
     */
    public function grouped(string $flag): JsonResponse
    {
        $data = Category::getAllCategoriesAsArrayByFlag($flag);

        return $this->sendResponse($data, '');
    }
}
