<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Rhpp;
use App\Imports\RhppImport;

class KpiMasterController extends Controller{
    
    public function index(Request $request){
        $nav = $request->input('nav');
        if($nav=='ts'){
            $nav1='';
            $nav2='active';
            $nav3='';
        }elseif($nav=='kp'){
            $nav1='';
            $nav2='';
            $nav3='active';
        }else{
            $nav1='active';
            $nav2='';
            $nav3='';
        }
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $tglawal = $tahun.'-01-01';
        if(!empty($tahun) && !empty($bulan)){
            $tglakhir = $tahun . '-' . $bulan . '-' . endTgl($bulan,$tahun);
        }else{
            $tglakhir = $tahun.'-12-31';
        }
     
        $bln = DB::select('SELECT kode, bulan FROM bulan ORDER BY kode+1 ASC');
        $thn = DB::select('SELECT tahun FROM tahun ORDER BY id+1 ASC');

        $nots = 1;
        $sqlTs = DB::select("SELECT a.namappl AS nama, b.unit, a.area, b.ap AS region,
                                ROUND((SUM(a.cokg)/SUM(a.coekor)),1) As bw,
                                ROUND((SUM(a.feedkgqty)/SUM(a.cokg)),1) as fcr,
                                ROUND((SUM(a.rmsbantudpls)/SUM(a.ciawal)),1) as dpls,
                                ROUND((SUM(a.rmsbantuumur)/SUM(a.ciawal)),1) as umur,
                                ROUND((SUM(a.nomrhpprugi)/SUM(a.ciawal)),0) as rugi,
                                ROUND((SUM(CASE WHEN a.rhpprugiproduksi>0 THEN 1 ELSE 0 END )/SUM(CASE WHEN a.ciawal>0 THEN 1 ELSE 0 END))*100,0) as frqrugi,
                                ROUND((SUM(a.rmsbantulabarugi)/SUM(cokg)),0) as margin,
                                ROUND(((((100-(SUM(a.rmsbantudpls)/SUM(a.ciawal)))*(100*(SUM(a.cokg)/SUM(a.coekor))))/(SUM(a.feedkgqty)/SUM(a.cokg)))/(SUM(a.rmsbantuumur)/SUM(a.ciawal))),0) AS ip
                            FROM table_rhpp a
                            LEFT JOIN (SELECT unit, ap, jabatanlengkap, nama FROM table_hr_demografi WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun') b ON b.nama = a.namappl
                            LEFT JOIN regions c ON c.koderegion = b.ap
                            WHERE b.jabatanlengkap = 'TECHNICAL SUPPORT' AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                            GROUP BY a.namappl ASC");

        $itemsTs = array();
        foreach ($sqlTs as $data) {
            array_push($itemsTs, ['',
                $nama = $data->nama,
                $unit = $data->unit,
                $reg = $data->region,
                $area = $data->area,
                $rugi = $data->rugi,
                $skor_rugi = rugipro($rugi),
                $frqrugi = $data->frqrugi,
                $skor_frqrugi = frqrugi($frqrugi),
                $diffmargin = valarea($data->margin, $area, $tglawal, $tglakhir, 'TECHNICAL SUPPORT'),
                $skor_diffmargin = skormargin($diffmargin),
                $ip = $data->ip,
                $skor_ip = skorip($ip),
                $total_skor = totSkorTs($skor_rugi, $skor_frqrugi, $skor_diffmargin, $skor_ip),
                $skor_2021 = skor_last($nama, '2021'),
                $skor_final = ($total_skor == 0 ? 0 : round($total_skor, 2)),
                $keterangan = ket($skor_final)]);
        }
        array_multisort(array_column($itemsTs, 13), SORT_DESC, $itemsTs);
    
        $nokp = 1;
        $sqlKp = DB::select("SELECT a.namakaprod AS nama, b.unit, a.area, b.ap AS region,
                                ROUND((SUM(a.cokg)/SUM(a.coekor)),1) As bw,
                                ROUND((SUM(a.feedkgqty)/SUM(a.cokg)),1) as fcr,
                                ROUND((SUM(a.rmsbantudpls)/SUM(a.ciawal)),1) as dpls,
                                ROUND((SUM(a.rmsbantuumur)/SUM(a.ciawal)),1) as umur,
                                ROUND((SUM(a.nomrhpprugi)/SUM(a.ciawal)),0) as rugi,
                                ROUND((SUM(CASE WHEN a.rhpprugiproduksi>0 THEN 1 ELSE 0 END )/SUM(CASE WHEN a.ciawal>0 THEN 1 ELSE 0 END))*100,0) as frqrugi,
                                ROUND((SUM(a.rmsbantulabarugi)/SUM(cokg)),0) as margin,
                                ROUND(((((100-(SUM(a.rmsbantudpls)/SUM(a.ciawal)))*(100*(SUM(a.cokg)/SUM(a.coekor))))/(SUM(a.feedkgqty)/SUM(a.cokg)))/(SUM(a.rmsbantuumur)/SUM(a.ciawal))),0) AS ip
                            FROM table_rhpp a
                            LEFT JOIN (SELECT unit, ap, jabatanlengkap, nama FROM table_hr_demografi WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun') b ON b.nama = a.namakaprod
                            LEFT JOIN regions c ON c.koderegion = b.ap
                            WHERE b.jabatanlengkap = 'KEPALA PRODUKSI' AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                            GROUP BY a.namakaprod ASC");
            
        $itemsKp = array();
        foreach($sqlKp as $data){
                array_push($itemsKp, ['', 
                    $nama = $data->nama,
                    $unit = $data->unit,
                    $reg = $data->region,
                    $area = $data->area,
                    $rugi = $data->rugi,
                    $skor_rugi = rugipro($rugi),
                    $frqrugi = $data->frqrugi,
                    $skor_frqrugi = frqrugi($frqrugi),
                    $diffmargin = valarea($data->margin, $area, $tglawal, $tglakhir, 'KEPALA PRODUKSI'),
                    $skor_diffmargin = skormargin($diffmargin),
                    $ip = $data->ip,
                    $skor_ip = skorip($ip),
                    $total_skor = totskor($skor_rugi, $skor_frqrugi, $skor_diffmargin, $skor_ip),
                    $skor_2021 = skor_last($nama, '2021'),            
                    $skor_final = ($total_skor == 0 ? 0 : round($total_skor,2)),
                    $keterangan = ket($skor_final),
                ]);           
        }
        array_multisort(array_column($itemsKp, 13), SORT_DESC, $itemsKp);

        $norhpp = 1;
        $sqlrhpp = DB::select("SELECT UPPER(DATE_FORMAT(tgldocfinal,'%M %Y')) AS tanggal, 
                                DATE_FORMAT(tgldocfinal,'%Y%m') AS thnbln,
                                COUNT(id) AS record FROM table_rhpp
                                GROUP BY DATE_FORMAT(tgldocfinal,'%Y%m')+1 DESC");
        $jmlrhpp = Rhpp::count();

        

        return view('dashboard.master.kpi', compact('nots','itemsTs','nokp','itemsKp','norhpp','sqlrhpp','jmlrhpp',
                                                    'nav1','nav2','nav3',
                                                    'bulan','tahun','bln','thn','tglakhir'));
    }
  

    public function createTs($tahun, $bulan){
        $tglawal = $tahun.'-01-01';
        $tglakhir = $tahun . '-' . $bulan . '-' . endTgl($bulan,$tahun);
        $tglcreate = $tahun . '-' . $bulan . '-01';

        $bln = DB::select('SELECT kode, bulan FROM bulan ORDER BY kode+1 ASC');
        $thn = DB::select('SELECT tahun FROM tahun ORDER BY id+1 ASC');

        $no = 1;
        $del = DB::table('kpi_ts')->where('tanggal', $tglcreate)->delete();
        if($del){
            $sqlTs = DB::select("SELECT a.namappl AS nama, b.unit, a.area, b.ap AS region,
                                    ROUND((SUM(a.cokg)/SUM(a.coekor)),1) As bw,
                                    ROUND((SUM(a.feedkgqty)/SUM(a.cokg)),1) as fcr,
                                    ROUND((SUM(a.rmsbantudpls)/SUM(a.ciawal)),1) as dpls,
                                    ROUND((SUM(a.rmsbantuumur)/SUM(a.ciawal)),1) as umur,
                                    ROUND((SUM(a.nomrhpprugi)/SUM(a.ciawal)),0) as rugi,
                                    ROUND((SUM(CASE WHEN a.rhpprugiproduksi>0 THEN 1 ELSE 0 END )/SUM(CASE WHEN a.ciawal>0 THEN 1 ELSE 0 END))*100,0) as frqrugi,
                                    ROUND((SUM(a.rmsbantulabarugi)/SUM(cokg)),0) as margin,
                                    ROUND(((((100-(SUM(a.rmsbantudpls)/SUM(a.ciawal)))*(100*(SUM(a.cokg)/SUM(a.coekor))))/(SUM(a.feedkgqty)/SUM(a.cokg)))/(SUM(a.rmsbantuumur)/SUM(a.ciawal))),0) AS ip
                                FROM table_rhpp a
                                LEFT JOIN (SELECT unit, ap, jabatanlengkap, nama FROM table_hr_demografi WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun') b ON b.nama = a.namappl
                                LEFT JOIN regions c ON c.koderegion = b.ap
                                WHERE b.jabatanlengkap = 'TECHNICAL SUPPORT' AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                                GROUP BY a.namappl ASC");
        }else{
            $sqlTs = DB::select("SELECT a.namappl AS nama, b.unit, a.area, b.ap AS region,
                                    ROUND((SUM(a.cokg)/SUM(a.coekor)),1) As bw,
                                    ROUND((SUM(a.feedkgqty)/SUM(a.cokg)),1) as fcr,
                                    ROUND((SUM(a.rmsbantudpls)/SUM(a.ciawal)),1) as dpls,
                                    ROUND((SUM(a.rmsbantuumur)/SUM(a.ciawal)),1) as umur,
                                    ROUND((SUM(a.nomrhpprugi)/SUM(a.ciawal)),0) as rugi,
                                    ROUND((SUM(CASE WHEN a.rhpprugiproduksi>0 THEN 1 ELSE 0 END )/SUM(CASE WHEN a.ciawal>0 THEN 1 ELSE 0 END))*100,0) as frqrugi,
                                    ROUND((SUM(a.rmsbantulabarugi)/SUM(cokg)),0) as margin,
                                    ROUND(((((100-(SUM(a.rmsbantudpls)/SUM(a.ciawal)))*(100*(SUM(a.cokg)/SUM(a.coekor))))/(SUM(a.feedkgqty)/SUM(a.cokg)))/(SUM(a.rmsbantuumur)/SUM(a.ciawal))),0) AS ip
                                FROM table_rhpp a
                                LEFT JOIN (SELECT unit, ap, jabatanlengkap, nama FROM table_hr_demografi WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun') b ON b.nama = a.namappl
                                LEFT JOIN regions c ON c.koderegion = b.ap
                                WHERE b.jabatanlengkap = 'TECHNICAL SUPPORT' AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                                GROUP BY a.namappl ASC");
        }
        
        $itemsTs = array();
        foreach ($sqlTs as $data) {
            array_push($itemsTs, ['',
                $nama = $data->nama,
                $unit = $data->unit,
                $reg = $data->region,
                $area = $data->area,
                $rugi = $data->rugi,
                $skor_rugi = rugipro($rugi),
                $frqrugi = $data->frqrugi,
                $skor_frqrugi = frqrugi($frqrugi),
                $diffmargin = valarea($data->margin, $area, $tglawal, $tglakhir, 'TECHNICAL SUPPORT'),
                $skor_diffmargin = skormargin($diffmargin),
                $ip = $data->ip,
                $skor_ip = skorip($ip),
                $total_skor = totSkorTs($skor_rugi, $skor_frqrugi, $skor_diffmargin, $skor_ip),
                $skor_final = ($total_skor == 0 ? 0 : round($total_skor, 2)),
                $keterangan = ket($total_skor)]);
        }
        array_multisort(array_column($itemsTs, 13), SORT_DESC, $itemsTs);

        foreach ($itemsTs as $data) {
            createKpiTs(
                $tanggal = $tglcreate, 
                $data[1], 
                $data[2], 
                $data[3],
                $data[4], 
                $data[5],
                $data[6],
                $data[7],
                $data[8], 
                $data[9], 
                $data[10],
                $data[11],
                $data[12], 
                $data[13], 
                $data[15],
            );
        }
        return view('dashboard.master.createKpiTs', compact('no','itemsTs','bulan','tahun','bln','thn','tglakhir','tglcreate'));
    }

    public function createKp($tahun, $bulan){
        $tglawal = $tahun.'-01-01';
        $tglakhir = $tahun . '-' . $bulan . '-' . endTgl($bulan,$tahun);
        $tglcreate = $tahun . '-' . $bulan . '-01';

        $bln = DB::select('SELECT kode, bulan FROM bulan ORDER BY kode+1 ASC');
        $thn = DB::select('SELECT tahun FROM tahun ORDER BY id+1 ASC');

        $no = 1;
        $del = DB::table('kpi_kp')->where('tanggal', $tglcreate)->delete();
        if($del){
            $sqlKp = DB::select("SELECT a.namakaprod AS nama, b.unit, a.area, b.ap AS region,
                                ROUND((SUM(a.cokg)/SUM(a.coekor)),1) As bw,
                                ROUND((SUM(a.feedkgqty)/SUM(a.cokg)),1) as fcr,
                                ROUND((SUM(a.rmsbantudpls)/SUM(a.ciawal)),1) as dpls,
                                ROUND((SUM(a.rmsbantuumur)/SUM(a.ciawal)),1) as umur,
                                ROUND((SUM(a.nomrhpprugi)/SUM(a.ciawal)),0) as rugi,
                                ROUND((SUM(CASE WHEN a.rhpprugiproduksi>0 THEN 1 ELSE 0 END )/SUM(CASE WHEN a.ciawal>0 THEN 1 ELSE 0 END))*100,0) as frqrugi,
                                ROUND((SUM(a.rmsbantulabarugi)/SUM(cokg)),0) as margin,
                                ROUND(((((100-(SUM(a.rmsbantudpls)/SUM(a.ciawal)))*(100*(SUM(a.cokg)/SUM(a.coekor))))/(SUM(a.feedkgqty)/SUM(a.cokg)))/(SUM(a.rmsbantuumur)/SUM(a.ciawal))),0) AS ip
                            FROM table_rhpp a
                            LEFT JOIN (SELECT unit, ap, jabatanlengkap, nama FROM table_hr_demografi WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun') b ON b.nama = a.namakaprod
                            LEFT JOIN regions c ON c.koderegion = b.ap
                            WHERE b.jabatanlengkap = 'KEPALA PRODUKSI' AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                            GROUP BY a.namakaprod ASC");
        }else{
            $sqlKp = DB::select("SELECT a.namakaprod AS nama, b.unit, a.area, b.ap AS region,
                                ROUND((SUM(a.cokg)/SUM(a.coekor)),1) As bw,
                                ROUND((SUM(a.feedkgqty)/SUM(a.cokg)),1) as fcr,
                                ROUND((SUM(a.rmsbantudpls)/SUM(a.ciawal)),1) as dpls,
                                ROUND((SUM(a.rmsbantuumur)/SUM(a.ciawal)),1) as umur,
                                ROUND((SUM(a.nomrhpprugi)/SUM(a.ciawal)),0) as rugi,
                                ROUND((SUM(CASE WHEN a.rhpprugiproduksi>0 THEN 1 ELSE 0 END )/SUM(CASE WHEN a.ciawal>0 THEN 1 ELSE 0 END))*100,0) as frqrugi,
                                ROUND((SUM(a.rmsbantulabarugi)/SUM(cokg)),0) as margin,
                                ROUND(((((100-(SUM(a.rmsbantudpls)/SUM(a.ciawal)))*(100*(SUM(a.cokg)/SUM(a.coekor))))/(SUM(a.feedkgqty)/SUM(a.cokg)))/(SUM(a.rmsbantuumur)/SUM(a.ciawal))),0) AS ip
                            FROM table_rhpp a
                            LEFT JOIN (SELECT unit, ap, jabatanlengkap, nama FROM table_hr_demografi WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun') b ON b.nama = a.namakaprod
                            LEFT JOIN regions c ON c.koderegion = b.ap
                            WHERE b.jabatanlengkap = 'KEPALA PRODUKSI' AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                            GROUP BY a.namakaprod ASC");
        }
        
        $itemsKp = array();
        foreach($sqlKp as $data){
                array_push($itemsKp, ['', 
                    $nama = $data->nama,
                    $unit = $data->unit,
                    $reg = $data->region,
                    $area = $data->area,
                    $rugi = $data->rugi,
                    $skor_rugi = rugipro($rugi),
                    $frqrugi = $data->frqrugi,
                    $skor_frqrugi = frqrugi($frqrugi),
                    $diffmargin = valarea($data->margin, $area, $tglawal, $tglakhir, 'KEPALA PRODUKSI'),
                    $skor_diffmargin = skormargin($diffmargin),
                    $ip = $data->ip,
                    $skor_ip = skorip($ip),
                    $total_skor = totSkorKp($skor_rugi, $skor_frqrugi, $skor_diffmargin, $skor_ip),       
                    $skor_final = ($total_skor == 0 ? 0 : round($total_skor,2)),
                    $keterangan = ket($skor_final),
                ]);           
        }
        array_multisort(array_column($itemsKp, 13), SORT_DESC, $itemsKp);
       
        foreach ($itemsKp as $data) {
            createKpiKp(
                $tanggal = $tglcreate, 
                $data[1], 
                $data[2], 
                $data[3],
                $data[4], 
                $data[5],
                $data[6],
                $data[7],
                $data[8], 
                $data[9], 
                $data[10],
                $data[11],
                $data[12], 
                $data[13], 
                $data[15],
            );
        }
        return view('dashboard.master.createKpiKp', compact('no','itemsKp','bulan','tahun','bln','thn','tglakhir','tglcreate'));
    }
}