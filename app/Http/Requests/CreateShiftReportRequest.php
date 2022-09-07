<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShiftReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_id'            => ['required', 'integer', 'exists:shifts,id'],
            'shift_date'          => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'start_time'          => ['required', 'date_format:H:i:s'],
            'shift_was_cancelled' => ['boolean'],
            'placements_count'    => ['integer'],
            'videos_count'        => ['integer'],
            'requests_count'      => ['integer'],
            'comments'            => ['string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'shift_was_cancelled.boolean' => 'Shift was cancelled must be a boolean',
            'placements_count.integer'    => 'Placements count must be a whole number',
            'videos_count.integer'        => 'Videos count must be a whole number',
            'requests_count.integer'      => 'Requests count must be a whole number',
            'comments.string'             => 'Comments must be a string',
            'comments.max'                => 'Comments must be less than 500 characters',
        ];
    }
}
