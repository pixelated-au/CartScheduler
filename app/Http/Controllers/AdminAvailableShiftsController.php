<?php

namespace App\Http\Controllers;

use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class AdminAvailableShiftsController extends Controller
{

    public function __invoke(Request $request, string $shiftDate)
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        try {
            $selectedDate = Carbon::parse($shiftDate);
        } catch (InvalidFormatException $e) {
            Bugsnag::notifyException($e);
            $selectedDate = Carbon::today();
        }

        // Locations are the list of locations in the 'accordion' menu
        $locations = Location::with([
            'shifts'       => fn(HasMany $query) => $query
                ->where('shifts.is_enabled', true)
                ->orderBy('shifts.start_time'),
            'shifts.users' => fn(BelongsToMany $query) => $query
                ->where('shift_user.shift_date', '=', $selectedDate)
        ])
            ->where('is_enabled', true)
            ->get();

        return [
            'locations' => LocationResource::collection($locations),
        ];
    }
}
