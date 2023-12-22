<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserLocationChoicesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id'             => ['integer', 'exists:users,id'],
            'selectedLocations.*' => ['nullable', 'integer', 'exists:locations,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
