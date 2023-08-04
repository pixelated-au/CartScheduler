<?php

namespace App\Models;

use App\Casts\SetAsEnumCollectionCast;
use App\Enums\AvailabilityPeriods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserAvailability extends Model
{
    use HasFactory;

    protected $casts = [
        'day_monday'    => SetAsEnumCollectionCast::class . ':' . AvailabilityPeriods::class,
        'day_tuesday'   => SetAsEnumCollectionCast::class . ':' . AvailabilityPeriods::class,
        'day_wednesday' => SetAsEnumCollectionCast::class . ':' . AvailabilityPeriods::class,
        'day_thursday'  => SetAsEnumCollectionCast::class . ':' . AvailabilityPeriods::class,
        'day_friday'    => SetAsEnumCollectionCast::class . ':' . AvailabilityPeriods::class,
        'day_saturday'  => SetAsEnumCollectionCast::class . ':' . AvailabilityPeriods::class,
        'day_sunday'    => SetAsEnumCollectionCast::class . ':' . AvailabilityPeriods::class,
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
