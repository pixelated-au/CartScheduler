<?php

namespace App\Http\Controllers;

use App\Actions\ErrorApiResource;
use App\Actions\ToggleUserOntoShift;
use App\Enums\ToggleReservationStatus;
use App\Http\Controllers\ValidationRules\ToggleShiftReservationControllerRules;
use Illuminate\Http\Request;

class ToggleUserOntoShiftReservationController extends Controller
{
    public function __construct(
        private readonly ToggleShiftReservationControllerRules $toggleShiftReservationControllerRules,
        private readonly ToggleUserOntoShift                   $toggleUserOntoShift,
    )
    {
    }

    public function __invoke(Request $request)
    {
        $rules = $this->toggleShiftReservationControllerRules->execute($request->user(), $request->all());
        $rules['user_id'] = ['required', 'integer', 'exists:users,id'];

        $data = $this->validate($request, $rules);
        $status = $this->toggleUserOntoShift->execute($request->user(), $data);

        return match ($status) {
            ToggleReservationStatus::RESERVATION_MADE => response('Reservation made', 200),
            ToggleReservationStatus::RESERVATION_REMOVED => response('Reservation removed', 200),
            ToggleReservationStatus::NO_AVAILABLE_SHIFTS => ErrorApiResource::create('No available shifts', ErrorApiResource::CODE_NO_AVAILABLE_SHIFTS, 422),
        };
    }
}
