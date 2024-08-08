<?php

namespace App\Http\Requests;

use App\Settings\GeneralSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UserLocationChoicesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id'             => ['integer', 'exists:users,id'],
            'selectedLocations'   => ['nullable'],
            'selectedLocations.*' => ['integer', 'exists:locations,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $settings = app()->make(GeneralSettings::class);
                if (!$settings->enableUserLocationChoices) {
                    $validator->errors()->add('featureDisabled', 'User location choices are not enabled.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'selectedLocations.*.exists' => 'The selected location is invalid.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
