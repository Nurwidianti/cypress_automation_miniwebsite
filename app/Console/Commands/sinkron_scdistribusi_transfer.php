<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class sinkron_scdistribusi_transfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:scdistribusi_transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data source distribusi transfer';

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
        DB::statement("TRUNCATE TABLE tb_dt_mutasi_pakan_temp");
        DB::statement("TRUNCATE TABLE tb_dt_mutasi_ovk_temp");
        DB::statement("TRUNCATE TABLE tb_dt_mutasi_doc_temp");
        DB::statement("TRUNCATE TABLE tb_dt_transfer_pakan_temp");
        DB::statement("TRUNCATE TABLE tb_dt_transfer_ovk_temp");
        DB::statement("TRUNCATE TABLE tb_dt_transfer_doc_temp");
        // DB::statement("TRUNCATE TABLE tb_dt_claim_culling_pakan_temp");
        // DB::statement("TRUNCATE TABLE tb_dt_claim_culling_ovk_temp");
        // DB::statement("TRUNCATE TABLE tb_dt_claim_culling_doc_temp");

        // MUTASI PAKAN
        getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        // MUTASI OVK
        getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        // MUTASI DOC
        getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        // TRANSFER PAKAN
        getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        // TRANSFER OVK
        getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        // TRANSFER DOC
        getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        // // CLAIM_CULLING PAKAN
        // getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // // CLAIM_CULLING OVK
        // getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // // CLAIM_CULLING DOC
        // getUrlDistribusiTransfer('AIL', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('BRU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('BTB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('GPS', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('KLB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('KSM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('LAN', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('LSW', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('MJR', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('MMB', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('MPU', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('MUM', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
        // getUrlDistribusiTransfer('SGA', Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');

        insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'PAKAN');
        insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'OVK');
        insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'MUTASI', 'DOC');
        insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'PAKAN');
        insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'OVK');
        insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'TRANSFER', 'DOC');
        // insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'PAKAN');
        // insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'OVK');
        // insertDistribusiTransfer(Carbon::yesterday()->format('Y-m-d'), Carbon::now()->format('Y-m-d'), 'CLAIM_CULLING', 'DOC');
    }
}
