<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class sinkron_scpembelian_pakan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:scpembelian_pakan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source pembelian pakan';

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
        DB::statement("TRUNCATE TABLE table_sclog_rekap_pembelian_pakan_temp");
        getUrlRekapPembelianPakan('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianPakan('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        insertRekapPembelianPakan(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
    }
}
