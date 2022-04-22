<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Traits\OrderableModel;

/**
 * @property string $name
 * @property int $flag
 * @property int $pos
 */
class ReportTitle extends Model
{
    use HasFactory, OrderableModel;

    const USER_TITLES = 1,
        TASK_TITLES = 2;

    protected $fillable = ['name', 'flag'];

    public static function getFlagLabel($flag)
    {
        $labels = self::getFlagLabels();
        return $labels[$flag];
    }

    public static function getFlagLabels()
    {
        return [
            self::USER_TITLES => 'user',
            self::TASK_TITLES => 'task',
        ];
    }

    public static function getReportTitles($flag)
    {
        $titles = self::where('flag', '=', $flag)->select('id', 'name')->get()->toArray();

        if (!$titles) {
            $titles = [['id' => 0, 'name' => 'Title 1']];
        }

        return $titles;
    }

    public function getOrderField()
    {
        return 'pos';
    }
}
