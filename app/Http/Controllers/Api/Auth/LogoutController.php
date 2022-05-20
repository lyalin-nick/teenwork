<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use Auth;
use Illuminate\Http\JsonResponse;

class LogoutController extends BaseController
{
    /**
     * Выход
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();

        $result = $user->currentAccessToken()->delete();
        if ($result) {
            return $this->sendResponse(['deleted' => $result], 'Token delete successfully');
        }

        return $this->sendError('Token not found', [], 401);
    }
}
