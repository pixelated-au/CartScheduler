<?php

namespace App\Models;

use Database\Factories\ShiftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method ShiftFactory factory()
 */
class Shift extends Model
{
    use HasFactory;

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
