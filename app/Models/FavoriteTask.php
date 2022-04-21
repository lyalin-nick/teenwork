<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteTask extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['task_id', 'user_id'];

    public function task()
    {
        return $this->hasOne(Task::class, 'id', 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
