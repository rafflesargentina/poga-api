@component('mail::message')
# Hola {{ $user->idPersona->nombre }}

@php $inmueblePadre = $pagare->idUnidad ? $pagare->idUnidad->idInmueblePadre : $pagare->idInmueble->idInmueblePadre; @endphp
@php $direccion = $inmueblePadre->idDireccion; @endphp

Los pagos pendientes por el inmueble "{{ $pagare->idUnidad ? $pagare->idUnidad->tipo.' piso '.$pagare->idUnidad->piso.' nº '.$pagare->idUnidad->numero.', del edificio "'.$inmueblePadre->nombre.'"' : $inmueblePadre->nombre }}, ubicado en {{ $direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->calle_secundaria : '')) }} de {{ \Carbon\Carbon::parse($pagare->fecha_pagare)->format('m/Y') }} fueron cancelados.

Inquilino: {{ $pagare->idPersonaDeudora->nombre_y_apellidos }}

@component('mail::table')
| Concepto               | Valor         |
| -----------------------|---------------|
@if ($boleta['description']['items'])
@foreach ($boleta['description']['items'] as $item)
| {{ $item['label'] }} | {{ number_format($item['amount']['value'],0,',','.') }} {{ $pagare->idMoneda->abbr }} |
@endforeach
@else
| {{ $boleta['label'] }} | {{ number_format($boleta['amount']['value'],0,',','.') }} {{ $pagare->idMoneda->abbr }} |
@endif
@endcomponent

@component('mail::button', ['url' => str_replace('api.', 'app.', url('/cuenta/mis-pagos'))])
Ir a "Mis Pagos"
@endcomponent

Gracias,<br>
El equipo de {{ config('app.name') }}
@endcomponent
