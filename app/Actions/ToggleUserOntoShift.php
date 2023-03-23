<?php

namespace App\Actions;

use App\Enums\ToggleReservationStatus;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ToggleUserOntoShift
{
    public function __construct(private readonly DoShiftReservation $doShiftReservation)
    {
    }

    public function execute(User $user, array $data)
    {
        return Cache::lock('shift_reservation', 10)->block(10, function () use ($user, $data) {
            // If 'data' contains a user_id key, toggle that user onto a shift. Otherwise, toggle the current user.
            $userIdToToggle = $data['user_id'] ?? $user->id;
            $shiftDate = Carbon::createFromFormat('Y-m-d', $data['date'])->setTime(12, 0);

            $location = Location::with([
                'shifts' => fn(HasMany $query) => $query->where('shifts.id', '=', $data['shift']),
                'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot(
                    'shift_date',
                    $shiftDate->toDateString(),
                ),
            ])->findOrFail($data['location']);

            /** @var Shift $shift */
            $shift = $location->shifts->first();

            if ($data['do_reserve']) {
                $didReserve = $this->doShiftReservation->execute($shift, $location, $userIdToToggle, $shiftDate);

                return $didReserve
                    ? ToggleReservationStatus::RESERVATION_MADE
                    : ToggleReservationStatus::NO_AVAILABLE_SHIFTS;
            }

            $shift->users()->wherePivot('shift_date', '=', $shiftDate->format('Y-m-d'))->detach($userIdToToggle);
            activity()
                ->performedOn($shift)
                ->causedBy($user)
                ->withProperties([
                    'user_id' => $userIdToToggle,
                    'shift_date' => $shiftDate,
                    'shift.location.name' => $location->name,
                ])
                ->log('shift_unreserved');

            return ToggleReservationStatus::RESERVATION_REMOVED;
        });
    }
}
