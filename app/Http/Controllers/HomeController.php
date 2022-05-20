<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('welcome');
    }

    public function refreshExpire()
    {
        ini_set('max_execution_time', 0);
        Task::where('expired_at', '<', date('Y-m-d H:i:s'))->chunk(100, function ($tasks) {
            foreach ($tasks as $task) {
                $start_date = Carbon::today()->days(rand(0, 50));
                $start_time = Carbon::now()->subMinutes(rand(1, 3600));
                $task->start_date = $start_date;
                $task->start_time = $start_time;
                $task->save();
            }
        });

        echo 'refresh';
    }
}
