<?php

namespace App\Http\Controllers;

use App\Actions\DoShiftReservation;
use App\Actions\ErrorApiResource;
use App\Actions\ValidateShiftIsAvailableAction;
use App\Actions\ValidateShiftIsNotFullAction;
use App\Actions\ValidateVolunteerIsAllowedToBeRosteredAction;
use App\Exceptions\ShiftAvailabilityException;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoveUserToNewShiftController extends Controller
{
    public function __construct(
        private readonly DoShiftReservation                           $doShiftReservation,
        private readonly ValidateShiftIsAvailableAction               $validateShiftIsAvailableAction,
        private readonly ValidateVolunteerIsAllowedToBeRosteredAction $validateVolunteerIsAllowedToBeRosteredAction,
        private readonly ValidateShiftIsNotFullAction                 $validateShiftIsNotFullAction,
    )
    {
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'user_id'      => ['required', 'exists:users,id'],
            'location_id'  => ['required', 'exists:locations,id'],
            'date'         => ['required', 'date'],
            'old_shift_id' => ['required', 'exists:shifts,id'],
        ]);

        $oldShift  = Shift::find($request->get('old_shift_id'));
        $date      = $request->date('date')?->midDay();
        $startTime = $date->copy()->setTimeFromTimeString($oldShift->start_time);
        $userId    = $request->integer('user_id');

        $range = [
            $startTime->copy()->sub(45, 'minutes')->format('H:i:s'),
            $startTime->copy()->add(45, 'minutes')->format('H:i:s'),
        ];

        $location = Location::with([
            'shifts'       => fn(HasMany $query) => $query->whereBetween('start_time', $range)
                ->where('shifts.is_enabled', true)
                ->where(fn(Builder $query) => $query
                    ->whereNull('shifts.available_from')
                    ->orWhere('shifts.available_from', '<=', $date)
                )
                ->where(fn(Builder $query) => $query
                    ->whereNull('shifts.available_to')
                    ->orWhere('shifts.available_to', '>=', $date)
                ),
            'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot('shift_date', $date->toDateString()),
        ])
            ->where('id', $request->get('location_id'))
            ->where('is_enabled', true)
            ->first();

        try {

            if (!$location) {
                return ErrorApiResource::create('Location not found',
                    ErrorApiResource::CODE_LOCATION_NOT_FOUND, 422);
            }

            $shift = $location->shifts->first(); // Should only be 1 matching shift due to the whereBetween() query above.
            if (!$shift) {
                return ErrorApiResource::create('No shift found for this location at this time',
                    ErrorApiResource::CODE_SHIFT_NOT_FOUND, 422);
            }

            $this->validateShiftIsAvailableAction->execute($shift, $date);

            $user      = User::find($userId);
            $isAllowed = $this->validateVolunteerIsAllowedToBeRosteredAction->execute($location, $user, $shift->users);
            if (is_string($isAllowed)) {
                return ErrorApiResource::create($isAllowed, ErrorApiResource::CODE_BROTHER_REQUIRED, 422);
            }

            $this->validateShiftIsNotFullAction->execute($shift, $date);

            DB::transaction(
                function () use ($date, $location, $shift, $userId, $oldShift) {
                    $oldShift->detachUserOnDate($userId, $date);

                    $this->doShiftReservation->execute($shift, $location, $userId, $date);
                });
        } catch (ShiftAvailabilityException $e) {
            return ErrorApiResource::create($e->getMessage(), $e->getExceptionType(), 422);
        }
        return response()->noContent();
    }
}
