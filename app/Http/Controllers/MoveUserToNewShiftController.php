<?php

namespace App\Http\Controllers;

use App\Actions\DoShiftReservation;
use App\Models\Location;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MoveUserToNewShiftController extends Controller
{
    public function __invoke(Request $request, DoShiftReservation $doShiftReservation)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'location_id'  => 'required|exists:locations,id',
            'old_shift_id' => 'required|exists:shifts,id',
            'date'         => 'required|date',
        ]);

        $oldShift  = Shift::find($request->get('old_shift_id'));
        $date      = Carbon::parse($request->get('date'));
        $startTime = $oldShift->start_time;

        $location = Location::with([
            'shifts'       => fn(HasMany $query) => $query->where('shifts.start_time', '=', $oldShift->start_time),
            'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot(
                'shift_date', $date->toDateString()
            )
        ])
                            ->where('id', $request->get('location_id'))
                            ->where('is_enabled', true)
                            ->first();

        if (!$location) {
            return response()->json([
                'message' => 'Location not found',
            ], 422);
        }

        $shift = $location->shifts->firstWhere('start_time', $startTime);
        if (!$shift) {
            return response()->json([
                'message' => 'No shift found for this location at this time',
            ], 422);
        }

        if ($shift->available_from || $shift->available_to) {
            if ($shift->available_from && $date->isBefore($shift->available_from)) {
                return response()->json([
                    'message' => 'Shift is not available yet',
                ], 422);
            }
            if ($shift->available_to && $date->isAfter($shift->available_to)) {
                return response()->json([
                    'message' => 'Shift is not available anymore',
                ], 422);
            }
        }

        $oldShift->users()->detach($request->get('user_id'));

        return $doShiftReservation->execute($shift, $location, $request->get('user_id'), $date);
    }
}
