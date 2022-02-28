@component('mail::message')
    # Email Confirmation

    Your code:

    ## {{ $code }}

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
