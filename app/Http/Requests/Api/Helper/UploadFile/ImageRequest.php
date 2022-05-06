<?php

namespace App\Http\Requests\Api\Helper\UploadFile;

use App\Http\Requests\Api\ApiFormRequest;

class ImageRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => ['image', 'mimes:jpeg,png,jpg'],
        ];
    }
}
