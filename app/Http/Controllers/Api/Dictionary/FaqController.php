<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;


class FaqController extends BaseController
{

    public function index(): JsonResponse
    {
        $data = Faq::getQuestions();

        return $this->sendResponse($data, 'Faq data');
    }

    public function answer($id): JsonResponse
    {
        $data = Faq::getAnswerById($id);

        if ($data)
            return $this->sendResponse($data, 'Question data');

        return $this->sendError('Question not found');
    }


}
