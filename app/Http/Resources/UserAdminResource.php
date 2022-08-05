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
 */
class UserAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'role'         => $this->role,
            'gender'       => $this->gender,
            'mobile_phone' => $this->mobile_phone,
            'is_enabled'   => $this->is_enabled,
        ];
    }
}
