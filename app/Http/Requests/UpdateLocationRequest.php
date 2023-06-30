<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Validator;

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
        $maxVolunteers = config('cart-scheduler.max_volunteers_per_location');
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'description'             => ['required', 'string', 'max:4000000000'],
            'min_volunteers'          => ['required', 'integer', 'min:0'],
            'max_volunteers'          => ['required', 'integer', "max:$maxVolunteers"],
            'requires_brother'        => ['boolean'],
            'latitude'                => ['nullable', 'numeric', 'min:-90', 'max:90.999999999999'],
            'longitude'               => ['nullable', 'numeric', 'min:-180', 'max:180.999999999999'],
            'is_enabled'              => ['nullable', 'boolean'],
            'shifts'                  => ['array'],
            'shifts.*.id'             => ['filled', 'integer'],
            'shifts.*.location_id'    => ['required', 'integer'],
            'shifts.*.day_monday'     => ['boolean'],
            'shifts.*.day_tuesday'    => ['boolean'],
            'shifts.*.day_wednesday'  => ['boolean'],
            'shifts.*.day_thursday'   => ['boolean'],
            'shifts.*.day_friday'     => ['boolean'],
            'shifts.*.day_saturday'   => ['boolean'],
            'shifts.*.day_sunday'     => ['boolean'],
            'shifts.*.start_time'     => ['required', 'date_format:H:i:s'],
            'shifts.*.end_time'       => ['required', 'date_format:H:i:s'],
            'shifts.*.available_from' => ['nullable', 'date'], // Note, extra validation is done in withValidator()
            'shifts.*.available_to'   => ['nullable', 'date'], // Note, extra validation is done in withValidator()
            'shifts.*.is_enabled'     => ['nullable', 'boolean'],
        ];
    }

    /** @noinspection PhpUnused */
    public function withValidator(Validator $validator): void
    {
        $validator->sometimes(
            'shifts.*.available_from',
            'before_or_equal:shifts.*.available_to',
            fn(Fluent $input, Fluent $shiftData) => (bool)$shiftData->get('available_to'));

        $validator->sometimes(
            'shifts.*.available_to',
            'after_or_equal:shifts.*.available_from',
            fn(Fluent $input, Fluent $shiftData) => (bool)$shiftData->get('available_from'));
    }

    public function messages(): array
    {
        $formatMsg = 'Please use the format 04xx xxx xxx';

        return [
            'mobile_phone.regex'                      => "The mobile phone can contain only numbers and spaces. $formatMsg",
            'mobile_phone.min'                        => $formatMsg,
            'mobile_phone.max'                        => $formatMsg,
            'shifts.*.start_time.date_format'         => 'Please use the format HH:mm:ss',
            'shifts.*.end_time.date_format'           => 'Please use the format HH:mm:ss',
            'shifts.*.available_from.date'            => "The 'available from' date must be a valid date and time",
            'shifts.*.available_to.date'              => "The 'available to' date must be a valid date and time",
            'shifts.*.available_from.before_or_equal' => "The 'available from' date must be before or the same as the 'available to' date",
            'shifts.*.available_to.after_or_equal'    => "The 'available to' date must be after or the same the 'available from' date",
        ];
    }

}
