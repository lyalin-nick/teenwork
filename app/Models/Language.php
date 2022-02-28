<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    const ENGLISH_LANGUAGE = 1;

    protected $fillable = ['name'];

    public static function getAllLanguagesAsArray()
    {
        return self::select('id', 'name')->orderBy('name')->get();
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function profiles()
    {
        return $this->belongsToMany(Profile::class);
    }
}
