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
 * @property mixed $is_enabled
 * @property mixed $sort_order
 * @property mixed $shifts
 * @deprecated 
 */
class LocationAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'clean_description' => strip_tags($this->description),
            'min_volunteers'    => $this->min_volunteers,
            'max_volunteers'    => $this->max_volunteers,
            'requires_brother'  => $this->requires_brother,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'is_enabled'        => $this->is_enabled,
            'sort_order'        => $this->sort_order,
            'shifts'            => ShiftAdminResource::collection($this->whenLoaded('shifts')),
        ];
    }
}
