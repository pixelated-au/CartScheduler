<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserVacationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vacations.*.id'          => ['nullable', 'integer', 'exists:user_vacations,id'],
            'vacations.*.user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'vacations.*.start_date'  => ['required', 'date'],
            'vacations.*.end_date'    => ['required', 'bool'],
            'vacations.*.description' => ['nullable', 'string', 'max:2'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'vacations.*.start_date.required' => 'Start date is required',
            'vacations.*.end_date.required'   => 'End date is required',
            'vacations.*.start_date.date'     => 'Start date is not a date',
            'vacations.*.end_date.date'       => 'End date is not a date',
            'vacations.*.description.max'     => 'Description is too long. Please keep it brief'
        ];
    }
}
