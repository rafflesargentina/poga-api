@component('mail::message')
@php $inmueblePadre = $renta->idUnidad ? $renta->idUnidad->idInmueblePadre : $renta->idInmueble->idInmueblePadre; @endphp
@php $direccion = $inmueblePadre->idDireccion; @endphp
@php $propietario = $renta->idInmueble->idPropietarioReferente->idPersona; @endphp

Se creó un contrato de renta para el inmueble "{{ $renta->idUnidad ? $renta->idUnidad->tipo.' piso '.$renta->idUnidad->piso.' nº '.$renta->idUnidad->numero.', del edificio "'.$inmueblePadre->nombre.'"' : $inmueblePadre->nombre }}, ubicado en {{ $direccion->calle_principal.' '.($direccion->numeracion ? $direccion->numeracion : ($direccion->calle_secundaria ? 'c/ '.$direccion->calle_secundaria : '')) }}.

Los detalles del contrato son los siguientes:

@component('mail::table')
| Cláusula               | Valor         |
| -----------------------|---------------|
| Fecha de inicio        | {{ $renta->fecha_inicio->format('d/m/Y') }} |
| Fecha de finalización  | {{ $renta->fecha_fin->format('d/m/Y') }} |
| Monto mensual de renta | {{ number_format($renta->monto,0,',','.') }} {{ $renta->idMoneda->abbr }} |
| Inquilino              | {{ $renta->idInquilino->nombre.' '.$renta->idInquilino->apellido.' ('.($renta->idInquilino->enum_tipo_persona === 'FISICA' ? 'CI: '.$renta->idInquilino->ci : 'RUC :'.$renta->idInquilino->ruc).')' }} |
| Propietario            | {{ $propietario->nombre.' '.$propietario->apellido.' ('.($propietario->enum_tipo_persona === 'FISICA' ? 'CI: '.$propietario->ci : 'RUC :'.$propietario->ruc).')' }} |
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
@endif

@component('mail::button', ['url' => str_replace('api.', 'app.', url('/rentas/'.$renta->id))])
Ver Contrato
@endcomponent

Gracias,<br>
El equipo de {{ config('app.name') }}
@endcomponent
