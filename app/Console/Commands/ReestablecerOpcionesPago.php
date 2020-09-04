<?php

namespace Raffles\Console\Commands;

use Raffles\Modules\Poga\Models\Pagare;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\{ ActualizarBoletaPago, TraerBoletaPago };

use Illuminate\Console\Command;

class ReestablecerOpcionesPago extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poga:reestablecer:opciones-pago';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reestablece la opción de pago a TOTAL para todas las pagos pendientes del tipo RENTA u OTRO.';

    /**
     * Create a new command instance.
     *
     * @param PagareRepository $repository The PagareRepository object.
     *
     * @return void
     */
    public function __construct(PagareRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	$pagares = $this->repository->whereIn('enum_clasificacion_pagare', ['RENTA','OTRO'])->where('enum_estado', 'PENDIENTE')->where('enum_opcion_pago', '!=', 'TOTAL')->get();

	foreach($pagares as $pagare) {
            $uc = new TraerBoletaPago($pagare->id);
            $boleta = $uc->handle();

            $this->actualizarBoletaPago($boleta, $pagare);
        }
    }

    /**
     * Actualizar Boleta de Pago.
     *
     * @param array  $boleta
     * @param Pagare $pagareMulta
     *
     * @return void
     */
    protected function actualizarBoletaPago(array $boleta, Pagare $pagare)
    {
        $data = $boleta;

	$debt = ['debt' => Arr::only($data['debt'], ['amount', 'description', 'label', 'validPeriod'])];
        $debt['debt']['amount']['value'] = $pagare->monto;

        $uc = new ActualizarBoletaPago($boleta['debt']['docId'], $debt);
        $uc->handle();
    }
}
