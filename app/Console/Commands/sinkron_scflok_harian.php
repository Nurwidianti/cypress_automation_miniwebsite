<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class sinkron_scflok_harian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:scflok_harian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source flok harian';

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
        getUrlProduksiDataHarian('AIL');
        getUrlProduksiDataHarian('BRU');
        getUrlProduksiDataHarian('BTB');
        getUrlProduksiDataHarian('GPS');
        getUrlProduksiDataHarian('KLB');
        getUrlProduksiDataHarian('KSM');
        getUrlProduksiDataHarian('LAN');
        getUrlProduksiDataHarian('LSW');
        getUrlProduksiDataHarian('MJR');
        getUrlProduksiDataHarian('MMB');
        getUrlProduksiDataHarian('MPU');
        getUrlProduksiDataHarian('MUM');
        getUrlProduksiDataHarian('SGA');
        insertProduksiDataHarian();
    }
}
