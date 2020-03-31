<?php

namespace Raffles\Modules\Poga\Http\Controllers\Rentas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\{ InmuebleRepository, RentaRepository };
use Raffles\Modules\Poga\UseCases\{ ActualizarRenta, BorrarRenta, CrearRenta };

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RafflesArgentina\ResourceController\Traits\{ FormatsValidJsonResponses, WorksWithFileUploads, WorksWithRelations };

class RentaController extends Controller
{
    use FormatsValidJsonResponses, WorksWithFileUploads, WorksWithRelations;

    protected $pruneHasMany;

    /**
     * Create a new RentaController instance.
     *
     * @param RentaRepository $repository
     *
     * @return void
     */
    public function __construct(RentaRepository $repository)
    {
        $this->middleware('auth:api');

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
        $this->authorize('view', new $this->repository->model);

        switch ($request->tipoListado) {
        case 'MisRentas':
            $items = $this->repository->misRentas($request);
        default:
	    $items = $this->repository->todos();
	    break;
        }

        return $this->validSuccessJsonResponse('Success', $items);
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
        $model->loadMissing('documentos', 'estados_inmueble', 'idUnidad');

        $this->authorize('view', $model);

        return $this->validSuccessJsonResponse('Success', $model);
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
        //$this->authorize('create', new $this->repository->model);

        $rInmueble = new InmuebleRepository;

        $request->validate(
            [
            'comision_administrador' => 'required|numeric',
            'dia_mes_pago' => 'required|numeric|max:28',
            'dias_multa' => 'required_if:multa,1',                
            'expensas' => 'boolean',
            'fecha_fin'  => 'required|date|after:fecha_inicio',
            'fecha_inicio' => 'required|date',
            'garantia'  => 'required|numeric',
            'id_inmueble' => 'required|numeric',
            'id_inquilino' => [
            'required',
            'numeric',
            Rule::unique('rentas')->where(
                function ($query) use ($request) {
                            $query->where('id_inmueble', $request->id_inmueble);
                        $query->whereIn('rentas.enum_estado', ['ACTIVO','PENDIENTE']);
                }
            )
            ],        
            'id_moneda' => 'required|numeric',
            'monto' => 'required',
            'monto_descontado_garantia_finalizacion_contrato' => 'numeric',
            'monto_multa_dia' => 'required_if:multa,1',
            'multa'=> 'required|boolean',
            'prim_comision_administrador' => 'required|numeric',
            ]
        );

        $data = $request->all();
	$user = $request->user('api');

        $resultado = $this->dispatchNow(new CrearRenta($data, $user));

        return $this->validSuccessJsonResponse('Success', $resultado);
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
        $model = $this->repository->findOrFail($id);
    
        //$this->authorize('update', $model);

        // Handle documents.
        if ($request->documentos) {
            $request->validate(
                [
                'documentos[]' => 'file',
                ]
            );

            $mergedRequest = $this->uploadFiles($request, $model);
            $this->updateOrCreateRelations($mergedRequest, $model);

            return $this->validSuccessJsonResponse('Success');
        }

        //$request->validate(
            //[
            //'comision_administrador' => 'required|numeric',
            //'dias_multa' => 'required_if:multa,1',
            //'monto_multa_dia' => 'required_if:multa,1|numeric',
            //'multa'=> 'required|boolean',
            //'prim_comision_administrador' => 'required|numeric',
            //]
        //);

        $data = $request->all();

        $renta = $this->dispatchNow(new ActualizarRenta($model, $data));

        return $this->validSuccessJsonResponse('Success', $renta);
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
        $model = $this->repository->findOrFail($id);

        $data = $request->all();
        $user = $request->user('api');
        $renta = $this->dispatchNow(new BorrarRenta($model, $user));

        return $this->validSuccessJsonResponse('Success', $renta);
    }
}
