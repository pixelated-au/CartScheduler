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
 * @property mixed $year_of_baptism
 * @property mixed $appointment
 * @property mixed $serving_as
 * @property mixed $marital_status
 * @property \App\Models\User $spouse
 * @property mixed $spouse_id
 */
class UserAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'role'            => $this->role,
            'gender'          => $this->gender,
            'mobile_phone'    => $this->mobile_phone,
            'year_of_baptism' => $this->year_of_baptism,
            'appointment'     => $this->appointment,
            'serving_as'      => $this->serving_as,
            'marital_status'  => $this->marital_status,
            'spouse_name'     => $this->whenHas('spouse_id', fn() => $this->spouse->name),
            'spouse_id'       => $this->whenHas('spouse_id', fn() => $this->spouse_id),
            'is_enabled'      => $this->is_enabled,
        ];
    }
}
