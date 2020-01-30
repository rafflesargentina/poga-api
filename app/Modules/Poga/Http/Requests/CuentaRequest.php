<?php

namespace Raffles\Modules\Poga\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Jenssegers\Date\Date;

class CuentaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = request()->user('api');

        return [
            'email' => ['required',Rule::unique('users')->ignore($user->id)],
            'id_persona.apellido' => 'required_if:id_persona.enum_tipo_persona,FISICA',
            'id_persona.ci' => [
                'required_if:id_persona.enum_tipo_persona,FISICA',
                Rule::unique('personas', 'ci')->where(function($query) {
                    $query->whereNotNull('ci');
                    $query->where('enum_estado', 'ACTIVO');
                })->ignore($user->id),
            ],
            'id_persona.cuenta_bancaria' => 'required_if:role_id,4',
            'id_persona.direccion' => 'required',
            'id_persona.direccion_facturacion' => 'required_if:role_id,4',
            'id_persona.fecha_nacimiento' => [
                'nullable',
                'required_if:id_persona.enum_tipo_persona,FISICA',
                'date',
                function($attribute, $value, $fail) {
                    if ($value && ((intval(Date::parse($value)->format('y')) - intval(Date::today()->format('y'))) < 18)) {
                        return $fail('Tienes que tener al menos 18 años para registrarte en POGA.');
                    }
                }
            ],
            'id_persona.id_banco' => 'required_if:role_id,4',
            'id_persona.id_pais' => 'required',
            'id_persona.nombre' => 'required',
            'id_persona.razon_social' => 'required_if:id_persona.enum_tipo_persona,JURIDICA',
            'id_persona.ruc' => [
                'required_if:id_persona.enum_tipo_persona,JURIDICA',
                Rule::unique('personas', 'ruc')->where(function($query) {
		    $query->whereNotNull('ruc');	
		    $query->where('enum_estado', 'ACTIVO');
                })->ignore($user->id),
            ],    
            'id_persona.telefono_celular' => 'required',
            'id_persona.titular_cuenta' => 'required_if:role_id,4',
        ];
    }
}
