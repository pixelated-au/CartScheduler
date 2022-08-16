<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class Report extends Model
{
    use HasTags;

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftUser::class)->with('shift');
    }
}
