<?php

namespace App\Http\Controllers;

use App\Actions\GetAvailableUsersForShift;
use App\Http\Resources\ExtendedUserResource;
use App\Models\Shift;
use Illuminate\Http\Request;

class GetAvailableUsersForShiftController extends Controller
{

    public function __construct(private readonly GetAvailableUsersForShift $getUsersForShift)
    {
    }

    public function __invoke(Request $request, Shift $shift)
    {
        $request->validate([
            'date'    => ['required', 'date'],
            'showAll' => ['nullable', 'boolean'],
        ]);

        return ExtendedUserResource::collection(
            $this->getUsersForShift->execute($shift, $request->date('date'), $request->boolean('showAll', false)),
        );
    }
}
