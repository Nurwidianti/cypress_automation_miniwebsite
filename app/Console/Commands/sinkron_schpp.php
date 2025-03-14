<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class sinkron_schpp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:schpp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source hpp';

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
     * @return int
     */
    public function handle()
    {
        getUrlHpp('AIL');
        getUrlHpp('BRU');
        getUrlHpp('BTB');
        getUrlHpp('GPS');
        getUrlHpp('KLB');
        getUrlHpp('KSM');
        getUrlHpp('LAN');
        getUrlHpp('LSW');
        getUrlHpp('MJR');
        getUrlHpp('MMB');
        getUrlHpp('MPU');
        getUrlHpp('MUM');
        getUrlHpp('SGA');
    }
}
