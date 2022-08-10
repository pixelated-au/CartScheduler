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
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'gender'       => $this->gender,
            'mobile_phone' => $this->mobile_phone,
            'shift_date'   => $this->pivot['shift_date'],
        ];
    }
}
