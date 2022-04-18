<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{

    /**
     * Отображение всех отзывов
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $reviews = Review::search($request);
        return $this->sendResponse($reviews, 'Reviews');
    }

    /**
     * Получение количества отзывов по оценкам
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function count(Request $request)
    {
        $user = $request->user();

        $count_by_rating = $user->getStars();

        return $this->sendResponse($count_by_rating, 'Portfolio info');
    }
}
