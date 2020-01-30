@component('mail::message')
# El usuario completó su registro:

@component('mail::table')
| Atributo                 | Valor         |
| -------------------------|---------------|
| id                       | {{ $user->id }} |
| Email                    | {{ $user->email }} |
| Nombre                   | {{ $user->idPersona->nombre }} |
| Apellido                 | {{ $user->idPersona->apellido }} |
| Tipo de persona          | {{ $user->idPersona->enum_tipo_persona }} |
| Teléfono                 | {{ $user->idPersona->telefono }} |
| Teléfono celular         | {{ $user->idPersona->telefono_ceular }} |
| Dirección                | {{ $user->idPersona->direccion }} |
| Dirección de facturación | {{ $user->idPersona->direccion_facturacion }} |
| Fecha de nacimiento      | {{ $user->idPersona->fecha_nacimiento }} |
| Cédula de identidad      | {{ $user->idPersona->ci }} |
| RUC                      | {{ $user->idPersona->ruc }} |
| RUC de facturación       | {{ $user->idPersona->ruc_facturacion }} |
| Razón social             | {{ $user->idPersona->razon_social }} |
| Titular cuenta           | {{ $user->idPersona->titular_cuenta }} |
| Cuenta bancaria          | {{ $user->idPersona->cuenta_bancaria }} |
| Banco                    | {{ isset($bancos[$user->idPersona->id_banco]) ? $bancos[$user->idPersona->id_banco] : 'Desconocido' }} |
| País                     | {{ isset($paises[$user->idPersona->id_pais]) ? $paises[$user->idPersona->id_pais] : 'Desconocido' }} |
| Rol                      | {{ isset($roles[$user->role_id]) ? $roles[$user->role_id] : 'Desconocido' }} |
@endcomponent

Gracias,<br>
El equipo de técnico {{ config('app.name') }}
@endcomponent
