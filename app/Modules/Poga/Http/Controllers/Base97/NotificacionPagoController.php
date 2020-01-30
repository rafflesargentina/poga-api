<?php

namespace Raffles\Modules\Poga\Http\Controllers\Base97;

use Raffles\Http\Controllers\Controller;
use Raffles\Modules\Poga\Models\User;
use Raffles\Modules\Poga\Notifications\{ PagoConfirmadoAcreedor, PagoConfirmadoDeudor, PagoConfirmadoParaAdminPoga };
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\{ ActualizarEstadoPagare, GenerarBoletaPago };

use Carbon\Carbon;
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
     *
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
	$items = $docModel['description']['items'];

        \Log::info($docModel);

        $pagare = $this->repository->findOrFail($docModel['docId']);

        $payStatus = $docModel['payStatus']['status'];
        if ($payStatus === 'success') {
            $estado = 'PAGADO';

            $adminUser = \Raffles\Modules\Poga\Models\User::where('email', 'josue.aveiro@mavaite.com')->first();

            if ($items) {
               foreach ($items as $item) {
                   $pagare = $this->repository->findOrFail($item['code']);

                   $pagareComision = $this->repository->create([
                       'descripcion' => 'Comisión POGA (%5.5)',
                       'enum_clasificacion_pagare' => 'COMISION_POGA',
                       'enum_estado' => 'PENDIENTE',
                       'fecha_pagare' => Carbon::now(),
                       'fecha_vencimiento' => Carbon::now()->addYear(),
                       'id_inmueble' => $pagare->id_inmueble,
                       'id_moneda' => '1',
                       'id_persona_acreedora' => $adminUser->id_persona,
                       'id_persona_deudora' => $pagare->id_persona_acreedora,
                       'id_tabla' => $pagare->id,
                       'monto' => $pagare->monto * 5.5 / 100
                   ])[1];
               }
            } else {
                $pagare = $this->repository->findOrFail($docModel['docId']);

                $pagareComision = $this->repository->create([
                    'descripcion' => 'Comisión POGA (%5.5)',
                    'enum_clasificacion_pagare' => 'COMISION_POGA',
                    'enum_estado' => 'PENDIENTE',
                    'fecha_pagare' => Carbon::now(),
                    'fecha_vencimiento' => Carbon::now()->addYear(),
                    'id_inmueble' => $pagare->id_inmueble,
                    'id_moneda' => '1',
                    'id_persona_acreedora' => $adminUser->id_persona,
                    'id_persona_deudora' => $pagare->id_persona_acreedora,
                    'id_tabla' => $pagare->id,
                    'monto' => $pagare->monto * 5.5 / 100
                ])[1]; 
            }

            $pagare->idPersonaAcreedora->user->notify(new PagoConfirmadoAcreedor($pagare, $docModel));
            $pagare->idPersonaDeudora->user->notify(new PagoConfirmadoDeudor($pagare, $docModel));

            $admin = User::where('email', env('MAIL_ADMIN_ADDRESS'))->firstOrFail();
	    $admin->notify(new PagoConfirmadoParaAdminPoga($pagare, $docModel));

        } elseif ($payStatus === 'pending')  {
            if ($pagare->enum_estado !== 'PENDIENTE') {    
                $this->repository->update($pagare, ['revertido' => '1']);
	    
                if ($items) {
                    foreach ($items as $item) {
                        $this->repository->update($item['code'], ['revertido' => '1']);
                    }
                }
	    }

            $estado = 'PENDIENTE';
        } else {
            $estado = 'PENDIENTE';
        }


        $this->dispatchNow(new ActualizarEstadoPagare($pagare, $estado));
        if ($items) {
            foreach ($items as $item) {
                $pagare = $this->repository->update($item['code'], ['fecha_pago_a_confirmar' => Carbon::today()])[1];
                $this->dispatchNow(new ActualizarEstadoPagare($pagare, $estado));
            }
	}

        return response()->json('Success');
    }
}
