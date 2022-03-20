<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends BaseController
{

    /**
     * @OA\Post (
     *     path="/logout",
     *     operationId="logout",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response="200",
     *         description="Success logout",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Token not found.",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Header auth token not found.",
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $header_auth = $request->header('Authorization');
        $header_auth = str_replace('Bearer ', '', $header_auth);
        $header_auth_arr = explode('|', $header_auth);
        if (isset($header_auth_arr[0])) {
            $header_id = $header_auth_arr[0];

            $result = $user->tokens()->where('id', $header_id)->delete();
            if ($result) {
                return $this->sendResponse(['deleted' => $result], 'Token delete successfully');
            }

            return $this->sendError('Token not found', []);
        }
        return $this->sendError('Header auth token not found', 401);
    }
}
