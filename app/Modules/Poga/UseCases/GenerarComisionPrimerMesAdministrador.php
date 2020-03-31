<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Renta;
use Raffles\Modules\Poga\Notifications\{ PagareCreadoPersonaDeudora, PagareCreadoAdministradorReferente };
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerarComisionPrimerMesAdministrador
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
        $inmueble = $this->renta->idInmueble;
    
        if ($inmueble->idAdministradorReferente) {
            $now = Carbon::now();
            $comision = $this->renta->prim_comision_administrador;

            $pagare = $repository->create(
                [
                'enum_clasificacion_pagare' => 'COMISION_RENTA_ADMIN',
                'enum_estado' => 'PENDIENTE',
                'fecha_pagare' => $now,
                'id_inmueble' => $this->renta->id_inmueble,
                'id_moneda' => $this->renta->id_moneda,
                'id_persona_acreedora' => $inmueble->idAdministradorReferente->id_persona,
                'id_persona_deudora' => $inmueble->idPropietarioReferente->id_persona,
                'id_tabla_hija' => $this->renta->id,
                'monto' => $comision,
                ]
            )[1];

                   $datosBoleta = [
                       'amount' => [
                    'currency' => $renta->id_moneda == 1 ? 'PYG' : 'USD',
                    'value' => $renta->monto
                       ],
                       'description' => [
                           'summary' => 'Pago de renta',
                           'text' => 'Pago de renta',
                       ],
                       'docId' => $renta->id,
                       'label' => 'Pago de renta',
                       'target' => [
                           'label' => $inquilino->nombre.' '.$inquilino->apellido,
                           'number' => $inquilino->ruc ? $inquilino->ruc : $inquilino->ci,
                           'type' => $inquilino->ruc ? 'RUC' : 'CI',
                       ],
                       'validPeriod' => [
                           'end' => $renta->fecha_inicio->toDateTimeString(),
                           'start' => $renta->fecha_fin->toDateTimeString(),
                       ]
                   ];

                   $acreedor = $pagare->idPersonaAcreedora->user;
                   $deudor = $pagare->idPersonaDeudora->user;

                   // El acreedor es el administrador.
                   $acreedor->notify(new PagareCreadoAdministradorReferente($pagare));

                   // El deudor es el propietario. Puede que no tenga usuario registrado.
                   if ($deudor) {
                       $deudor->notify(new PagareCreadoPersonaDeudora($pagare));
                   }

                   return $pagare;
        }
    }
}
