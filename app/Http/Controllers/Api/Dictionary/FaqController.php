<?php

namespace App\Http\Controllers\Api\Dictionary;

use App\Http\Controllers\Api\BaseController;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class FaqController extends BaseController
{

    /**
     * Получение списка вопросов FAQ
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $str = htmlspecialchars($request->get('search'));
        $data = Faq::getQuestions($str);

        return $this->sendResponse($data, 'Faq data');
    }

    /**
     * Просмотр ответа
     *
     * @param int $id
     * @return JsonResponse
     */
    public function answer($id): JsonResponse
    {
        $data = Faq::getAnswerById($id);

        if ($data)
            return $this->sendResponse($data, 'Question data');

        return $this->sendError('Question not found');
    }


}
