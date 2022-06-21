<?php

namespace App\Actions\Notification;

use App\Models\User;
use App\Notifications\SmsCode;
use Exception;
use Log;

class SendSmsWithCodeAction
{

    public function __invoke(User $user, string $verify_code): bool
    {
//        try {
//            $user->notify(new SmsCode($verify_code));
//        } catch (Exception $e) {
//            Log::error('Failed to send message. ' . $e->getMessage() . '. ' . $e->getLine());
//            return false;
//        }

        return true;
    }
}
