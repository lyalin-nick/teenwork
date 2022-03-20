<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\VerifyCodeController;
use App\Http\Controllers\Api\Dictionary\CategoryController;
use App\Http\Controllers\Api\Dictionary\LanguageController;
use App\Http\Controllers\Api\Employer\Profile\ProfileController as EmployerProfileController;
use App\Http\Controllers\Api\Employer\Task\TaskController;
use App\Http\Controllers\Api\Helper\GoogleMapController;
use App\Http\Controllers\Api\Performer\Profile\PortfolioImageController;
use App\Http\Controllers\Api\Performer\Profile\PortfolioLinkController;
use App\Http\Controllers\Api\Performer\Profile\ProfileController as PerformerProfileController;
use App\Http\Controllers\Api\Profile\ConfirmPhoneController;
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
    Route::get('/coords', [GoogleMapController::class, 'c']);
    Route::get('/place-id', [GoogleMapController::class, 'placeId']);
});

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
            Route::post('/upload-images', [TaskController::class, 'uploadImages']);
            Route::post('/upload-video', [TaskController::class, 'uploadVideo']);
        });
    });

    Route::prefix('/performer')->group(function () {

        Route::prefix('/profile')->group(function () {
            Route::post('/update', [PerformerProfileController::class, 'update']);
            Route::post('/upload-image', [PerformerProfileController::class, 'uploadImage']);
            Route::post('/upload-video', [PerformerProfileController::class, 'uploadVideo']);
            Route::post('/portfolio/images', [PerformerProfileController::class, 'uploadImages']);
        });
    });

    Route::prefix('/profile')->group(function () {

        Route::get('/', [ProfileController::class, 'index']);
        Route::put('/base-info', [ProfileController::class, 'setBaseInfo']);
        Route::put('/about', [ProfileController::class, 'setAbout']);

        Route::put('/address', [PerformerProfileController::class, 'setAddress'])->middleware('performer');
        Route::put('/categories', [PerformerProfileController::class, 'setCategories'])->middleware('performer');

        Route::post('/image', [ProfileController::class, 'uploadImage']);
        Route::post('/video', [ProfileController::class, 'uploadVideo']);

        Route::prefix('/portfolio')->group(function () {

            Route::get('/', [PerformerProfileController::class, 'portfolio']);

            Route::prefix('/images')->group(function () {
                Route::post('/', [PortfolioImageController::class, 'store']);
                Route::delete('/{id}', [PortfolioImageController::class, 'delete']);
            });

            Route::prefix('/link')->group(function () {
                Route::post('/', [PortfolioLinkController::class, 'store']);
                Route::put('/{id}', [PortfolioLinkController::class, 'edit']);
                Route::delete('/{id}', [PortfolioLinkController::class, 'delete']);
            });
        });

        Route::prefix('/setting')->group(function () {
            Route::put('/push-notification', [ProfileController::class, 'setPushNotification']);
            Route::put('/email-notification', [ProfileController::class, 'setEmailNotification']);
            Route::put('/invisible', [ProfileController::class, 'setInvisible']);
        });

        Route::prefix('/role')->group(function () {
            Route::put('/employer', [PerformerProfileController::class, 'setRoleEmployer'])->middleware('performer');
            Route::put('/performer', [EmployerProfileController::class, 'setRolePerformer'])->middleware('employer');
        });

        Route::prefix('/confirm-phone')->group(function () {
            Route::put('/send', [ConfirmPhoneController::class, 'send']);
            Route::put('/confirm', [ConfirmPhoneController::class, 'confirm']);
        });
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [LogoutController::class, 'logout']);

});
