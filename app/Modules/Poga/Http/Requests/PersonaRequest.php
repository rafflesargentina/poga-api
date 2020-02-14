<?php

namespace Raffles\Modules\Poga\Http\Requests;

use Raffles\Modules\Poga\Repositories\PersonaRepository;

use Illuminate\Validation\Rule;
use Jenssegers\Date\Date;
use RafflesArgentina\ActionBasedFormRequest\ActionBasedFormRequest as FormRequest;

class PersonaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the store action.
     *
     * @return array
     */
    public static function store()
    {
    $repository = new PersonaRepository;    
        $persona = $repository->find(request()->id);
        $id = $persona ? $persona->id : null;

        return [
            'enum_rol' => 'required',
            'apellido' => 'required_if:enum_tipo_persona,FISICA',
            'ci' => [
                'required_if:enum_tipo_persona,FISICA',
                Rule::unique('personas')->where(function($query) {
                    $query->whereNotNull('ci');    
                    $query->where('enum_estado', 'ACTIVO');
                })->ignore($id),
            ],
            'cuenta_bancaria' => 'required_if:enum_rol,4',
            'direccion_facturacion' => 'required_if:enum_rol,4',
            'enum_tipo_persona' => 'required',
            'fecha_nacimiento' => [
                'nullable',
                'date',
                function($attribute, $value, $fail) {
                    if ($value && ((intval(Date::parse($value)->format('y')) - intval(Date::today()->format('y'))) < 18)) {
                        return $fail('Tienes que tener al menos 18 años para registrarte en POGA.');
                    }
                }
            ],
            'id_banco' => 'required_if:enum_rol,4',
            'id_pais' => 'required',
	    'mail' => ['nullable',Rule::unique('personas')->ignore($id)],
            'nombre' => 'required',
            'razon_social' => 'required_if:enum_rol,4',
            'ruc' => [
                'required_if:enum_tipo_persona,JURIDICA',
                Rule::unique('personas')->where(function($query) {
                    $query->whereNotNull('ruc');
                    $query->where('enum_estado', 'ACTIVO');
                })->ignore($id),
            ],
            'ruc_facturacion' => 'required_if:enum_rol,4',
            'telefono' => 'nullable|phone:PY,AR',
            'telefono_celular' => 'required|phone:PY,AR',
            'titular_cuenta' => 'required_if:enum_rol,4',
        ];
    }

    /**
     * Get the validation rules that apply to the update action.
     *
     * @return array
     */
    public static function update()
    {
        return static::store();
    }
}
