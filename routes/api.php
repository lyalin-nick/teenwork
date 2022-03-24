<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\VerifyCodeController;
use App\Http\Controllers\Api\Dictionary\CategoryController;
use App\Http\Controllers\Api\Dictionary\LanguageController;
use App\Http\Controllers\Api\Employer\Profile\ProfileController as EmployerProfileController;
use App\Http\Controllers\Api\Employer\Task\TaskController;
use App\Http\Controllers\Api\Helper\GoogleMapController;
use App\Http\Controllers\Api\Helper\UploadController;
use App\Http\Controllers\Api\Performer\Profile\PortfolioImageController;
use App\Http\Controllers\Api\Performer\Profile\PortfolioLinkController;
use App\Http\Controllers\Api\Performer\Profile\ProfileController as PerformerProfileController;
use App\Http\Controllers\Api\Profile\ConfirmPhoneController;
use App\Http\Controllers\Api\Profile\ProfileController;
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

Route::prefix('/send')->group(function () {

    Route::post('/email', [VerifyCodeController::class, 'sendEmail']);
    Route::post('/sms', [VerifyCodeController::class, 'sendSms']);

});

Route::prefix('/dictionary')->group(function () {

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{flag}', [CategoryController::class, 'grouped'])->where('flag', '(online|offline)');
    Route::get('/languages', [LanguageController::class, 'index']);

});

Route::prefix('/helper')->group(function () {

    Route::get('/autocomplete', [GoogleMapController::class, 'autocomplete']);
    Route::get('/coords', [GoogleMapController::class, 'c']);
    Route::get('/place-id', [GoogleMapController::class, 'placeId']);

    Route::post('/upload-image', [UploadController::class, 'uploadImage']);
    Route::post('/upload-video', [UploadController::class, 'uploadVideo']);
});

Route::prefix('/auth')->group(function () {

    Route::post('/email', [AuthController::class, 'authByEmail']);
    Route::post('/phone', [AuthController::class, 'authByPhone']);

    Route::get('/{provider}', [AuthController::class, 'getNetworkRedirect']);
    Route::get('/{provider}/callback', [AuthController::class, 'authByNetwork']);

});


Route::middleware('auth:sanctum')->group(function () {

    //================================= Методы доступные для роли Заказчик =============================================
    Route::prefix('/employer')->middleware('employer')->group(function () {

        Route::prefix('/task')->group(function () {
            Route::post('/store', [TaskController::class, 'store']);
            Route::get('/{id}', [TaskController::class, 'edit']);
            Route::put('/{id}', [TaskController::class, 'update']);
            Route::delete('/{id}', [TaskController::class, 'delete']);
        });

    });
    //==================================================================================================================


    //================================= Методы доступные для роли Исполнитель =============================================
    Route::prefix('/performer')->middleware('performer')->group(function () {

        Route::prefix('/profile')->group(function () {
            Route::post('/update', [PerformerProfileController::class, 'update']);
        });

    });
    //==================================================================================================================


    //=============================== Методы для работы с модулем Профиль  =============================================
    Route::prefix('/profile')->group(function () {

        //================================ Общие методы ================================================================
        Route::get('/', [ProfileController::class, 'index']);
        Route::put('/base-info', [ProfileController::class, 'setBaseInfo']);
        Route::put('/about', [ProfileController::class, 'setAbout']);
        Route::post('/image', [ProfileController::class, 'uploadImage']);
        Route::post('/video', [ProfileController::class, 'uploadVideo']);

        Route::prefix('/setting')->group(function () {
            Route::put('/push-notification', [ProfileController::class, 'setPushNotification']);
            Route::put('/email-notification', [ProfileController::class, 'setEmailNotification']);
            Route::put('/invisible', [ProfileController::class, 'setInvisible']);
        });

        Route::prefix('/confirm-phone')->middleware('user.phone')->group(function () {
            Route::put('/send', [ConfirmPhoneController::class, 'send']);
            Route::put('/confirm', [ConfirmPhoneController::class, 'confirm']);
        });
        //==============================================================================================================


        //============================= Методы доступные для роли Исполнитель ==========================================
        Route::middleware('performer')->group(function (){

            Route::put('/address', [PerformerProfileController::class, 'setAddress']);
            Route::put('/categories', [PerformerProfileController::class, 'setCategories']);

            Route::prefix('/portfolio')->group(function () {

                Route::get('/', [PerformerProfileController::class, 'portfolio']);

                Route::prefix('/image')->group(function () {
                    Route::post('/', [PortfolioImageController::class, 'store']);
                    Route::delete('/{id}', [PortfolioImageController::class, 'delete']);
                });

                Route::prefix('/link')->group(function () {
                    Route::post('/', [PortfolioLinkController::class, 'store']);
                    Route::put('/{id}', [PortfolioLinkController::class, 'edit']);
                    Route::delete('/{id}', [PortfolioLinkController::class, 'delete']);
                });

            });
        });
        //==============================================================================================================


        Route::prefix('/role')->group(function () {
            Route::put('/employer', [PerformerProfileController::class, 'setRoleEmployer'])->middleware('performer');
            Route::put('/performer', [EmployerProfileController::class, 'setRolePerformer'])->middleware('employer');
        });

    });
    //==================================================================================================================


    Route::post('/logout', [LogoutController::class, 'logout']);

});
