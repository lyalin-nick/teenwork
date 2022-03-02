<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\PortfolioPhoto;
use Illuminate\Support\Facades\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $portfolio_photos = $profile->profilePhotos;
        $portfolio_links = $profile->profileLinks;



    }
}
