<?php

namespace Raffles\Modules\Poga\Http\Controllers\Cuenta;

use Raffles\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class ActualizarCuentaController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     *
     * @param  Request $request The request object.
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $user = $request->user('api');

        abort_if(!$user, 401);

        $request->validate(
            [
            'email' => ['required',Rule::unique('users')->ignore($user->id)],
            'id_persona.apellido' => 'required_if:id_persona.enum_tipo_persona,FISICA',
            'id_persona.ci' => 'required_if:id_persona.enum_tipo_persona,FISICA',
            'id_persona.cuenta_bancaria' => 'required',
            'id_persona.direccion' => 'required',
            'id_persona.direccion_facturacion' => 'required',
            'id_persona.fecha_nacimiento' => 'required|date',
            'id_persona.id_pais' => 'required',
            'id_persona.nombre' => 'required',
            'id_persona.razon_social' => 'required_if:id_persona.enum_tipo_persona,JURIDICA',
            'id_persona.ruc' => 'required_if:id_persona.enum_tipo_persona,JURIDICA',
            'id_persona.telefono' => 'required',
            'id_persona.titular_cuenta' => 'required',
            ]
        );

        $user->update(array_except($request->all(), ['id_persona']));

        $user->idPersona()->update($request->id_persona);

        return $this->validSuccessJsonResponse('Success', $user);
    }
}
