<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Notifications\SmsCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends BaseController
{
    public function phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(6)->numbers()],
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $verify_code = '000000';//(string)random_int(100000, 999999);

        $user = User::register($request->phone, $request->password, $verify_code);

        if (!$user) {
            return $this->sendError('Error creating user.', [], 501);
        }

//        try {
//            $user->notify(new SmsCode($verify_code));
//        } catch (\Exception $e) {
//            return $this->sendError('Failed to send message. ' . $e->getMessage() . '. ' . $e->getLine());
//        }

        return $this->sendResponse(['expires_in' => User::SECONDS_TO_EXPIRE], 'Code send successfully.');
    }

    public function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'code' => 'required|string|min:6|max:6',
            'device_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

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
