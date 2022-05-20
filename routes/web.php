<?php

use App\Http\Controllers\HomeController;
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

//    $images = \App\Models\PortfolioImage::all();
//    foreach ($images as $image) {
//        $image->delete();
//    }
//    $links = \App\Models\PortfolioLink::all();
//    foreach ($links as $link) {
//        $link->delete();
//    }
//    for ($i = 0; $i < 100; $i++){
//        try {
//            \App\Models\Review::createReview([
//                'task_id' => \App\Models\Task::where('expired_at', '<', date('Y-m-d'))->inRandomOrder()->first()->id,
//                'employer_id' => rand(11, 12),
//                'performer_id' => rand(9, 10),
//                'rating' => rand(3, 5),
//                'text' => \Illuminate\Support\Str::random(rand(16, 30))
//            ]);
//        }catch (Exception $e){
//            echo 'ece';
//        }
//    }
    return "clear!!!!";
});

Route::get('/', [HomeController::class, 'index']);
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/phpinfo', function () {
    phpinfo();
});
Route::get('/refresh-expire', [HomeController::class, 'refreshExpire']);
Auth::routes();
