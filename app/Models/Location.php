<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method LocationFactory factory()
 */
class Location extends Model
{
    use HasFactory;

    protected $casts = [
        'latitude'  => 'decimal',
        'longitude' => 'decimal',
    ];
}
