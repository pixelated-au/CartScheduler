<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserVacationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id'                 => ['integer', 'exists:users,id', Rule::prohibitedIf($this->user()->role !== 'admin')],
            'vacations.*.id'          => [
                'nullable',
                'integer',
                Rule::when(
                    fn() => $this->user()->role === 'admin',
                    static fn() => 'exists:user_vacations,id,user_id',
                    fn() => Rule::exists('user_vacations', 'id')->where('user_id', $this->user()->id),
                )
            ],
            'vacations.*.start_date'  => ['required', 'date'],
            'vacations.*.end_date'    => ['required', 'date'],
            'vacations.*.description' => ['nullable', 'string', 'max:255'],
            'deletedVacations.*.id'   => [
                'required',
                'integer',
                Rule::when(
                    fn() => $this->user()->role === 'admin',
                    static fn() => 'exists:user_vacations,id,user_id',
                    fn() => Rule::exists('user_vacations', 'id')->where('user_id', $this->user()->id),
                )],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'user_id.prohibited'              => "You cannot update another user's vacations",
            'vacations.*.id.exists'           => 'Vacation ID is invalid',
            'vacations.*.start_date.required' => 'Start date is required',
            'vacations.*.end_date.required'   => 'End date is required',
            'vacations.*.start_date.date'     => 'Start date is not a date',
            'vacations.*.end_date.date'       => 'End date is not a date',
            'vacations.*.description.max'     => 'Description is too long. Please keep it brief',
            'deletedVacations.*.id.exists'    => 'Vacation ID is invalid',
        ];
    }
}
