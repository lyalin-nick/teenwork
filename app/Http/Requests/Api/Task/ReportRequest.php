<?php

namespace App\Http\Requests\Api\Task;

use App\Http\Requests\Api\ApiFormRequest;
use Auth;

class ReportRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title_id' => ['required', 'integer'],
            'title' => ['string'],
            'text' => ['required', 'string'],
        ];
    }
}
