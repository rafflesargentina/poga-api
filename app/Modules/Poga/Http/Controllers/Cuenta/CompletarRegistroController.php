<?php

namespace Raffles\Modules\Poga\Http\Controllers\Cuenta;

use Raffles\Modules\Poga\Http\Requests\CuentaRequest;
use Raffles\Modules\Poga\Mail\RegistroCompletadoParaAdminPoga;
use Raffles\Modules\Poga\Notifications\RegistroCompletado;

use Illuminate\Support\Facades\Mail;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class CompletarRegistroController extends ActualizarCuentaController
{
    /**
     * Handle the incoming request.
     *
     * @param  CuentaRequest $request The FormRequest object.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(CuentaRequest $request)
    {
        parent::__invoke($request);

        $user = $request->user('api');

        $user->notify(new RegistroCompletado($user));
        Mail::to(env('MAIL_ADMIN_ADDRESS'))->send(new RegistroCompletadoParaAdminPoga($user));

        return $this->validSuccessJsonResponse('Success', $user);
    }
}
