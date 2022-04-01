<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');

    $images = \App\Models\PortfolioImage::all();
    foreach ($images as $image) {
        $image->delete();
    }
    $links= \App\Models\PortfolioLink::all();
    foreach ($links as $link) {
        $link->delete();
    }

    return "clear!!!!";
});

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index']);

Route::namespace('App\Http\Controllers\Admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'MainController@index')->name('index');

    Route::resource('/categories', 'CategoryController');
    Route::resource('/languages', 'LanguageController');
    Route::resource('/faqs', 'FaqController');
});

Auth::routes();

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::get('/phpinfo', function () {
    phpinfo();
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
