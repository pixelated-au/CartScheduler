<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $shift
 * @property mixed $user
 * @property mixed $shift_date
 * @property mixed $placements_count
 * @property mixed $videos_count
 * @property mixed $requests_count
 * @property mixed $comments
 * @property mixed $shift_was_cancelled
 * @property mixed $tags
 */
class ReportsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'shift'               => ShiftResource::make($this->shift),
            'submitted_by'        => UserResource::make($this->user),
            'shift_date'          => $this->shift_date,
            'placements_count'    => $this->placements_count,
            'videos_count'        => $this->videos_count,
            'requests_count'      => $this->requests_count,
            'comments'            => $this->comments,
            'shift_was_cancelled' => $this->shift_was_cancelled,
            'tags'                => $this->tags,
        ];
    }
}
