<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    public function index(Request $request)
    {
        $user = $request->user();

        return $this->sendResponse($user->getFullData(), 'Success');
    }
}
