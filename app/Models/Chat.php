<?php

namespace App\Models;

use App\Events\ChatUpdated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $type
 * @property integer $identifier
 * @property string $name
 * @property string $logo
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User[] $users
 * @property Message[] $messages
 * @property Message $lastMessage
 *
 * @mixin Builder
 */
class Chat extends Model
{
    use HasFactory;

    const
        TYPE_MY_QUESTION = 'my_question',
        TYPE_TASK = 'task';

    const
        STATUS_CURRENT = 'current',
        STATUS_HISTORY = 'history',
        STATUS_SUPPORT = 'support';

    protected $fillable = [
        'type', 'identifier', 'name', 'logo', 'status', 'last_message_id'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('unread_messages_count');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'id', 'last_message_id');
    }

    public function getChatLogo($user_id = null)
    {
        $logo = [];

        $users = $this->users();

        if ($user_id)
            $users->where('id', '!=', $user_id);

        $users = $users->get();

        if ($users)
            foreach ($users as $chat_user) {
                $logo[] = $chat_user->profile->getProfilePreviewImageLink();
            }

        return $logo;
    }

    protected static function booted()
    {
        static::updated(function ($chat) {
            $last_message = $chat->lastMessage;
            $users_addressees = $chat->users()->where('id', '!=', $last_message->user_id)->get();

            foreach ($users_addressees as $user) {
                $chat_data = [
                    "id" => $chat->id,
                    "name" => $chat->name,
                    "logo" => $chat->getChatLogo($user->id),
                    "last_message" => $last_message->text,
                    "last_message_user_id" => $last_message->user_id,
                    "last_message_created_at" => $last_message->created_at,
                    "unread_messages_count" => $user->pivot->unread_messages_count,
                ];

                broadcast(new ChatUpdated($chat_data, $user->id));
            }
        });
    }
}
