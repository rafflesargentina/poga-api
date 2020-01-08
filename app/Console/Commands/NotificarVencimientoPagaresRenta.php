<?php

namespace Raffles\Console\Commands;

use Raffles\Modules\Poga\Notifications\{ PagareRentaPorVencerDeudor, PagareRentaPorVencerAcreedor };
use Raffles\Modules\Poga\Repositories\PagareRepository;

use Carbon\Carbon;
use GuzzleHttp\Client;
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

        $this->client = new Client(
            [
            'headers' => [
                'apiKey' => env('DEBTS_API_KEY'),
                'Content-Type' => 'application/json'
            ]
            ]
        );
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
                $response = $this->client->get('https://poga-test.base97.com/api/v1/debts/'.$pagarePendiente->id);

                $boleta = json_decode($response->getBody(), true);

                $deudor->notify(new PagareRentaPorVencerDeudor($pagarePendiente, $boleta));
                $acreedor->notify(new PagareRentaPorVencerAcreedor($pagarePendiente, $boleta));
            }
        }
    }
}
