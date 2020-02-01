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
            $this->generarPagareRenta($renta);
        }
    }

    public function generarPagareRenta(Renta $renta)
    {
        $now = Carbon::now();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfMonth = $now->copy()->startOfMonth();

        if ($renta->created_at->format('m') !== $now->format('m')) {
            $fechaCreacionPagare = $now;
            $fechaVencimiento = $startOfMonth->copy()->addDays($renta->dia_mes_pago + $renta->dias_multa - 1);

            $inmueble = $renta->idInmueble;

            $pagare = $inmueble->pagares()->where('id_persona_acreedora', $inmueble->idPropietarioReferente->id_persona)->where('id_persona_deudora', $renta->id_inquilino)->whereBetween('fecha_pagare', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])->where('enum_clasificacion_pagare', 'RENTA')->where('id_tabla', $renta->id)->first();

            if (!$pagare) {
                $pagare = $inmueble->pagares()->create([
                    'enum_clasificacion_pagare' => 'RENTA',
                    'enum_estado' => 'PENDIENTE',
                    'fecha_pagare' => $fechaCreacionPagare->toDateString(),
                    'fecha_vencimiento' => $fechaVencimiento,
                    'id_moneda' => $renta->id_moneda,
                    'id_persona_acreedora' => $inmueble->idPropietarioReferente->id_persona,
                    'id_persona_deudora' => $renta->id_inquilino,
                    'id_tabla' => $renta->id,
                    'monto' => $renta->monto,
                ]);

                \Log::info('GenerarPagares: Pagare creado id '.$pagare->id);

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
}
