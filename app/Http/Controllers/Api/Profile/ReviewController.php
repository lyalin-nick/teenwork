<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Review;
use Auth;
use Illuminate\Http\JsonResponse;

class ReviewController extends BaseController
{

    /**
     * Отображение всех отзывов
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $reviews_data = Review::search($user->id, $request->dates ?? null);
        return $this->sendResponse($reviews_data, 'Reviews');
    }

    /**
     * Получение количества отзывов по оценкам
     *
     * @return JsonResponse
     */
    public function count(): JsonResponse
    {
        $user = Auth::user();

        $count_by_rating = $user->getStars();

        return $this->sendResponse($count_by_rating, 'Portfolio info');
    }
}
