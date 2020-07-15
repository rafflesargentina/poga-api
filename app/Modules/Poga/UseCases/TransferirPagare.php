<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\{ Pagare, User };
use Raffles\Modules\Poga\Mail\{ PagoTransferidoAcreedor, PagoTransferidoParaAdminPoga };
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Mail;

class TransferirPagare
{
    use DispatchesJobs;

    /**
     * The form data and The Pagare model.
     *
     * @var array
     * @var Pagare
     */
    protected $data, $pagare;

    /**
     * Create a new job instance.
     *
     * @param Pagare  $pagare The Pagare model.
     * @param array   $data   The form data.
     *
     * @return void
     */
    public function __construct(Pagare $pagare, $data)
    {
	$this->data = $data;
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

	$pagareHijo = $repository->where('enum_clasificacion_pagare', 'COMISION_POGA')->where('id_tabla', $this->pagare->id)->first();
	$repository->update($pagareHijo, ['fecha_pago_confirmado' => Carbon::today(), 'enum_estado' => 'TRANSFERIDO']);

        $pagare = $repository->update($this->pagare, ['descripcion' => $this->data['descripcion'], 'fecha_pago_confirmado' => Carbon::today(), 'enum_estado' => 'TRANSFERIDO', 'nro_comprobante' => $this->data['nro_comprobante']])[1];

	$personaAcreedora = $pagare->idPersonaAcreedora;
	if ($personaAcreedora) {
	    $usuarioAcreedor = $personaAcreedora->user;
	    if ($usuarioAcreedor) {
                Mail::to($usuarioAcreedor->email)->send(new PagoTransferidoAcreedor($pagareHijo, $usuarioAcreedor));
	    }
	}

        $adminPoga = User::where('email', env('MAIL_ADMIN_ADDRESS'))->first();
        if ($adminPoga) {
            Mail::to($adminPoga->email)->send(new PagoTransferidoParaAdminPoga($pagareHijo, $adminPoga));
        }

        return $pagare;
    }   
}
