<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ResetPasswordController extends BaseController
{
    public function phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::findByPhone($request->phone);

        if (!$user) {
            return $this->sendError('User not found. Please, register!');
        }

        $verify_code = '000000';//(string)random_int(100000, 999999);

        try {
            $user->setResetToken($verify_code);
//            $user->notify(new SmsCode($verify_code));
        } catch (\Exception $e) {
            return $this->sendError('Failed to send message. ' . $e->getMessage() . '. ' . $e->getLine());
        }

        return $this->sendResponse(['expires_in' => User::SECONDS_TO_EXPIRE], 'Code send successfully.', 201);
    }

    public function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'code' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

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

    public function password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Password::min(6)->numbers()],
            'password_confirmation' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

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
