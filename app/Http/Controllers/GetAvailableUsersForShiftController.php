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
            'date'                        => ['required', 'date'],
            'showAll'                     => ['nullable', 'boolean'],
            'showOnlyResponsibleBros'     => ['nullable', 'boolean'],
            'hidePublishers'              => ['nullable', 'boolean'],
            'showOnlyElders'              => ['nullable', 'boolean'],
            'showOnlyMinisterialServants' => ['nullable', 'boolean'],
        ]);

        // TODO create tests for this controller. Include tests for the following:
        // ...amongst other things, test that if a volunteer has been allocated to another shift that has been disabled, they are still returned as available for this shift


        return ExtendedUserResource::collection(
            $this->getUsersForShift->execute(
                shift: $shift,
                date: $request->date('date'),
                showUnavailable: $request->boolean('showAll'),
                showOnlyResponsibleBros: $request->boolean('showOnlyResponsibleBros'),
                hidePublishers: $request->boolean('hidePublishers'),
                showOnlyElders: $request->boolean('showOnlyElders'),
                showOnlyMninsterialServants: $request->boolean('showOnlyMinisterialServants'),
            ),
        );
    }
}
