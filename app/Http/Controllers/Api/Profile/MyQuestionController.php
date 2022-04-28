<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\BaseController;
use App\Models\MyQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class MyQuestionController extends BaseController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $my_questions_paginator = MyQuestion::questionList($user->id)
            ->paginate(20);

        $curPage = $my_questions_paginator->currentPage();
        $lastPage = $my_questions_paginator->lastPage();

        $my_questions = $my_questions_paginator->items();

        return $this->sendResponse(['currentPage' => $curPage, 'lastPage' => $lastPage, 'my_questions' => $my_questions], 'MyQuestion list');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'question' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = $request->user();

        $new_my_question = MyQuestion::new($user->id, $request->subject, $request->question, $request->images);

        if ($new_my_question) {
            return $this->sendResponse([], 'Create successful', 201);
        }

        return $this->sendError('Error creating', [], 500);
    }
}

