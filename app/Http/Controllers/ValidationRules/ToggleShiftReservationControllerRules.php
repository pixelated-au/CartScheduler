<?php

namespace App\Http\Controllers\ValidationRules;

use App\Actions\GetMaxShiftReservationDateAllowed;
use App\Enums\DBPeriod;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use RuntimeException;

class ToggleShiftReservationControllerRules
{
    public function __construct(private readonly GetMaxShiftReservationDateAllowed $getMaxShiftReservationDateAllowed,
    )
    {
    }

    public function execute(User $user, array $data)
    {
        return [
            'location' => ['required', 'integer', 'exists:locations,id'],
            'shift' => [
                'required',
                'integer',
                'exists:shifts,id',
                function ($attribute, $value, $fail) use ($user, $data) {
                    // only validate if adding a shift
                    if (!$data['do_reserve']) {
                        return;
                    }
                    $shiftDate = Carbon::parse($data['date']);
                    $this->isOverlappingShift($user, $shiftDate, $data['shift'], $fail);
                    $this->isUserAllowedToReserveShifts($shiftDate, $data['shift'], $fail);
                    $this->isUserActive($data, $fail);
                },
            ],
            'do_reserve' => ['required', 'boolean'],
            'date' => [
                'required',
                'date',
                'after_or_equal:today',
                //'before_or_equal:last day of next month zulu',
                function ($attribute, $value, $fail) use ($data) {
                    // only validate if adding a shift
                    if (!$data['do_reserve']) {
                        return;
                    }
                    $date = Carbon::createFromFormat('Y-m-d', $value);
                    /** @noinspection PhpParamsInspection - will always parse because of previous validation */
                    $this->isShiftInAllowedPeriod($date, $fail);
                },
            ],
        ];
    }

    private function isOverlappingShift(User $user, Carbon $shiftDate, int $shift, $fail): void
    {
        $userShiftsOnDate = ShiftUser::with(['shift', 'shift.location'])
            ->where('user_id', $user->id)
            ->where('shift_date', $shiftDate->toDateString())
            ->get();

        $requestedShift = Shift::find($shift);
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
            $end = Carbon::parse($foundOverlappingShift->shift->end_time)->format('h:i a');
            $fail(
                "Sorry, you already have another shift that overlaps this shift at {$foundOverlappingShift->shift->location->name} between $start and $end.",
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
            // not enough volunteers to require a brother
            return;
        }

        // check if user is a sister. If so, fail
        $hasSisters = $shifts->contains(fn(ShiftUser $shiftUser) => $shiftUser->user->gender === 'female');
        if ($hasSisters) {
            $fail('Sorry, you cannot reserve this shift because the last shift requires a brother');
        }
    }

    private function isShiftInAllowedPeriod(Carbon $shiftDate, $fail): void
    {
        $dbPeriod = DBPeriod::getConfigPeriod();
        $doReleaseShiftsDaily = config('cart-scheduler.do_release_shifts_daily');
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

    private function isUserActive(array $data, $fail): void
    {
        if (!$data['do_reserve'] || !isset($data['user_id'])) {
            return;
        }
        $user = User::find($data['user_id']);
        if (!$user->is_enabled) {
            $fail("Sorry, $user->name has been disabled. You cannot reserve a shift for them.");
        }
    }
}
