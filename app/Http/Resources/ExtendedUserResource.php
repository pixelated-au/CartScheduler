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
 * TODO COMPLETE INTEGRATION OF SPOUSE
 * @property mixed $marital_status
 * @property mixed $appointment
 * @property mixed $serving_as
 * @property mixed $responsible_brother
 * @property mixed $pivot
 * @property mixed $last_shift_date
 * @property mixed $last_shift_start_time
 * @property mixed $num_sundays
 * @property mixed $num_mondays
 * @property mixed $num_tuesdays
 * @property mixed $num_wednesdays
 * @property mixed $num_thursdays
 * @property mixed $num_fridays
 * @property mixed $num_saturdays
 * @property mixed $filled_sundays
 * @property mixed $filled_mondays
 * @property mixed $filled_tuesdays
 * @property mixed $filled_wednesdays
 * @property mixed $filled_thursdays
 * @property mixed $filled_fridays
 * @property mixed $filled_saturdays
 * @property mixed $comments
 */

class ExtendedUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'gender'                => $this->gender,
            'mobile_phone'          => $this->mobile_phone,
            'email'                 => $this->email,
            'marital_status'        => $this->marital_status,
            'appointment'           => $this->appointment,
            'serving_as'            => $this->serving_as,
            'responsible_brother'   => $this->responsible_brother,
            'shift_id'              => $this->whenPivotLoaded('shift_user', fn() => $this->pivot['shift_id']),
            'shift_date'            => $this->whenPivotLoaded('shift_user', fn() => $this->pivot['shift_date']),
            'last_shift_date'       => $this->whenNotNull($this->last_shift_date),
            'last_shift_start_time' => $this->whenNotNull($this->last_shift_start_time),
            'num_sundays'           => $this->whenNotNull($this->num_sundays),
            'num_mondays'           => $this->whenNotNull($this->num_mondays),
            'num_tuesdays'          => $this->whenNotNull($this->num_tuesdays),
            'num_wednesdays'        => $this->whenNotNull($this->num_wednesdays),
            'num_thursdays'         => $this->whenNotNull($this->num_thursdays),
            'num_fridays'           => $this->whenNotNull($this->num_fridays),
            'num_saturdays'         => $this->whenNotNull($this->num_saturdays),
            'filled_sundays'        => $this->whenNotNull((int)$this->filled_sundays),
            'filled_mondays'        => $this->whenNotNull((int)$this->filled_mondays),
            'filled_tuesdays'       => $this->whenNotNull((int)$this->filled_tuesdays),
            'filled_wednesdays'     => $this->whenNotNull((int)$this->filled_wednesdays),
            'filled_thursdays'      => $this->whenNotNull((int)$this->filled_thursdays),
            'filled_fridays'        => $this->whenNotNull((int)$this->filled_fridays),
            'filled_saturdays'      => $this->whenNotNull((int)$this->filled_saturdays),
            'availability_comments' => $this->whenNotNull($this->comments),
        ];
    }
}
