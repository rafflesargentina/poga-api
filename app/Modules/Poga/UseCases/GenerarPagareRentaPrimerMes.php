<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Renta;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\Notifications\{ PagareCreadoAdministradorReferente, PagareCreadoPersonaAcreedora, PagareCreadoPersonaDeudora };

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerarPagareRentaPrimerMes
{
    use DispatchesJobs;

    /**
     * The Renta model.
     *
     * @var Renta
     */
    protected $renta;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Renta $renta)
    {
        $this->renta = $renta;
    }

    /**
     * Execute the job.
     *
     * @param PagareRepository $repository The PagareRepository object.
     *
     * @return void
     */
    public function handle(PagareRepository $repository)
    {
        $renta = $this->renta;
        $inmueble = $renta->idInmueble;

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $fechaInicio = $renta->fecha_inicio;
        $fechaCreacionPagare = $now;
        $fechaVencimiento = $fechaInicio->copy()->addDays(10);

        $primerDiaPeriodo = $startOfMonth->copy()->addDays($renta->dia_mes_pago)->subDay();
        $ultimoDiaPeriodo = $primerDiaPeriodo->copy()->addMonth()->subDay();
        $cantDiasPeriodo = $ultimoDiaPeriodo->diffInDays($primerDiaPeriodo) + 1;

        $offset = $fechaInicio->diffInDays($ultimoDiaPeriodo) + 1;

        $monto = intval(round(($renta->monto / $cantDiasPeriodo * $offset), 2, PHP_ROUND_HALF_UP));

        $pagare = $repository->create(
            [
            'id_inmueble' => $renta->id_inmueble,
            'id_persona_acreedora' => $inmueble->idPropietarioReferente->id_persona,
            'id_persona_deudora' => $renta->id_inquilino,
            'monto' => $monto,
            'id_moneda' => $renta->id_moneda,
            'fecha_pagare' => $fechaCreacionPagare,
            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
            'enum_estado' => 'PENDIENTE',
            'enum_clasificacion_pagare' => 'RENTA',
            'id_tabla' => $renta->id,
            ]
        )[1];

        $inquilino = $renta->idInquilino;

        $targetLabel = $inquilino->nombre_y_apellidos;
        $targetType = $inquilino->enum_tipo_persona === 'FISICA' ? 'cip' : 'ruc';
        $targetNumber = $inquilino->enum_tipo_persona === 'FISICA' ? $inquilino->ci : $inquilino->ruc;
        $label = 'Pago de renta para el inquilino ('.$targetNumber.') '.$targetLabel.', mes '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
        $summary = $label;

        $items = [];
        array_push(
            $items, [
            'label' => $summary,
            'code' => $pagare->id,
            'amount' => [
                'currency' => $renta->id_moneda == 1 ? 'PYG' : 'USD',
                'value' => $pagare->monto,
            ]
            ]
        );

        if (!$renta->vigente) {
            if ($renta->garantia > 0) {
                $pagareDepositoGarantia = $this->dispatchNow(new GenerarPagareDepositoGarantia($renta));

                $summary = $summary.'; con depósito de garantía';

                array_push(
                    $items, [
                        'label' => 'Depósito de garantía',
                        'code' => $pagareDepositoGarantia->id,
                        'amount' => [
                            'currency' => $renta->id_moneda == 1 ? 'PYG' : 'USD',
                            'value' => $pagareDepositoGarantia->monto,
                        ]
                    ]
                );
    
                $monto = intval(round($monto + $pagareDepositoGarantia->monto, 2, PHP_ROUND_HALF_UP));
            }

            if ($renta->comision_inmobiliaria > 0) {
                $pagareComisionInmobiliaria = $this->dispatchNow(new GenerarPagareComisionInmobiliaria($renta));

                $summary = $summary.' y con pago de comisión inmobiliaria.';

                array_push(
                    $items, [
                    'label' => 'Comisión inmobiliaria',
                    'code' => $pagareComisionInmobiliaria->id,
                    'amount' => [
                        'currency' => $renta->id_moneda == 1 ? 'PYG' : 'USD',
                        'value' => $pagareComisionInmobiliaria->monto,
                    ]
                    ]
                );


                $monto = intval(round($monto + $pagareComisionInmobiliaria->monto, 2, PHP_ROUND_HALF_UP));
            }
        }

        $datosBoleta = [
            'amount' => [
                'currency' => $renta->id_moneda == 1 ? 'PYG' : 'USD',
                'value' => $monto,
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

        $boleta = $this->dispatchNow(new GenerarBoletaPago($datosBoleta));

        return $boleta;
    }
}
