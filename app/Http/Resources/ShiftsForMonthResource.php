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
class ShiftsForMonthResource extends JsonResource
{
    public $preserveKeys = true;

    public function toArray($request): array
    {
        ray($this);

        return [
            'locations' => LocationResource::collection($this->locations),
            'dates'     => ShiftDateResourceCollection::collection($this->dates),
        ];
    }
}
