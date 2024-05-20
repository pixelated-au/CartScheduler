<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\UserVacation */
class UserVacationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'start_date'  => $this->start_date,
            'end_date'    => $this->end_date,
            'description' => $this->description,
        ];
    }
}
