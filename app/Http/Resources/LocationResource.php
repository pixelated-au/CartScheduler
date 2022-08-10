<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $description
 * @property mixed $min_volunteers
 * @property mixed $max_volunteers
 * @property mixed $requires_brother
 * @property mixed $latitude
 * @property mixed $longitude
 * @property mixed $shifts
 */
class LocationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'description'      => $this->description,
            'min_volunteers'   => $this->min_volunteers,
            'max_volunteers'   => $this->max_volunteers,
            'requires_brother' => $this->requires_brother,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'shifts'           => ShiftResource::collection($this->shifts),
        ];
    }
}
