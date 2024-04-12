<?php

namespace App\Models;

use App\Mail\UserAccountCreated;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

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
    use LogsActivity;
    use CausesActivity;

    protected static function booted(): void
    {
        static::created(static function (self $user) {
            Mail::to($user->email)->send(new UserAccountCreated($user));
        });
        static::saved(static function (self $user) {
            // called on updated and created events
            if ($user->spouse_id) {
                $spouse                 = User::find($user->spouse_id);
                $spouse->spouse_id      = $user->id;
                $spouse->marital_status = 'married';
                $spouse->saveQuietly();
            }
        });
        static::updating(static function (self $user) {
            $dirty    = $user->getDirty();
            $original = $user->getOriginal();
            if (array_key_exists('spouse_id', $dirty) && $dirty['spouse_id'] === null && $original['spouse_id'] !== null) {
                $spouse            = User::find($original['spouse_id']);
                $spouse->spouse_id = null;
                $spouse->saveQuietly();
            }
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
        'is_unrestricted',
        'mobile_phone',
        'year_of_birth',
        'marital_status',
        'spouse_id',
        'appointment',
        'serving_as',
        'responsible_brother',
        'is_unrestricted',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
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
        'is_enabled'          => 'boolean',
        'is_unrestricted'     => 'boolean',
        'responsible_brother' => 'boolean',
        'email_verified_at'   => 'datetime',
        'year_of_birth'       => 'integer',
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

    /** @noinspection PhpUnused */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Format the phone number
     *
     * @noinspection PhpUnused
     */
    protected function mobilePhone(): Attribute
    {
        // todo https://github.com/Propaganistas/Laravel-Phone
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

    protected function isRestricted(): Attribute
    {
        return Attribute::make(
            get: static function ($value, $attributes) {
                return isset($attributes['is_unrestricted']) && !$attributes['is_unrestricted'];
            },
        );
    }

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class)->withPivot('shift_user');
    }

    public function spouse(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'spouse_id');
    }

    public function bookings(): HasMany
    {
        return $this->HasMany(ShiftUser::class);
    }

    public function availability(): HasOne
    {
        return $this->hasOne(UserAvailability::class);
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(UserVacation::class);
    }

    public function rosterLocations(): BelongsToMany
    {
        return $this->belongsToMany(related: Location::class, table: 'user_roster_locations');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'])
            ->logOnlyDirty();
    }
}
