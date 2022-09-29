<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateShiftReportRequest;
use App\Models\Report;
use App\Models\ShiftUser;

class SaveShiftReportController extends Controller
{
    public function __invoke(CreateShiftReportRequest $request)
    {
        $shiftUser = ShiftUser::with('shift.location')
                              ->where('shift_id', $request->get('shift_id'))
                              ->where('shift_date', $request->get('shift_date'))
                              ->first();
        if (!$shiftUser || $shiftUser->shift->start_time !== $request->get('start_time')) {
            return response()->json(
                ['message' => 'The supplied data does not match a shift. Please contact the administrator and cite this message'],
                422
            );
        }

        if ($shiftUser->shift->location->requires_brother && $request->user()->gender === 'female') {
            return response()->json(['message' => 'A brother is required to report on this shift.'], 422);
        }

        $cancelled = $request->get('shift_was_cancelled', false);

        $report                           = new Report();
        $report->report_submitted_user_id = $request->user()->id;
        $report->shift_id                 = $request->get('shift_id');
        $report->shift_date               = $request->get('shift_date');
        $report->shift_was_cancelled      = $cancelled;
        $report->placements_count         = $cancelled ? 0 : $request->get('placements_count', 0);
        $report->videos_count             = $cancelled ? 0 : $request->get('videos_count', 0);
        $report->requests_count           = $cancelled ? 0 : $request->get('requests_count', 0);
        $report->comments                 = $request->get('comments');
        $report->save();

        $report->tags()->sync($request->get('tags', []));

        ray($report->tags);

        return response()->json(['message' => 'Report saved successfully']);
    }
}
