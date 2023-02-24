<?php

namespace App\Http\Controllers;

use App\Actions\DoShiftReservation;
use App\Actions\ErrorApiResource;
use App\Actions\GetMaxShiftReservationDateAllowed;
use App\Enums\DBPeriod;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

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
            $data      = $this->doValidate($request);
            $shiftDate = Carbon::createFromFormat('Y-m-d', $data['date'])->setTime(12, 0);

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
            activity()
                ->performedOn($shift)
                ->causedBy($request->user())
                ->withProperties([
                    'user_id'             => $request->user()->id,
                    'shift_date'          => $shiftDate,
                    'shift.location.name' => $location->name,
                ])
                ->log('shift_unreserved');

            return response('Reservation removed', 200);
        });
    }

    protected function doValidate(Request $request): array
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
                    if ($request->user()->gender === 'female') {
                        $this->isUserAllowedToReserveShifts($shiftDate, $data['shift'], $fail);
                    }
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
                    $date = Carbon::createFromFormat('Y-m-d', $value);
                    /** @noinspection PhpParamsInspection - will always parse because of previous validation */
                    $this->isShiftInAllowedPeriod($date, $fail);
                },
            ],
        ]);
    }

    private function isOverlappingShift(Request $request, Carbon $shiftDate, int $shift, $fail): void
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

    private function isUserAllowedToReserveShifts(Carbon $shiftDate, int $shiftId, $fail): void
    {
        $location = Location::whereRelation('shifts', 'id', $shiftId)->first();
        if (!$location) {
            throw new RuntimeException('Location not found for shift');
        }
        if (!$location->requires_brother) {
            return;
        }

        $shifts = ShiftUser::with(['user' => fn(BelongsTo $query) => $query->select(['id', 'gender'])])
                           ->where('shift_id', $shiftId)
                           ->where('shift_date', $shiftDate->toDateString())
                           ->get();

        if ($shifts->count() < $location->max_volunteers - 1) {
            // not enough volunteers to require a brother at this stage
            return;
        }

        // check if user is a sister. If so, fail
        $isOnlySisters = $shifts->doesntContain(fn(ShiftUser $shiftUser) => $shiftUser->user->gender === 'male');
        if ($isOnlySisters) {
            $fail('Sorry, you cannot reserve this shift because the last shift requires a brother');
        }
    }

    /**
     * @throws \Exception
     */
    private function isShiftInAllowedPeriod(Carbon $shiftDate, $fail): void
    {
        $dbPeriod                       = DBPeriod::getConfigPeriod();
        $doReleaseShiftsDaily           = config('cart-scheduler.do_release_shifts_daily');
        $maxShiftReservationDateAllowed = $this->getMaxShiftReservationDateAllowed->execute();

        if (
            $dbPeriod->value === DBPeriod::Week->value
            && !$doReleaseShiftsDaily
            && $shiftDate->isSameDay($maxShiftReservationDateAllowed)
            && Carbon::now()->format('Gis.u') > $maxShiftReservationDateAllowed
                ->addDay()
                ->format('Gis.u')
        ) {
            $fail($this->getMaxShiftReservationDateAllowed->getFailMessage());

            return;
        }

        if ($shiftDate->isAfter($maxShiftReservationDateAllowed)) {
            $fail($this->getMaxShiftReservationDateAllowed->getFailMessage());
        }
    }
}
