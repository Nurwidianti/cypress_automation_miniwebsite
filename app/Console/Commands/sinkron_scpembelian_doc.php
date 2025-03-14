<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class sinkron_scpembelian_doc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:scpembelian_doc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source pembelian doc';

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
        DB::statement("TRUNCATE TABLE table_sclog_rekap_pembelian_doc_temp");
        getUrlRekapPembelianDoc('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlRekapPembelianDoc('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        insertRekapPembelianDoc(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
    }
}
