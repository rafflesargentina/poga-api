<?php

namespace Raffles\Modules\Poga\Http\Controllers\Base97;

use Raffles\Http\Controllers\Controller;
use Raffles\Modules\Poga\Notifications\BoletaPagada;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\ActualizarEstadoPagare;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class NotificacionPagoController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * The PagareRepository object.
     *
     * @var PagareRepository 
     */
    protected $repository;

    /**
     * Create a new NotificacionPagoController instance.
     *
     * @param  PagareRepository $repository
     * @return void
     */
    public function __construct(PagareRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $b64UrlModel = $request->doc_model;
        $jsonModel =  base64_decode(str_replace(['-','_'], ['+','/'], $b64UrlModel));
	$docModel = json_decode($jsonModel, true);

	\Log::info($docModel);

	//try {
	    $pagare = $this->repository->findOrFail($docModel['docId']);
	    $this->dispatchNow(new ActualizarEstadoPagare($pagare, 'PAGADO'));
	//} catch (\Exception $e) {
            //return $this->validInternalServerErrorJsonResponse($e, 'Ocurrió un error al intentar actualizar el estado del pagaré de la boleta de pago de Renta.');
	//}

	    $items = $docModel['description']['items'];
	    \Log::info("QUE PASAAAA");
	\Log::info($items);
	foreach ($items as $item) {
		\Log::info('CODIGO: '.$item['code']);
	    //try {
	        $pagare = $this->repository->findOrFail($item['code']);
		$this->dispatchNow(new ActualizarEstadoPagare($pagare, 'PAGADO'));
	    //} catch (\Exception $e) {
                //return $this->validInternalServerErrorJsonResponse($e, 'Ocurrió un error al intentar actualizar el estado de pagaré de uno de los items de la boleta de pago.');
	    //}
	}

        return response()->json('Success');
    }
}
