<?php

namespace App\Models;

use App\Casts\SetAsEnumCollectionCast;
use App\Enums\WeekdayAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserAvailability extends Model
{
    use HasFactory;

    protected $casts = [
        'day_monday'    => SetAsEnumCollectionCast::class . ':' . WeekdayAvailability::class,
        'day_tuesday'   => SetAsEnumCollectionCast::class . ':' . WeekdayAvailability::class,
        'day_wednesday' => SetAsEnumCollectionCast::class . ':' . WeekdayAvailability::class,
        'day_thursday'  => SetAsEnumCollectionCast::class . ':' . WeekdayAvailability::class,
        'day_friday'    => SetAsEnumCollectionCast::class . ':' . WeekdayAvailability::class,
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
