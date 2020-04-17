<?php

namespace Raffles\Modules\Poga\Http\Controllers\Finanzas;

use Raffles\Modules\Poga\Http\Controllers\Controller;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\{ ActualizarBoletaPago, TraerBoletaPago };

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class ActualizarMontoPagoController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * The PagareRepository object.
     *
     * @var PagareRepository
     */
    protected $repository;

    /**
     * Create a new ActivarLeyEmergenciaController instance.
     *
     * @param  PagareRepository $repository
     *
     * @return void
     */
    public function __construct(PagareRepository $repository)
    {
        //$this->middleware('auth:api');
    
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request The request object.
     * @param int     $id      The Pagare id.
     *
     * @return \Illuminate\Http\JsonResponse
     */    
    public function __invoke(Request $request, $id)
    {
        $model = $this->repository->find($id);

        if (!$model) {
            return $this->validNotFoundJsonResponse();
	}

        $opcionPago = $request->enum_opcion_pago;
        $montoManual = $request->monto_manual;
        switch ($request->enum_opcion_pago) {
        case 'TOTAL':
            $monto = $model->monto;
        break;
        case 'MINIMO':
            $monto = $model->monto_minimo;
        break;
        case 'MANUAL':
            $monto = $montoManual;
        break;
        //default:
            //$monto = $model->monto;
        }

        $pagare = $this->repository->update($model, ['monto_manual' => $opcionPago === 'MANUAL' ? $montoManual : 0, 'enum_opcion_pago' => $opcionPago])[1];

        $data = [
            'debt' => [
                'amount' => [
                    'currency' => $pagare->id_moneda == 1 ? 'PYG' : 'USD',
                    'value' => $monto
                ]
            ]
        ];

        $boleta = $this->dispatchNow(new TraerBoletaPago($pagare->id));


        $debt = ['debt' => Arr::only($boleta['debt'], ['amount','description'])];
        $itemExistente = array_search($pagare->id, array_column($debt['debt']['description']['items'], 'code'));
        if (!is_null($itemExistente)) {
            $debt['debt']['amount']['value'] = $monto;
            $debt['debt']['description']['items'][$itemExistente]['amount']['value'] = $monto;
        } else {
            $debt['debt']['amount']['value'] = $monto;
        }


        $boleta = $this->dispatchNow(new ActualizarBoletaPago($pagare->id, $debt));

        return $this->validSuccessJsonResponse('Success', ['boleta' => $boleta, 'pagare' => $pagare]);
    }
}
