<?php

namespace Raffles\Modules\Poga\Http\Controllers\Finanzas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class BoletasRentaController extends Controller
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
        $data = $this->repository->boletasRenta($request);

        return $this->validSuccessJsonResponse('Success', $data);
    }
}
