<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'day_monday' => ['required', 'array'],
            'day_monday.*' => ['required', 'integer', 'min:0', 'max:23'],
            'day_tuesday' => ['required', 'array'],
            'day_tuesday.*' => ['required', 'integer', 'min:0', 'max:23'],
            'day_wednesday' => ['required', 'array'],
            'day_wednesday.*' => ['required', 'integer', 'min:0', 'max:23'],
            'day_thursday' => ['required', 'array'],
            'day_thursday.*' => ['required', 'integer', 'min:0', 'max:23'],
            'day_friday' => ['required', 'array'],
            'day_friday.*' => ['required', 'integer', 'min:0', 'max:23'],
            'day_saturday' => ['required', 'array'],
            'day_saturday.*' => ['required', 'integer', 'min:0', 'max:23'],
            'day_sunday' => ['required', 'array'],
            'day_sunday.*' => ['required', 'integer', 'min:0', 'max:23'],

            'num_mondays' => ['required', 'integer', 'min:0', 'max:4'],
            'num_tuesdays' => ['required', 'integer', 'min:0', 'max:4'],
            'num_wednesdays' => ['required', 'integer', 'min:0', 'max:4'],
            'num_thursdays' => ['required', 'integer', 'min:0', 'max:4'],
            'num_fridays' => ['required', 'integer', 'min:0', 'max:4'],
            'num_saturdays' => ['required', 'integer', 'min:0', 'max:4'],
            'num_sundays' => ['required', 'integer', 'min:0', 'max:4'],

            'comments' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
