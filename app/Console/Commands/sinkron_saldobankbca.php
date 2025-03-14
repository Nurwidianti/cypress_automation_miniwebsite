<?php

namespace App\Console\Commands;

use App\Models\MasterRekeningBukuBank;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class sinkron_saldobankbca extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinkron:saldobankbca';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi saldo bank bca all unit';

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
        $tanggalKemarin = Carbon::yesterday()->toDateString('Y-m-d');
        $nomorRekeningList = MasterRekeningBukuBank::select('nomor', 'unit')->where('namabank', 'BCA')->get();

        DB::statement("TRUNCATE TABLE tbl_saldo_bank_bca_temp");

        foreach ($nomorRekeningList as $rekening) {
            $nomor = $rekening->nomor;
            $unit = $rekening->unit;
            try {
                getUrlSaldoBankBca(toAp($unit), $unit, $tanggalKemarin, $nomor);
                echo "Saldo berhasil diambil untuk nomor rekening $nomor pada tanggal $tanggalKemarin\n";
                Log::info("Saldo berhasil diambil untuk nomor rekening $nomor pada tanggal $tanggalKemarin");
            } catch (\Exception $e) {
                echo "Terjadi kesalahan saat mengambil saldo untuk nomor rekening $nomor: ".$e->getMessage()."\n";
                Log::error("Terjadi kesalahan saat mengambil saldo untuk nomor rekening $nomor: ".$e->getMessage());
            }
        }

        insertSaldoBankBca($tanggalKemarin);
    }
}
