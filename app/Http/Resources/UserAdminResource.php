<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $is_temporary
 * @property mixed $name
 * @property mixed $email
 * @property mixed $role
 * @property mixed $gender
 * @property mixed $mobile_phone
 * @property mixed $is_enabled
 * @property mixed $year_of_birth
 * @property mixed $appointment
 * @property mixed $serving_as
 * @property mixed $marital_status
 * @property \App\Models\User $spouse
 * @property mixed $spouse_id
 * @property mixed $responsible_brother
 */
class UserAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'is_temporary'        => $this->is_temporary,
            'name'                => $this->name,
            'email'               => $this->email,
            'role'                => $this->role,
            'gender'              => $this->gender,
            'mobile_phone'        => $this->mobile_phone,
            'year_of_birth'       => $this->year_of_birth,
            'appointment'         => $this->appointment,
            'serving_as'          => $this->serving_as,
            'marital_status'      => $this->marital_status,
            'spouse_name'         => $this->whenLoaded('spouse', fn() => $this->spouse->name),
            'spouse_id'           => $this->whenNotNull('spouse_id', fn() => $this->spouse_id),
            'is_enabled'          => $this->is_enabled,
            'responsible_brother' => (boolean)$this->responsible_brother,
            'vacations'           => UserVacationResource::collection($this->whenLoaded('vacations')),
            'availability'        => AvailabilityResource::make($this->whenLoaded('availability')),
        ];
    }
}
