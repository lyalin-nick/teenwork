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
        $user = $request->user();
        $reviews_data = Review::search($user->id, $request->dates ?? null);
        return $this->sendResponse($reviews_data, 'Reviews');
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
