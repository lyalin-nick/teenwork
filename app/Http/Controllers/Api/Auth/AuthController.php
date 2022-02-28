<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\Network;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends BaseController
{

    public function authByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'code' => 'required|string|min:6|max:6',
            'device_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::findByEmail($request->email);

        if (!$user) {
            return $this->sendError('User Error.', ['email' => 'User with this email not found.']);
        } elseif (!Hash::check($request->code, $user->verify_token)) {
            return $this->sendError('User Error.', ['code' => 'Incorrect code.']);
        } elseif (strtotime($user->verify_token_expire) < strtotime(Carbon::now())) {
            return $this->sendError('User Error.', ['code' => 'The verify code has expired.']);
        }

        $user->verify();
        $token = $user->createToken($request->device_name);

        return $this->sendResponse(['token' => $token->plainTextToken], 'Success auth');
    }

    public function authByPhone(Request $request)
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
            return $this->sendError('User Error.', ['phone' => 'User with this phone not found.']);
        } elseif (!Hash::check($request->code, $user->phone_verify_token)) {
            return $this->sendError('User Error.', ['code' => 'Incorrect code.']);
        } elseif (strtotime($user->phone_verify_token_expire) < strtotime(Carbon::now())) {
            return $this->sendError('User Error.', ['code' => 'The verify code has expired.']);
        }
        $user->verifyPhone();
        $token = $user->createToken($request->device_name);

        return $this->sendResponse(['token' => $token->plainTextToken], 'Success auth');
    }

    public function authByNetwork($provider)
    {
        try {

            $network_user = Socialite::driver($provider)->stateless()->user();
            $network = Network::where('network_user_id', $network_user->id)->where('network', $provider)->first();

            if ($network) {
                $user = $network->user;
            } else {
                $user = User::findByEmail($network_user->email);
                if (!$user) {
                    $user = User::create([
                        'name' => $network_user->name,
                        'email' => $network_user->email,
                        'status' => User::STATUS_ACTIVE,
                    ]);
                    $user->email_verified_at = Carbon::now();
                    $user->password = bcrypt((string)random_int(100000, 999999));
                    $user->verify_token = null;
                    $user->verify_token_expire = null;
                    $user->saveOrFail();
                }


                $network = Network::create([
                    'user_id' => $user->id,
                    'network_user_id' => $network_user->id,
                    'network' => $provider,
                    'token' => $network_user->token,
                    'refreshToken' => $network_user->refreshToken,
                    'expiresIn' => Carbon::now()->addSeconds($network_user->expiresIn)
                ]);
            }


            $token = $user->createToken($provider);

            return $this->sendResponse(['token' => $token->plainTextToken], 'Success auth');

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 403);
        }
    }

    public function getNetworkRedirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }
}
