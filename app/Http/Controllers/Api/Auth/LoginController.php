<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Auth\Login\LoginRequest;
use App\Http\Requests\Api\Auth\Login\NetworkRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends BaseController
{
    /**
     * Логинизация
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
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

    /**
     * Авторизация через соцсети
     *
     * @param $provider
     * @param NetworkRequest $request
     * @return JsonResponse
     */
    public function network(NetworkRequest $request, $provider): JsonResponse
    {
        $socialite_user = Socialite::driver($provider)->userFromToken($request->access_token);

        if ($socialite_user) {
            $user = User::findByNetwork($provider, $socialite_user);

            $user->tokens()->where('name', $request->device_name)->delete();

            $token = $user->createToken($request->device_name);

            return $this->sendResponse(['token' => $token->plainTextToken], 'Success auth');
        }

        return $this->sendError('FB user not found');
    }
}
