{{--@formatter:off--}}
@component('mail::message')

# {{ config('app.name') }} Upcoming Shift

Dear @if($gender == "male") Brother @else Sister @endif {{ $name }}, this is a reminder that you have an upcoming shift scheduled with the {{ config('app.name') }} Public Witnessing web application on this date: **{{ $date }}**.

Thank you,<br>
The {{ config('app.name') }} Team
@endcomponent
