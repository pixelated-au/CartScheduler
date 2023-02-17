<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Tags\HasTags;

class Report extends Model
{
    use HasFactory;
    use HasTags;

    protected $fillable = [
        'shift_id',
        'report_submitted_user_id',
        'shift_date',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'report_submitted_user_id');
    }
}
