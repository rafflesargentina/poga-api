@component('mail::message')
# Hola {{ $user->idPersona->nombre }}

@php $inmueblePadre = $renta->idUnidad ? $renta->idUnidad->idInmueblePadre : $renta->idInmueble->idInmueblePadre; @endphp
@php $direccion = $inmueblePadre->idDireccion; @endphp

Fuiste asociado a un contrato de renta para el inmueble "{{ $renta->idUnidad ? $renta->idUnidad->tipo.' piso '.$renta->idUnidad->piso.' nº '.$renta->idUnidad->numero.', del edificio "'.$inmueblePadre->nombre.'"' : $inmueblePadre->nombre }}, ubicado en {{ $direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->calle_secundaria : '')) }}.

Los detalles del contrato son los siguientes:

@php $propietario = $renta->idInmueble->idPropietarioReferente->idPersona; @endphp

@component('mail::table')
| Cláusula               | Valor         |
| -----------------------|---------------|
| Fecha de inicio        | {{ $renta->fecha_inicio->format('d/m/Y') }} |
| Fecha de finalización  | {{ $renta->fecha_fin->format('d/m/Y') }} |
| Monto mensual de renta | {{ number_format($renta->monto,0,',','.') }} {{ $renta->idMoneda->abbr }} |
| Propietario              | {{ $propietario->nombre.' '.$propietario->apellido.' ('.($propietario->enum_tipo_persona === 'FISICA' ? 'CI: '.$propietario->ci : 'RUC :'.$propietario->ruc).')' }} |
@endcomponent

@if(!$renta->vigente)
Se generaron los siguientes pagos pendientes que debes cancelar:

@if($boleta['debt']['description']['items'])
@component('mail::table')
| Concepto               | Valor         |
| -----------------------|---------------|
@foreach ($boleta['debt']['description']['items'] as $item)
| {{ $item['label'] }} | {{ number_format($item['amount']['value'],0,',','.') }} {{ $renta->idMoneda->abbr }} |
@endforeach
@endcomponent
@endif

En el siguiente link podés ver la información para la cancelación de los pagos pendientes:

<a href="{{ str_replace('api.', 'app.', url('/realiza-un-pago/'.$boleta['debt']['docId'])) }}">Pagar boleta</a>

@endif

@component('mail::button', ['url' => str_replace('api.', 'app.', url('/rentas/'.$renta->id))])
Ver Contrato
@endcomponent

Gracias,<br>
El equipo de {{ config('app.name') }}
@endcomponent
