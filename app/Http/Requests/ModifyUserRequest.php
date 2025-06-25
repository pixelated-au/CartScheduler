<?php

namespace App\Http\Requests;

use App\Actions\GetUserValidationUtils;
use App\Enums\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifyUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function prepareForValidation(): void
    {
        $validationUtils = app()->make(GetUserValidationUtils::class);

        $this->merge(
            $validationUtils->prepare($this->all())
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $validationUtils = app()->make(GetUserValidationUtils::class);
        $rules           = $validationUtils->rules();

        $rules['email'][]      = Rule::unique('users')->ignore($this->get('id'));
        $rules['is_enabled'][] = ['boolean'];

        return $rules;
    }

    public function after(): array
    {
        // NOTE, if updating these, also update the rules in the UsersImport class which also has validations
        return [
            function (Validator $validator) {
                $fields = $validator->validated();
                if (isset($fields['gender']) && $fields['gender'] === 'female' && $fields['appointment']) {
                    $validator->errors()->add('gender', 'A sister user cannot have an appointment');
                }
                if (!$fields['is_unrestricted'] && $fields['role'] === Role::Admin->value) {
                    $validator->errors()->add('is_unrestricted', 'Restricted users cannot be an administrator');
                }
            }
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
