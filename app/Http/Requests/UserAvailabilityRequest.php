<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserAvailabilityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id'         => ['integer', 'exists:users,id'],
            'day_monday'      => ['nullable', 'present', Rule::requiredIf(fn() => $this->input('num_mondays', 0) > 0), 'array'],
            'day_monday.*'    => ['required', 'integer', 'min:0', 'max:23'],
            'day_tuesday'     => ['nullable', 'present', Rule::requiredIf(fn() => $this->input('num_tuesdays', 0) > 0), 'array'],
            'day_tuesday.*'   => ['required', 'integer', 'min:0', 'max:23'],
            'day_wednesday'   => ['nullable', 'present', Rule::requiredIf(fn() => $this->input('num_wednesdays', 0) > 0), 'array'],
            'day_wednesday.*' => ['required', 'integer', 'min:0', 'max:23'],
            'day_thursday'    => ['nullable', 'present', Rule::requiredIf(fn() => $this->input('num_thursdays', 0) > 0), 'array'],
            'day_thursday.*'  => ['required', 'integer', 'min:0', 'max:23'],
            'day_friday'      => ['nullable', 'present', Rule::requiredIf(fn() => $this->input('num_fridays', 0) > 0), 'array'],
            'day_friday.*'    => ['required', 'integer', 'min:0', 'max:23'],
            'day_saturday'    => ['nullable', 'present', Rule::requiredIf(fn() => $this->input('num_saturdays', 0) > 0), 'array'],
            'day_saturday.*'  => ['required', 'integer', 'min:0', 'max:23'],
            'day_sunday'      => ['nullable', 'present', Rule::requiredIf(fn() => $this->input('num_sundays', 0) > 0), 'array'],
            'day_sunday.*'    => ['required', 'integer', 'min:0', 'max:23'],

            'num_mondays'    => ['required', 'integer', 'min:0', 'max:4'],
            'num_tuesdays'   => ['required', 'integer', 'min:0', 'max:4'],
            'num_wednesdays' => ['required', 'integer', 'min:0', 'max:4'],
            'num_thursdays'  => ['required', 'integer', 'min:0', 'max:4'],
            'num_fridays'    => ['required', 'integer', 'min:0', 'max:4'],
            'num_saturdays'  => ['required', 'integer', 'min:0', 'max:4'],
            'num_sundays'    => ['required', 'integer', 'min:0', 'max:4'],

            'comments' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->parseDay('monday');
        $this->parseDay('tuesday');
        $this->parseDay('wednesday');
        $this->parseDay('thursday');
        $this->parseDay('friday');
        $this->parseDay('saturday');
        $this->parseDay('sunday');
    }

    protected function parseDay(string $dayOfWeek): void
    {
        if ($this->input("num_{$dayOfWeek}s", 0) === 0) {
            // if there are no 'days' set for $dayOfWeek, reset the day to an empty array
            $this->merge(["day_$dayOfWeek" => null]);
            return;
        }

        // if there are 'days' set for $dayOfWeek, make sure the full range of hours are set
        $hours = $this->input("day_$dayOfWeek", []);
        $first = $hours[0] ?? 0;
        $last  = $hours[count($hours) - 1] ?? 0;
        $this->merge(["day_$dayOfWeek" => range($first, $last)]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
