<?php

namespace App\Http\Requests;

use App\Enums\Appontment;
use App\Enums\MaritalStatus;
use App\Enums\Role;
use App\Enums\ServingAs;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->get('id'))],
            'role'           => ['required', 'string', new Enum(Role::class)],
            'gender'         => ['required', 'string', 'in:male,female'],
            'mobile_phone'   => ['required', 'string', 'regex:/^([0-9\+\-\s]+)$/', 'min:10', 'max:15'],
            'year_of_birth'  => ['nullable', 'integer', 'min:' . date('Y') - 100, 'max:' . date('Y')],
            'appointment'    => ['nullable', 'string', new Enum(Appontment::class)],
            'serving_as'     => ['nullable', 'string', new Enum(ServingAs::class)],
            'marital_status' => ['nullable', 'string', new Enum(MaritalStatus::class)],
            'is_enabled'     => ['boolean']
        ];
    }

    public function prepareForValidation(): void
    {
        $data = $this->all();

        $data['mobile_phone'] = isset($data['mobile_phone'])
            ? Str::of($data['mobile_phone'])
                ->tap(fn(string $value) => Str::startsWith($value, '+') ? "0$value" : "$value")
                ->replaceMatches('/[^A-Za-z0-9]++/', '')
                ->trim()
                ->toString()
            : null;

        $data['email'] = isset($data['email'])
            ? Str::of($data['email'])->lower()->trim()->toString()
            : null;

        if (isset($data['gender']) && $data['gender'] === 'm') {
            $data['gender'] = 'male';
        }
        if (isset($data['gender']) && $data['gender'] === 'f') {
            $data['gender'] = 'female';
        }

        if (isset($data['spouse_email'])) {
            $spouse = User::where('email', $data['spouse_email'])->first();
            if ($spouse) {
                $data['spouse_id'] = $spouse->id;
            }
        }

        if (!isset($data['year_of_birth'])) {
            $data['year_of_birth'] = null;
        }
        if (!isset($data['appointment'])) {
            $data['appointment'] = null;
        }
        if (!isset($data['serving_as'])) {
            $data['serving_as'] = null;
        }
        if (!isset($data['marital_status'])) {
            $data['marital_status'] = null;
        }
        if (!isset($data['spouse_id'])) {
            $data['spouse_id'] = null;
        }
        if (!isset($data['responsible_brother'])) {
            $data['responsible_brother'] = false;
        }

        $this->merge($data);
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
