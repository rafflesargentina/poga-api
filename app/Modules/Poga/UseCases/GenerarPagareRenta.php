<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Renta;
use Raffles\Modules\Poga\Notifications\{ PagareCreadoAdministradorReferente, PagareCreadoPersonaAcreedora, PagareCreadoPersonaDeudora };
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerarPagareRenta
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

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $fechaInicio = $renta->fecha_inicio;
	$fechaCreacionPagare = $now;
	$fechaVencimiento = $startOfMonth->copy()->addDays($renta->dia_mes_pago + $renta->dias_multa - 1);

        $inquilino = $renta->idInquilino;
        $inmueble = $renta->idInmueble;

        $pagare = $repository->create(
            [
            'id_inmueble' => $renta->id_inmueble,
            'id_persona_acreedora' => $inmueble->idPropietarioReferente->id_persona,
            'id_persona_deudora' => $renta->id_inquilino,
            'monto' => $renta->monto,
            'id_moneda' => $renta->id_moneda,
            'fecha_pagare' => $fechaCreacionPagare,
            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
            'enum_estado' => 'PENDIENTE',
            'enum_clasificacion_pagare' => 'RENTA',
            'id_tabla' => $renta->id,
            ]
        )[1];

	$targetLabel = $inquilino->nombre_y_apellidos;
	$targetType = $inquilino->enum_tipo_persona === 'FISICA' ? 'cip' : 'ruc';
	$targetNumber = $inquilino->enum_tipo_persona === 'FISICA' ? $inquilino->ci : $inquilino->ruc;
	$label = 'Pago de renta para el inquilino ('.$targetNumber.') '.$targetLabel.', mes '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
	$summary = $label;

        $datosBoleta = [
            'amount' => [
                'currency' => 'PYG',
                'value' => $renta->monto,
            ],
            'description' => [
                'summary' => $summary,
                'text' => $summary,
            ],
            'docId' => $renta->id,
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
