<?php

namespace App\Http\Requests\Api\Portfolio;

use App\Http\Requests\Api\ApiFormRequest;
use Auth;

class UploadImageRequest extends ApiFormRequest
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
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg'],
            'description' => ['string'],
        ];
    }
}
