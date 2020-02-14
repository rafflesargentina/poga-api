<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Pagare;
use Raffles\Modules\Poga\Notifications\EstadoPagareActualizado;
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\TraerBoletaPago;

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
        $pagare = $this->pagare;
        $inmueble = $pagare->idInmueble;

	if ($pagare->idRenta->enum_estado === 'FINALIZADO') {
            $data = [
                'debt' => [
                    'objStatus' => [
                        'status' => 'inactive',
                    ]
                ]
	    ];

            switch ($pagare->enum_clasificacion_pagare) {
	        case 'RENTA':
                    $uc = new TraerBoletaPago($pagare->id);	
		    $boleta = $uc->handle();
		    $items = $boleta['debt']['description']['items'];

		    foreach($items as $item) {
			$p = $repository->find($item['code']);
                        $this->dispatchNow(new ActualizarEstadoPagare($p, 'ANULADO'));
		    }

                    $pagare = $this->dispatchNow(new ActualizarEstadoPagare($pagare, 'ANULADO'));
                break;
		case 'OTRO':
                    $pagare = $this->dispatchNow(new ActualizarEstadoPagare($pagare, 'ANULADO'));
		break;
	    }
	} elseif ($pagare->idRenta->enum_estado === 'ACTIVO') {
	    switch ($pagare->enum_clasificacion_pagare) {
                case 'RENTA':
                    $data = [
                        'debt' => [
                            'objStatus' => [
                                'status' => 'active',
                            ]
                        ]
                    ];

                    $uc = new TraerBoletaPago($pagare->id);
                    $boleta = $uc->handle();		
		    $items = $boleta['debt']['description']['items'];

		    // Arroja el key del item del array.
		    $multaExistente = array_search('Multa por atraso', array_column($items, 'label'));
		    if ($multaExistente) {
			$amountCurrency = $boleta['debt']['amount']['currency'];
			$amountValue = $boleta['debt']['amount']['value'];
                        $summary = $boleta['debt']['description']['summary'];

			// Descuenta del total de la boleta el monto de la multa.
			$data['debt']['amount']['currency'] = $amountCurrency;
			$data['debt']['amount']['value'] = intVal($amountValue) - intVal($items[$multaExistente]['amount']['value']);

			$data['debt']['description'] = $boleta['debt']['description'];

			// Modifica el summary y text de la boleta para remover ", con multa por atraso."
			$data['debt']['description']['summary'] = str_replace(', con multa por atraso.', '.', $summary);
			$data['debt']['description']['text'] = $data['debt']['description']['summary'];
			
			// Borra el item de multa.
			unset($data['debt']['description']['items'][$multaExistente]);

			$p = $repository->find($items[$multaExistente]['code']);
                        $this->dispatchNow(new ActualizarEstadoPagare($p, 'ANULADO'));
		    }
	        break;
		case 'OTRO':
                    $data = [
                        'debt' => [
                            'objStatus' => [
                                'status' => 'inactive',
                            ]
                        ]
	            ];

                    $pagare = $this->dispatchNow(new ActualizarEstadoPagare($pagare, 'ANULADO'));
		break;
	    }
        } else {
            $uc = new TraerBoletaPago($pagare->id);
            $boleta = $uc->handle();
	}

	$boleta = $this->dispatchNow(new ActualizarBoletaPago($pagare->id, $data));

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
