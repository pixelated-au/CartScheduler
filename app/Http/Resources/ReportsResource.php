<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property integer $id
 * @property \App\Models\Shift $shift
 * @property \App\Models\User $user
 * @property mixed $shift_date
 * @property integer $placements_count
 * @property integer $videos_count
 * @property integer $requests_count
 * @property string $comments
 * @property boolean $shift_was_cancelled
 * @property mixed $tags
 * @property array $metadata
 * @deprecated
 */
class ReportsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'shift'               => ShiftResource::make($this->whenLoaded('shift')),
            'submitted_by'        => UserResource::make($this->whenLoaded('user')),
            'shift_date'          => $this->shift_date,
            'placements_count'    => $this->placements_count,
            'videos_count'        => $this->videos_count,
            'requests_count'      => $this->requests_count,
            'comments'            => $this->comments,
            'shift_was_cancelled' => $this->shift_was_cancelled,
            'tags'                => $this->tags,
            'metadata'            => $this->when($this->metadata && count($this->metadata), [
                'shift_id'           => $this->metadata['shift_id'] ?? null,
                'shift_time'         => $this->metadata['shift_time'] ?? null,
                'location_id'        => $this->metadata['location_id'] ?? null,
                'location_name'      => $this->metadata['location_name'] ?? null,
                'submitted_by_name'  => $this->metadata['submitted_by_name'] ?? null,
                'submitted_by_email' => $this->metadata['submitted_by_email'] ?? null,
                'submitted_by_phone' => $this->metadata['submitted_by_phone'] ?? null,
                'associates'         => $this->metadata['associates'] ?? null,
            ]),
        ];
    }
}
