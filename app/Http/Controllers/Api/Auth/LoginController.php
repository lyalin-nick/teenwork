<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends BaseController
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'device_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::findByPhone($request->phone);

        if (!$user) {
            return $this->sendError('Error! User with this email or phone not found.');
        } elseif (!Hash::check($request->password, $user->password)) {
            return $this->sendError('Error! Wrong password.', [], 405);
        } elseif (!$user->isActive()) {
            return $this->sendError('Please, reset your password and try again', [], 406);
        }

        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name);

        return $this->sendResponse(['token' => $token->plainTextToken], 'Success auth');
    }

    public function network($provider, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
            'device_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $socialite_user = Socialite::driver($provider)->userFromToken($request->access_token);

        if ($socialite_user) {
            $user = User::findByNetwork($provider, $socialite_user);

            $user->tokens()->where('name', $request->device_name)->delete();

            $token = $user->createToken($request->device_name);

            return $this->sendResponse(['token' => $token->plainTextToken], 'Success auth');
        }

        return $this->sendError([], 'FB user not found');
    }
}
