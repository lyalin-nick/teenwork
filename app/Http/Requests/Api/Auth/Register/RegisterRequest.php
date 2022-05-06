<?php

namespace App\Http\Requests\Api\Auth\Register;

use App\Http\Requests\Api\ApiFormRequest;
use Auth;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends ApiFormRequest
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
            'phone' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->numbers()->mixedCase()],
            'password_confirmation' => ['required'],
        ];
    }
}
