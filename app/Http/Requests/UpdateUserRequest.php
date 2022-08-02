<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->get('id'))],
            'role'         => ['required', 'string', 'in:admin,user'],
            'gender'       => ['required', 'string', 'in:male,female'],
            'mobile_phone' => ['required', 'string', 'regex:/^([0-9\+\-\s]+)$/', 'min:10', 'max:15'],
            'is_active'    => ['boolean']
        ];
    }

    public function messages(): array
    {
        $formatMsg = 'Please use the format 04xx xxx xxx';

        return [
            'mobile_phone.regex' => "The mobile phone can contain only numbers and spaces. $formatMsg",
            'mobile_phone.min'   => $formatMsg,
            'mobile_phone.max'   => $formatMsg,
        ];
    }

}
