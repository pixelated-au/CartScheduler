<?php

namespace App\Http\Controllers;

use App\Actions\ErrorApiResource;
use App\Actions\ToggleUserOntoShift;
use App\Enums\ToggleReservationStatus;
use App\Http\Controllers\ValidationRules\ToggleShiftReservationControllerRules;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $userId = $request->integer('user');
        if (!$userId) {
            throw new ValidationException('Missing User ID');
        }
        $user = User::findOrFail($userId);

        $rules = $this->toggleShiftReservationControllerRules->execute($user, $request->all(), true);

        $data = $this->validate($request, $rules);
        $status = $this->toggleUserOntoShift->execute($user, $data);

        return match ($status) {
            ToggleReservationStatus::RESERVATION_MADE => response('Reservation made', 200),
            ToggleReservationStatus::RESERVATION_REMOVED => response('Reservation removed', 200),
            ToggleReservationStatus::NO_AVAILABLE_SHIFTS => ErrorApiResource::create('Shift is at maximum capacity', ErrorApiResource::CODE_NO_AVAILABLE_SHIFTS, 422),
        };
    }
}
