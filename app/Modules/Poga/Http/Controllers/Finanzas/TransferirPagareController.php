<?php

namespace Raffles\Modules\Poga\Http\Controllers\Finanzas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Illuminate\Http\Request;
use Raffles\Modules\Poga\UseCases\TransferirPagare;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class TransferirPagareController extends Controller
{
    use FormatsValidJsonResponses;

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
     * @param int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */    

    public function __invoke(Request $request, $id)
    {
	$request->validate([
            'descripcion' => 'nullable',
	    'nro_comprobante' => 'required',
	]);

	$model = $this->repository->findOrFail($id);

        $pagare = $this->dispatch(new TransferirPagare($model, $request->all()));

        return $this->validSuccessJsonResponse('Success', $pagare);
    }
}
