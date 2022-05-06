<?php

namespace App\Http\Requests\Api\Task;

use App\Http\Requests\Api\ApiFormRequest;

class EmployerReviewRequest extends ApiFormRequest
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
            'users' => ['required', 'array'],
            'users.*' => ['integer'],
            'rating' => ['required', 'integer'],
            'text' => ['required', 'string']
        ];
    }
}
