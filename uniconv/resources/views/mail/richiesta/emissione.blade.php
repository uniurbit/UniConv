@component('mail::message')  

{{-- contenuto email --}}
@if (!empty($description))
{!! trim(nl2br(e($description))) !!} 
@endif

@component('mail::button', ['url' => $urlInfo, 'color' => 'blue'])
Informazioni convenzione
@endcomponent
<br>
@component('mail::button', ['url' => $urlChiusura, 'color' => 'blue'])
Gestisci la richiesta di emissione
@endcomponent

Cordiali saluti, <br> Il team di UniConv
@endcomponent