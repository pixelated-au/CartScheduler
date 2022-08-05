<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed weekday
 * @property mixed start_time
 * @property mixed end_time
 * @property mixed is_enabled
 */
class ShiftResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'weekday'    => $this->weekday,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
            'is_enabled' => $this->is_enabled,
        ];
    }
}
