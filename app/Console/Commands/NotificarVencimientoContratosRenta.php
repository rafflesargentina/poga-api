<?php

namespace Raffles\Console\Commands;

use Raffles\Modules\Poga\Notifications\{ RentaPorFinalizarInquilinoReferente, RentaPorFinalizarPropietarioReferente };
use Raffles\Modules\Poga\Repositories\RentaRepository;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NotificarVencimientoContratosRenta extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poga:notificar:vencimiento-contratos-renta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificar vencimiento de contratos de renta.';

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
        $rentasActivas = $repository->findWhere(['enum_estado' => 'ACTIVO']);

        $now = Carbon::now();

        foreach ($rentasActivas as $rentaActiva) {
            $vencimiento = $rentaActiva->fecha_fin->toDateString();
            $inquilinoReferente = $rentaActiva->idInquilino->user;
            $propietarioReferente = $rentaActiva->idInmueble->idPropietarioReferente->idPersona->user;

            if ($rentaActiva->dias_notificacion_previa_renovacion) {
                $diasDesdeHoy = $now->copy()->addDays($rentaActiva->dias_notificacion_previa_renovacion)->toDateString();
                if ($diasDesdeHoy === $vencimiento) {
                    $inquilinoReferente->notify(new RentaPorFinalizarInquilinoReferente($rentaActiva));
                    $propietarioReferente->notify(new RentaPorFinalizarPropietarioReferente($rentaActiva));
                }
            }
        }
    }
}
