<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends BaseController
{
    /**
     * Выход
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        $result = $user->currentAccessToken()->delete();
        if ($result) {
            return $this->sendResponse(['deleted' => $result], 'Token delete successfully');
        }

        return $this->sendError('Token not found', [], 401);
    }
}
