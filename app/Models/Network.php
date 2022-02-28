<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
class Network extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'network_user_id', 'network', 'token', 'refreshToken', 'expiresIn'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
