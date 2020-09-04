<?php

namespace Raffles\Modules\Poga\Http\Controllers\Finanzas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\Pagos\RegistrarPagoManual;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class RegistrarPagoManualController extends Controller
{
    use FormatsValidJsonResponses;

    protected $repository;

    /**
     * Create a new CrearPagoController instance.
     *
     * @return void
     */
    public function __construct(PagareRepository $repository)
    {
        $this->middleware('auth:api');

        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request The request object.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $request->validate(
            [
                'enum_opcion_pago' => 'required|in:TOTAL,MANUAL',
                'id_pagare' => 'required',
                'monto' => 'required_if:enum_opcion_pago,MANUAL',
                'pagado_fuera_sistema' => 'required|boolean'
            ]
        );

        $pagares = $this->repository->misPagos($request);

        $pagare = $pagares->where('id', $request->id_pagare)->first();

        if ($pagare) {
            $uc = new RegistrarPagoManual($pagare, $request->monto, $request->pagado_fuera_sistema, $request->descripcion);
            $boleta = $uc->handle();
        }

        return $this->validSuccessJsonResponse('Success', ['boleta' => $boleta, 'pagare' => $pagare]);
    }
}
