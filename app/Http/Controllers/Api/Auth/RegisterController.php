<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\User\RegisterAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Auth\Register\ConfirmRegisterRequest;
use App\Http\Requests\Api\Auth\Register\RegisterRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends BaseController
{
    /**
     * Регистрация по телефону
     * TODO: на продакшн привести в боевой вид
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function phone(RegisterRequest $request, RegisterAction $register): JsonResponse
    {
        $verify_code = '000000';//(string)random_int(100000, 999999);

        $user = $register($request->phone, $request->password, $verify_code);// User::register($request->phone, $request->password, $verify_code);

        if (!$user) {
            return $this->sendError('Error creating user.', [], 501);
        }

        return $this->sendResponse(['expires_in' => User::SECONDS_TO_EXPIRE], 'Code send successfully.');
    }

    /**
     * Подтверждение телефона
     *
     * @param ConfirmRegisterRequest $request
     * @return JsonResponse
     */
    public function confirm(ConfirmRegisterRequest $request): JsonResponse
    {
        $user = User::findByPhone($request->phone);

        if (!$user) {
            return $this->sendError('User with this phone not found.');
        } elseif (!Hash::check($request->code, $user->verify_token)) {
            return $this->sendError('Incorrect code.', [], 405);
        } elseif (strtotime($user->verify_token_expire) < strtotime(Carbon::now())) {
            return $this->sendError('The verify code has expired.', [], 406);
        }

        try {
            $user->verify();
        } catch (Exception $e) {
            return $this->sendError('Verify user error.', [], 501);
        }

        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name);

        return $this->sendResponse(['token' => $token->plainTextToken], 'Success auth');
    }
}
