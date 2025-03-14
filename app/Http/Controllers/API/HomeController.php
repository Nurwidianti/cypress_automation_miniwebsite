<?php
namespace App\Http\Controllers\API;

use App\Helper\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function kpi(){
        $kp_terbaik = DB::select("SELECT a.nama, b.nik, REPLACE(CONCAT('http://mis.ptmjl.co.id/assets/img/users/',b.nik,'_',a.nama,'.png'), ' ', '%20' ) AS foto FROM kpi_kp a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT BAIK' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_kp) 
                    ORDER BY a.totskor DESC");

        $kp_terburuk = DB::select("SELECT a.nama, b.nik, REPLACE(CONCAT('http://mis.ptmjl.co.id/assets/img/users/',b.nik,'_',a.nama,'.png'), ' ', '%20' ) AS foto FROM kpi_kp a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT KURANG' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_kp) 
                    ORDER BY a.totskor DESC");

        $ts_terbaik = DB::select("SELECT a.nama, b.nik, REPLACE(CONCAT('http://mis.ptmjl.co.id/assets/img/users/',b.nik,'_',a.nama,'.png'), ' ', '%20' ) AS foto FROM kpi_ts a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT BAIK' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_ts)
                    ORDER BY a.totskor DESC");

        $ts_terburuk = DB::select("SELECT a.nama, b.nik, REPLACE(CONCAT('http://mis.ptmjl.co.id/assets/img/users/',b.nik,'_',a.nama,'.png'), ' ', '%20' ) AS foto FROM kpi_ts a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT KURANG' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_ts)
                    ORDER BY a.totskor DESC");
        
        $output = collect([
                    'kp_terbaik' => $kp_terbaik,
                    'kp_terburuk' => $kp_terburuk,
                    'ts_terbaik' => $ts_terbaik,
                    'ts_terburuk' => $ts_terburuk,
                ]);

        return ResponseFormatter::success($output,'Data berhasil diambil');
    }

    
}
