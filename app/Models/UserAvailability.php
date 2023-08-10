<?php

namespace App\Models;

use App\Casts\SetAsEnumCollectionCast;
use App\Enums\AvailabilityHours;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserAvailability extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    protected $casts = [
        'day_monday'    => SetAsEnumCollectionCast::class . ':' . AvailabilityHours::class,
        'day_tuesday'   => SetAsEnumCollectionCast::class . ':' . AvailabilityHours::class,
        'day_wednesday' => SetAsEnumCollectionCast::class . ':' . AvailabilityHours::class,
        'day_thursday'  => SetAsEnumCollectionCast::class . ':' . AvailabilityHours::class,
        'day_friday'    => SetAsEnumCollectionCast::class . ':' . AvailabilityHours::class,
        'day_saturday'  => SetAsEnumCollectionCast::class . ':' . AvailabilityHours::class,
        'day_sunday'    => SetAsEnumCollectionCast::class . ':' . AvailabilityHours::class,
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
