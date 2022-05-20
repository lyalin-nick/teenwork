<?php

namespace App\Http\Requests\Api\Auth\Login;

use App\Http\Requests\Api\ApiFormRequest;
use Auth;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string']
        ];
    }
}
