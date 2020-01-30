<?php

namespace Raffles\Modules\Poga\Http\Controllers\Base97;

use Raffles\Http\Controllers\Controller;
use Raffles\Modules\Poga\UseCases\TraerBoletaPago;

use Illuminate\Http\Request;
use RafflesArgentina\ResourceController\Traits\FormatsValidJsonResponses;

class ConsultarBoletaController extends Controller
{
    use FormatsValidJsonResponses;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        //$b64UrlModel = $request->doc_model;
        //$jsonModel =  base64_decode(str_replace(['-','_'], ['+','/'], $b64UrlModel));
	//$docModel = json_decode($jsonModel, true);

	$boleta = $this->dispatchNow(new TraerBoletaPago($id));    
	    
	return response()->json('Success', $boleta);
    }
}
