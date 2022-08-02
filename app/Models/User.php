<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method UserFactory factory()
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'is_active',
        'mobile_phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',

    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Format the phone number
     *
     * @noinspection PhpUnused
     */
    protected function mobilePhone(): Attribute
    {
        return Attribute::make(
            get: static function ($value) {
                if (preg_match('/^(\d{4})(\d{3})(\d{3})$/', $value, $matches)) {
                    $result = $matches[1] . ' ' . $matches[2] . ' ' . $matches[3];
                } else {
                    $result = $value;
                }

                return $result;
            },
            set: static function ($value) {
                $value = preg_replace('/\D+/', '', $value); // remove all non-digits

                return preg_replace('/^614/', '04', $value); // If the number starts with 614, replace with 04
            },
        );
    }
}
