<?php

namespace Raffles\Modules\Poga\Http\Controllers\Cuenta;

use Raffles\Http\Controllers\Controller;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class ActualizarTelefonoCelularController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * Handle the incoming request.
     *
     * @param  Request $request The Request object.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
	$request->validate([
            'id_persona.telefono_celular' => 'required|numeric',
	]);

	$user = $request->user('api');
	$user->idPersona()->update(['telefono_celular' => $request->id_persona['telefono_celular']]);

        return $this->validSuccessJsonResponse('Success', $user->refresh());
    }
}
