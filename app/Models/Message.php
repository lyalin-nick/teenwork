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
 *
 * @property Chat $chat
 * @property MessageStatus[] $messageStatuses
 * @property User $sender
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'text', 'chat_id', 'images'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Chat
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|User
     */
    public function sender()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|MessageStatus
     */
    public function messageStatuses()
    {
        return $this->hasMany(MessageStatus::class);
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
            $sender = $message->sender;
            $chat = $message->chat()
                ->with(['users' => function ($query) use ($sender) {
                    $query->where('users.id', '!=', $sender->id);
                }])
                ->first();
            foreach ($chat->users as $user) {
                $message->messageStatuses()->create([
                    'user_id' => $user->id,
                    'message_id' => $message->id,
                    'reading' => false
                ]);
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
