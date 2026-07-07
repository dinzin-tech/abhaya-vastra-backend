@component('mail::message')
{!! nl2br(e($body)) !!}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
