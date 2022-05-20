<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Notification\SendSmsWithCodeAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Auth\ResetPassword\CodeConfirmRequest;
use App\Http\Requests\Api\Auth\ResetPassword\PhoneRequest;
use App\Http\Requests\Api\Auth\ResetPassword\ResetPasswordRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends BaseController
{
    /**
     * Отправка кода на телефон для сброса пароля
     * TODO: раскомментировать на продакшн
     *
     * @param PhoneRequest $request
     * @return JsonResponse
     */
    public function phone(PhoneRequest $request, SendSmsWithCodeAction $sendSmsWithCodeAction): JsonResponse
    {
        $user = User::findByPhone($request->phone);

        if (!$user) {
            return $this->sendError('User not found. Please, register!');
        }

        $verify_code = '000000';//(string)random_int(100000, 999999);

        $user->setResetToken($verify_code);
        $sending = $sendSmsWithCodeAction($user, $verify_code);

        return ($sending) ?
            $this->sendResponse(['expires_in' => User::SECONDS_TO_EXPIRE], 'Code send successfully.', 201)
            : $this->sendError('Error! Message did not send', 501);
    }

    /**
     * Подтверждение кода
     *
     * @param CodeConfirmRequest $request
     * @return JsonResponse
     */
    public function confirm(CodeConfirmRequest $request): JsonResponse
    {
        $user = User::findByPhone($request->phone);

        if (!$user) {
            return $this->sendError('User with this phone not found.');
        } elseif (!Hash::check($request->code, $user->reset_token)) {
            return $this->sendError('Incorrect code.', [], 405);
        } elseif (strtotime($user->reset_token_expire) < strtotime(Carbon::now())) {
            return $this->sendError('The verify code has expired.', [], 406);
        }

        try {
            $user->removeResetToken();
        } catch (Exception $e) {
            return $this->sendError('Reset user error.', [], 501);
        }

        return $this->sendResponse([], 'Confirm success', 201);
    }

    /**
     * Сброс пароля
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function password(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::findResetByPhone($request->phone);

        if (!$user) {
            return $this->sendError('User not found.');
        }

        try {
            $user->setPassword($request->password);
        } catch (Exception $e) {
            return $this->sendError('Password updating error.', [], 501);
        }

        return $this->sendResponse([], 'Password reset success', 201);
    }
}
