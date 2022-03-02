<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\VerifyCodeController;
use App\Http\Controllers\Api\Dictionary\CategoryController;
use App\Http\Controllers\Api\Dictionary\LanguageController;
use App\Http\Controllers\Api\Employer\Task\TaskController;
use App\Http\Controllers\Api\Helper\GoogleMapController;
use App\Http\Controllers\Api\Profile\PortfolioController;
use App\Http\Controllers\Api\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/send/email', [VerifyCodeController::class, 'sendEmail']);
Route::post('/send/sms', [VerifyCodeController::class, 'sendSms']);

Route::prefix('/dictionary')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{flag}', [CategoryController::class, 'grouped'])->where('flag', '(online|offline)');
    Route::get('/languages', [LanguageController::class, 'index']);
});

Route::prefix('/helper')->group(function () {
    Route::get('/autocomplete', [GoogleMapController::class, 'autocomplete']);
});
//Route::post('/task/create', [TaskController::class, 'create']);

Route::prefix('/auth')->group(function () {


    Route::post('/email', [AuthController::class, 'authByEmail']);
    Route::post('/phone', [AuthController::class, 'authByPhone']);

    Route::get('/{provider}', [AuthController::class, 'getNetworkRedirect']);
    Route::get('/{provider}/callback', [AuthController::class, 'authByNetwork']);

});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('/employer')->group(function () {

        Route::prefix('/task')->group(function () {
            Route::post('/store', [TaskController::class, 'store'])->middleware('employer');
            Route::post('/upload-images', [TaskController::class, 'images']);
        });
    });

    Route::prefix('/profile')->group(function () {

        Route::put('/base-info', [ProfileController::class, 'setBaseInfo']);
        Route::put('/about', [ProfileController::class, 'setAbout']);
        Route::put('/address', [ProfileController::class, 'setAddress']);
        Route::put('/categories', [ProfileController::class, 'setCategories'])->middleware('performer');
        Route::post('/image', [ProfileController::class, 'uploadImage']);

        Route::prefix('/portfolio')->group(function () {

            Route::get('/', [PortfolioController::class, 'index']);

            Route::prefix('/image')->group(function () {
                Route::post('/upload', [PortfolioController::class, 'uploadImage']);
                Route::delete('/{id}', [PortfolioController::class, 'deleteImage']);
            });

            Route::prefix('/link')->group(function () {
                Route::post('/store', [PortfolioController::class, 'storeLink']);
                Route::delete('/{id}', [PortfolioController::class, 'deleteLink']);
            });
        });

        Route::prefix('/setting')->group(function () {
            Route::put('/push-notification', [ProfileController::class, 'setPushNotification']);
            Route::put('/email-notification', [ProfileController::class, 'setEmailNotification']);
            Route::put('/invisible', [ProfileController::class, 'setInvisible']);
        });


        Route::prefix('/role')->group(function () {
            Route::put('/employer', [ProfileController::class, 'setRoleEmployer'])->middleware('performer');
            Route::put('/performer', [ProfileController::class, 'setRolePerformer'])->middleware('employer');
        });
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [LogoutController::class, 'logout']);

});
