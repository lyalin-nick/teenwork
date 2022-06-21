<?php

namespace App\Actions\Chat;

use App\Models\Chat;
use App\Models\MyQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class QuestionChatCreateAction
{

    /**
     * @param $user
     * @param MyQuestion $question
     * @return Chat|Model|null
     */
    public function __invoke($user, MyQuestion $question)
    {
        $admin_user = User::getAdminUser();
        $new_chat = Chat::create(
            [
                'type' => Chat::TYPE_MY_QUESTION,
                'identifier' => $question->id,
                'name' => $question->subject,
                'logo' => asset('/img/matchagency.png'),
            ]
        );

        if ($new_chat) {
            $new_chat->users()->attach($user);
            $new_chat->users()->attach($admin_user);

            $new_chat->messages()->create([
                'user_id' => $user->id,
                'text' => $question->question
            ]);

            if ($question->myQuestionImages) {
                foreach ($question->myQuestionImages as $image)
                    $new_chat->messages()->create([
                        'user_id' => $user->id,
                        'text' => 'Фотография',
                        'img' => $image->getImageLink()
                    ]);
            }

            return $new_chat;
        }
        return null;
    }
}
