<?php

namespace App\Models;

use App\Models\Events\ShiftUpdated;
use Database\Factories\ShiftFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @method ShiftFactory factory()
 */
class Shift extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'location_id',
        'day_monday',
        'day_tuesday',
        'day_wednesday',
        'day_thursday',
        'day_friday',
        'day_saturday',
        'day_sunday',
        'start_time',
        'end_time',
        'available_from',
        'available_to',
        'is_enabled',
    ];

    protected $casts = [
        'day_monday'     => 'boolean',
        'day_tuesday'    => 'boolean',
        'day_wednesday'  => 'boolean',
        'day_thursday'   => 'boolean',
        'day_friday'     => 'boolean',
        'day_saturday'   => 'boolean',
        'day_sunday'     => 'boolean',
        'is_enabled'     => 'boolean',
        'available_from' => 'datetime',
        'available_to'   => 'datetime',
    ];

    protected $dispatchesEvents = [
        'updated' => ShiftUpdated::class,
    ];

    protected function availableFrom(): Attribute
    {
        return Attribute::make(
            get: static fn(?string $value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: static fn(?string $value) => $value ? Carbon::parse($value)->startOfDay() : null,
        );
    }

    protected function availableTo(): Attribute
    {
        return Attribute::make(
            get: static fn(?string $value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: static fn(?string $value) => $value ? Carbon::parse($value)->endOfDay() : null,
        );
    }

    protected function startHour(): Attribute
    {
        return Attribute::make(
            get: static fn(?string $value, array $attributes) => Str::of($attributes['start_time'])->before(':')->toInteger(),
        );
    }

    protected function endHour(): Attribute
    {
        return Attribute::make(
            get: static fn(?string $value, array $attributes) => Str::of($attributes['end_time'])->before(':')->toInteger(),
        );
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['id', 'shift_date']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
}
