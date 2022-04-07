<?php

namespace App\Http\Controllers\Api\Performer\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    public function index(Request $request)
    {
        $reviews = Review::search($request);
        return $this->sendResponse($reviews, 'Reviews');
    }

    public function count(Request $request)
    {
        $user = $request->user();

        $count_by_rating = [
            5 => $user->reviews()->where('rating', '=', 5)->count(),
            4 => $user->reviews()->where('rating', '=', 4)->count(),
            3 => $user->reviews()->where('rating', '=', 3)->count(),
            2 => $user->reviews()->where('rating', '=', 2)->count(),
            1 => $user->reviews()->where('rating', '=', 1)->count(),
        ];

        return $this->sendResponse($count_by_rating, 'Portfolio info');
    }
}
