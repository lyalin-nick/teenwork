<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id', 'link'
    ];

    public function profile()
    {
        $this->belongsTo(Profile::class, 'profile_id');
    }
}
