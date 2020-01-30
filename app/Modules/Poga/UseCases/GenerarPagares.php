<?php

namespace Raffles\Modules\Poga\UseCases;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Raffles\Modules\Poga\Repositories\RentaRepository;
use Raffles\Modules\Poga\Models\{ Inmueble, Renta, Pagare };
use Raffles\Modules\Poga\Notifications\{ PagareCreadoAdministradorReferente, PagareCreadoPersonaAcreedora, PagareCreadoPersonaDeudora };

class GenerarPagares implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RentaRepository $repository)
    {
        $rentas = $repository->findWhere(['enum_estado' => 'ACTIVO']);

        foreach($rentas as $renta) {
            $this->generarPagoRenta($renta);
            //$this->generarPagoConserje($renta);
            //$this->generarPagoAdministrador($renta);
        }
    }

    public function generarPagoRenta(Renta $renta)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        if ($renta->created_at->format('m') !== $now->format('m')) {
            $fechaInicioRenta = $renta->fecha_inicio;
	    $fechaCreacionPagare = Carbon::create($now->year, $now->month, $fechaInicioRenta->day, 0, 0, 0);
            $fechaVencimiento = $startOfMonth->copy()->addDays($renta->dia_mes_pago + $renta->dias_multa - 1);

            // En el raro caso que el job corra luego de la fecha del pagaré.
            if ($fechaVencimiento->toDateString() < $now->toDateString()) {
                $fechaVencimiento = $now->copy()->addDays(10);
            }

	    $inmueble = $renta->idInmueble;

            $pagare = $inmueble->pagares()->firstOrCreate(
                [
                'id_persona_acreedora' => $inmueble->idPropietarioReferente->id_persona,
                'id_persona_deudora' => $renta->id_inquilino,
                'fecha_pagare' => $fechaCreacionPagare,
                'enum_estado' => 'PENDIENTE',
                'enum_clasificacion_pagare' => 'RENTA',
                ],        
                [
                'id_persona_acreedora' => $inmueble->idPropietarioReferente->id_persona,
                'id_persona_deudora' => $renta->id_inquilino,
                'monto' => $renta->monto,
                'id_moneda' => $renta->id_moneda,
		'fecha_pagare' => $fechaCreacionPagare,                      
		'fecha_vencimiento' => $fechaVencimiento,
                'enum_estado' => 'PENDIENTE',
                'enum_clasificacion_pagare' => 'RENTA',
                'id_tabla' => $renta->id,
                ]
            );

	    if ($pagare->wasRecentlyCreated) {
		    var_dump('recientemente creado');
	        $inquilino = $renta->idInquilino;

                $targetLabel = $inquilino->nombre_y_apellidos;
                $targetType = $inquilino->enum_tipo_persona === 'FISICA' ? 'cip' : 'ruc';
                $targetNumber = $inquilino->enum_tipo_persona === 'FISICA' ? $inquilino->ci : $inquilino->ruc;
                $label = 'Pago de renta para el inquilino ('.$targetNumber.') '.$targetLabel.', mes '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
                $summary = $label;

                $items = [];
                array_push(
                    $items, [
                        'label' => $label,
                        'code' => $pagare->id,
                        'amount' => [
                            'currency' => 'PYG',
                            'value' => $pagare->monto,
                        ]
                    ]
                );
	    
	        $datosBoleta = [
                    'amount' => [
                        'currency' => 'PYG',
                        'value' => $pagare->monto,
                    ],
                    'description' => [
                        'items' => $items,
                        'summary' => $summary,
                        'text' => $summary,
                    ],
                    'docId' => $pagare->id,
                    'label' => $label,
                    'target' => [
                        'label' => $targetLabel,
                        'number' => $targetNumber,
                        'type' => $targetType,
                    ],
                    'validPeriod' => [
                        'end' => $fechaVencimiento->toAtomString(),
                        'start' => $fechaCreacionPagare->toAtomString()
                    ]
                ];

		$uc = new GenerarBoletaPago($datosBoleta);
	        $boleta = $uc->handle();

                $pagare->idPersonaAcreedora->user->notify(new PagareCreadoPersonaAcreedora($pagare, $boleta));
                $pagare->idPersonaDeudora->user->notify(new PagareCreadoPersonaDeudora($pagare, $boleta));
	    }
	}
    }

    protected function generarComisionRenta(Renta $renta)
    {
   
        $now = Carbon::now()->startOfDay();              
        $comision = $renta->comision_administrador * $renta->monto / 100;
        //Si está pasado el proporcional de los dias del mes

        $pagare = $inmueble->pagares()->updateOrCreate(
            [
            'id_persona_acreedora' => $renta->idInmueble->idAdministradorReferente->id_persona,
            'id_persona_deudora' => $renta->idInmueble->idPropietarioReferente->id_persona,
            'monto' => $comision, 
            'id_moneda' => $renta->id_moneda,
            'fecha_pagare' => $fechaCreacionPagare,                      
            'enum_estado' => 'PENDIENTE',
            'enum_clasificacion_pagare' => 'COMISION_RENTA_ADMIN',
            'id_tabla_hija' => $renta->id,
            ]
        );       
    }

    //public function generarPagoConserje(Renta $renta){

        //$now = $now = Carbon::now()->startOfDay();              
        //$fechaInicioRenta = Carbon::createFromFormat('Y-m-d', $renta->fecha_inicio);  
        //$fechaCreacionPagare = Carbon::create($now->year, $now->month, $fechaInicioRenta->day, 0, 0, 0);

        //$pagare = $inmueble->pagares()->create([
            //'id_persona_acreedora' => $renta->idInmueble->idAdministradorReferente()->first()->id,
            //'monto' => $comision, 
            //'id_moneda' => $renta->id_moneda,
            //'fecha_pagare' => $fechaCreacionPagare,                      
            //'enum_estado' => 'PENDIENTE',
            //'enum_clasificacion_pagare' => 'SALARIO_CONSERJE',
            //'id_tabla_hija' => $renta->id,
        //]);     

    //}

    //public function generarPagoAdministrador(Renta $renta)
    //{
        //$now = $now = Carbon::now()->startOfDay();              
        //$fechaInicioRenta = Carbon::createFromFormat('Y-m-d', $renta->fecha_inicio);  
    //$fechaCreacionPagare = Carbon::create($now->year, $now->month, $fechaInicioRenta->day, 0, 0, 0);
    //$comision = $renta->comision_administrador * $renta->monto / 100;

        //$pagare = $renta->idInmueble->pagares()->create([
            //'id_persona_acreedora' => $renta->idInmueble->idAdministradorReferente()->first()->id,
            //'monto' => $comision, 
            //'id_moneda' => $renta->id_moneda,
            //'fecha_pagare' => $fechaCreacionPagare,                      
            //'enum_estado' => 'PENDIENTE',
            //'enum_clasificacion_pagare' => 'SALARIO_ADMINISTRADOR',
            //'id_tabla_hija' => $renta->id,
        //]);
    //}
}
