<?php

namespace App\Http\Controllers;

use App\Actions\DoShiftReservation;
use App\Actions\ErrorApiResource;
use App\Actions\ValidateShiftIsAvailableAction;
use App\Actions\ValidateVolunteerIsAllowedToBeRosteredAction;
use App\Exceptions\ShiftAvailabilityException;
use App\Exceptions\VolunteerIsAllowedException;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MoveUserToNewShiftController extends Controller
{
    public function __construct(
        private readonly DoShiftReservation                           $doShiftReservation,
        private readonly ValidateShiftIsAvailableAction               $validateShiftIsAvailableAction,
        private readonly ValidateVolunteerIsAllowedToBeRosteredAction $validateVolunteerIsAllowedToBeRosteredAction,
    )
    {
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'user_id'      => ['required', 'exists:users,id'],
            'location_id'  => ['required', 'exists:locations,id'],
            'old_shift_id' => ['required', 'exists:shifts,id'],
            'date'         => ['required', 'date'],
        ]);

        $oldShift = Shift::find($request->get('old_shift_id'));
        $date     = Carbon::parse($request->get('date'));
        //$startTime = $oldShift->start_time;
        $startTime = $date->copy()->setTimeFromTimeString($oldShift->start_time);

        $range = [
            $startTime->copy()->sub(45, 'minutes')->format('H:i:s'),
            $startTime->copy()->add(45, 'minutes')->format('H:i:s'),
        ];

        $location = Location::with([
            'shifts'       => fn(HasMany $query) => $query->whereBetween('start_time', $range),
            'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot('shift_date', $date->toDateString()),
        ])
            ->where('id', $request->get('location_id'))
            ->where('is_enabled', true)
            ->first();

        if (!$location) {
            return ErrorApiResource::create('Location not found',
                ErrorApiResource::CODE_LOCATION_NOT_FOUND, 422);
        }

        $shift = $location->shifts->first(); // Should only be 1 matching shift due to the whereBetween() query above.
        if (!$shift) {
            return ErrorApiResource::create('No shift found for this location at this time',
                ErrorApiResource::CODE_SHIFT_NOT_FOUND, 422);
        }

        try {
            $this->validateShiftIsAvailableAction->execute($shift, $date);
        } catch (ShiftAvailabilityException $e) {
            return ErrorApiResource::create($e->getMessage(), $e->getExceptionType(), 422);
        }

        $user = User::find($request->get('user_id'));
        try {
            $this->validateVolunteerIsAllowedToBeRosteredAction->execute($location, $user, $shift->users);
        } catch (VolunteerIsAllowedException $e) {
            return ErrorApiResource::create($e->getMessage(), $e->getExceptionType(), 422);
        }

        return DB::transaction(
            function () use ($date, $location, $shift, $request, $oldShift) {
                $oldShift->users()->detach($request->get('user_id'));

                return $this->doShiftReservation->execute($shift, $location, $request->get('user_id'), $date);
            });
    }
}
