<?php

namespace Raffles\Modules\Poga\Http\Controllers\Finanzas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\CrearPagare;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class PagareController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * The PagareRepository object.
     *
     * @var PagareRepository
     */
    protected $repository;

    /**
     * Create a new PagareController instance.
     *
     * @param PagareRepository $repository The PagareRepository object.
     *
     * @return void
     */
    public function __construct(PagareRepository $repository)
    {
        $this->middleware('auth:api')->except('show');

        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
	switch ($request->tipoListado) {	
            case 'MisPagos':
	        $items = $this->repository->misPagos($request);
	    break;
	    default:
	        $items = $this->repository->filter()->sort()->get();
	}

        return $this->validSuccessJsonResponse('Success', $items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate(
            [
            //'enum_estado' => 'required',
	    'fecha_pagare' => 'required|date',
	    'fecha_vencimiento' => 'required|date|after:fecha_pagare',
	    'id_moneda' => 'required',
            'id_persona_deudora' => 'required',
            'id_persona_acreedora' => 'required',
            'monto' => 'required',
            'enum_clasificacion_pagare' => 'required',
            'enum_origen_fondos' => 'required_if:enum_estado,PAGADO',
            'descripcion' => 'required',
            'id_inmueble' => 'required'
            ]
        );

        $data = $request->all();
        $retorno = $this->dispatchNow(new CrearPagare($data));

        return $this->validSuccessJsonResponse('Success', $retorno);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $model = $this->repository->findOrFail($id);

        return $this->validSuccessJsonResponse('Success', $model);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        //
    }
}
