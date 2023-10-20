<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
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
            'is_enabled'   => ['boolean']
        ];
    }

    public function prepareForValidation(array $data, int $index)
    {
        $data['mobile_phone'] = Str::of($data['mobile_phone'])
            ->tap(fn(string $value) => Str::startsWith($value, '+') ? "0$value" : "$value")
            ->replaceMatches('/[^A-Za-z0-9]++/', '')
            ->trim()
            ->toString();

        $data['email'] = Str::of($data['email'])->lower()->trim()->toString();

        if ($data['gender'] === 'm') {
            $data['gender'] = 'male';
        }
        if ($data['gender'] === 'f') {
            $data['gender'] = 'female';
        }

        if ($data['spouse_email']) {
            $spouse = User::where('email', $data['spouse_email'])->first();
            if ($spouse) {
                $data['spouse_id'] = $spouse->id;
            }
        }

        if (!isset($data['year_of_baptism'])) {
            $data['year_of_baptism'] = null;
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

        return $data;
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
