<?php

namespace App\Http\Requests\Api\Profile;

use App\Http\Requests\Api\ApiFormRequest;

class ProfileUpdateRequest extends ApiFormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'string', 'max:255'],
            'about' => ['string'],
            'address' => ['required', 'string', 'max:255'],
            'place_id' => ['required', 'string'],
            'languages' => ['array'],
            'languages.*' => ['integer'],
            'categories' => ['required', 'array'],
            'categories.*' => ['integer'],
            'image' => ['nullable', 'string'],
            'video' => ['nullable', 'string'],
            'portfolio_images' => ['array'],
            'portfolio_images.*' => ['array'],
            'portfolio_links' => ['array'],
            'portfolio_links.*' => ['string', 'max:255']
        ];
    }
}
