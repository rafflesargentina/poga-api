<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\Models\Pagare;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ReestablecerOpcionesPago implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @param PagareRepository $repository The PagareRepository object.
     *
     * @return void
     */
    public function handle(PagareRepository $repository)
    {
	$pagares = $repository->whereIn('enum_clasificacion_pagare', ['RENTA','OTRO'])->where('enum_estado', 'PENDIENTE')->where('enum_opcion_pago', '!=', 'TOTAL')->get();

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
