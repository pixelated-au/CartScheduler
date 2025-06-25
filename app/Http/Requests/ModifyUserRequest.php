<?php

namespace App\Http\Requests;

use App\Actions\GetUserValidationPreparations;
use App\Enums\Appointment;
use App\Enums\MaritalStatus;
use App\Enums\Role;
use App\Enums\ServingAs;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ModifyUserRequest extends FormRequest
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
        // NOTE, if updating these, also update the rules in the UsersImport class which also has validations
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->get('id'))],
            'role'                => ['required', 'string', rule::enum(Role::class)],
            'gender'              => ['required', 'string', 'in:male,female'],
            'mobile_phone'        => ['required', 'string', 'regex:/^([0-9\+\-\s]+)$/', 'min:8', 'max:15'],
            'year_of_birth'       => [
                'nullable', 'integer', 'min:' . Carbon::now()->year - 100, 'max:' . Carbon::now()->year
            ],
            'appointment'         => ['nullable', 'string', rule::enum(Appointment::class)],
            'serving_as'          => ['nullable', 'string', rule::enum(ServingAs::class)],
            'marital_status'      => ['nullable', 'string', rule::enum(MaritalStatus::class)],
            'responsible_brother' => ['nullable', 'boolean'],
            'is_enabled'          => ['boolean'],
            'is_unrestricted'     => ['boolean'],
        ];
    }

    public function after(): array
    {
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

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function prepareForValidation(): void
    {
        $getUserValidationPreparations = app()->make(GetUserValidationPreparations::class);

        $this->merge(
            $getUserValidationPreparations->execute($this->all())
        );
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
