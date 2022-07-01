<?php

namespace App\Actions\Chat;

use App\Models\Chat;
use App\Models\TaskOffer;
use App\Models\TaskResponse;
use Illuminate\Database\Eloquent\Model;

class TaskChatCreateAction
{

    /**
     * @param $user_from
     * @param $user_to
     * @param $task
     * @param TaskOffer|TaskResponse $chat_subject
     * @return Chat|Model|null
     */
    public function __invoke($user_from, $user_to, $task, $chat_subject)
    {
        $chat = Chat::where('type', '=', Chat::TYPE_TASK)
            ->where('identifier', '=', $task->id)
            ->select('chats.*')
            ->whereRaw("(SELECT COUNT(*) FROM chat_user WHERE chats.id=chat_user.chat_id)=2")
            ->whereRaw("(SELECT COUNT(*) FROM chat_user WHERE chats.id=chat_user.chat_id AND  chat_user.user_id IN ({$user_from->id}, {$user_to->id}))=2")
            ->first();

        if (!$chat) {
            $chat = Chat::create(
                [
                    'type' => Chat::TYPE_TASK,
                    'identifier' => $task->id,
                    'name' => $task->name,
                    'logo' => $task->user->profile->getProfilePreviewImageLink(),
                ]
            );
            if ($chat) {
                $chat->users()->attach($user_from);
                $chat->users()->attach($user_to);
            }
        }

        if ($chat) {
            if ($chat_subject->text) {
                $message = $chat->messages()->create([
                    'user_id' => $user_from->id,
                    'text' => $chat_subject->text
                ]);
                if ($message) {
                    $chat_subject->chat_id = $chat->id;
                    $chat_subject->message_id = $message->id;
                    $chat_subject->save();
                }
            }
            return $chat;
        }
        return null;
    }
}
