<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class sinkron_lrharian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:lrharian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // parameter
        // $awal = '2023-06-02';
        // $akhir = '2023-06-04';

        // $awal = \Carbon\Carbon::yesterday()->format('Y-m-d'); // kemarin
        // $akhir = \Carbon\Carbon::now()->format('Y-m-d'); // hari ini

        $akhir = date("Y-m-d");
        $awal = date('Y-m-d', strtotime($akhir . ' -2 days'));
        // fungsi helper
        $doc = sinkronHarianPembelianDOC($awal, $akhir);
        $ovk = sinkronHarianPembelianOVK($awal, $akhir);
        $pakan = sinkronHarianPembelianPakan($awal, $akhir);
        $keuangan = sinkronPenjualanRealisasiRealtime($awal, $akhir);
        $mutasi_pakan = sinkronMutasiPakan($awal, $akhir);
        $mutasi_doc = sinkronMutasiDOC($awal, $akhir);
        $mutasi_ovk = sinkronMutasiOVK($awal, $akhir);

        // membuat log di storage/app/log/namafile.txt
        return \Illuminate\Support\Facades\Storage::disk('local')
            ->put(
                'log/lrharian_' . time() . '.txt',
                json_encode([
                    'doc' => $doc,
                    'ovk' => $ovk,
                    'pakan' => $pakan,
                    'keuangan' => $keuangan,
                    'mutasi_pakan' => $mutasi_pakan,
                    'mutasi_doc' => $mutasi_doc,
                    'mutasi_ovk' => $mutasi_ovk
                ])
            );
    }
}
