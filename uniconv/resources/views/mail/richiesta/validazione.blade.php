@component('mail::message')  

{{-- contenuto email --}}
@if (!empty($description))
{{ $description }}
@endif

@component('mail::button', ['url' => $urlInfo, 'color' => 'blue'])
  Informazioni convenzione
@endcomponent

@component('mail::button', ['url' => $urlChiusura, 'color' => 'blue'])
  Gestisci la richiesta di approvazione
@endcomponent

Cordiali saluti,<br>
Il team di UniConv
@endcomponent
