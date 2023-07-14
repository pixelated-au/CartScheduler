<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'siteName' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'siteName.required' => 'Site name is required',
        ];
    }
}
