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
        $now = Carbon::now();
        $renta = $this->renta;
        $startOfMonth = $now->copy()->startOfMonth();

        $fechaInicio = $renta->fecha_inicio;
        $fechaCreacionPagare = Carbon::create($now->year, $now->month, $fechaInicio->day, 0, 0, 0);
        //$fechaVencimiento = $startOfMonth->copy()->addDays($renta->dia_mes_pago + $renta->dias_multa - 1);
        $fechaVencimiento = $now->copy()->addDays(10);

        $primerDiaPeriodo = $startOfMonth->copy()->addDays($renta->dia_mes_pago)->subDay();
        $ultimoDiaPeriodo = $primerDiaPeriodo->copy()->addMonth()->subDay();
        $cantDiasPeriodo = $ultimoDiaPeriodo->diffInDays($primerDiaPeriodo);

        $inmueble = $renta->idInmueble;

        if ($now->toDateString() > $primerDiaPeriodo->toDateString()) {
            $offset = $now->diffInDays($primerDiaPeriodo);
        } else {
            $offset = 0;
        }

        $monto = round(($renta->monto / $cantDiasPeriodo * ($cantDiasPeriodo - $offset)), 2, PHP_ROUND_HALF_UP);

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

        $items = [];
        array_push(
            $items, [
            'label' => 'Pago de renta',
            'code' => $pagare->id,
            'amount' => [
                'currency' => 'PYG',
                'value' => $pagare->monto,
            ]
            ]
        );

        if (!$renta->vigente) {
            if ($renta->garantia > 0) {
                $pagareDepositoGarantia = $this->dispatchNow(new GenerarPagareDepositoGarantia($renta));

                array_push(
                    $items, [
                        'label' => 'Depósito de garantía',
                        'code' => $pagareDepositoGarantia->id,
                        'amount' => [
                            'currency' => 'PYG',
                            'value' => $pagareDepositoGarantia->monto,
                        ]
                    ]
                );
    
                $monto = $monto + $pagareDepositoGarantia->monto;
            }

            if ($renta->comision_inmobiliaria > 0) {
                $pagareComisionInmobiliaria = $this->dispatchNow(new GenerarPagareComisionInmobiliaria($renta));

                array_push(
                    $items, [
                    'label' => 'Comisión inmobiliaria',
                    'code' => $pagareComisionInmobiliaria->id,
                    'amount' => [
                        'currency' => 'PYG',
                        'value' => $pagareComisionInmobiliaria->monto,
                    ]
                    ]
                );

                       $monto = $monto + $pagareComisionInmobiliaria->monto;
            }
        }

        $datosBoleta = [
            'amount' => [
                'currency' => 'PYG',
                'value' => $monto,
            ],
            'description' => [
            'items' => $items,
                'summary' => 'Pago de renta con fecha: ',
            'text' => 'Pago de renta con fecha: ',
            ],
            'docId' => $pagare->id,
            'label' => 'Pago de renta con fecha: ',
            'target' => [
                'label' => $inquilino->nombre.' '.$inquilino->apellido,
                'number' => $inquilino->ruc ? $inquilino->ruc : $inquilino->ci,
                'type' => $inquilino->ruc ? 'ruc' : 'cip',
            ],
            'validPeriod' => [
                'end' => $fechaVencimiento->toAtomString(),
                'start' => $fechaCreacionPagare->toAtomString()
            ]
        ];

        $boleta = $this->dispatchNow(new GenerarBoletaPago($datosBoleta));

        $personaAdministradorReferente = $renta->idInmueble->idAdministradorReferente;
        // Puede que el inmueble no tenga un administrador referente.
        if ($personaAdministradorReferente) {
            $userAdministradorReferente = $personaAdministradorReferente->idPersona->user;
            if ($userAdministradorReferente) {
                $userAdministradorReferente->notify(new PagareCreadoAdministradorReferente($pagare));
            }
        }

        $acreedor = $pagare->idPersonaAcreedora->user;
        // El acreedor es el propietario. Puede que no tenga usuario registrado.
        if ($acreedor) {
            $acreedor->notify(new PagareCreadoPersonaAcreedora($pagare));
        }

        $deudor = $pagare->idPersonaDeudora->user;
        // El deudor es el inquilino. Puede que no tenga usuario registrado.
        if ($deudor) {
            $deudor->notify(new PagareCreadoPersonaDeudora($pagare));
        }

        return $boleta;
    }
}
