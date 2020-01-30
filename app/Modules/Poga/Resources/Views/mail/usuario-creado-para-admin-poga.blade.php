@component('mail::message')
# Nuevo usuario registrado:

@php $roles = ['1' => 'Administrador', '2' => 'Conserje', '3' => 'Inquilino', '4' => 'Propietario', '5' => 'Proveedor']; @endphp

@component('mail::table')
| Atributo               | Valor         |
| -----------------------|---------------|
| id                     | {{ $user->id }} |
| Email                  | {{ $user->email }} |
| Nombre                 | {{ $user->idPersona->nombre }} |
| Apellido               | {{ $user->idPersona->apellido }} |
| Fecha de nacimiento    | {{ $user->idPersona->fecha_nacimiento }} |
| Rol                    | {{ isset($roles[$user->role_id]) ? $roles[$user->role_id] : 'Desconocido' }}
@endcomponent

Gracias,<br>

El equipo de técnico {{ config('app.name') }}
@endcomponent
