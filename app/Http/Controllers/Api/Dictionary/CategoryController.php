<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\Category;

class CategoryController extends BaseController
{
    public function index()
    {
        $data = Category::getAllCategoriesAsArray();

        return $this->sendResponse($data, '');
    }

    public function grouped($flag)
    {
        $data = Category::getAllCategoriesAsArrayByFlag($flag);

        return $this->sendResponse($data, '');
    }
}
