<?php

namespace App\Http\Requests;

use App\Actions\GetUserValidationPreparations;
use App\Enums\Appontment;
use App\Enums\MaritalStatus;
use App\Enums\Role;
use App\Enums\ServingAs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

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
            'role'                => ['required', 'string', new Enum(Role::class)],
            'gender'              => ['required', 'string', 'in:male,female'],
            'mobile_phone'        => ['required', 'string', 'regex:/^([0-9\+\-\s]+)$/', 'min:8', 'max:15'],
            'year_of_birth'       => ['nullable', 'integer', 'min:' . date('Y') - 100, 'max:' . date('Y')],
            'appointment'         => ['nullable', 'string', new Enum(Appontment::class)],
            'serving_as'          => ['nullable', 'string', new Enum(ServingAs::class)],
            'marital_status'      => ['nullable', 'string', new Enum(MaritalStatus::class)],
            'responsible_brother' => ['nullable', 'boolean'],
            'is_enabled'          => ['boolean'],
            'is_unrestricted'     => ['boolean'],
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
