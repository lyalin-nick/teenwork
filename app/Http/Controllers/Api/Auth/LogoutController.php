<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class LogoutController extends BaseController
{
    public function logout(Request $request)
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

            return $this->sendError('Token not found');
        }
        return $this->sendError('Header auth token not found');
    }
}
