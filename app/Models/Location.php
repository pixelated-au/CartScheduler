<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method LocationFactory factory()
 */
class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'min_volunteers',
        'max_volunteers',
        'requires_brother',
        'latitude',
        'longitude',
        'is_enabled',
    ];

    protected $casts = [
        'min_volunteers'   => 'integer',
        'max_volunteers'   => 'integer',
        'requires_brother' => 'boolean',
        'latitude'         => 'decimal:8',
        'longitude'        => 'decimal:8',
        'is_enabled'       => 'boolean',
    ];

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }
}
