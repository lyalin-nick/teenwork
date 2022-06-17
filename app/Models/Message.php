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
 * @property array $images
 * @property Chat $chat
 * @property User $user
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'text', 'chat_id', 'images'];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function unreadUsers()
    {
        return $this->belongsToMany(User::class, 'message_user');
    }

    /**
     * @param $value
     * @return void
     */
    public function setImagesAttribute($value): void
    {
        $this->attributes['images'] = json_encode($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getImagesAttribute($value)
    {
        return json_decode($value);
    }

    protected static function booted()
    {
        static::created(function ($message) {
            $user = $message->user;
            $chat = $message->chat()
                ->with(['users' => function ($query) use ($user) {
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
