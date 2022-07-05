<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'message_id', 'reading'];

    protected $casts = [
        'reading' => 'boolean'
    ];

    public $timestamps = false;

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
