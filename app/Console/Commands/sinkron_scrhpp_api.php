<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class sinkron_scrhpp_api extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:scrhpp_api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source RHPP API';

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
        DB::statement("TRUNCATE TABLE tb_performa_produksi_temp");
        DB::statement("TRUNCATE TABLE tb_performa_usaha_unit_temp");
        getUrlPerformaUsahaUnit('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaUsahaUnit('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        getUrlPerformaProduksi('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        insertPerformaUsahaUnit(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        insertPerformaProduksi(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        insertRHPP(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        return 0;
    }
}
