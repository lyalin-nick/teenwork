<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Traits\OrderableModel;

/**
 * @property string $name
 * @property int $pos
 *
 * @property Task[] $tasks
 * @property Profile[] $profiles
 *
 * @mixin Builder
 */
class Language extends Model
{
    use HasFactory, OrderableModel;

    const ENGLISH_LANGUAGE = 1;

    protected $fillable = ['name'];

    /**
     * Получение массива категорий
     * @return mixed
     */
    public static function getAllLanguagesAsArray()
    {
        return self::select('id', 'name')->orderBy('name')->get();
    }

    protected static function booted()
    {
        static::deleted(function (self $category) {
            $category->tasks()->detach();//убираем прилинкованные языки к задаче
            $category->profiles()->detach();//убираем прилинкованные языки к профилю
        });
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function profiles()
    {
        return $this->belongsToMany(Profile::class);
    }

    public function getOrderField()
    {
        return 'pos';
    }
}
