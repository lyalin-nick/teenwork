<?php

namespace App\Http\Requests\Api\Task;

use App\Http\Requests\Api\ApiFormRequest;

class NewTaskRequest extends ApiFormRequest
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
            'category_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'result' => ['required', 'string'],
            'images' => ['array', 'max:10'],
            'images.*' => ['string'],
            'video' => ['nullable', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'place_id' => ['required', 'string'],
            'dates' => ['required', 'array'],
            'dates.*' => ["string"],
            'start_time' => ['required', 'string'],
            'amount_of_workers' => ['required', 'integer'],
            'minimum_age' => ['required', 'integer'],
            'languages' => ['array'],
            'languages.*' => ['integer'],
            'price' => ['required', 'integer'],
            'payment_type' => ['required', 'string'],
            'safe_deal' => ['required', 'boolean'],
            'hot_work' => ['required', 'boolean'],
            'account_verified' => ['required', 'boolean']
        ];
    }
}
