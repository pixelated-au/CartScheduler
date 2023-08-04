<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $user_id
 * @property mixed $day_monday
 * @property mixed $day_tuesday
 * @property mixed $day_wednesday
 * @property mixed $day_thursday
 * @property mixed $day_friday
 * @property mixed $day_saturday
 * @property mixed $day_sunday
 * @property mixed $num_mondays
 * @property mixed $num_tuesdays
 * @property mixed $num_wednesdays
 * @property mixed $num_thursdays
 * @property mixed $num_fridays
 * @property mixed $num_saturdays
 * @property mixed $num_sundays
 * @property mixed $comments
 */
class AvailabilityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user_id' => $this->user_id,

            'day_monday' => $this->day_monday,
            'day_tuesday' => $this->day_tuesday,
            'day_wednesday' => $this->day_wednesday,
            'day_thursday' => $this->day_thursday,
            'day_friday' => $this->day_friday,
            'day_saturday' => $this->day_saturday,
            'day_sunday' => $this->day_sunday,

            'num_mondays' => $this->num_mondays,
            'num_tuesdays' => $this->num_tuesdays,
            'num_wednesdays' => $this->num_wednesdays,
            'num_thursdays' => $this->num_thursdays,
            'num_fridays' => $this->num_fridays,
            'num_saturdays' => $this->num_saturdays,
            'num_sundays' => $this->num_sundays,

            'comments' => $this->comments,
        ];
    }
}
