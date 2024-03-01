<?php

namespace Database\Factories\Sequences;

use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * Extend the Sequence class, to set the start and end times for the shifts
 */
class ShiftTimeSequence extends Sequence
{
    public function __construct()
    {
        parent::__construct(
            ['start_time' => '09:00:00', 'end_time' => '12:00:00'],
            ['start_time' => '12:00:00', 'end_time' => '15:00:00'],
            ['start_time' => '15:00:00', 'end_time' => '18:00:00'],
        );
    }
}
