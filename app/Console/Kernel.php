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
        $schedule->command('poga:notificar:vencimiento-contratos-renta')->dailyAt('00:00');
        $schedule->command('poga:notificar:vencimiento-contratos-renta')->dailyAt('09:00');
	$schedule->command('poga:renovar-contratos-renta')->dailyAt('01:00');
        $schedule->command('poga:generar:multas')->dailyAt('03:00');
	$schedule->command('poga:generar:pagares')->dailyAt('03:00');
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
