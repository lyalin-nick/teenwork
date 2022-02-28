<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id', 'path', 'name', 'ext', 'description'
    ];

    public function profile()
    {
        $this->belongsTo(Profile::class, 'id', 'profile_id');
    }
}
