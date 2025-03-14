<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class sinkron_screalisasi_panen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:screalisasi_panen';

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
        getUrlPenjualanRealisasi('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPenjualanRealisasi('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        insertRealisasi(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
    }
}
