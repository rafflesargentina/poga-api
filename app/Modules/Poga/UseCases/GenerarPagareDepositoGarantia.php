<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Renta;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\Notifications\{ PagareCreadoAdministradorReferente, PagareCreadoPersonaAcreedora, PagareCreadoPersonaDeudora };

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class GenerarPagareDepositoGarantia
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
        $fechaVencimientoPagare = $renta->fecha_fin;

        $inmueble = $renta->idInmueble;
        $personaAcreedora = $inmueble->idPropietarioReferente->idPersona;
        $personaDeudora = $renta->idInquilino;

        $pagare = $repository->create(
            [
            'id_inmueble' => $renta->id_inmueble,
            'id_persona_acreedora' => $personaAcreedora->id,
            'id_persona_deudora' => $personaDeudora->id,
            'monto' => $renta->garantia,
            'id_moneda' => $renta->id_moneda,
            'fecha_pagare' => $fechaCreacionPagare,
            'fecha_vencimiento' => $fechaVencimientoPagare,
            'enum_estado' => 'PENDIENTE',
            'enum_clasificacion_pagare' => 'DEPOSITO_GARANTIA',
            'id_tabla' => $renta->id,
            ]
        )[1];

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

        return $pagare;
    }
}
