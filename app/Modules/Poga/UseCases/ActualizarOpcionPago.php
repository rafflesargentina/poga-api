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

class ActualizarOpcionPago implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The Pagare model and the data.
     *
     * @var Pagare
     * @var array
     */
    protected $pagare, $data;

    public function __construct(Pagare $pagare, array $data)
    {
        $this->pagare = $pagare;
        $this->data = $data;
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
	$data = $this->data;
	$opcionPago = $data['enum_opcion_pago'];
	$p = $this->pagare;

	switch($data['enum_opcion_pago']) {
        case 'MANUAL':
            $monto = $data['monto_manual'];
            $pagare = $repository->update($p, ['enum_opcion_pago' => $opcionPago, 'monto' => $monto])[1];
        break;
	case 'MINIMO':
	    $monto = $p->monto * env('PORC_PAGO_MINIMO') / 100;	
            $pagare = $repository->update($p, ['enum_opcion_pago' => $opcionPago, 'monto' => $monto, 'monto_manual' => 0])[1];
	break;
	case 'TOTAL':
            $pagare = $repository->update($p, ['enum_opcion_pago' => $opcionPago, 'monto_manual' => 0]);
	break;
	}

	$uc = new TraerBoletaPago($p->id);
	$boleta = $uc->handle();

        $boleta = $this->actualizarBoletaPago($boleta, $pagare);

	return ['pagare' => $pagare, 'boleta' => $boleta];
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
        return $uc->handle();
    }
}
