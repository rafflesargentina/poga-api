<?php

namespace Raffles\Console\Commands;

use Raffles\Modules\Poga\Notifications\{ PagareRentaPorVencerDeudor, PagareRentaPorVencerAcreedor };
use Raffles\Modules\Poga\Repositories\PagareRepository;
use Raffles\Modules\Poga\UseCases\TraerBoletaPago;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class NotificarVencimientoPagaresRenta extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poga:notificar:vencimiento-pagares-renta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificar vencimiento de pagares de renta.';

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
    public function handle(PagareRepository $repository)
    {
        $pagaresPendientes = $repository->findWhere(['enum_estado' => 'PENDIENTE', 'enum_clasificacion_pagare' => 'RENTA']);

        $now = Carbon::now();

        foreach ($pagaresPendientes as $pagarePendiente) {
            $vencimiento = Carbon::parse($pagarePendiente->fecha_vencimiento)->toDateString();
            $deudor = $pagarePendiente->idPersonaDeudora->user;
            $acreedor = $pagarePendiente->idPersonaAcreedora->user;
            $diasDesdeHoy = $now->copy()->addDays(2)->toDateString();

            if ($diasDesdeHoy === $vencimiento) {
                $uc = new TraerBoletaPago($pagarePendiente->id);
                $boleta = $uc->handle();

                $deudor->notify(new PagareRentaPorVencerDeudor($pagarePendiente, $boleta));
                $acreedor->notify(new PagareRentaPorVencerAcreedor($pagarePendiente, $boleta));
            }
        }
    }
}
