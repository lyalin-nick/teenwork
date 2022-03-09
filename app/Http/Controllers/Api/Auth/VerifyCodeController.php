<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerifyCodeController extends BaseController
{

    /**
     * Отправка письма с кодом подтверждения для авторизации
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::findByEmail($request->email);
        if (!$user) {
            $user = User::create([
                'email' => $request->email,
                'status' => User::STATUS_WAIT
            ]);
        }
        $new_pass = '000000';//(string)random_int(100000, 999999);
        try {
            $user->setEmailVerificationData($new_pass);
        } catch (Exception $e) {
            return $this->sendError('Failed to update user. ' . $e->getMessage() . '. ' . $e->getLine());
        }

//        try {
//            $user->notify(new EmailCode($new_pass));
//        } catch (\Exception $e) {
//            return $this->sendError('Failed to send message. ' . $e->getMessage() . '. ' . $e->getLine());
//        }

        return $this->sendResponse(['expires_in' => User::SECONDS_TO_EXPIRE], 'Code send successfully.');
    }

    /**
     * Отправка SMS-сообщения с кодом для авторизации
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::findByPhone($request->phone);
        if (!$user) {
            $user = User::create([
                'phone' => $request->phone,
                'status' => User::STATUS_WAIT
            ]);
        }
        $new_pass = '000000';//(string)random_int(100000, 999999);
        try {
            $user->setPhoneVerificationData($new_pass);
        } catch (Exception $e) {
            return $this->sendError('Failed to update user. ' . $e->getMessage() . '. ' . $e->getLine());
        }

//        try {
//            $user->notify(new SmsCode($new_pass));
//        } catch (\Exception $e) {
//            return $this->sendError('Failed to send message. ' . $e->getMessage() . '. ' . $e->getLine());
//        }

        return $this->sendResponse(['expires_in' => User::SECONDS_TO_EXPIRE], 'Code send successfully.');
    }

}
