<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Repositories\{ PagareRepository, RentaRepository };
use Raffles\Modules\Poga\Models\{ Pagare, User };
use Raffles\Modules\Poga\Notifications\{ PagareRentaVencidoAcreedor, PagareRentaVencidoDeudor, PagareRentaVencidoParaAdminPoga };

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class GenerarMultas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @param RentaRepository $repository The RentaRepository object.
     *
     * @return void
     */
    public function handle(RentaRepository $repository, PagareRepository $rPagare)
    {
        Log::info('Comienzo de ejecución de "GenerarMultas".');

        $rentasActivasConMulta = $repository->activasConMulta();

	$now = Carbon::now();

        $pagaresVencidos = collect();

        foreach($rentasActivasConMulta as $renta) {
            $inmueble = $renta->idInmueble;
            $rPagare->deRentaVencidosParaInmueble($inmueble)
                ->each(
                    function ($item) use ($pagaresVencidos) {
                        $pagaresVencidos->push($item);
                    }
                );
	}

	foreach($pagaresVencidos->unique('id') as $pagareVencido) {
            // Misma fecha que la del pagare de renta.
            $fechaPagare = $pagareVencido->fecha_pagare;
	    // Vence hoy.
	    $fechaVencimiento = $now;

            $renta = $pagareVencido->idRenta;
	    $inmueble = $renta->idInmueble;
	    $multaRenta = $renta->multas()->firstOrCreate(
                [ 
                    'id_pagare' => $pagareVencido->id, 
                    'mes' => $now->month, 
                    'anno' => $now->year,
                ]
	    );

	    $pagareMulta = $rPagare->deMultaPorPeriodoParaRenta($fechaPagare, $renta)->first();
	    if (!$pagareMulta) {
                $monto = $renta->monto_multa_dia;

                $pagareMulta = $rPagare->create(
                    [
                    'id_inmueble' => $inmueble->id,
                    'fecha_pagare' => $fechaPagare,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'id_persona_acreedora' => $pagareVencido->id_persona_acreedora,
                    'id_persona_deudora' => $pagareVencido->id_persona_deudora,
                    'id_moneda'=> $pagareVencido->id_moneda,
                    'enum_estado'=>'PENDIENTE',
                    'enum_clasificacion_pagare'=>'MULTA_RENTA',
                    'id_tabla'=> $pagareVencido->id_tabla,
                    'monto' => $monto, 
                    ]
	        )[1];

                try {
                    $uc = new TraerBoletaPago($pagareVencido->id);
                    $boleta = $uc->handle();

                    $this->actualizarBoletaPago($boleta, $pagareMulta);

                    // Notifica solamente la primer vez que genera pagare de multa.
                    $pagareVencido->idPersonaAcreedora->user->notify(new PagareRentaVencidoAcreedor($pagareVencido));
                    $pagareVencido->idPersonaDeudora->user->notify(new PagareRentaVencidoDeudor($pagareVencido));

                    $admin = User::where('email', env('EMAIL_ADMIN_ADDRESS'))->first();
                    if ($admin) {
                        $admin->notify(new PagareRentaVencidoParaAdminPoga($pagareVencido));
                    }
                } catch (\Exception $e) {
                    //
                } 
	    } else {
                if ($pagareMulta->enum_estado === 'PENDIENTE') {
		    $diasAtraso = $fechaVencimiento->diffInDays($pagareVencido->fecha_vencimiento);
		    $monto = $renta->monto_multa_dia * $diasAtraso;
       
                    $pagareMulta->update(
                        [
			    'fecha_vencimiento' => $fechaVencimiento->toDateString(),    
                            'monto' => $monto, 
                        ]
		    );
	
                    try {	
                        $uc = new TraerBoletaPago($pagareVencido->id);
                        $boleta = $uc->handle();

                        $this->actualizarBoletaPago($boleta, $pagareMulta);
                    } catch (\Exception $exception) {
                        //
                    }
		}	
            }
        }

        Log::info('Fin de ejecución de "GenerarMultas".');
    }

    /**
     * Actualizar Boleta de Pago.
     *
     * @param array  $boleta
     * @param Pagare $pagareMulta
     *
     * @return void
     */
    protected function actualizarBoletaPago(array $boleta, Pagare $pagareMulta)
    {
        $data = $boleta;

        $inquilino = $pagareMulta->idPersonaDeudora;
        $targetLabel = $inquilino->nombre_y_apellidos;
        $targetNumber = $inquilino->enum_tipo_persona === 'FISICA' ? $inquilino->ci : $inquilino->ruc;
	$label = 'Pago de renta para el inquilino '.$targetNumber.' '.$targetLabel.', mes '.Carbon::parse($pagareMulta->fecha_pagare)->format('m/Y');
	$montoMulta = $pagareMulta->monto;
	$montoRenta = $pagareMulta->idRenta->monto;
	$summary = $label.', con multa por atraso.';
	$validPeriodEnd = Carbon::parse($pagareMulta->fecha_vencimiento)->endOfDay()->toAtomString();
	$validPeriodStart = $data['debt']['validPeriod']['start'];

        $itemExistente = array_search($pagareMulta->id, array_column($boleta['debt']['description']['items'], 'code'));
	if (false !== $itemExistente) {
            $debt = ['debt' => Arr::only($data['debt'], ['amount', 'description', 'label', 'validPeriod'])];
            $debt['debt']['amount']['value'] = $montoRenta + $montoMulta;
            $debt['debt']['description']['items'][$itemExistente]['amount']['value'] = $montoMulta;
            $debt['debt']['description']['summary'] = $summary;
            $debt['debt']['description']['text'] = $summary;
	    $debt['debt']['label'] = $label;
            $debt['debt']['validPeriod']['end'] = $validPeriodEnd;
	    $debt['debt']['validPeriod']['start'] = $validPeriodStart;
        } else {
            $itemMulta = [
                'label' => 'Multa por atraso',
                'code' => $pagareMulta->id,
                'amount' => [
                    'currency' => 'PYG',
                    'value' => $montoMulta
                ]
            ];

	    $debt = ['debt' => Arr::only($data['debt'], ['amount', 'description', 'label', 'validPeriod'])];
            $debt['debt']['amount']['value'] = $montoRenta + $montoMulta;
            $debt['debt']['description']['summary'] = $summary;
            $debt['debt']['description']['text'] = $summary;
	    $debt['debt']['label'] = $summary;
            $debt['debt']['validPeriod']['end'] = $validPeriodEnd;
            $debt['debt']['validPeriod']['start'] = $validPeriodStart;

	    array_push($debt['debt']['description']['items'], $itemMulta);
        }

        $uc = new ActualizarBoletaPago($boleta['debt']['docId'], $debt);
        $uc->handle();
    }
}
