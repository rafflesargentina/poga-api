<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Repositories\{ PagareRepository, RentaRepository };
use Raffles\Modules\Poga\Models\{ Inmueble, Pagare, User };
use Raffles\Modules\Poga\Notifications\{ PagareRentaVencidoAcreedor, PagareRentaVencidoDeudor, PagareRentaVencidoParaAdminPoga };

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
	$rentasActivas = $repository->findWhere(['multa' => 1, 'enum_estado' => 'ACTIVO']);

        $now = Carbon::now();

	$pagaresVencidos = collect();

        foreach($rentasActivas as $renta) {
	    $fechaInicioRenta = $renta->fecha_inicio;

	    $inmueble = $renta->idInmueble;

	    $inmueble->pagares()
                ->where('enum_clasificacion_pagare', 'RENTA')
                ->where('enum_estado', 'PENDIENTE')
		->where('fecha_vencimiento', '<', $now->toDateString())
		->get()
	        ->each(function($item) use($pagaresVencidos) {
                    $pagaresVencidos->push($item);
	        });
	}

	foreach($pagaresVencidos->unique('id') as $pagareVencido) {
            $renta = $pagareVencido->idRenta;
            $multaRenta = $renta->multas()->firstOrCreate(
                [ 
                    'id_pagare' => $pagareVencido->id, 
                    'mes' => $now->month, 
                    'anno' => $now->year,
                ]
	    );

            $uc = new TraerBoletaPago($pagareVencido->id);
            $boleta = $uc->handle();

            $inquilino = $pagareVencido->idPersonaDeudora;
            $targetLabel = $inquilino->nombre_y_apellidos;
            $targetType = $inquilino->enum_tipo_persona === 'FISICA' ? 'cip' : 'ruc';
	    $targetNumber = $inquilino->enum_tipo_persona === 'FISICA' ? $inquilino->ci : $inquilino->ruc;
	    $label = 'Pago de renta para el inquilino '.$targetNumber.' '.$targetLabel.', mes '.Carbon::parse($pagareVencido->fecha_pagare)->format('m/Y');
            $summary = $label.', con multa por atraso.';

            $inicioMes = $now->copy()->startOfMonth()->toDateString();
                
            $pagareMulta = $inmueble->pagares()
                ->where('enum_estado', 'PENDIENTE')
		->where('fecha_pagare', '>=', $inicioMes)
		->where('id_tabla', $renta->id)
                ->where('enum_clasificacion_pagare', 'MULTA_RENTA')
		->first();

	    if(!$pagareMulta) {
                // Notifica solamente la primer vez que genera pagare de multa.
                $pagareVencido->idPersonaAcreedora->user->notify(new PagareRentaVencidoAcreedor($pagareVencido, $boleta));
                $pagareVencido->idPersonaDeudora->user->notify(new PagareRentaVencidoDeudor($pagareVencido, $boleta));

                $admin = User::where('email', env('EMAIL_ADMIN_ADDRESS'))->first();
                if ($admin) {
                    $admin->notify(new PagareRentaVencidoParaAdminPoga($pagareVencido, $boleta));
                }

                $fechaCreacionPagare = Carbon::create($now->year, $now->month, $fechaInicioRenta->day, 0, 0, 0);
		    
		$monto = $renta->monto_multa_dia;

                $pagareMulta = $rPagare->create(
                    [
                    'id_inmueble' => $inmueble->id,
		    'fecha_pagare' => $fechaCreacionPagare,
		    'fecha_vencimiento' => $pagareVencido->idRenta->fecha_fin,
                    'id_persona_acreedora' => $pagareVencido->id_persona_acreedora,
                    'id_persona_deudora' => $pagareVencido->id_persona_deudora,
                    'id_moneda'=> $pagareVencido->id_moneda,
                    'enum_estado'=>'PENDIENTE',
                    'enum_clasificacion_pagare'=>'MULTA_RENTA',
                    'id_tabla'=> $pagareVencido->id_tabla,
                    'monto' => $monto, 
                    ]
		 )[1];

		if ($boleta) {
                    $data = $boleta;

		    $itemMulta = [ 
                        'label' => 'Multa por días de atraso',
		        'code' => $pagareMulta->id,
		        'amount' => [
                            'currency' => 'PYG',
			    'value' => $monto
                        ]
		    ];

                    array_push($data['debt']['description']['items'], $itemMulta);
		    $data['debt']['amount']['value'] = $boleta['debt']['amount']['value'] + $monto;

		    $debt = ['debt' => Arr::only($data['debt'], ['amount', 'description', 'validPeriod'])];

                    $debt['debt']['description']['summary'] = $summary;
		    $debt['debt']['description']['text'] = $summary;
		    $debt['debt']['label'] = $label;
		    $debt['debt']['validPeriod']['start'] = $data['debt']['validPeriod']['start'];
		    $debt['debt']['validPeriod']['end'] = $now->toAtomString();

		    $uc = new ActualizarBoletaPago($pagareVencido->id, $debt);
		    $boleta = $uc->handle();
		}
	    } else {
	        $monto = $pagareMulta->monto + $renta->monto_multa_dia;
		
		$pagareMulta->update(
                    [
                        'monto' => $monto, 
                    ]
                );
		
		if ($boleta) {
		    $data = $boleta;

		    $itemExistente = array_search($pagareMulta->id, array_column($boleta['debt']['description']['items'], 'code'));

		    if ($itemExistente) {
			$data['debt']['amount']['value'] = $boleta['debt']['amount']['value'] + $renta->monto_multa_dia;
			$data['debt']['description']['items'][$itemExistente]['amount']['value'] = $monto;

			$debt = ['debt' => Arr::only($data['debt'], ['amount', 'description', 'validPeriod'])];

                        $debt['debt']['description']['summary'] = $summary;
			$debt['debt']['description']['text'] = $summary;
			$debt['debt']['label'] = $label;
                        $debt['debt']['validPeriod']['start'] = $data['debt']['validPeriod']['start'];
			$debt['debt']['validPeriod']['end'] = $now->toAtomString();
		    } else {
			$itemMulta = [
                            'label' => 'Multa por atraso',
                            'code' => $pagareMulta->id,
                            'amount' => [
                                'currency' => 'PYG',
                                'value' => $monto
			    ]
			];

			array_push($data['debt']['description']['items'], $itemMulta);
			$data['debt']['amount']['value'] = $boleta['debt']['amount']['value'] + $monto;

			$debt = ['debt' => Arr::only($data['debt'], ['amount', 'description', 'validPeriod'])];

                        $debt['debt']['description']['summary'] = $summary;
			$debt['debt']['description']['text'] = $summary;
			$debt['debt']['label'] = $summary;
                        $debt['debt']['validPeriod']['start'] = $data['debt']['validPeriod']['start'];
			$debt['debt']['validPeriod']['end'] = $now->toAtomString();
		    }

                    $uc = new ActualizarBoletaPago($pagareVencido->id, $debt);
                    $uc->handle();
                }
            }
        }
    }
}
