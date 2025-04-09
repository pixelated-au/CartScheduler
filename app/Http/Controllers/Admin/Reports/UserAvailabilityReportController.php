<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Actions\Admin\Reports\GetUserAvailabilityReportAction;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserAvailabilityReportController extends Controller
{
    public function __invoke(Request $request, GetUserAvailabilityReportAction $action)
    {
        // Validate date parameters if provided
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
        ]);

        // Get start date, defaulting to today if not provided
        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : Carbon::today();

        // Get end date, defaulting to today + 4 weeks if not provided
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])
            : Carbon::today()->addWeeks(4);

        // Execute the action with the date parameters
        $reportData = $action->execute($startDate, $endDate);

        // Return JSON response with data and metadata
        return response()->json([
            'data' => $reportData,
            'meta' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
        ]);
    }
}
