<?php

namespace Raffles\Console\Commands;

use Raffles\Modules\Poga\Models\Renta;
use Raffles\Modules\Poga\Repositories\RentaRepository;
use Raffles\Modules\Poga\Notifications\{ RentaRenovadaInquilinoReferente, RentaRenovadaPropietarioReferente };
use Raffles\Modules\Poga\UseCases\{ RenovarContratoRenta, GenerarPagareRenta };

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class RenovarContratosRenta extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poga:renovar-contratos-renta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renovar contratos de renta vencidos.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(RentaRepository $repository)
    {
        $now = Carbon::now();

        $rentasActivas = $repository->findWhere(['enum_estado' => 'ACTIVO']);

        foreach ($rentasActivas as $rentaActiva) {
            $vencimiento = $rentaActiva->fecha_fin->toDateString();
            $inquilinoReferente = $rentaActiva->idInquilino->user;
            $propietarioReferente = $rentaActiva->idInmueble->idPropietarioReferente->idPersona->user;

            if ($vencimiento === $now->copy()->subDay()->toDateString()) {
                if ($rentaActiva->renovacion === 'NO_RENOVAR') {
                    $estado = 'PENDIENTE_FINALIZACION';
                } else {
                    $estado = 'RENOVADO';
                }

                $rentaFinalizada = $repository->update($rentaActiva, ['enum_estado' => $estado])[1];
                $rentaRenovada = $this->dispatchNow(new RenovarContratoRenta($rentaFinalizada, []));

                $inquilinoReferente->notify(new RentaRenovadaInquilinoReferente($rentaRenovada));
                $propietarioReferente->notify(new RentaRenovadaPropietarioReferente($rentaRenovada));
            }
        }

        $rentasPendientes = $repository->findWhere(['enum_estado' => 'PENDIENTE', ['id_renta_padre', '!=', null]]);

        foreach ($rentasPendientes as $rentaPendiente) {
            $inicio = $rentaPendiente->fecha_inicio->toDateString();
            $inquilinoReferente = $rentaPendiente->idInquilino->user;
            $propietarioReferente = $rentaPendiente->idInmueble->idPropietarioReferente->idPersona->user;

            if ($inicio === $now->copy()->toDateString()) {
                if ($rentaPendiente->id_renta_padre) {
                    $rentaPendiente = $repository->update($rentaPendiente, ['enum_estado' => 'ACTIVO'])[1];
                    $inquilinoReferente->notify(new RentaRenovadaInquilinoReferente($rentaPendiente));
                    $propietarioReferente->notify(new RentaRenovadaPropietarioReferente($rentaPendiente));
                }
            }
        }
    }
}
