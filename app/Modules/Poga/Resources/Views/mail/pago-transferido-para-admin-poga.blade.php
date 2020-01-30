@component('mail::message')
# Hola {{ $user->idPersona->nombre }}

@php $inmueblePadre = $pagare->idUnidad ? $pagare->idUnidad->idInmueblePadre : $pagare->idInmueble->idInmueblePadre; @endphp
@php $direccion = $inmueblePadre->idDireccion; @endphp

Los pagos confirmados por el contrato del inmueble "{{ $pagare->idUnidad ? $pagare->idUnidad->tipo.' piso '.$pagare->idUnidad->piso.' nº '.$pagare->idUnidad->numero.', del edificio "'.$inmueblePadre->nombre.'"' : $inmueblePadre->nombre }}, ubicado en {{ $direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->calle_secundaria : '')) }} de {{ \Carbon\Carbon::parse($pagare->fecha_pagare)->format('m/Y') }} fueron transferidos con el descuento del servicio Poga.

@component('mail::table')
| Concepto               | Valor         |
| -----------------------|---------------|
| Propietario            | {{ $pagare->idPersonaAcreedora->nombre_y_apellidos }} |
| Inquilino              | {{ $pagare->idPersonaDeudora->nombre_y_apellidos }} |
| {{ str_replace('_', ' ', ucwords(strtolower($pagare->idPagarePadre->enum_clasificacion_pagare))) }} | {{ number_format($pagare->idPagarePadre->monto,0,',','.') }} {{ $pagare->idMoneda->abbr }} |
| Nº Operación Bancaria  | {{ $pagare->idPagarePadre->nro_comprobante }} |
| Servicio Poga          | {{ number_format(($pagare->monto * -1),0,',','.') }} {{ $pagare->idMoneda->abbr }} |
| Transferido neto       | {{ number_format($pagare->idPagarePadre->monto - $pagare->monto,0,',','.') }} {{ $pagare->idMoneda->abbr }} |
@endcomponent

La acreditación a su cuenta bancaria podría llevar de 48 a 72 horas hábiles de acuerdo proceso bancario.

@component('mail::button', ['url' => str_replace('api.', 'app.', url('/cuenta/mis-pagos'))])
Ir a "Mis Pagos"
@endcomponent

Gracias,<br>
El equipo de {{ config('app.name') }}
@endcomponent
