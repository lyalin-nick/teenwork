<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Dictionary\CategoryController;
use App\Http\Controllers\Api\Dictionary\FaqController;
use App\Http\Controllers\Api\Dictionary\LanguageController;
use App\Http\Controllers\Api\Employer\Profile\ProfileController as EmployerProfileController;
use App\Http\Controllers\Api\Employer\Task\TaskController;
use App\Http\Controllers\Api\Helper\GoogleMapController;
use App\Http\Controllers\Api\Helper\UploadController;
use App\Http\Controllers\Api\Home\HomeController;
use App\Http\Controllers\Api\Performer\Profile\PortfolioImageController;
use App\Http\Controllers\Api\Performer\Profile\PortfolioLinkController;
use App\Http\Controllers\Api\Performer\Profile\ProfileController as PerformerProfileController;
use App\Http\Controllers\Api\Profile\PersonalInformationController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Profile\SettingController;
use App\Http\Controllers\Api\Task\TasksController;
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

Route::post('/login', [LoginController::class, 'login']);

Route::prefix('/register')->group(function () {

    Route::post('/phone', [RegisterController::class, 'phone']);
    Route::post('/confirm', [RegisterController::class, 'confirm']);

    Route::get('/{provider}', [RegisterController::class, 'getNetworkRedirect']);
    Route::get('/{provider}/callback', [RegisterController::class, 'authByNetwork']);

});
Route::prefix('/reset')->group(function () {

    Route::post('/phone', [ResetPasswordController::class, 'phone']);
    Route::post('/confirm', [ResetPasswordController::class, 'confirm']);
    Route::post('/password', [ResetPasswordController::class, 'password']);

});


Route::prefix('/dictionary')->group(function () {

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{flag}', [CategoryController::class, 'grouped'])->where('flag', '(online|offline)');
    Route::get('/languages', [LanguageController::class, 'index']);

    Route::get('/faqs', [FaqController::class, 'index']);
    Route::get('/faqs/{id}', [FaqController::class, 'answer']);

});

Route::prefix('/helper')->group(function () {

    Route::get('/autocomplete', [GoogleMapController::class, 'autocomplete']);
    Route::get('/coords', [GoogleMapController::class, 'c']);
    Route::get('/place-id', [GoogleMapController::class, 'placeId']);

    Route::post('/upload-image', [UploadController::class, 'uploadImage']);
    Route::post('/upload-video', [UploadController::class, 'uploadVideo']);
});

Route::prefix('/home')->group(function () {
    Route::get('/count', [HomeController::class, 'count']);
    Route::get('/{flag}', [HomeController::class, 'index'])->where('flag', '(online|offline)');
    Route::get('/map/{flag}', [HomeController::class, 'map'])->where('flag', '(online|offline)');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('/tasks')->middleware('employer')->group(function () {
        Route::get('/', [TasksController::class, 'index']);
    });

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

        Route::put('/base-info', [PersonalInformationController::class, 'setBaseInfo']);
        Route::put('/about', [PersonalInformationController::class, 'setAbout']);
        Route::post('/image', [PersonalInformationController::class, 'uploadImage']);
        Route::post('/video', [PersonalInformationController::class, 'uploadVideo']);

        Route::prefix('/setting')->group(function () {
            Route::put('/push-notification', [SettingController::class, 'pushNotification']);
            Route::put('/email-notification', [SettingController::class, 'emailNotification']);
            Route::put('/invisible', [SettingController::class, 'invisible']);
        });
        //==============================================================================================================


        //============================= Методы доступные для роли Исполнитель ==========================================
        Route::middleware('performer')->group(function () {

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
