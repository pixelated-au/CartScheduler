<?php

namespace App\Models;

use App\Mail\UserAccountCreated;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method UserFactory factory()
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::created(static function ($user) {
            Mail::to($user->email)->send(new UserAccountCreated($user));
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'gender',
        'is_enabled',
        'mobile_phone',
        'password',
        'role',
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
        'is_enabled'        => 'boolean',
        'email_verified_at' => 'datetime',

    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'has_logged_in', // Used to determine if the user has logged in before by checking the password field
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

    /**
     * @noinspection PhpUnused
     */
    protected function hasLoggedIn(): Attribute
    {
        return Attribute::make(
            get: static function ($value, $attributes) {
                return isset($attributes['password']) && $attributes['password'];
            },
        );
    }

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class)->withPivot('shift_user');
    }
}
