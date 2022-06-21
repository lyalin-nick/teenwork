<?php

namespace App\Exceptions;

use GuzzleHttp\Client;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            try {
                $client = new Client();
                $error_mes = "Project: " . config('app.name') . "\n";
                $error_mes .= "Message: {$e->getMessage()}\n";
                $error_mes .= "Line: {$e->getLine()}\n";
                $error_mes .= "File: {$e->getFile()}\n";
                $link = "https://api.telegram.org/bot1538817875:AAFhqgqqncu3aJO8ni_1owaC2ffOt3RDCDI/sendMessage?chat_id=795629321&text={$error_mes}&parse_mode=html";

                $response = $client->request('GET', $link);
            } catch (\Exception $e) {
                Log::error($e);
            }
        });
    }
}
