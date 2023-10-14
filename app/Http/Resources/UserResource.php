<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $email
 * @property mixed $role
 * @property mixed $gender
 * @property mixed $mobile_phone
 * @property mixed $is_enabled
 * @property mixed $pivot
 * @property mixed $last_shift_date
 * @property mixed $last_shift_start_time
 * @property mixed $num_sunday
 * @property mixed $num_monday
 * @property mixed $num_tuesday
 * @property mixed $num_wednesday
 * @property mixed $num_thursday
 * @property mixed $num_friday
 * @property mixed $num_saturday
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'gender'                => $this->gender,
            'mobile_phone'          => $this->mobile_phone,
            'email'                 => $this->email,
            'shift_id'              => $this->whenPivotLoaded('shift_user', fn() => $this->pivot['shift_id']),
            'shift_date'            => $this->whenPivotLoaded('shift_user', fn() => $this->pivot['shift_date']),
            'last_shift_date'       => $this->whenNotNull($this->last_shift_date),
            'last_shift_start_time' => $this->whenNotNull($this->last_shift_start_time),
            'num_sundays'           => $this->whenNotNull($this->num_sunday),
            'num_mondays'           => $this->whenNotNull($this->num_monday),
            'num_tuesdays'          => $this->whenNotNull($this->num_tuesday),
            'num_wednesdays'        => $this->whenNotNull($this->num_wednesday),
            'num_thursdays'         => $this->whenNotNull($this->num_thursday),
            'num_fridays'           => $this->whenNotNull($this->num_friday),
            'num_saturdays'         => $this->whenNotNull($this->num_saturday),
        ];
    }
}
