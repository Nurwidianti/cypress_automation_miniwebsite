<?php

namespace App\Console;

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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {


        $schedule->command('sinkron:scpembayaran')->cron('00 11,23 * * *');
        $schedule->command('sinkron:scstok_ayam')->cron('10 11,23 * * *');
        $schedule->command('sinkron:screalisasi_panen')->cron('20 11,23 * * *');
        $schedule->command('sinkron:scflok_harian')->cron('30 11,23 * * *');
        $schedule->command('sinkron:schpp')->cron('40 11,23 * * *');
        $schedule->command('sinkron:scpembelian_doc')->cron('50 11,23 * * *');
        $schedule->command('sinkron:scpembelian_pakan')->cron('00 12,00 * * *');
        $schedule->command('sinkron:scpembelian_ovk')->cron('10 12,00 * * *');
        $schedule->command('sinkron:scrhpp_api')->cron('20 12,00 * * *');
        $schedule->command('sinkron:scdistribusi_transfer')->cron('30 12,00 * * *');

        $schedule->command('sinkron:saldobankbca')->cron('30 06,00 * * *');

        // yaqin start
        $schedule->command('sinkron:lrharian')->cron('15 12 * * *'); // jam 14 menit 43
        $schedule->command('sinkron:lrharian')->cron('07 13 * * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
