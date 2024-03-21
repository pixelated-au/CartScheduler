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
use RuntimeException;

class ToggleUserOntoShift
{
    public function __construct(private readonly DoShiftReservation $doShiftReservation)
    {
    }

    /**
     * Execute the shift reservation or unreserve the shift based on the given data.
     *
     * @param User $user The user performing the shift reservation or un-reservation.
     * @param array{user?: int, date: string, shift: int, location: int, do_reserve: bool } $data The data required for the shift reservation or un-reservation.
     *     The array should contain the following keys:
     *     - 'user': (optional) The ID of the user to toggle onto the shift. If not provided, the current user ID will be used.
     *     - 'date': The date of the shift in the format 'Y-m-d'.
     *     - 'shift': The ID of the shift to reserve or unreserve.
     *     - 'location': The ID of the location where the shift is located.
     *     - 'do_reserve': A boolean flag indicating whether to reserve the shift. If false, the shift will be un-reserved.
     *
     * @return ToggleReservationStatus The status of the shift reservation or un-reservation. Possible values are:
     *     - ToggleReservationStatus::RESERVATION_MADE: The shift was successfully reserved.
     *     - ToggleReservationStatus::NO_AVAILABLE_SHIFTS: No available shifts to reserve.
     *     - ToggleReservationStatus::RESERVATION_REMOVED: The shift was successfully unreserved.
     * @throws RuntimeException if the user cannot be removed from the shift.
     */
    public function execute(User $user, array $data): ToggleReservationStatus
    {
        return Cache::lock('shift_reservation', 10)->block(10, function () use ($user, $data) {
            // If 'data' contains a user key, toggle that user onto a shift. Otherwise, toggle the current user.
            $userIdToToggle = $data['user'] ?? $user->id;
            $shiftDate      = Carbon::createFromFormat('Y-m-d', $data['date'])->setTime(12, 0);

            $location = Location::with([
                'shifts'       => fn(HasMany $query) => $query->where('shifts.id', '=', $data['shift']),
                'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot(
                    'shift_date',
                    $shiftDate->toDateString(),
                ),
            ])->findOrFail($data['location']);

            /** @var Shift $shift */
            $shift = $location->shifts->first();
            if ($data['do_reserve']) {
                $this->doShiftReservation->execute($shift, $location, $userIdToToggle, $shiftDate);

                return ToggleReservationStatus::RESERVATION_MADE;
            }

            $removeCount = $shift->users()->wherePivot('shift_date', '=', $shiftDate->format('Y-m-d'))->detach($userIdToToggle);
            if (!$removeCount) {
                throw new RuntimeException('Could not remove user from shift');
            }
            activity()
                ->performedOn($shift)
                ->causedBy($user)
                ->withProperties([
                    'user'                => $userIdToToggle,
                    'shift_date'          => $shiftDate,
                    'shift.location.name' => $location->name,
                ])
                ->log('shift_unreserved');

            return ToggleReservationStatus::RESERVATION_REMOVED;
        });
    }
}
