@component('mail::message')
# Hola {{ $user->idPersona->nombre }}

@php $inmueblePadre = $renta->idUnidad ? $renta->idUnidad->idInmueblePadre : $renta->idInmueble->idInmueblePadre; @endphp
@php $direccion = $inmueblePadre->idDireccion; @endphp

Se creó un contrato de renta para tu inmueble "{{ $renta->idUnidad ? $renta->idUnidad->tipo.' piso '.$renta->idUnidad->piso.' nº '.$renta->idUnidad->numero.', del edificio "'.$inmueblePadre->nombre.'"' : $inmueblePadre->nombre }}, ubicado en {{ $direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->calle_secundaria : '')) }}.

Los detalles del contrato son los siguientes:

@component('mail::table')
| Cláusula               | Valor         |
| -----------------------|---------------|
| Fecha de inicio        | {{ $renta->fecha_inicio->format('d/m/Y') }} |
| Fecha de finalización  | {{ $renta->fecha_fin->format('d/m/Y') }} |
| Monto mensual de renta | {{ number_format($renta->monto,0,',','.') }} {{ $renta->idMoneda->abbr }} |
| Inquilino              | {{ $renta->idInquilino->nombre.' '.$renta->idInquilino->apellido.' ('.($renta->idInquilino->enum_tipo_persona === 'FISICA' ? 'CI: '.$renta->idInquilino->ci : 'RUC :'.$renta->idInquilino->ruc).')' }} |
@endcomponent

@if(!$renta->vigente)
Se generaron los siguientes pagos pendientes para el inquilino:

@component('mail::table')
| Concepto               | Valor         |
| -----------------------|---------------|
@foreach ($boleta['debt']['description']['items'] as $item)
| {{ $item['label'] }} | {{ number_format($item['amount']['value'],0,',','.') }} {{ $renta->idMoneda->abbr }} |
@endforeach
@endcomponent

Comparta el siguiente link con el inquilino para la realización de los pagos:

<a href="{{ str_replace('api.', 'app.', url('/realiza-un-pago/'.$boleta['debt']['docId'])) }}">Pagar boleta</a>
@endif

@component('mail::button', ['url' => str_replace('api.', 'app.', url('/rentas/'.$renta->id))])
Ver Contrato
@endcomponent

Gracias,<br>
El equipo de {{ config('app.name') }}
@endcomponent
