{{--@formatter:off--}}
@component('mail::message')

# {{ config('app.name') }} Upcoming Shift

Dear {{ $name }}, this is a reminder that you have
@if(count($shifts) > 1) upcoming shifts @else an upcoming shift @endif
scheduled **{{ $relativeDate }}** ({{ $date->format('l, F jS') }}):

@foreach($shifts as $shift)
- {{ $shift->location->name }} from {{ $shift->start_time12_hr }} to {{ $shift->end_time12_hr }} <br>
@endforeach

Thank you,<br>
The {{ config('app.name') }} Team
@endcomponent
