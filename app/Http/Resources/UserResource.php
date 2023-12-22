<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $email
 * @property mixed $gender
 * @property mixed $mobile_phone
 * @property mixed $is_enabled
 * @property mixed $is_unrestricted
 * @property mixed $pivot
 * @property mixed $last_shift_date
 * @property mixed $last_shift_start_time
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
            'is_unrestricted'       => $this->when($this->is_unrestricted, $this->is_unrestricted),
            'shift_id'              => $this->whenPivotLoaded('shift_user', fn() => $this->pivot['shift_id']),
            'shift_date'            => $this->whenPivotLoaded('shift_user', fn() => $this->pivot['shift_date']),
            'last_shift_date'       => $this->whenNotNull($this->last_shift_date),
            'last_shift_start_time' => $this->whenNotNull($this->last_shift_start_time),
        ];
    }
}
