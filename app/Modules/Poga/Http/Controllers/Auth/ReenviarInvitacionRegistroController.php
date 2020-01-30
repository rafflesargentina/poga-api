<?php

namespace Raffles\Modules\Poga\Http\Controllers\Auth;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\PersonaRepository;
use Raffles\Modules\Poga\Notifications\InvitacionCreada;

use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class ReenviarInvitacionRegistroController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * Create a new ReenviarInvitacionRegistroController instance.
     *
     * @return void
     */
    public function __construct(PersonaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request)
    {
	$validador = $request->validador;

        $request->validate([
            'validador' => 'required',
	]);

        $persona = $this->repository->where('mail', $validador)->orWhere('ci', $validador)->orWhere('ruc', $validador)->first();

	if (!$persona) {
            $error = new MessageBag([
                'validador' => 'No se encontró un inquilino registrado con estos datos.'
	    ]);
	
	    return $this->validUnprocessableEntityJsonResponse($error, 'Ocurrió un error de validación');
	}

	$user = $persona->user;
	if ($user) {
            if ($user->password) {
                $error = new MessageBag([
                    'validador' => 'El inquilino ya tiene cuenta creada. Debería solicitar reset de contraseña.'
                ]);

                return $this->validUnprocessableEntityJsonResponse($error, 'Ocurrió un error de validación');
	    }
	}

	$persona->user->notify(new InvitacionCreada($persona, null));

	return $this->validSuccessJsonResponse('Success', $persona);
    }
}
