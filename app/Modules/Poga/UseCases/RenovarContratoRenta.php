<?php

namespace Raffles\Modules\Poga\UseCases;

use Raffles\Modules\Poga\Models\Renta;
use Raffles\Modules\Poga\Repositories\RentaRepository;
use Raffles\Modules\Poga\Notifications\{ RentaRenovadaAdministradorReferente, RentaRenovadaInquilinoReferente, RentaRenovadaPropietarioReferente };

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;

class RenovarContratoRenta
{
    use DispatchesJobs;
    
    /**
     * The Renta model.
     *
     * @var Renta
     */
    protected $renta;

    /**
     * The form data.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @param Renta $renta The Renta model.
     * @param array $data  The form data.
     *
     * @return void
     */
    public function __construct(Renta $renta, $data)
    {
        $this->renta = $renta;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param RentaRepository $repository The RentaRepository object.
     *
     * @return void
     */
    public function handle(RentaRepository $repository)
    {
        $data = $this->data;
	$renta = $this->renta;

	$now = Carbon::now();

	$vencimiento = $renta->fecha_fin->toDateString();
        $inquilinoReferente = $renta->idInquilino->user;
	$propietarioReferente = $renta->idInmueble->idPropietarioReferente->idPersona->user;

	if ($renta->renovacion === 'AUTOMATICA') {
            if ($renta->fecha_fin->toDateString() < $now->toDateString()) {
                $rentaRenovada = $repository->create([
                    'comision_administrador' => $renta->comision_administrador,
                    'comision_inmobiliaria' => $renta->comision_inmobiliaria,
                    'dia_mes_pago' => $renta->dia_mes_pago,
                    'dias_notificacion_previa_renovacion' => $renta->dias_notificacion_previa_renovacion,
                    'dias_multa' => $renta->dias_multa,
		    'enum_estado' => 'ACTIVO',
		    'expensas' => $renta->expensas,
                    'fecha_fin' => $renta->fecha_fin->addYear(),
                    'fecha_inicio' => $renta->fecha_fin->addDay(),
                    'garantia' => $renta->garantia,
                    'id_inmueble' => $renta->id_inmueble,
                    'id_inquilino' => $renta->id_inquilino,
                    'id_moneda' => $renta->id_moneda,
                    'id_renta_padre' => $renta->id,
                    'monto' => $renta->monto,
                    'monto_multa_dia' => $renta->monto_multa_dia,
                    'multa' => $renta->multa,
	        ])[1];
	    }
	} elseif ($renta->renovacion === 'MANUAL') {
            if (count($repository->findWhere(['id_renta_padre' => $renta->id])) === 0) {
                $rentaRenovada = $repository->create([
                    'dia_mes_pago' => $data['dia_mes_pago'],
                    'dias_notificacion_previa_renovacion' => $data['dias_notificacion_previa_renovacion'],
                    'dias_multa' => $data['dias_multa'],
		    'enum_estado' => 'PENDIENTE',
		    'expensas' => $renta->expensas,
                    'fecha_fin' => $data['fecha_fin'],
                    'fecha_inicio' => $data['fecha_inicio'],
                    'garantia' => $renta->garantia,
                    'id_inmueble' => $renta->id_inmueble,
                    'id_inquilino' => $renta->id_inquilino,
                    'id_moneda' => $data['id_moneda'],
                    'id_renta_padre' => $renta->id,
                    'monto' => $data['monto'],
                    'monto_multa_dia' => $data['monto_multa_dia'],
		    'multa' => $data['multa'],
	        ])[1];
	    }
	}

	$inquilinoReferente->notify(new RentaRenovadaInquilinoReferente(isset($rentaRenovada) ? $rentaRenovada : $renta));
        $propietarioReferente->notify(new RentaRenovadaPropietarioReferente(isset($rentaRenovada) ? $rentaRenovada : $renta));

	return isset($rentaRenovada) ? $rentaRenovada : $renta;
    }
}
