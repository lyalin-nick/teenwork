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

        $count_by_rating = $user->getStars();

        return $this->sendResponse($count_by_rating, 'Portfolio info');
    }
}
