<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class sinkron_scstok_ayam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:scstok_ayam';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source stok ayam';

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
        DB::statement("TRUNCATE TABLE table_scstok_ayam");
        getUrlStokAyam('AIL');
        getUrlStokAyam('BRU');
        getUrlStokAyam('BTB');
        getUrlStokAyam('GPS');
        getUrlStokAyam('KLB');
        getUrlStokAyam('KSM');
        getUrlStokAyam('LAN');
        getUrlStokAyam('LSW');
        getUrlStokAyam('MJR');
        getUrlStokAyam('MMB');
        getUrlStokAyam('MPU');
        getUrlStokAyam('MUM');
        getUrlStokAyam('SGA');
        insertStokAyam();
    }
}
