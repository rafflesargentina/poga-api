<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Notifications\{ PagareCreadoPersonaAcreedora, PagareCreadoPersonaDeudora };
use Raffles\Modules\Poga\Repositories\{ PagareRepository, RentaRepository };

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CrearPagare
{
    use DispatchesJobs;

    /**
     * The form data and the User model.
     *
     * @var array $data
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data The form data.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param PagareRepository $rPagare The PagareRepository object.
     * @param RentaRepository  $rRenta  The RentaRepository object.
     *
     * @return void
     */
    public function handle(PagareRepository $rPagare, RentaRepository $rRenta)
    {
	$data = $this->data;

        $renta = $rRenta->where('id_inquilino', $data['id_persona_deudora'])->where('id_inmueble', $data['id_inmueble'])->where('enum_estado', 'ACTIVO')->firstOrFail();

        $pagare = $rPagare->create(
            [
	    'descripcion' => $data['descripcion'],
	    'id_inmueble' => $data['id_inmueble'],
	    'id_persona_acreedora' => $data['id_persona_acreedora'],
            'id_persona_deudora' => $data['id_persona_deudora'],
            'id_tabla' => $renta->id,
            'monto' => $data['monto'], 
            'id_moneda' => $data['id_moneda'],
	    'fecha_pagare' => $data['fecha_pagare'],
	    'fecha_vencimiento' => $data['fecha_vencimiento'],
            'enum_estado' => 'PENDIENTE',
            'enum_clasificacion_pagare' => $data['enum_clasificacion_pagare'],
            ]
        )[1];

	$inmueble = $pagare->idInmueble;
	$inquilinoReferente = $inmueble->idInquilinoReferente->idPersona;

        $pagare->idPersonaAcreedora->user->notify(new PagareCreadoPersonaAcreedora($pagare));
        $pagare->idPersonaDeudora->user->notify(new PagareCreadoPersonaDeudora($pagare));

        $targetLabel = $inquilinoReferente->nombre_y_apellidos;
        $targetType = $inquilinoReferente->enum_tipo_persona === 'FISICA' ? 'cip' : 'ruc';
	$targetNumber = $inquilinoReferente->enum_tipo_persona === 'FISICA' ? $inquilinoReferente->ci : $inquilinoReferente->ruc;
        $label = 'Solicitud de pago para ('.$targetNumber.') '.$targetLabel.', mes '.Carbon::parse($pagare->fecha_pagare)->format('m/Y');
        $summary = $label.'. Observación: '.$pagare->descripcion;
        $datosBoleta = [
            'amount' => [
                'currency' => $pagare->id_moneda == 1 ? 'PYG' : 'USD',
                'value' => $pagare->monto,
            ],
            'description' => [
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
                'end' => Carbon::parse($pagare->fecha_vencimiento)->toAtomString(),
                'start' => Carbon::parse($pagare->fecha_pagare)->toAtomString()
            ]
        ];

	$boleta = $this->dispatchNow(new GenerarBoletaPago($datosBoleta));

        return ['pagare' => $pagare, 'boleta' => $boleta];
    }
}
