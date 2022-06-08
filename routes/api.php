<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\Dictionary\CategoryController;
use App\Http\Controllers\Api\Dictionary\FaqController;
use App\Http\Controllers\Api\Dictionary\LanguageController;
use App\Http\Controllers\Api\Dictionary\ReportTitleController;
use App\Http\Controllers\Api\Favorite\FavoriteController;
use App\Http\Controllers\Api\Helper\GoogleMapController;
use App\Http\Controllers\Api\Helper\UploadController;
use App\Http\Controllers\Api\Home\HomeController;
use App\Http\Controllers\Api\Profile\MyQuestionController;
use App\Http\Controllers\Api\Profile\PerformerProfileController;
use App\Http\Controllers\Api\Profile\Portfolio\PortfolioImageController;
use App\Http\Controllers\Api\Profile\Portfolio\PortfolioLinkController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Profile\ReviewController;
use App\Http\Controllers\Api\Profile\RoleController;
use App\Http\Controllers\Api\Profile\SettingController;
use App\Http\Controllers\Api\Task\EmployerTaskController;
use App\Http\Controllers\Api\Task\PerformerTaskController;
use App\Http\Controllers\Api\Task\RecommendedController;
use App\Http\Controllers\Api\Task\ResponsesController;
use App\Http\Controllers\Api\Task\TaskController;
use App\Http\Controllers\Api\User\UserController;
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

Route::prefix('/login')->group(function () {

    Route::post('/', [LoginController::class, 'login']);
    Route::post('/{provider}', [LoginController::class, 'network'])->where('flag', '(facebook)');

});

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

    Route::get('/report-titles/user', [ReportTitleController::class, 'userTitles']);
    Route::get('/report-titles/task', [ReportTitleController::class, 'taskTitles']);

});

Route::prefix('/helper')->group(function () {

    Route::get('/autocomplete', [GoogleMapController::class, 'autocomplete']);
    Route::get('/place-id', [GoogleMapController::class, 'placeId']);

    Route::post('/upload-image', [UploadController::class, 'uploadImage']);
    Route::post('/upload-video', [UploadController::class, 'uploadVideo']);
});

Route::prefix('/home')->group(function () {
    Route::get('/count', [HomeController::class, 'count']);
    Route::get('/count/{flag}', [HomeController::class, 'count'])->where('flag', '(online|offline)');
    Route::get('/{flag}', [HomeController::class, 'index'])->where('flag', '(online|offline)');
    Route::get('/map/{flag}', [HomeController::class, 'map'])->where('flag', '(online|offline)');
});

Route::prefix('/task')->group(function () {
    Route::get('/{id}', [TaskController::class, 'view']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/{id}/report', [TaskController::class, 'report']);
    });
});


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('/user')->group(function () {
        Route::get('/{id}', [UserController::class, 'view']);
        Route::post('/{id}/report', [UserController::class, 'report']);
        Route::get('/{id}/reviews', [UserController::class, 'reviews']);
        Route::get('/{id}/reviews/count', [UserController::class, 'reviewsCount']);
    });

    Route::prefix('/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'tasks']);
    });

    //================================= Методы доступные для роли Заказчик =============================================
    Route::prefix('/employer')->middleware('employer')->group(function () {

        Route::prefix('/task')->group(function () {
            Route::post('/store', [EmployerTaskController::class, 'store']);
            Route::get('/{id}', [EmployerTaskController::class, 'edit']);
            Route::put('/{id}', [EmployerTaskController::class, 'update']);
            Route::delete('/{id}', [EmployerTaskController::class, 'delete']);

            Route::get('/{id}/recommended', [RecommendedController::class, 'recommended']);
            Route::get('/{id}/responses', [ResponsesController::class, 'responses']);
            Route::post('/{id}/offer', [EmployerTaskController::class, 'offer']);
            Route::post('/{id}/review', [EmployerTaskController::class, 'review']);
        });

        Route::prefix('/favorite')->group(function () {
            Route::get('/', [FavoriteController::class, 'view']);
            Route::post('/{identify}', [FavoriteController::class, 'add']);
            Route::delete('/{identify}', [FavoriteController::class, 'remove']);
        });

    });
    //==================================================================================================================


    //================================= Методы доступные для роли Исполнитель =============================================
    Route::prefix('/performer')->middleware('performer')->group(function () {

        Route::prefix('/profile')->group(function () {
            Route::post('/update', [PerformerProfileController::class, 'update']);
        });

        Route::prefix('/task')->group(function () {
            Route::post('/{id}/response', [PerformerTaskController::class, 'response']);
            Route::post('/{id}/review', [PerformerTaskController::class, 'review']);
        });

        Route::prefix('/favorite')->group(function () {
            Route::get('/', [FavoriteController::class, 'view']);
            Route::post('/{identify}', [FavoriteController::class, 'add']);
            Route::delete('/{identify}', [FavoriteController::class, 'remove']);
        });
    });
    //==================================================================================================================

    //=============================== Методы для работы с модулем Профиль  =============================================
    Route::prefix('/profile')->group(function () {

        //================================ Общие методы ================================================================
        Route::get('/', [ProfileController::class, 'index']);

        Route::put('/base-info', [ProfileController::class, 'baseInfo']);
        Route::post('/image', [ProfileController::class, 'image']);
        Route::post('/video', [ProfileController::class, 'video']);

        Route::prefix('/setting')->group(function () {
            Route::put('/push-notification', [SettingController::class, 'pushNotification']);
            Route::put('/email-notification', [SettingController::class, 'emailNotification']);
            Route::put('/invisible', [SettingController::class, 'invisible']);
        });

        Route::prefix('/my-questions')->group(function () {
            Route::get('/', [MyQuestionController::class, 'index']);
            Route::post('/store', [MyQuestionController::class, 'store']);
        });

        Route::prefix('/reviews')->group(function () {
            Route::get('/', [ReviewController::class, 'index']);
            Route::get('/count', [ReviewController::class, 'count']);
        });
        //==============================================================================================================


        //============================= Методы доступные для роли Исполнитель ==========================================
        Route::middleware('performer')->group(function () {

            Route::put('/address', [ProfileController::class, 'address']);
            Route::put('/categories', [ProfileController::class, 'categories']);

            Route::prefix('/portfolio')->group(function () {

                Route::get('/', [ProfileController::class, 'portfolio']);

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
            Route::put('/employer', [RoleController::class, 'roleEmployer'])->middleware('performer');
            Route::put('/performer', [RoleController::class, 'rolePerformer'])->middleware('employer');
        });

    });
    //==================================================================================================================


    Route::prefix('/chat')->group(function () {
        Route::get('/', [ChatController::class, 'chatsList']);
        Route::get('/{chatId}', [ChatController::class, 'fetchMessages']);
        Route::post('/{chatId}', [ChatController::class, 'addMessage']);
    });

    Route::post('/logout', [LogoutController::class, 'logout']);

});
