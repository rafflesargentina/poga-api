<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\{ Nominacion, Renta };
use Raffles\Modules\Poga\Repositories\{ InmueblePersonaRepository, NominacionRepository, RentaRepository };
use Raffles\Modules\Poga\Notifications\EstadoNominacionActualizado;

use Illuminate\Foundation\Bus\DispatchesJobs;

class AceptarNominacion
{
    use DispatchesJobs;

    /**
     * The Nominacion model.
     *
     * @var Nominacion $nominacion
     */
    protected $nominacion;

    /**
     * Create a new job instance.
     *
     * @param Nominacion $nominacion The Nominacion model.
     *
     * @return void
     */
    public function __construct(Nominacion $nominacion)
    {
        $this->nominacion = $nominacion;
    }

    /**
     * Execute the job.
     *
     * @param NominacionRepository $repository The NominacionRepository object.
     *
     * @return void
     */
    public function handle(NominacionRepository $repository, InmueblePersonaRepository $rInmueblePersona, RentaRepository $rRenta)
    {
        switch ($this->nominacion->role_id) {
        case 3:
            return $this->handleInquilino($repository, $rInmueblePersona, $rRenta);
        case 4:
            return $this->handlePropietario($repository, $rInmueblePersona);
        }
    }

    protected function handleInquilino(NominacionRepository $repository, InmueblePersonaRepository $rInmueblePersona, RentaRepository $rRenta)
    {
        $nominacion = $repository->update($this->nominacion, ['enum_estado' => 'ACEPTADO'])[1];

        $inmueblePersona = $rInmueblePersona->create(['id_persona' => $nominacion->id_persona_nominada, 'id_inmueble' => $nominacion->id_inmueble, 'referente' => '1', 'enum_rol' => 'INQUILINO'])[1];

        $personaNominada = $nominacion->idPersonaNominada;
        $renta = $personaNominada->rentas->where('id_inmueble', $nominacion->id_inmueble)->where('enum_estado', 'PENDIENTE')->first();
        if ($renta) {
            $renta->update(['enum_estado' => 'ACTIVO']);
        
            $boletaComisionAdministrador = $this->dispatchNow(new GenerarComisionPrimerMesAdministrador($renta));
            $boletaRenta = $this->dispatchNow(new GenerarPagareRentaPrimerMes($renta));
        } else {
            $boletaComisionAdministrador = null;
            $boletaRenta = null;
        }

        $userNominado = $personaNominada->user;
        if ($userNominado) {
            $userNominado->notify(new EstadoNominacionActualizado($nominacion));
        }

        $personaAdministradorReferente = $nominacion->idInmueble->idAdministradorReferente;
        if ($personaAdministradorReferente) {
            $userAdministradorReferente = $personaAdministradorReferente->idPersona->user;
            if ($userAdministradorReferente) {
                $userAdministradorReferente->notify(new EstadoNominacionActualizado($nominacion));
            }
        }

        return ['nominacion' => $nominacion, 'boletas' => ['renta' => $boletaRenta, 'comisionAdministrador' => $boletaComisionAdministrador]];
    }

    protected function handlePropietario(NominacionRepository $repository, InmueblePersonaRepository $rInmueblePersona)
    {
        $nominacion = $repository->update($this->nominacion, ['enum_estado' => 'ACEPTADO'])[1];

        $inmueblePersona = $rInmueblePersona->create(['id_persona' => $nominacion->id_persona_nominada, 'id_inmueble' => $nominacion->id_inmueble, 'referente' => '1', 'enum_rol' => 'PROPIETARIO'])[1];

        $personaNominada = $nominacion->idPersonaNominada;
        $userNominado = $personaNominada->user;
        if ($userNominado) {
            $userNominado->notify(new EstadoNominacionActualizado($nominacion));
        }

        $personaAdministradorReferente = $nominacion->idInmueble->idAdministradorReferente;
        if ($personaAdministradorReferente) {
            $userAdministradorReferente = $personaAdministradorReferente->idPersona->user;
            if ($userAdministradorReferente) {
                $userAdministradorReferente->notify(new EstadoNominacionActualizado($nominacion));
            }
        }

        return $nominacion;
    }
}
