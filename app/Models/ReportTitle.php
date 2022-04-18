<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTitle extends Model
{
    use HasFactory;

    const USER_TITLES = 1,
        TASK_TITLES = 2;

    protected $fillable = ['name', 'flag'];

    static function getFlagLabel($flag)
    {
        $labels = self::getFlagLabels();
        return $labels[$flag];
    }

    static function getFlagLabels()
    {
        return [
            self::USER_TITLES => 'user',
            self::TASK_TITLES => 'task',
        ];
    }

    static function getReportTitles()
    {
        $user_titles = self::where('flag', '=', self::USER_TITLES)->select('id', 'name')->get()->toArray();
        if (!$user_titles) {
            $user_titles = ['id' => 0, 'name' => 'User Title'];
        }
        $tasks_titles = self::where('flag', '=', self::TASK_TITLES)->select('id', 'name')->get()->toArray();
        if (!$tasks_titles) {
            $tasks_titles = ['id' => 0, 'name' => 'Task Title'];
        }

        return ['user_titles' => $user_titles, 'tasks_titles' => $tasks_titles];
    }
}
