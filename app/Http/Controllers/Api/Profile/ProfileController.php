<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class ProfileController extends BaseController
{
    public function index(Request $request)
    {
        $user = $request->user();

        return $this->sendResponse($user->getFullData(), 'Success');
    }
}
