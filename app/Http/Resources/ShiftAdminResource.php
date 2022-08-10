<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed location_id
 * @property mixed day_monday
 * @property mixed day_tuesday
 * @property mixed day_wednesday
 * @property mixed day_thursday
 * @property mixed day_friday
 * @property mixed day_saturday
 * @property mixed day_sunday
 * @property mixed start_time
 * @property mixed end_time
 * @property mixed available_from
 * @property mixed available_to
 * @property mixed is_enabled
 */
class ShiftAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'location_id'    => $this->location_id,
            'day_monday'     => $this->day_monday,
            'day_tuesday'    => $this->day_tuesday,
            'day_wednesday'  => $this->day_wednesday,
            'day_thursday'   => $this->day_thursday,
            'day_friday'     => $this->day_friday,
            'day_saturday'   => $this->day_saturday,
            'day_sunday'     => $this->day_sunday,
            'start_time'     => $this->start_time,
            'end_time'       => $this->end_time,
            'available_from' => $this->available_from,
            'available_to'   => $this->available_to,
            'is_enabled'     => $this->is_enabled,
        ];
    }
}
