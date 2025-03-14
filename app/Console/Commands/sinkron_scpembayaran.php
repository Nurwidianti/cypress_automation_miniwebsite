<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class sinkron_scpembayaran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:scpembayaran';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source pembayaran';

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
        DB::statement("TRUNCATE TABLE table_penjualan_realisasi_temp");
        getUrlPembayaranBakul('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPembayaranBakul('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        insertPembayaranBakul(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
    }
}
