<?php

namespace App\Http\Controllers;

use App\Actions\DoShiftReservation;
use App\Actions\ErrorApiResource;
use App\Actions\GetMaxShiftReservationDateAllowed;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ToggleShiftReservationController extends Controller
{
    public function __construct(
        private readonly DoShiftReservation $doShiftReservation,
        private readonly GetMaxShiftReservationDateAllowed $getMaxShiftReservationDateAllowed,
    ) {
    }

    public function __invoke(Request $request)
    {
        return Cache::lock('shift_reservation', 10)->block(10, function () use ($request) {
            $data      = $this->getValidated($request);
            $shiftDate = Carbon::parse($data['date']);

            $location = Location::with([
                'shifts'       => fn(HasMany $query) => $query->where('shifts.id', '=', $data['shift']),
                'shifts.users' => fn(BelongsToMany $query) => $query->wherePivot(
                    'shift_date',
                    $shiftDate->toDateString(),
                ),
            ])->findOrFail($data['location']);

            /** @var \App\Models\Shift $shift */
            $shift = $location->shifts->first();

            if ($data['do_reserve']) {
                $didReserve = $this->doShiftReservation->execute($shift, $location, $request->user()->id, $shiftDate);

                return $didReserve
                    ? response('Reservation made', 200)
                    : ErrorApiResource::create('No available shifts', ErrorApiResource::CODE_NO_AVAILABLE_SHIFTS, 422);
            }

            $shift->users()->wherePivot('shift_date', '=', $shiftDate->format('Y-m-d'))->detach($request->user()->id);

            return response('Reservation removed', 200);
        });
    }

    protected function getValidated(Request $request): array
    {
        return $this->validate($request, [
            'location'   => ['required', 'integer', 'exists:locations,id'],
            'shift'      => [
                'required',
                'integer',
                'exists:shifts,id',
                function ($attribute, $value, $fail) use ($request) {
                    $data = $request->all();
                    // only validate if adding a shift
                    if (!$data['do_reserve']) {
                        return;
                    }
                    $shiftDate = Carbon::parse($data['date']);
                    $this->isOverlappingShift($request, $shiftDate, $data['shift'], $fail);
                },
            ],
            'do_reserve' => ['required', 'boolean'],
            'date'       => [
                'required',
                'date',
                'after_or_equal:today',
                //'before_or_equal:last day of next month zulu',
                function ($attribute, $value, $fail) use ($request) {
                    $data = $request->all();
                    // only validate if adding a shift
                    if (!$data['do_reserve']) {
                        return;
                    }
                    $this->isShiftInAllowedPeriod(
                        Carbon::parse($value, config('app.timezone'))->setTime(12, 0),
                        $fail,
                    );
                },
            ],
        ]);
    }

    private function isOverlappingShift(Request $request, Carbon $shiftDate, $shift, $fail): void
    {
        $userShiftsOnDate = ShiftUser::with(['shift', 'shift.location'])
                                     ->where('user_id', $request->user()->id)
                                     ->where('shift_date', $shiftDate->toDateString())
                                     ->get();

        $requestedShift       = Shift::find($shift);
        $requestedShiftPeriod = CarbonPeriod::create(
            $requestedShift->start_time,
            $requestedShift->end_time,
        );

        $foundOverlappingShift = $userShiftsOnDate->first(
            fn(ShiftUser $currentShift) => $requestedShiftPeriod->overlaps(
                CarbonPeriod::create($currentShift->shift->start_time, $currentShift->shift->end_time),
            ),
        );

        if ($foundOverlappingShift) {
            $start = Carbon::parse($foundOverlappingShift->shift->start_time)->format('h:i a');
            $end   = Carbon::parse($foundOverlappingShift->shift->end_time)->format('h:i a');
            $fail("Sorry, you already have another shift that overlaps this shift at {$foundOverlappingShift->shift->location->name} between $start and $end.",
            );
        }
    }

    /**
     * @throws \Exception
     */
    private function isShiftInAllowedPeriod(Carbon $shiftDate, $fail): void
    {
        $maxShiftReservationDateAllowed = $this->getMaxShiftReservationDateAllowed->execute();

        if ($shiftDate->isAfter($maxShiftReservationDateAllowed)) {
            $fail($this->getMaxShiftReservationDateAllowed->getFailMessage());
        }
    }
}
