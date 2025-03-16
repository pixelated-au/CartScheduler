{{--@formatter:off--}}
@component('mail::message')

# {{ config('app.name') }} Upcoming Shift

Dear {{ $name }}, this is a reminder that you have
@if(count($shifts) > 1) upcoming shifts @else an upcoming shift @endif
scheduled for **{{ $relativeDate }}** ({{ $date->format('l, F jS') }}).
In {{ num_overseer_shifts }} of these, you are shift overseer:

@foreach($shifts as $shift)
- {{ $shift->location->name }} from {{ $shift->start_time12_hr }} to {{ $shift->end_time12_hr }} @if($shift->overseer) **(Overseer)** @endif <br>
@endforeach

Thank you,<br>
The {{ config('app.name') }} Team
@endcomponent
