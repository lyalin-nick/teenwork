<?php

namespace App\Http\Controllers\Api\Profile;

use App\Actions\Chat\QuestionChatCreateAction;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\MyQuestion\QuestionRequest;
use App\Models\MyQuestion;
use Auth;
use Illuminate\Http\JsonResponse;

class MyQuestionController extends BaseController
{
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $my_questions_paginator = MyQuestion::questionList($user->id)
            ->paginate(20);

        $curPage = $my_questions_paginator->currentPage();
        $lastPage = $my_questions_paginator->lastPage();

        $my_questions = $my_questions_paginator->items();

        return $this->sendResponse(['currentPage' => $curPage, 'lastPage' => $lastPage, 'my_questions' => $my_questions], 'MyQuestion list');
    }

    public function store(QuestionRequest $request, QuestionChatCreateAction $chatCreateAction): JsonResponse
    {
        $user = Auth::user();

        $new_my_question = MyQuestion::new($user->id, $request->subject, $request->question, $request->images);

        if ($new_my_question) {
            $chat = $chatCreateAction($user, $new_my_question);
            $new_my_question->chat_id = $chat->id;
            $new_my_question->save();

            return $this->sendResponse(['chat_id' => $chat->id], 'Create successful', 201);
        }

        return $this->sendError('Error creating', [], 500);
    }
}

