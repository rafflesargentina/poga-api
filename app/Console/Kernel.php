<?php

namespace Raffles\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('poga:reestablecer:opciones-pago')->dailyAt('00:00');
        $schedule->command('poga:renovar-contratos-renta')->dailyAt('01:00');
        $schedule->command('poga:generar:pagares')->dailyAt('02:00');
        $schedule->command('poga:generar:multas')->dailyAt('03:00');
        $schedule->command('poga:notificar:vencimiento-contratos-renta')->dailyAt('07:00');
        $schedule->command('poga:notificar:vencimiento-pagares-renta')->dailyAt('08:00');
        $schedule->command('poga:verificar:escritura-logs')->dailyAt('08:30');

        $schedule->command('poga:procesar:comprobantes-transferencia')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        include base_path('routes/console.php');
    }
}
