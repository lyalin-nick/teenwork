<?php

namespace App\Http\Requests\Api\MyQuestion;

use App\Http\Requests\Api\ApiFormRequest;

class QuestionRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'question' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string']
        ];
    }
}
