<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
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
            'id'               => ['required', 'integer'],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:4000000000'],
            'min_volunteers'   => ['required', 'integer', 'min:0'],
            'max_volunteers'   => ['required', 'integer', 'max:4'],
            'requires_brother' => ['boolean'],
            'latitude'         => ['numeric', 'min:-90', 'max:90.999999999999'],
            'longitude'        => ['numeric', 'min:-180', 'max:180.999999999999'],
            'is_enabled'       => ['boolean']
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
