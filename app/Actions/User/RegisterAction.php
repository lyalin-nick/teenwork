<?php

namespace App\Actions\User;

use App\Actions\Notification\SendSmsWithCodeAction;
use App\Models\User;
use Carbon\Carbon;

class RegisterAction
{
    private $smsWithCodeAction;

    public function __construct(SendSmsWithCodeAction $smsWithCodeAction)
    {
        $this->smsWithCodeAction = $smsWithCodeAction;
    }

    public function __invoke(string $phone, string $password, string $verify_code)
    {
        $user = User::create([
            'phone' => $phone,
            'password' => bcrypt($password),
            'verify_token' => bcrypt($verify_code),
            'verify_token_expire' => Carbon::now()->addSeconds(User::SECONDS_TO_EXPIRE),
            'role' => null,
            'status' => User::STATUS_WAIT,
        ]);
        if ($user) {
            $sending = ($this->smsWithCodeAction)($user, $verify_code);
            return ($sending) ? $user : false;
        }
        return null;
    }
}
