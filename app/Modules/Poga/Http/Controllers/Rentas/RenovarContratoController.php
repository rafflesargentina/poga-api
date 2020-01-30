<?php

namespace Raffles\Modules\Poga\Http\Controllers\Rentas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\RentaRepository;
use Raffles\Modules\Poga\UseCases\RenovarContratoRenta;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\{ FormatsValidJsonResponses, WorksWithFileUploads, WorksWithRelations };

class RenovarContratoController extends Controller
{
    use FormatsValidJsonResponses, WorksWithFileUploads, WorksWithRelations;

    /**
     * The RentaRepository object.
     *
     * @var RentaRepository
     */
    protected $repository;
    
    /**
     * Create a new RenovarContratoController instance.
     *
     * @param RentaRepository $repository The RentaRepository object.
     *
     * @return void
     */
    public function __construct(RentaRepository $repository)
    {
        $this->middleware('auth:api');
    
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request The request object.
     * @param int     $id      The Renta model id.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, $id)
    {
        $model = $this->repository->findOrFail($id);

	if ($model->enum_estado === 'RENOVADO') {
            abort(403, 'El contrato de renta ya se cuentra renovado.');
	}

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

        $request->validate(
            [
            'dias_multa' => 'required_if:multa,1',
            'expensas' => 'boolean',
            'fecha_fin'  => 'required|date',
            'fecha_inicio' => 'required|date',
            'id_inmueble' => 'required|numeric',
            'id_inquilino' => 'required|numeric',
	    'id_moneda' => 'required|numeric',
	    'id_renta_padre' => 'required',
            'monto' => 'numeric',
            'monto_multa_dia' => 'required_if:multa,1|numeric',
	    'multa'=> 'required|boolean',
	    'renovacion' => 'required|in:AUTOMATICA,MANUAL,NO_RENOVAR',
            ]
        );

        $data = $request->all();
	$model = $this->repository->update($model, ['renovacion' => $data['renovacion']])[1];

        $renta = $this->dispatchNow(new RenovarContratoRenta($model, $data));

        return $this->validSuccessJsonResponse('Success', $renta);
    }
}
