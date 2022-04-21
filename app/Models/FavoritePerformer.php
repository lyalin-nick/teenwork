<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoritePerformer extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['performer_id', 'user_id'];

    public function performer()
    {
        return $this->hasOne(User::class, 'id', 'performer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
