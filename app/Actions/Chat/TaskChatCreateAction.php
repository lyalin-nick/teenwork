<?php

namespace App\Actions\Chat;

use App\Models\Chat;

class TaskChatCreateAction
{

    /**
     * @param $user_from
     * @param $user_to
     * @param $task
     * @param null $message
     * @return Chat|\Illuminate\Database\Eloquent\Model|null
     */
    public function __invoke($user_from, $user_to, $task, $message = null)
    {
        //TODO: сделать проверку на существование такого чата
        $new_chat = Chat::create(
            [
                'type' => Chat::TYPE_TASK,
                'identifier' => $task->id,
                'name' => $task->name,
                'logo' => $task->user->profile->getProfilePreviewImageLink(),
            ]
        );

        if ($new_chat) {
            $new_chat->users()->attach($user_from);
            $new_chat->users()->attach($user_to);

            if ($message) {
                $new_chat->messages()->create([
                    'user_id' => $user_from->id,
                    'text' => $message
                ]);
            }
            return $new_chat;
        }
        return null;
    }
}
