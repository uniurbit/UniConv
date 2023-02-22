@component('mail::message')  

{{-- contenuto email --}}
@if (!empty($description))
{{ $description }}
@endif

Si richede di portare la convezione "{{ $conv->descrizione_titolo }}" 
in approvazione agli organi di dipartimento o di Ateneo

@component('mail::button', ['url' => $urlInfo, 'color' => 'blue'])
  Informazioni convenzione
@endcomponent

e di inserire il numero e la data del documento di approvazione collegandosi
a [UniConv]({{$urlChiusura}})

@component('mail::button', ['url' => $urlChiusura, 'color' => 'blue'])
  Gestisci approvazione
@endcomponent

<br>
Cordiali saluti, <br>
Il team di UniConv
@endcomponent
