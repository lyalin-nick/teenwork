<?php

namespace App\Models;

use App\Events\MessageSent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $chat_id
 * @property integer $user_id
 * @property string $text
 * @property string $img
 * @property Chat $chat
 * @property User $user
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'text', 'chat_id', 'img'];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($message) {
            $user = $message->user;
            $chat = $message->chat()->with(['users' => function ($query) use ($user) {
                $query->where('users.id', '!=', $user->id);
            }])
                ->first();
            foreach ($chat->users as $user) {
                $message->unreadUsers()->attach($user);
                $chat->users()->updateExistingPivot($user, ['unread_messages_count' => $user->pivot->unread_messages_count + 1]);
            }
            if ($chat) {
                $chat->last_message_id = $message->id;
                $chat->save();
            }

            broadcast(new MessageSent($message, $chat))->toOthers();
        });
    }
}
