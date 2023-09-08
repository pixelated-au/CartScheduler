<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserVacation extends Model
{
    use HasFactory;
    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value->format('Y-m-d'),
            set: fn($value) => $this->attributes['start_date'] = Carbon::parse($value)->startOfDay()
        );
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value->format('Y-m-d'),
            set: fn($value) => $this->attributes['end_date'] = Carbon::parse($value)->endOfDay()
        );
    }
}
