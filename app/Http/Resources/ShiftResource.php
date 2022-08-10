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
 * @property mixed users
 * @property mixed is_enabled
 */
class ShiftResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            //'day_monday'     => $this->day_monday,
            //'day_tuesday'    => $this->day_tuesday,
            //'day_wednesday'  => $this->day_wednesday,
            //'day_thursday'   => $this->day_thursday,
            //'day_friday'     => $this->day_friday,
            //'day_saturday'   => $this->day_saturday,
            //'day_sunday'     => $this->day_sunday,
            'start_time'     => $this->start_time,
            'end_time'       => $this->end_time,
            'available_from' => $this->available_from,
            'available_to'   => $this->available_to,
            //'users'      => $this->users,
            'js_days'        => [ // These will map to JavaScript date() days
                                  0 => $this->day_sunday,
                                  1 => $this->day_monday,
                                  2 => $this->day_tuesday,
                                  3 => $this->day_wednesday,
                                  4 => $this->day_thursday,
                                  5 => $this->day_friday,
                                  6 => $this->day_saturday,
            ],
            'volunteers'     => UserResource::collection($this->users),
        ];
    }
}
