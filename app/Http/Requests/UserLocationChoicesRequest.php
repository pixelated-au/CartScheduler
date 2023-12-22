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
            'selectedLocations.*' => ['nullable', 'integer', 'exists:locations,id'],
        ];
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @noinspection PhpUnused
     */
    public function withValidator(Validator $validator): void
    {
        // TODO AFTER upgrade to laravel V10, THIS NEEDS TO BE CONVERTED TO THE FUNCTION after()
        $settings = app()->make(GeneralSettings::class);
        $validator->after(function (Validator $validator) use ($settings) {
            if (!$settings->enableUserLocationChoices) {
                $validator->errors()->add('featureDisabled', 'User location choices are not enabled.');
            }
        });
        // Laravel V10 syntax
//        return [
//            function (Validator $validator) use ($settings) {
//                if (!$settings->enableUserLocationChoices) {
//                    $validator->errors()->add('featureDisabled', 'User location choices are not enabled.');
//                }
//            },
//        ];
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
