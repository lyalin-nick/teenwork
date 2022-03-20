<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ConfirmPhoneController extends BaseController
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        if ($user->updatePhone($request->phone)) {
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

        return $this->sendError('User doesn`t update. ');
    }

    public function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|min:6|max:6'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();

        if (!Hash::check($request->code, $user->phone_verify_token)) {
            return $this->sendError('User Error.', ['code' => 'Incorrect code.'], 405);
        } elseif (strtotime($user->phone_verify_token_expire) < strtotime(Carbon::now())) {
            return $this->sendError('User Error.', ['code' => 'The verify code has expired.'], 406);
        }

        $user->verifyPhone();

        return $this->sendResponse([], 'Confirmed success');
    }

}
