{{--@formatter:off--}}
@component('mail::message')

# {{ config('app.name') }} Upcoming Shift

Dear {{ $name }}, this is a reminder that you have
@if(count($shifts) > 1) upcoming shifts @else an upcoming shift @endif
scheduled with the {{ config('app.name') }} Public Witnessing web application on the **{{ $date }}**:

@foreach($shifts as $shift)
&nbsp;&nbsp; {{ $shift[1] }} at {{ $shift[2] }} <br>
@endforeach

Thank you,<br>
The {{ config('app.name') }} Team
@endcomponent
