<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Pagare;
use Raffles\Modules\Poga\Notifications\EstadoPagareActualizado;
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Illuminate\Foundation\Bus\DispatchesJobs;

class AnularPagare
{
    use DispatchesJobs;

    /**
     * The Pagare model.
     *
     * @var Pagare $pagare
     */
    protected $pagare;

    /**
     * Create a new job instance.
     *
     * @param Pagare  $pagare The Pagare model.
     *
     * @return void
     */
    public function __construct(Pagare $pagare)
    {
        $this->pagare = $pagare;
    }

    /**
     * Execute the job.
     *
     * @param  PagareRepository $repository The PagareRepository object.
     *
     * @return void
     */
    public function handle(PagareRepository $repository)
    {
	$inmueble = $this->pagare->idInmueble;

        $data = [
            "debt" => [
                "objectStatus" => [
                    "status" => "canceled"
                ]
            ]
        ];

	try {
	    $boleta = $this->dispatchNow(new ActualizarBoletaPago($this->pagare->id, $data));
	} catch (\Exception $e) {
            $boleta = null;
	}

        $pagare = $this->dispatchNow(new ActualizarEstadoPagare($this->pagare, "ANULADO"));

	$administradorReferente = $inmueble->idAdministradorReferente;
	if ($administradorReferente) {
	    $personaAdministradorReferente = $administradorReferente->idPersona;
            if ($personaAdministradorReferente) {
	        $usuarioAdministradorReferente = $personaAdministradorReferente->user;
		if ($usuarioAdministradorReferente) {
                    $usuarioAdministradorReferente->notify(new EstadoPagareActulizado($pagare));
		}
	    }
	}

	$personaAcreedora = $pagare->idPersonaAcreedora;
	if ($personaAcreedora) {
	    $usuarioAcreedor = $personaAcreedora->user;
	    if ($usuarioAcreedor) {
	        $usuarioAcreedor->notify(new EstadoPagareActualizado($pagare));
	    }
	}

	$personaDeudora = $pagare->idPersonaDeudora;
        if ($personaDeudora) {
	    $usuarioDeudor = $personaDeudora->user;
	    if ($usuarioDeudor) {
	        $usuarioDeudor->notify(new EstadoPagareActualizado($pagare));
	    }
	}

        return ['pagare' => $pagare, 'boleta' => $boleta];
    }   
}
