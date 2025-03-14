<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Produksi;
use App\Models\KontrakSapronak;
use App\Models\KontrakBonus;
use App\Models\KontrakAdj;
use App\Export\ExcelRugiProduksi;
use App\Imports\PrdPantuanFlokPanenProduksi;
use Yajra\DataTables\DataTables;
use App\Models\Regions;
use Auth;
use Alert;

class ProduksiController extends Controller{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        $jabatan = Auth::user()->jabatan;
        $nik = Auth::user()->nik;

        $batas = 100;
        $no = 1;

        $produksi = Produksi::where('akses','LIKE','%'.$jabatan.'%')
        ->orWhere('akses','LIKE','%'.$nik.'%')
        ->orderBy('id','ASC')->paginate($batas);

        $no = $batas*($produksi->currentPage()-1);
        $jml = Produksi::where('akses','LIKE','%'.$jabatan.'%')
        ->orWhere('akses','LIKE','%'.$nik.'%')->count();

        return view('dashboard.produksi.menuList',compact('no','produksi','jml'));
    }

    public function rhpp(Request $request){
        $index = $request->segment(3);
        $sort = $request->segment(4);
        $reg = $request->segment(5);
        $thn = $request->segment(6);

        $tahun = $request->input('tahun');
        $region = $request->input('region');

        if($region==''){
            $region='SEMUA';
        }

        if($index!=''){
           $region = $reg;
        }

        if($tahun==''){
            $tahun=$thn;
        }

        if($thn==''){
            $tahun = date('Y');
        }

        $kosong = '';
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");

        /*
        $jabatan = Auth::user()->jabatan;
        $koderegion = Auth::user()->region;
        $kosong = '';
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF QA");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if (in_array($jabatan, $akses)) {
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
        } else {
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $koderegion . '" ORDER BY koderegion ASC');
        }
        */

        if($region!='SEMUA'){
            $sql = DB::select("SELECT unit AS kodeunit, region,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN ciawal ELSE 0 END ) AS popYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN coekor ELSE 0 END ) AS BwYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END ) AS FcrYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN ciawal ELSE 0 END ) AS RhppYtd,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN ciawal ELSE 0 END ) AS pop1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN coekor ELSE 0 END ) AS bw1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN cokg ELSE 0 END ) AS fcr1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN ciawal ELSE 0 END ) AS rhpp1,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN ciawal ELSE 0 END ) AS pop2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN coekor ELSE 0 END ) AS bw2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN cokg ELSE 0 END ) AS fcr2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN ciawal ELSE 0 END ) AS rhpp2,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN ciawal ELSE 0 END ) AS pop3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN coekor ELSE 0 END ) AS bw3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN cokg ELSE 0 END ) AS fcr3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN ciawal ELSE 0 END ) AS rhpp3,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN ciawal ELSE 0 END ) AS pop4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN coekor ELSE 0 END ) AS bw4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN cokg ELSE 0 END ) AS fcr4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN ciawal ELSE 0 END ) AS rhpp4,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN ciawal ELSE 0 END ) AS pop5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN coekor ELSE 0 END ) AS bw5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN cokg ELSE 0 END ) AS fcr5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN ciawal ELSE 0 END ) AS rhpp5,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN ciawal ELSE 0 END ) AS pop6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN coekor ELSE 0 END ) AS bw6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN cokg ELSE 0 END ) AS fcr6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN ciawal ELSE 0 END ) AS rhpp6,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN ciawal ELSE 0 END ) AS pop7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN coekor ELSE 0 END ) AS bw7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN cokg ELSE 0 END ) AS fcr7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN ciawal ELSE 0 END ) AS rhpp7,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN ciawal ELSE 0 END ) AS pop8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN coekor ELSE 0 END ) AS bw8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN cokg ELSE 0 END ) AS fcr8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN ciawal ELSE 0 END ) AS rhpp8,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN ciawal ELSE 0 END ) AS pop9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN coekor ELSE 0 END ) AS bw9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN cokg ELSE 0 END ) AS fcr9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN ciawal ELSE 0 END ) AS rhpp9,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN ciawal ELSE 0 END ) AS pop10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN coekor ELSE 0 END ) AS bw10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN cokg ELSE 0 END ) AS fcr10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN ciawal ELSE 0 END ) AS rhpp10,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN ciawal ELSE 0 END ) AS pop11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN coekor ELSE 0 END ) AS bw11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN cokg ELSE 0 END ) AS fcr11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN ciawal ELSE 0 END ) AS rhpp11,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN ciawal ELSE 0 END ) AS pop12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN coekor ELSE 0 END ) AS bw12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN cokg ELSE 0 END ) AS fcr12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN ciawal ELSE 0 END ) AS rhpp12
                            FROM vrhpp WHERE unit IS NOT NULL AND YEAR(tgldocfinal)='$tahun' AND region = '$region' GROUP BY region, unit ASC");
        }else{
            $sql = DB::select("SELECT unit AS kodeunit, region,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN ciawal ELSE 0 END ) AS popYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN coekor ELSE 0 END ) AS BwYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END ) AS FcrYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN ciawal ELSE 0 END ) AS RhppYtd,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN ciawal ELSE 0 END ) AS pop1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN coekor ELSE 0 END ) AS bw1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN cokg ELSE 0 END ) AS fcr1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN ciawal ELSE 0 END ) AS rhpp1,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN ciawal ELSE 0 END ) AS pop2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN coekor ELSE 0 END ) AS bw2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN cokg ELSE 0 END ) AS fcr2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN ciawal ELSE 0 END ) AS rhpp2,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN ciawal ELSE 0 END ) AS pop3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN coekor ELSE 0 END ) AS bw3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN cokg ELSE 0 END ) AS fcr3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN ciawal ELSE 0 END ) AS rhpp3,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN ciawal ELSE 0 END ) AS pop4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN coekor ELSE 0 END ) AS bw4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN cokg ELSE 0 END ) AS fcr4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN ciawal ELSE 0 END ) AS rhpp4,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN ciawal ELSE 0 END ) AS pop5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN coekor ELSE 0 END ) AS bw5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN cokg ELSE 0 END ) AS fcr5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN ciawal ELSE 0 END ) AS rhpp5,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN ciawal ELSE 0 END ) AS pop6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN coekor ELSE 0 END ) AS bw6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN cokg ELSE 0 END ) AS fcr6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN ciawal ELSE 0 END ) AS rhpp6,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN ciawal ELSE 0 END ) AS pop7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN coekor ELSE 0 END ) AS bw7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN cokg ELSE 0 END ) AS fcr7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN ciawal ELSE 0 END ) AS rhpp7,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN ciawal ELSE 0 END ) AS pop8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN coekor ELSE 0 END ) AS bw8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN cokg ELSE 0 END ) AS fcr8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN ciawal ELSE 0 END ) AS rhpp8,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN ciawal ELSE 0 END ) AS pop9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN coekor ELSE 0 END ) AS bw9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN cokg ELSE 0 END ) AS fcr9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN ciawal ELSE 0 END ) AS rhpp9,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN ciawal ELSE 0 END ) AS pop10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN coekor ELSE 0 END ) AS bw10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN cokg ELSE 0 END ) AS fcr10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN ciawal ELSE 0 END ) AS rhpp10,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN ciawal ELSE 0 END ) AS pop11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN coekor ELSE 0 END ) AS bw11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN cokg ELSE 0 END ) AS fcr11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN ciawal ELSE 0 END ) AS rhpp11,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN ciawal ELSE 0 END ) AS pop12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN coekor ELSE 0 END ) AS bw12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN cokg ELSE 0 END ) AS fcr12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN ciawal ELSE 0 END ) AS rhpp12
                            FROM vrhpp WHERE unit IS NOT NULL AND YEAR(tgldocfinal)='$tahun' GROUP BY region, unit ASC");
        }
        $nounit = 1;
        $arrRhpp = array();
        foreach($sql as $data){
            array_push($arrRhpp, [
                $data->kodeunit,
                $data->region,

                //$data->popYtd,
                //$data->BwYtd,
                //$data->FcrYtd,
                DiffStd($data->BwYtd,$data->FcrYtd),
                $data->RhppYtd,

                //$data->pop1,
                //$data->bw1,
                //$data->fcr1,
                DiffStd($data->bw1,$data->fcr1),
                $data->rhpp1,

                //$data->pop2,
                //$data->bw2,
                //$data->fcr2,
                DiffStd($data->bw2,$data->fcr2),
                $data->rhpp2,

                //$data->pop3,
                //$data->bw3,
                //$data->fcr3,
                DiffStd($data->bw3,$data->fcr3),
                $data->rhpp3,

                //$data->pop4,
                //$data->bw4,
                //$data->fcr4,
                DiffStd($data->bw4,$data->fcr4),
                $data->rhpp4,

                //$data->pop5,
                //$data->bw5,
                //$data->fcr5,
                DiffStd($data->bw5,$data->fcr5),
                $data->rhpp5,

                //$data->pop6,
                //$data->bw6,
                //$data->fcr6,
                DiffStd($data->bw6,$data->fcr6),
                $data->rhpp6,

                //$data->pop7,
                //$data->bw7,
                //$data->fcr7,
                DiffStd($data->bw7,$data->fcr7),
                $data->rhpp7,

                //$data->pop8,
                //$data->bw8,
                //$data->fcr8,
                DiffStd($data->bw8,$data->fcr8),
                $data->rhpp8,

                //$data->pop9,
                //$data->bw9,
                //$data->fcr9,
                DiffStd($data->bw9,$data->fcr9),
                $data->rhpp9,

                //$data->pop10,
                //$data->bw10,
                //$data->fcr10,
                DiffStd($data->bw10,$data->fcr10),
                $data->rhpp10,

                //$data->pop11,
                //$data->bw11,
                //$data->fcr11,
                DiffStd($data->bw11,$data->fcr11),
                $data->rhpp11,

                //$data->pop12,
                //$data->bw12,
                //$data->fcr12,
                DiffStd($data->bw12,$data->fcr12),
                $data->rhpp12,
            ]);
        }
        // return $arrRhpp;
        $maxfcrYtd = max(array_column($arrRhpp, 2));
        $maxrhppYtd = max(array_column($arrRhpp, 3));
        $maxfcr1 = max(array_column($arrRhpp, 4));
        $maxrhpp1 = max(array_column($arrRhpp, 5));
        $maxfcr2 = max(array_column($arrRhpp, 6));
        $maxrhpp2 = max(array_column($arrRhpp, 7));
        $maxfcr3 = max(array_column($arrRhpp, 8));
        $maxrhpp3 = max(array_column($arrRhpp, 9));
        $maxfcr4 = max(array_column($arrRhpp, 10));
        $maxrhpp4 = max(array_column($arrRhpp, 11));
        $maxfcr5 = max(array_column($arrRhpp, 12));
        $maxrhpp5 = max(array_column($arrRhpp, 13));
        $maxfcr6 = max(array_column($arrRhpp, 14));
        $maxrhpp6 = max(array_column($arrRhpp, 15));
        $maxfcr7 = max(array_column($arrRhpp, 16));
        $maxrhpp7 = max(array_column($arrRhpp, 17));
        $maxfcr8 = max(array_column($arrRhpp, 18));
        $maxrhpp8 = max(array_column($arrRhpp, 19));
        $maxfcr9 = max(array_column($arrRhpp, 20));
        $maxrhpp9 = max(array_column($arrRhpp, 21));
        $maxfcr10 = max(array_column($arrRhpp, 22));
        $maxrhpp10 = max(array_column($arrRhpp, 23));
        $maxfcr11 = max(array_column($arrRhpp, 24));
        $maxrhpp11 = max(array_column($arrRhpp, 25));
        $maxfcr12 = max(array_column($arrRhpp, 26));
        $maxrhpp12 = max(array_column($arrRhpp, 27));

        $minfcrYtd = min(array_column($arrRhpp, 2));
        $minrhppYtd = min(array_column($arrRhpp, 3));
        $minfcr1 = min(array_column($arrRhpp, 4));
        $minrhpp1 = min(array_column($arrRhpp, 5));
        $minfcr2 = min(array_column($arrRhpp, 6));
        $minrhpp2 = min(array_column($arrRhpp, 7));
        $minfcr3 = min(array_column($arrRhpp, 8));
        $minrhpp3 = min(array_column($arrRhpp, 9));
        $minfcr4 = min(array_column($arrRhpp, 10));
        $minrhpp4 = min(array_column($arrRhpp, 11));
        $minfcr5 = min(array_column($arrRhpp, 12));
        $minrhpp5 = min(array_column($arrRhpp, 13));
        $minfcr6 = min(array_column($arrRhpp, 14));
        $minrhpp6 = min(array_column($arrRhpp, 15));
        $minfcr7 = min(array_column($arrRhpp, 16));
        $minrhpp7 = min(array_column($arrRhpp, 17));
        $minfcr8 = min(array_column($arrRhpp, 18));
        $minrhpp8 = min(array_column($arrRhpp, 19));
        $minfcr9 = min(array_column($arrRhpp, 20));
        $minrhpp9 = min(array_column($arrRhpp, 21));
        $minfcr10 = min(array_column($arrRhpp, 22));
        $minrhpp10 = min(array_column($arrRhpp, 23));
        $minfcr11 = min(array_column($arrRhpp, 24));
        $minrhpp11 = min(array_column($arrRhpp, 25));
        $minfcr12 = min(array_column($arrRhpp, 26));
        $minrhpp12 = min(array_column($arrRhpp, 27));

        if($sort=='desc'){
             array_multisort(array_column($arrRhpp, $index), SORT_DESC, $arrRhpp);
             $sort='asc';
        }elseif($sort=='asc'){
            array_multisort(array_column($arrRhpp, $index), SORT_ASC, $arrRhpp);
            $sort='desc';
        }else{
            array_multisort(array_column($arrRhpp, 1), SORT_DESC, $arrRhpp);
            $sort='asc';
        }

        $sqlAp = DB::select("SELECT unit AS kodeunit, region as koderegion,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN ciawal ELSE 0 END ) AS popYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN coekor ELSE 0 END ) AS BwYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END ) AS FcrYtd,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN ciawal ELSE 0 END ) AS RhppYtd,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN ciawal ELSE 0 END ) AS pop1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN coekor ELSE 0 END ) AS bw1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN cokg ELSE 0 END ) AS fcr1,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 1 THEN ciawal ELSE 0 END ) AS rhpp1,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN ciawal ELSE 0 END ) AS pop2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN coekor ELSE 0 END ) AS bw2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN cokg ELSE 0 END ) AS fcr2,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 2 THEN ciawal ELSE 0 END ) AS rhpp2,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN ciawal ELSE 0 END ) AS pop3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN coekor ELSE 0 END ) AS bw3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN cokg ELSE 0 END ) AS fcr3,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 3 THEN ciawal ELSE 0 END ) AS rhpp3,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN ciawal ELSE 0 END ) AS pop4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN coekor ELSE 0 END ) AS bw4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN cokg ELSE 0 END ) AS fcr4,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 4 THEN ciawal ELSE 0 END ) AS rhpp4,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN ciawal ELSE 0 END ) AS pop5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN coekor ELSE 0 END ) AS bw5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN cokg ELSE 0 END ) AS fcr5,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 5 THEN ciawal ELSE 0 END ) AS rhpp5,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN ciawal ELSE 0 END ) AS pop6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN coekor ELSE 0 END ) AS bw6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN cokg ELSE 0 END ) AS fcr6,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 6 THEN ciawal ELSE 0 END ) AS rhpp6,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN ciawal ELSE 0 END ) AS pop7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN coekor ELSE 0 END ) AS bw7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN cokg ELSE 0 END ) AS fcr7,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 7 THEN ciawal ELSE 0 END ) AS rhpp7,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN ciawal ELSE 0 END ) AS pop8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN coekor ELSE 0 END ) AS bw8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN cokg ELSE 0 END ) AS fcr8,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 8 THEN ciawal ELSE 0 END ) AS rhpp8,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN ciawal ELSE 0 END ) AS pop9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN coekor ELSE 0 END ) AS bw9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN cokg ELSE 0 END ) AS fcr9,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 9 THEN ciawal ELSE 0 END ) AS rhpp9,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN ciawal ELSE 0 END ) AS pop10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN coekor ELSE 0 END ) AS bw10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN cokg ELSE 0 END ) AS fcr10,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 10 THEN ciawal ELSE 0 END ) AS rhpp10,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN ciawal ELSE 0 END ) AS pop11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN coekor ELSE 0 END ) AS bw11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN cokg ELSE 0 END ) AS fcr11,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 11 THEN ciawal ELSE 0 END ) AS rhpp11,

                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN ciawal ELSE 0 END ) AS pop12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN cokg ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN coekor ELSE 0 END ) AS bw12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN feedkgqty ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN cokg ELSE 0 END ) AS fcr12,
                                SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN nomrhpptotal ELSE 0 END )/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)= 12 THEN ciawal ELSE 0 END ) AS rhpp12
                            FROM vrhpp WHERE unit IS NOT NULL AND YEAR(tgldocfinal)='$tahun' GROUP BY region ASC");

        $noap = 1;
        $arrRhppAp = array();
        foreach($sqlAp as $data){
            array_push($arrRhppAp, [
                $data->koderegion,
                DiffStd($data->BwYtd,$data->FcrYtd),
                $data->RhppYtd,
                DiffStd($data->bw1,$data->fcr1),
                $data->rhpp1,
                DiffStd($data->bw2,$data->fcr2),
                $data->rhpp2,
                DiffStd($data->bw3,$data->fcr3),
                $data->rhpp3,
                DiffStd($data->bw4,$data->fcr4),
                $data->rhpp4,
                DiffStd($data->bw5,$data->fcr5),
                $data->rhpp5,
                DiffStd($data->bw6,$data->fcr6),
                $data->rhpp6,
                DiffStd($data->bw7,$data->fcr7),
                $data->rhpp7,
                DiffStd($data->bw8,$data->fcr8),
                $data->rhpp8,
                DiffStd($data->bw9,$data->fcr9),
                $data->rhpp9,
                DiffStd($data->bw10,$data->fcr10),
                $data->rhpp10,
                DiffStd($data->bw11,$data->fcr11),
                $data->rhpp11,
                DiffStd($data->bw12,$data->fcr12),
                $data->rhpp12,
            ]);
        }
        //return $arrRhppAp;
        $maxfcrYtdAp = max(array_column($arrRhppAp, 1));
        $maxrhppYtdAp = max(array_column($arrRhppAp, 2));
        $maxfcr1Ap = max(array_column($arrRhppAp, 3));
        $maxrhpp1Ap = max(array_column($arrRhppAp, 4));
        $maxfcr2Ap = max(array_column($arrRhppAp, 5));
        $maxrhpp2Ap = max(array_column($arrRhppAp, 6));
        $maxfcr3Ap = max(array_column($arrRhppAp, 7));
        $maxrhpp3Ap = max(array_column($arrRhppAp, 8));
        $maxfcr4Ap = max(array_column($arrRhppAp, 9));
        $maxrhpp4Ap = max(array_column($arrRhppAp, 10));
        $maxfcr5Ap = max(array_column($arrRhppAp, 11));
        $maxrhpp5Ap = max(array_column($arrRhppAp, 12));
        $maxfcr6Ap = max(array_column($arrRhppAp, 13));
        $maxrhpp6Ap = max(array_column($arrRhppAp, 14));
        $maxfcr7Ap = max(array_column($arrRhppAp, 15));
        $maxrhpp7Ap = max(array_column($arrRhppAp, 16));
        $maxfcr8Ap = max(array_column($arrRhppAp, 17));
        $maxrhpp8Ap = max(array_column($arrRhppAp, 18));
        $maxfcr9Ap = max(array_column($arrRhppAp, 19));
        $maxrhpp9Ap = max(array_column($arrRhppAp, 20));
        $maxfcr10Ap = max(array_column($arrRhppAp, 21));
        $maxrhpp10Ap = max(array_column($arrRhppAp, 22));
        $maxfcr11Ap = max(array_column($arrRhppAp, 23));
        $maxrhpp11Ap = max(array_column($arrRhppAp, 24));
        $maxfcr12Ap = max(array_column($arrRhppAp, 25));
        $maxrhpp12Ap = max(array_column($arrRhppAp, 26));

        $minfcrYtdAp = min(array_column($arrRhppAp, 1));
        $minrhppYtdAp = min(array_column($arrRhppAp, 2));
        $minfcr1Ap = min(array_column($arrRhppAp, 3));
        $minrhpp1Ap = min(array_column($arrRhppAp, 4));
        $minfcr2Ap = min(array_column($arrRhppAp, 5));
        $minrhpp2Ap = min(array_column($arrRhppAp, 6));
        $minfcr3Ap = min(array_column($arrRhppAp, 7));
        $minrhpp3Ap = min(array_column($arrRhppAp, 8));
        $minfcr4Ap = min(array_column($arrRhppAp, 9));
        $minrhpp4Ap = min(array_column($arrRhppAp, 10));
        $minfcr5Ap = min(array_column($arrRhppAp, 11));
        $minrhpp5Ap = min(array_column($arrRhppAp, 12));
        $minfcr6Ap = min(array_column($arrRhppAp, 13));
        $minrhpp6Ap = min(array_column($arrRhppAp, 14));
        $minfcr7Ap = min(array_column($arrRhppAp, 15));
        $minrhpp7Ap = min(array_column($arrRhppAp, 16));
        $minfcr8Ap = min(array_column($arrRhppAp, 17));
        $minrhpp8Ap = min(array_column($arrRhppAp, 18));
        $minfcr9Ap = min(array_column($arrRhppAp, 19));
        $minrhpp9Ap = min(array_column($arrRhppAp, 20));
        $minfcr10Ap = min(array_column($arrRhppAp, 21));
        $minrhpp10Ap = min(array_column($arrRhppAp, 22));
        $minfcr11Ap = min(array_column($arrRhppAp, 23));
        $minrhpp11Ap = min(array_column($arrRhppAp, 24));
        $minfcr12Ap = min(array_column($arrRhppAp, 25));
        $minrhpp12Ap = min(array_column($arrRhppAp, 26));

        array_multisort(array_column($arrRhppAp, 2), SORT_DESC, $arrRhppAp);

        if($tahun==''){
            $tahun='PILIH';
        }

        return view('dashboard.produksi.rhppList',compact('tahun','nounit','arrRhpp','sort','ap','region','noap','arrRhppAp',
            'maxfcrYtd','maxrhppYtd','maxfcr1','maxrhpp1','maxfcr2','maxrhpp2','maxfcr3','maxrhpp3','maxfcr4','maxrhpp4','maxfcr5','maxrhpp5','maxfcr6','maxrhpp6','maxfcr7','maxrhpp7','maxfcr8','maxrhpp8','maxfcr9','maxrhpp9','maxfcr10','maxrhpp10','maxfcr11','maxrhpp11','maxfcr12','maxrhpp12',
            'minfcrYtd','minrhppYtd','minfcr1','minrhpp1','minfcr2','minrhpp2','minfcr3','minrhpp3','minfcr4','minrhpp4','minfcr5','minrhpp5','minfcr6','minrhpp6','minfcr7','minrhpp7','minfcr8','minrhpp8','minfcr9','minrhpp9','minfcr10','minrhpp10','minfcr11','minrhpp11','minfcr12','minrhpp12',
            'maxfcrYtdAp','maxrhppYtdAp','maxfcr1Ap','maxrhpp1Ap','maxfcr2Ap','maxrhpp2Ap','maxfcr3Ap','maxrhpp3Ap','maxfcr4Ap','maxrhpp4Ap','maxfcr5Ap','maxrhpp5Ap','maxfcr6Ap','maxrhpp6Ap','maxfcr7Ap','maxrhpp7Ap','maxfcr8Ap','maxrhpp8Ap','maxfcr9Ap','maxrhpp9Ap','maxfcr10Ap','maxrhpp10Ap','maxfcr11Ap','maxrhpp11Ap','maxfcr12Ap','maxrhpp12Ap',
            'minfcrYtdAp','minrhppYtdAp','minfcr1Ap','minrhpp1Ap','minfcr2Ap','minrhpp2Ap','minfcr3Ap','minrhpp3Ap','minfcr4Ap','minrhpp4Ap','minfcr5Ap','minrhpp5Ap','minfcr6Ap','minrhpp6Ap','minfcr7Ap','minrhpp7Ap','minfcr8Ap','minrhpp8Ap','minfcr9Ap','minrhpp9Ap','minfcr10Ap','minrhpp10Ap','minfcr11Ap','minrhpp11Ap','minfcr12Ap','minrhpp12Ap'
        ));
    }

    public function rhppUnitExcel($region, $tahun){
        if($region!='SEMUA'){
            $sql = DB::select("SELECT a.kodeunit, a.region,
                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS popYtd,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS BwYtd,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS FcrYtd,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS RhppYtd,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop1,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw1,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr1,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp1,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop2,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw2,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr2,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp2,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop3,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw3,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr3,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp3,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop4,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw4,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr4,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp4,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop5,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw5,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr5,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp5,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop6,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw6,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr6,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp6,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop7,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw7,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr7,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp7,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop8,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw8,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr8,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp8,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop9,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw9,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr9,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp9,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop10,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw10,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr10,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp10,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop11,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw11,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr11,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp11,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop12,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw12,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr12,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp12
                FROM units a WHERE a.region = '$region' ORDER BY a.region ASC");
        }else{
            $sql = DB::select("SELECT a.kodeunit, a.region,
                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS popYtd,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS BwYtd,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS FcrYtd,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS RhppYtd,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop1,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw1,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr1,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp1,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop2,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw2,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr2,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp2,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop3,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw3,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr3,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp3,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop4,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw4,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr4,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp4,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop5,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw5,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr5,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp5,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop6,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw6,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr6,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp6,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop7,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw7,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr7,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp7,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop8,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw8,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr8,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp8,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop9,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw9,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr9,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp9,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop10,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw10,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr10,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp10,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop11,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw11,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr11,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp11,

                (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop12,
                ROUND((SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw12,
                ROUND((SELECT SUM(feedkgqty) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr12,
                ROUND((SELECT SUM(nomrhpptotal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp12
                FROM units a ORDER BY a.region ASC");
        }

        $arrRhpp = array();
        foreach($sql as $data){
            array_push($arrRhpp, [
                $data->kodeunit,
                $data->region,
                DiffStd($data->BwYtd,$data->FcrYtd),
                $data->RhppYtd,
                DiffStd($data->bw1,$data->fcr1),
                $data->rhpp1,
                DiffStd($data->bw2,$data->fcr2),
                $data->rhpp2,
                DiffStd($data->bw3,$data->fcr3),
                $data->rhpp3,
                DiffStd($data->bw4,$data->fcr4),
                $data->rhpp4,
                DiffStd($data->bw5,$data->fcr5),
                $data->rhpp5,
                DiffStd($data->bw6,$data->fcr6),
                $data->rhpp6,
                DiffStd($data->bw7,$data->fcr7),
                $data->rhpp7,
                DiffStd($data->bw8,$data->fcr8),
                $data->rhpp8,
                DiffStd($data->bw9,$data->fcr9),
                $data->rhpp9,
                DiffStd($data->bw10,$data->fcr10),
                $data->rhpp10,
                DiffStd($data->bw11,$data->fcr11),
                $data->rhpp11,
                DiffStd($data->bw12,$data->fcr12),
                $data->rhpp12,
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AC1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'RHPP');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AC4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', 'YTD '.$tahun);
        $spreadsheet->getActiveSheet()->mergeCells('D3:E3');
        $sheet->setCellValue('D4', 'DIFF FCR');
        $sheet->setCellValue('E4', 'RHPP');

        $sheet->setCellValue('F3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('F3:G3');
        $sheet->setCellValue('F4', 'DIFF FCR');
        $sheet->setCellValue('G4', 'RHPP');

        $sheet->setCellValue('H3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('H3:I3');
        $sheet->setCellValue('H4', 'DIFF FCR');
        $sheet->setCellValue('I4', 'RHPP');

        $sheet->setCellValue('J3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('J3:K3');
        $sheet->setCellValue('J4', 'DIFF FCR');
        $sheet->setCellValue('K4', 'RHPP');

        $sheet->setCellValue('L3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('L3:M3');
        $sheet->setCellValue('L4', 'DIFF FCR');
        $sheet->setCellValue('M4', 'RHPP');

        $sheet->setCellValue('N3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('N3:O3');
        $sheet->setCellValue('N4', 'DIFF FCR');
        $sheet->setCellValue('O4', 'RHPP');

        $sheet->setCellValue('P3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('P3:Q3');
        $sheet->setCellValue('P4', 'DIFF FCR');
        $sheet->setCellValue('Q4', 'RHPP');

        $sheet->setCellValue('R3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('R3:S3');
        $sheet->setCellValue('R4', 'DIFF FCR');
        $sheet->setCellValue('S4', 'RHPP');

        $sheet->setCellValue('T3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('T3:U3');
        $sheet->setCellValue('T4', 'DIFF FCR');
        $sheet->setCellValue('U4', 'RHPP');

        $sheet->setCellValue('V3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('V3:W3');
        $sheet->setCellValue('V4', 'DIFF FCR');
        $sheet->setCellValue('W4', 'RHPP');

        $sheet->setCellValue('X3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('X3:Y3');
        $sheet->setCellValue('X4', 'DIFF FCR');
        $sheet->setCellValue('Y4', 'RHPP');

        $sheet->setCellValue('Z3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('Z3:AA3');
        $sheet->setCellValue('Z4', 'DIFF FCR');
        $sheet->setCellValue('AA4', 'RHPP');

        $sheet->setCellValue('AB3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('AB3:AC3');
        $sheet->setCellValue('AB4', 'DIFF FCR');
        $sheet->setCellValue('AC4', 'RHPP');

        $rows = 5;
        $no = 1;

        foreach ($arrRhpp as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data[0]);
            $sheet->setCellValue('C' . $rows, $data[1]);
            $sheet->setCellValue('D' . $rows, number_indo_excel_koma1($data[2]));
            $sheet->setCellValue('E' . $rows, number_indo_excel_koma1($data[3]));
            $sheet->setCellValue('F' . $rows, number_indo_excel_koma1($data[4]));
            $sheet->setCellValue('G' . $rows, number_indo_excel_koma1($data[5]));
            $sheet->setCellValue('H' . $rows, number_indo_excel_koma1($data[6]));
            $sheet->setCellValue('I' . $rows, number_indo_excel_koma1($data[7]));
            $sheet->setCellValue('J' . $rows, number_indo_excel_koma1($data[8]));
            $sheet->setCellValue('K' . $rows, number_indo_excel_koma1($data[9]));
            $sheet->setCellValue('L' . $rows, number_indo_excel_koma1($data[10]));
            $sheet->setCellValue('M' . $rows, number_indo_excel_koma1($data[11]));
            $sheet->setCellValue('N' . $rows, number_indo_excel_koma1($data[12]));
            $sheet->setCellValue('O' . $rows, number_indo_excel_koma1($data[13]));
            $sheet->setCellValue('P' . $rows, number_indo_excel_koma1($data[14]));
            $sheet->setCellValue('Q' . $rows, number_indo_excel_koma1($data[15]));
            $sheet->setCellValue('R' . $rows, number_indo_excel_koma1($data[16]));
            $sheet->setCellValue('S' . $rows, number_indo_excel_koma1($data[17]));
            $sheet->setCellValue('T' . $rows, number_indo_excel_koma1($data[18]));
            $sheet->setCellValue('U' . $rows, number_indo_excel_koma1($data[19]));
            $sheet->setCellValue('V' . $rows, number_indo_excel_koma1($data[20]));
            $sheet->setCellValue('W' . $rows, number_indo_excel_koma1($data[21]));
            $sheet->setCellValue('X' . $rows, number_indo_excel_koma1($data[22]));
            $sheet->setCellValue('Y' . $rows, number_indo_excel_koma1($data[23]));
            $sheet->setCellValue('Z' . $rows, number_indo_excel_koma1($data[24]));
            $sheet->setCellValue('AA' . $rows, number_indo_excel_koma1($data[25]));
            $sheet->setCellValue('AB' . $rows, number_indo_excel_koma1($data[26]));
            $sheet->setCellValue('AC' . $rows, number_indo_excel_koma1($data[27]));


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('15');
            $sheet->getColumnDimension('C')->setWidth('15');
            foreach (range('D', 'AC') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $fileName = "RHPP_PER_UNIT.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function rhppApExcel(){

        $tahun = date('Y');
        $sqlAp = DB::select("SELECT a.koderegion,
                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS popYtd,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS BwYtd,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS FcrYtd,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS RhppYtd,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop1,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw1,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr1,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='1' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp1,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop2,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw2,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr2,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='2' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp2,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop3,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw3,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr3,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='3' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp3,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop4,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw4,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr4,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='4' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp4,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop5,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw5,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr5,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='5' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp5,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop6,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw6,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr6,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='6' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp6,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop7,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw7,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr7,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='7' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp7,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop8,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw8,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr8,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='8' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp8,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop9,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw9,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr9,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='9' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp9,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop10,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw10,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr10,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='10' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp10,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop11,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw11,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr11,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='11' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp11,

                (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0') AS pop12,
                ROUND((SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),2) AS bw12,
                ROUND((SELECT SUM(feedkgqty) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),3) AS fcr12,
                ROUND((SELECT SUM(nomrhpptotal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0')
                / (SELECT SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)='12' AND YEAR(tgldocfinal)='$tahun' AND rhpprugiproduksi <= '0'),0) AS rhpp12
                FROM regions a ORDER BY a.koderegion ASC");

        $arrRhppAp = array();
        foreach($sqlAp as $data){
            array_push($arrRhppAp, [
                $data->koderegion,
                DiffStd($data->BwYtd,$data->FcrYtd),
                $data->RhppYtd,
                DiffStd($data->bw1,$data->fcr1),
                $data->rhpp1,
                DiffStd($data->bw2,$data->fcr2),
                $data->rhpp2,
                DiffStd($data->bw3,$data->fcr3),
                $data->rhpp3,
                DiffStd($data->bw4,$data->fcr4),
                $data->rhpp4,
                DiffStd($data->bw5,$data->fcr5),
                $data->rhpp5,
                DiffStd($data->bw6,$data->fcr6),
                $data->rhpp6,
                DiffStd($data->bw7,$data->fcr7),
                $data->rhpp7,
                DiffStd($data->bw8,$data->fcr8),
                $data->rhpp8,
                DiffStd($data->bw9,$data->fcr9),
                $data->rhpp9,
                DiffStd($data->bw10,$data->fcr10),
                $data->rhpp10,
                DiffStd($data->bw11,$data->fcr11),
                $data->rhpp11,
                DiffStd($data->bw12,$data->fcr12),
                $data->rhpp12,
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AB1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'RHPP');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AB4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'YTD '.$tahun);
        $spreadsheet->getActiveSheet()->mergeCells('C3:D3');
        $sheet->setCellValue('C4', 'DIFF FCR');
        $sheet->setCellValue('D4', 'RHPP');

        $sheet->setCellValue('E3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('E3:F3');
        $sheet->setCellValue('E4', 'DIFF FCR');
        $sheet->setCellValue('F4', 'RHPP');

        $sheet->setCellValue('G3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('G3:H3');
        $sheet->setCellValue('G4', 'DIFF FCR');
        $sheet->setCellValue('H4', 'RHPP');

        $sheet->setCellValue('I3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('I3:J3');
        $sheet->setCellValue('I4', 'DIFF FCR');
        $sheet->setCellValue('J4', 'RHPP');

        $sheet->setCellValue('K3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('K3:L3');
        $sheet->setCellValue('K4', 'DIFF FCR');
        $sheet->setCellValue('L4', 'RHPP');

        $sheet->setCellValue('M3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('M3:N3');
        $sheet->setCellValue('M4', 'DIFF FCR');
        $sheet->setCellValue('N4', 'RHPP');

        $sheet->setCellValue('O3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('O3:P3');
        $sheet->setCellValue('O4', 'DIFF FCR');
        $sheet->setCellValue('P4', 'RHPP');

        $sheet->setCellValue('Q3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('Q3:R3');
        $sheet->setCellValue('Q4', 'DIFF FCR');
        $sheet->setCellValue('R4', 'RHPP');

        $sheet->setCellValue('S3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('S3:T3');
        $sheet->setCellValue('S4', 'DIFF FCR');
        $sheet->setCellValue('T4', 'RHPP');

        $sheet->setCellValue('U3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('U3:V3');
        $sheet->setCellValue('U4', 'DIFF FCR');
        $sheet->setCellValue('V4', 'RHPP');

        $sheet->setCellValue('W3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('W3:X3');
        $sheet->setCellValue('W4', 'DIFF FCR');
        $sheet->setCellValue('X4', 'RHPP');

        $sheet->setCellValue('Y3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('Y3:Z3');
        $sheet->setCellValue('Y4', 'DIFF FCR');
        $sheet->setCellValue('Z4', 'RHPP');

        $sheet->setCellValue('AA3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('AA3:AB3');
        $sheet->setCellValue('AA4', 'DIFF FCR');
        $sheet->setCellValue('AB4', 'RHPP');

        $rows = 5;
        $no = 1;

        foreach ($arrRhppAp as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data[0]);
            $sheet->setCellValue('C' . $rows, number_indo_excel_koma1($data[1]));
            $sheet->setCellValue('D' . $rows, number_indo_excel_koma1($data[2]));
            $sheet->setCellValue('E' . $rows, number_indo_excel_koma1($data[3]));
            $sheet->setCellValue('F' . $rows, number_indo_excel_koma1($data[4]));
            $sheet->setCellValue('G' . $rows, number_indo_excel_koma1($data[5]));
            $sheet->setCellValue('H' . $rows, number_indo_excel_koma1($data[6]));
            $sheet->setCellValue('I' . $rows, number_indo_excel_koma1($data[7]));
            $sheet->setCellValue('J' . $rows, number_indo_excel_koma1($data[8]));
            $sheet->setCellValue('K' . $rows, number_indo_excel_koma1($data[9]));
            $sheet->setCellValue('L' . $rows, number_indo_excel_koma1($data[10]));
            $sheet->setCellValue('M' . $rows, number_indo_excel_koma1($data[11]));
            $sheet->setCellValue('N' . $rows, number_indo_excel_koma1($data[12]));
            $sheet->setCellValue('O' . $rows, number_indo_excel_koma1($data[13]));
            $sheet->setCellValue('P' . $rows, number_indo_excel_koma1($data[14]));
            $sheet->setCellValue('Q' . $rows, number_indo_excel_koma1($data[15]));
            $sheet->setCellValue('R' . $rows, number_indo_excel_koma1($data[16]));
            $sheet->setCellValue('S' . $rows, number_indo_excel_koma1($data[17]));
            $sheet->setCellValue('T' . $rows, number_indo_excel_koma1($data[18]));
            $sheet->setCellValue('U' . $rows, number_indo_excel_koma1($data[19]));
            $sheet->setCellValue('V' . $rows, number_indo_excel_koma1($data[20]));
            $sheet->setCellValue('W' . $rows, number_indo_excel_koma1($data[21]));
            $sheet->setCellValue('X' . $rows, number_indo_excel_koma1($data[22]));
            $sheet->setCellValue('Y' . $rows, number_indo_excel_koma1($data[23]));
            $sheet->setCellValue('Z' . $rows, number_indo_excel_koma1($data[24]));
            $sheet->setCellValue('AA' . $rows, number_indo_excel_koma1($data[25]));
            $sheet->setCellValue('AB' . $rows, number_indo_excel_koma1($data[26]));


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('15');
            $sheet->getColumnDimension('C')->setWidth('15');
            foreach (range('D', 'AB') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $fileName = "RHPP_PER_AP.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function rugiProduksi(Request $request){
        $index = $request->segment(3);
        $sort = $request->segment(4);
        $thn = $request->segment(5);
        $reg = $request->segment(6);
        $tahun = $request->input('tahun');
        $region = $request->input('region');

        if($tahun==''){
            $tahun=$thn;
        }

        if($region==''){
            $region='SEMUA';
        }

        if($reg==''){
            $reg='SEMUA';
        }

        $kosong = '';
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");

        $no = 0;
        if($region !='SEMUA'){
            $strWhere = " WHERE region='$region'";
        }else{
            $strWhere = "";
        }
        $produksi = DB::select("SELECT id, kodeunit, region,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun'),'') AS jan,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun'),'') AS feb,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun'),'') AS mar,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun'),'') AS apr,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun'),'') AS mei,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun'),'') AS jun,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun'),'') AS jul,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun'),'') AS agu,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun'),'') AS sep,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun'),'') AS okt,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun'),'') AS nov,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun'),'') AS des,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun'),'') AS cum,
                            IFNULL((SELECT COUNT(nomrhpprugi) FROM table_rhpp WHERE unit=a.kodeunit AND nomrhpprugi > 0 AND YEAR(tgldocfinal)='$tahun'),0)/(SELECT COUNT(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND ciawal > 0 AND YEAR(tgldocfinal)='$tahun')*100 AS flok_rugi
                        FROM units a $strWhere");

        $arrProduksi = array();
        foreach($produksi as $data){
            array_push($arrProduksi, [
                $data->kodeunit,
                $data->region,
                $jan = $data->jan,
                $feb = $data->feb,
                $mar = $data->mar,
                $apr = $data->apr,
                $mei = $data->mei,
                $jun = $data->jun,
                $jul = $data->jul,
                $agu = $data->agu,
                $sep = $data->sep,
                $okt = $data->okt,
                $nov = $data->nov,
                $des = $data->des,
                $cum = $data->cum,
                $flok_rugi = $data->flok_rugi,
                $ket =  evaluasiProduksi($cum)
            ]);
        }

        $max_jan = max(array_column($arrProduksi, 2));
        $max_feb = max(array_column($arrProduksi, 3));
        $max_mar = max(array_column($arrProduksi, 4));
        $max_apr = max(array_column($arrProduksi, 5));
        $max_mei = max(array_column($arrProduksi, 6));
        $max_jun = max(array_column($arrProduksi, 7));
        $max_jul = max(array_column($arrProduksi, 8));
        $max_agu = max(array_column($arrProduksi, 9));
        $max_sep = max(array_column($arrProduksi, 10));
        $max_okt = max(array_column($arrProduksi, 11));
        $max_nov = max(array_column($arrProduksi, 12));
        $max_des = max(array_column($arrProduksi, 13));
        $max_cum = max(array_column($arrProduksi, 14));
        $max_flok_rugi = max(array_column($arrProduksi, 15));

        $min_jan = min(array_column($arrProduksi, 2));
        $min_feb = min(array_column($arrProduksi, 3));
        $min_mar = min(array_column($arrProduksi, 4));
        $min_apr = min(array_column($arrProduksi, 5));
        $min_mei = min(array_column($arrProduksi, 6));
        $min_jun = min(array_column($arrProduksi, 7));
        $min_jul = min(array_column($arrProduksi, 8));
        $min_agu = min(array_column($arrProduksi, 9));
        $min_sep = min(array_column($arrProduksi, 10));
        $min_okt = min(array_column($arrProduksi, 11));
        $min_nov = min(array_column($arrProduksi, 12));
        $min_des = min(array_column($arrProduksi, 13));
        $min_cum = min(array_column($arrProduksi, 14));
        $min_flok_rugi = min(array_column($arrProduksi, 15));

        if($sort=='desc'){
            array_multisort(array_column($arrProduksi, $index), SORT_DESC, $arrProduksi);
            $sort='asc';
        }else{
            array_multisort(array_column($arrProduksi, $index), SORT_ASC, $arrProduksi);
            $sort='desc';
        }


        $rugibulan = DB::select("SELECT MONTH(tgldocfinal) AS bln, round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                    FROM table_rhpp WHERE YEAR(tgldocfinal)='$tahun' GROUP BY MONTH(tgldocfinal)");

        $rugifoot = DB::select("SELECT kode, val FROM (SELECT MONTH(tgldocfinal) AS bln, round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                                FROM table_rhpp WHERE YEAR(tgldocfinal)='$tahun' GROUP BY MONTH(tgldocfinal) ) a
                                RIGHT JOIN ( SELECT kode FROM bulan) b ON a.bln = b.kode
                                UNION ALL
                                SELECT 'ALL', round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                                FROM table_rhpp WHERE YEAR(tgldocfinal)='$tahun'
                                UNION ALL
                                SELECT 'PERSEN', COUNT(nomrhpprugi)/
										  (SELECT COUNT(ciawal) FROM table_rhpp)*100 AS val
                                FROM table_rhpp WHERE nomrhpprugi > 0 AND YEAR(tgldocfinal)='$tahun'");

        $arrfoot = [];
        foreach ($rugifoot as $val) {
            array_push($arrfoot,$val->val);
        }

        $arrBln = [];
        foreach ($rugibulan as $val) {
            array_push($arrBln,shortMonth($val->bln));
        }

        $arrVal = [];
        foreach ($rugibulan as $val) {
            array_push($arrVal,$val->val);
        }

        $valArray = [];
        foreach ($arrProduksi as $val) {
            array_push($valArray, $val[16]);
        }

        $counts = array_count_values($valArray);
        $sangatbaik = !empty($counts['SANGAT BAIK']) ? $counts['SANGAT BAIK'] : '0';
        $baik = !empty($counts['BAIK']) ? $counts['BAIK'] : '0';
        $sedang = !empty($counts['SEDANG']) ? $counts['SEDANG'] : '0';
        $kurang = !empty($counts['KURANG']) ? $counts['KURANG'] : '0';
        $sangatkurang = !empty($counts['SANGAT KURANG']) ? $counts['SANGAT KURANG'] : '0';
        $total = count($valArray);

        //array_multisort(array_column($arrProduksi, 14), SORT_ASC, $arrProduksi);
        $arrSangatBaik = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'SANGAT BAIK'){
                array_push($arrSangatBaik, [$val[0], round($val[14],0)]);
            }
        }

        $arrBaik = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'BAIK'){
                array_push($arrBaik, [$val[0], round($val[14],0)]);
            }
        }

        $arrSedang = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'SEDANG'){
                array_push($arrSedang, [$val[0], round($val[14],0)]);
            }
        }

        $arrKurang = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'KURANG'){
                array_push($arrKurang, [$val[0], round($val[14],0)]);
            }
        }

        $arrSangatKurang = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'SANGAT KURANG'){
                array_push($arrSangatKurang, [$val[0], round($val[14],0)]);
            }
        }

        if($tahun==''){
            $tahun='PILIH';
        }

        if($region==''){
            $region='SEMUA';
        }
        return view('dashboard.produksi.rugiProduksi',compact('no','arrProduksi','sort','arrVal','arrBln', 'tahun','thn','ap','region',
        'sangatbaik','baik','sedang','kurang','sangatkurang','total','arrfoot',
        'arrSangatBaik','arrBaik','arrSedang','arrKurang','arrSangatKurang',
        'max_jan','max_feb','max_mar','max_apr','max_mei','max_jun','max_jul','max_agu','max_sep','max_okt','max_nov','max_des','max_cum','max_flok_rugi',
        'min_jan','min_feb','min_mar','min_apr','min_mei','min_jun','min_jul','min_agu','min_sep','min_okt','min_nov','min_des','min_cum','min_flok_rugi'));
    }

    public function rugiProduksiAp(Request $request){
        $index = $request->segment(3);
        $sort = $request->segment(4);
        $thn = $request->segment(5);
        $tahun = $request->input('tahun');

        if($tahun==''){
            $tahun=$thn;
        }

        $no = 0;
        $produksi = DB::select("SELECT id, koderegion,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun'),'') AS jan,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun'),'') AS feb,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun'),'') AS mar,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun'),'') AS apr,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun'),'') AS mei,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun'),'') AS jun,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun'),'') AS jul,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun'),'') AS agu,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun'),'') AS sep,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun'),'') AS okt,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun'),'') AS nov,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun'),'') AS des,
                            IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun'),'') AS cum,
                            IFNULL((SELECT COUNT(nomrhpprugi) FROM vrhpp WHERE region=a.koderegion AND nomrhpprugi > 0 AND YEAR(tgldocfinal)='$tahun'),0)/(SELECT COUNT(ciawal) FROM vrhpp WHERE region=a.koderegion AND ciawal > 0 AND YEAR(tgldocfinal)='$tahun')*100 AS flok_rugi
                        FROM regions a");
        $arrProduksi = array();
        foreach($produksi as $data){
            array_push($arrProduksi, [
                $id = $data->koderegion,
                $region = $data->koderegion,
                $jan = $data->jan,
                $feb = $data->feb,
                $mar = $data->mar,
                $apr = $data->apr,
                $mei = $data->mei,
                $jun = $data->jun,
                $jul = $data->jul,
                $agu = $data->agu,
                $sep = $data->sep,
                $okt = $data->okt,
                $nov = $data->nov,
                $des = $data->des,
                $cum = $data->cum,
                $flok_rugi = $data->flok_rugi,
                $ket =  evaluasiProduksi($cum)
            ]);
        }

        $max_jan = max(array_column($arrProduksi, 2));
        $max_feb = max(array_column($arrProduksi, 3));
        $max_mar = max(array_column($arrProduksi, 4));
        $max_apr = max(array_column($arrProduksi, 5));
        $max_mei = max(array_column($arrProduksi, 6));
        $max_jun = max(array_column($arrProduksi, 7));
        $max_jul = max(array_column($arrProduksi, 8));
        $max_agu = max(array_column($arrProduksi, 9));
        $max_sep = max(array_column($arrProduksi, 10));
        $max_okt = max(array_column($arrProduksi, 11));
        $max_nov = max(array_column($arrProduksi, 12));
        $max_des = max(array_column($arrProduksi, 13));
        $max_cum = max(array_column($arrProduksi, 14));
        $max_flok_rugi = max(array_column($arrProduksi, 15));

        $min_jan = min(array_column($arrProduksi, 2));
        $min_feb = min(array_column($arrProduksi, 3));
        $min_mar = min(array_column($arrProduksi, 4));
        $min_apr = min(array_column($arrProduksi, 5));
        $min_mei = min(array_column($arrProduksi, 6));
        $min_jun = min(array_column($arrProduksi, 7));
        $min_jul = min(array_column($arrProduksi, 8));
        $min_agu = min(array_column($arrProduksi, 9));
        $min_sep = min(array_column($arrProduksi, 10));
        $min_okt = min(array_column($arrProduksi, 11));
        $min_nov = min(array_column($arrProduksi, 12));
        $min_des = min(array_column($arrProduksi, 13));
        $min_cum = min(array_column($arrProduksi, 14));
        $min_flok_rugi = min(array_column($arrProduksi, 15));

        if($sort=='desc'){
            array_multisort(array_column($arrProduksi, $index), SORT_DESC, $arrProduksi);
            $sort='asc';
        }else{
            array_multisort(array_column($arrProduksi, $index), SORT_ASC, $arrProduksi);
            $sort='desc';
        }


        $rugibulan = DB::select("SELECT MONTH(tgldocfinal) AS bln, round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                    FROM table_rhpp WHERE YEAR(tgldocfinal) = '$tahun' GROUP BY MONTH(tgldocfinal)");

        $rugifoot = DB::select("SELECT kode, val FROM (SELECT MONTH(tgldocfinal) AS bln, round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                                FROM table_rhpp WHERE YEAR(tgldocfinal) = '$tahun' GROUP BY MONTH(tgldocfinal) ) a
                                RIGHT JOIN ( SELECT kode FROM bulan) b ON a.bln = b.kode
                                UNION ALL
                                SELECT 'ALL', round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                                FROM table_rhpp WHERE YEAR(tgldocfinal) = '$tahun'
                                UNION ALL
                                SELECT 'PERSEN', COUNT(nomrhpprugi)/
										  (SELECT COUNT(ciawal) FROM table_rhpp)*100 AS val
                                FROM table_rhpp WHERE nomrhpprugi > 0 AND YEAR(tgldocfinal) = '$tahun'");

        $arrfoot = [];
        foreach ($rugifoot as $val) {
            array_push($arrfoot,$val->val);
        }

        $arrBln = [];
        foreach ($rugibulan as $val) {
            array_push($arrBln,shortMonth($val->bln));
        }

        $arrVal = [];
        foreach ($rugibulan as $val) {
            array_push($arrVal,$val->val);
        }

        $valArray = [];
        foreach ($arrProduksi as $val) {
            array_push($valArray, $val[16]);
        }

        $counts = array_count_values($valArray);
        $sangatbaik = !empty($counts['SANGAT BAIK']) ? $counts['SANGAT BAIK'] : '0';
        $baik = !empty($counts['BAIK']) ? $counts['BAIK'] : '0';
        $sedang = !empty($counts['SEDANG']) ? $counts['SEDANG'] : '0';
        $kurang = !empty($counts['KURANG']) ? $counts['KURANG'] : '0';
        $sangatkurang = !empty($counts['SANGAT KURANG']) ? $counts['SANGAT KURANG'] : '0';
        $total = count($valArray);

        $arrSangatBaik = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'SANGAT BAIK'){
                array_push($arrSangatBaik, [$val[0], round($val[14],0)]);
            }
        }

        $arrBaik = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'BAIK'){
                array_push($arrBaik, [$val[0], round($val[14],0)]);
            }
        }

        $arrSedang = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'SEDANG'){
                array_push($arrSedang, [$val[0], round($val[14],0)]);
            }
        }

        $arrKurang = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'KURANG'){
                array_push($arrKurang, [$val[0], round($val[14],0)]);
            }
        }

        $arrSangatKurang = [];
        foreach ($arrProduksi as $val) {
            if ($val[16] == 'SANGAT KURANG'){
                array_push($arrSangatKurang, [$val[0], round($val[14],0)]);
            }
        }

        if($tahun==''){
            $tahun='PILIH';
        }
        return view('dashboard.produksi.rugiProduksiAp',compact('no','arrProduksi','sort','arrVal','arrBln','tahun','thn',
        'sangatbaik','baik','sedang','kurang','sangatkurang','total','arrfoot',
        'arrSangatBaik','arrBaik','arrSedang','arrKurang','arrSangatKurang',
        'max_jan','max_feb','max_mar','max_apr','max_mei','max_jun','max_jul','max_agu','max_sep','max_okt','max_nov','max_des','max_cum','max_flok_rugi',
        'min_jan','min_feb','min_mar','min_apr','min_mei','min_jun','min_jul','min_agu','min_sep','min_okt','min_nov','min_des','min_cum','min_flok_rugi'));
    }

    public function margin(Request $request){
        $index = $request->segment(3);
        $sort = $request->segment(4);
        $reg = $request->segment(5);
        $tab = $request->segment(6);
        $thn = $request->segment(7);
        $region = $request->input('region');
        $tahun = $request->input('tahun');

        if($tahun==''){
            $tahun=$thn;
        }

        if($region==''){
            $region='SEMUA';
        }

        if($reg==''){
            $reg='SEMUA';
        }

        if($tab=='2'){
            $region='SEMUA';
        }

        $kosong = '';
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");


        if($region!='SEMUA' || $reg!='SEMUA'){
            $strWhereUnit = "WHERE YEAR(tgldocfinal)= '$tahun' AND region='$region' AND unit IS NOT NULL";
        }else{
            $strWhereUnit = "WHERE YEAR(tgldocfinal)= '$tahun' AND unit IS NOT NULL";
        }

        $noUnit = 1;
        $marginUnit = DB::select("SELECT unit AS kodeunit, region,
                                            (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) AS YtdHpp,
                                            (SUM(jualayamactual)/SUM(cokg)) AS YtdHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END) AS JanHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END) AS JanHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END) AS FebHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END) AS FebHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END) AS MarHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END) AS MarHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END) AS AprHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END) AS AprHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END) AS MeiHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END) AS MeiHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END) AS JunHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END) AS JunHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END) AS JulHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END) AS JulHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END) AS AguHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END) AS AguHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END) AS SepHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END) AS SepHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END) AS OktHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END) AS OktHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END) AS NovHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END) AS NovHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END) AS DesHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END) AS DesHj
                                            FROM vrhpp $strWhereUnit GROUP BY unit ORDER BY region, unit ASC");

            $unitFoot = DB::select("SELECT unit AS kodeunit, region,
                                            (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) AS YtdHpp,
                                            (SUM(jualayamactual)/SUM(cokg)) AS YtdHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END) AS JanHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END) AS JanHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END) AS FebHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END) AS FebHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END) AS MarHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END) AS MarHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END) AS AprHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END) AS AprHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END) AS MeiHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END) AS MeiHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END) AS JunHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END) AS JunHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END) AS JulHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END) AS JulHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END) AS AguHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END) AS AguHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END) AS SepHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END) AS SepHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END) AS OktHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END) AS OktHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END) AS NovHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END) AS NovHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END) AS DesHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END) AS DesHj
                                            FROM vrhpp $strWhereUnit ORDER BY region, unit ASC");

        $units = array();
        foreach($marginUnit as $data){
            array_push($units, [$kodeunit = $data->kodeunit,
            $koderegion = $data->region,
            $YtdHpp = $data->YtdHpp,
            $YtdHj = $data->YtdHj,
            $YtdMargin = $YtdHj-$YtdHpp,

            $JanHpp = $data->JanHpp,
            $JanHj = $data->JanHj,
            $JanMargin = $JanHj-$JanHpp,

            $FebHpp = $data->FebHpp,
            $FebHj = $data->FebHj,
            $FebMargin = $FebHj-$FebHpp,

            $MarHpp = $data->MarHpp,
            $MarHj = $data->MarHj,
            $MarMargin = $MarHj-$MarHpp,

            $AprHpp = $data->AprHpp,
            $AprHj = $data->AprHj,
            $AprMargin = $AprHj-$AprHpp,

            $MeiHpp = $data->MeiHpp,
            $MeiHj = $data->MeiHj,
            $MeiMargin = $MeiHj-$MeiHpp,

            $JunHpp = $data->JunHpp,
            $JunHj = $data->JunHj,
            $JunMargin = $JunHj-$JunHpp,

            $JulHpp = $data->JulHpp,
            $JulHj = $data->JulHj,
            $JulMargin = $JulHj-$JulHpp,

            $AguHpp = $data->AguHpp,
            $AguHj = $data->AguHj,
            $AguMargin = $AguHj-$AguHpp,

            $SepHpp = $data->SepHpp,
            $SepHj = $data->SepHj,
            $SepMargin = $SepHj-$SepHpp,

            $OktHpp = $data->OktHpp,
            $OktHj = $data->OktHj,
            $OktMargin = $OktHj-$OktHpp,

            $NovHpp = $data->NovHpp,
            $NovHj = $data->NovHj,
            $NovMargin = $NovHj-$NovHpp,

            $DesHpp = $data->DesHpp,
            $DesHj = $data->DesHj,
            $DesMargin = $DesHj-$DesHpp,

            ]);
        }

        $arrUnitFoot = array();
        foreach($unitFoot as $data){
            array_push($arrUnitFoot, [$kodeunit = '',
            $koderegion = '',
            $YtdHpp = $data->YtdHpp,
            $YtdHj = $data->YtdHj,
            $YtdMargin = $YtdHj-$YtdHpp,

            $JanHpp = $data->JanHpp,
            $JanHj = $data->JanHj,
            $JanMargin = $JanHj-$JanHpp,

            $FebHpp = $data->FebHpp,
            $FebHj = $data->FebHj,
            $FebMargin = $FebHj-$FebHpp,

            $MarHpp = $data->MarHpp,
            $MarHj = $data->MarHj,
            $MarMargin = $MarHj-$MarHpp,

            $AprHpp = $data->AprHpp,
            $AprHj = $data->AprHj,
            $AprMargin = $AprHj-$AprHpp,

            $MeiHpp = $data->MeiHpp,
            $MeiHj = $data->MeiHj,
            $MeiMargin = $MeiHj-$MeiHpp,

            $JunHpp = $data->JunHpp,
            $JunHj = $data->JunHj,
            $JunMargin = $JunHj-$JunHpp,

            $JulHpp = $data->JulHpp,
            $JulHj = $data->JulHj,
            $JulMargin = $JulHj-$JulHpp,

            $AguHpp = $data->AguHpp,
            $AguHj = $data->AguHj,
            $AguMargin = $AguHj-$AguHpp,

            $SepHpp = $data->SepHpp,
            $SepHj = $data->SepHj,
            $SepMargin = $SepHj-$SepHpp,

            $OktHpp = $data->OktHpp,
            $OktHj = $data->OktHj,
            $OktMargin = $OktHj-$OktHpp,

            $NovHpp = $data->NovHpp,
            $NovHj = $data->NovHj,
            $NovMargin = $NovHj-$NovHpp,

            $DesHpp = $data->DesHpp,
            $DesHj = $data->DesHj,
            $DesMargin = $DesHj-$DesHpp,

            ]);
        }
        //return $units;
        // $mn_adjfc_jbr =
        $maxUnit_hppYtd = array_filter(array_column($units, 2)) == null ? 0 : max(array_filter(array_column($units, 2)));
        $maxUnit_hjYtd = array_filter(array_column($units, 3)) == null ? 0 : max(array_filter(array_column($units, 3)));
        $maxUnit_mgrYtd = array_filter(array_column($units, 4)) == null ? 0 : max(array_filter(array_column($units, 4)));

        $minUnit_hppYtd = array_filter(array_column($units, 2)) == null ? 0 : min(array_filter(array_column($units, 2)));
        $minUnit_hjYtd = array_filter(array_column($units, 3)) == null ? 0 : min(array_filter(array_column($units, 3)));
        $minUnit_mgrYtd = array_filter(array_column($units, 4)) == null ? 0 : min(array_filter(array_column($units, 4)));

        $maxUnit_hppJan = array_filter(array_column($units, 5)) == null ? 0 : max(array_filter(array_column($units, 5)));
        $maxUnit_hjJan = array_filter(array_column($units, 6)) == null ? 0 : max(array_filter(array_column($units, 6)));
        $maxUnit_mgrJan = array_filter(array_column($units, 7)) == null ? 0 : max(array_filter(array_column($units, 7)));

        $minUnit_hppJan = array_filter(array_column($units, 5)) == null ? 0 : min(array_filter(array_column($units, 5)));
        $minUnit_hjJan = array_filter(array_column($units, 6)) == null ? 0 : min(array_filter(array_column($units, 6)));
        $minUnit_mgrJan = array_filter(array_column($units, 7)) == null ? 0 : min(array_filter(array_column($units, 7)));

        $maxUnit_hppFeb = array_filter(array_column($units, 8)) == null ? 0 : max(array_filter(array_column($units, 8)));
        $maxUnit_hjFeb = array_filter(array_column($units, 9)) == null ? 0 : max(array_filter(array_column($units, 9)));
        $maxUnit_mgrFeb = array_filter(array_column($units, 10)) == null ? 0 : max(array_filter(array_column($units, 10)));

        $minUnit_hppFeb = array_filter(array_column($units, 8)) == null ? 0 : min(array_filter(array_column($units, 8)));
        $minUnit_hjFeb = array_filter(array_column($units, 9)) == null ? 0 : min(array_filter(array_column($units, 9)));
        $minUnit_mgrFeb = array_filter(array_column($units, 10)) == null ? 0 : min(array_filter(array_column($units, 10)));

        $maxUnit_hppMar = array_filter(array_column($units, 11)) == null ? 0 : max(array_filter(array_column($units, 11)));
        $maxUnit_hjMar = array_filter(array_column($units, 12)) == null ? 0 : max(array_filter(array_column($units, 12)));
        $maxUnit_mgrMar = array_filter(array_column($units, 13)) == null ? 0 : max(array_filter(array_column($units, 13)));

        $minUnit_hppMar = array_filter(array_column($units, 11)) == null ? 0 : min(array_filter(array_column($units, 11)));
        $minUnit_hjMar = array_filter(array_column($units, 12)) == null ? 0 : min(array_filter(array_column($units, 12)));
        $minUnit_mgrMar = array_filter(array_column($units, 13)) == null ? 0 : min(array_filter(array_column($units, 13)));

        $maxUnit_hppApr = array_filter(array_column($units, 14)) == null ? 0 : max(array_filter(array_column($units, 14)));
        $maxUnit_hjApr = array_filter(array_column($units, 15)) == null ? 0 : max(array_filter(array_column($units, 15)));
        $maxUnit_mgrApr = array_filter(array_column($units, 16)) == null ? 0 : max(array_filter(array_column($units, 16)));

        $minUnit_hppApr = array_filter(array_column($units, 14)) == null ? 0 : min(array_filter(array_column($units, 14)));
        $minUnit_hjApr = array_filter(array_column($units, 15)) == null ? 0 : min(array_filter(array_column($units, 15)));
        $minUnit_mgrApr = array_filter(array_column($units, 16)) == null ? 0 : min(array_filter(array_column($units, 16)));

        $maxUnit_hppMei = array_filter(array_column($units, 17)) == null ? 0 : max(array_filter(array_column($units, 17)));
        $maxUnit_hjMei = array_filter(array_column($units, 18)) == null ? 0 : max(array_filter(array_column($units, 18)));
        $maxUnit_mgrMei = array_filter(array_column($units, 19)) == null ? 0 : max(array_filter(array_column($units, 19)));

        $minUnit_hppMei = array_filter(array_column($units, 17)) == null ? 0 : min(array_filter(array_column($units, 17)));
        $minUnit_hjMei = array_filter(array_column($units, 18)) == null ? 0 : min(array_filter(array_column($units, 18)));
        $minUnit_mgrMei = array_filter(array_column($units, 19)) == null ? 0 : min(array_filter(array_column($units, 19)));

        $maxUnit_hppJun = array_filter(array_column($units, 20)) == null ? 0 : max(array_filter(array_column($units, 20)));
        $maxUnit_hjJun = array_filter(array_column($units, 21)) == null ? 0 : max(array_filter(array_column($units, 21)));
        $maxUnit_mgrJun = array_filter(array_column($units, 22)) == null ? 0 : max(array_filter(array_column($units, 22)));

        $minUnit_hppJun = array_filter(array_column($units, 20)) == null ? 0 : min(array_filter(array_column($units, 20)));
        $minUnit_hjJun = array_filter(array_column($units, 21)) == null ? 0 : min(array_filter(array_column($units, 21)));
        $minUnit_mgrJun = array_filter(array_column($units, 22)) == null ? 0 : min(array_filter(array_column($units, 22)));

        $maxUnit_hppJul = array_filter(array_column($units, 23)) == null ? 0 : max(array_filter(array_column($units, 23)));
        $maxUnit_hjJul = array_filter(array_column($units, 24)) == null ? 0 : max(array_filter(array_column($units, 24)));
        $maxUnit_mgrJul = array_filter(array_column($units, 25)) == null ? 0 : max(array_filter(array_column($units, 25)));

        $minUnit_hppJul = array_filter(array_column($units, 23)) == null ? 0 : min(array_filter(array_column($units, 23)));
        $minUnit_hjJul = array_filter(array_column($units, 24)) == null ? 0 : min(array_filter(array_column($units, 24)));
        $minUnit_mgrJul = array_filter(array_column($units, 25)) == null ? 0 : min(array_filter(array_column($units, 25)));

        $maxUnit_hppAgu = array_filter(array_column($units, 26)) == null ? 0 : max(array_filter(array_column($units, 26)));
        $maxUnit_hjAgu = array_filter(array_column($units, 27)) == null ? 0 : max(array_filter(array_column($units, 27)));
        $maxUnit_mgrAgu = array_filter(array_column($units, 28)) == null ? 0 : max(array_filter(array_column($units, 28)));

        $minUnit_hppAgu = array_filter(array_column($units, 26)) == null ? 0 : min(array_filter(array_column($units, 26)));
        $minUnit_hjAgu = array_filter(array_column($units, 27)) == null ? 0 : min(array_filter(array_column($units, 27)));
        $minUnit_mgrAgu = array_filter(array_column($units, 28)) == null ? 0 : min(array_filter(array_column($units, 28)));

        $maxUnit_hppSep = array_filter(array_column($units, 29)) == null ? 0 : max(array_filter(array_column($units, 29)));
        $maxUnit_hjSep = array_filter(array_column($units, 30)) == null ? 0 : max(array_filter(array_column($units, 30)));
        $maxUnit_mgrSep = array_filter(array_column($units, 31)) == null ? 0 : max(array_filter(array_column($units, 31)));

        $minUnit_hppSep = array_filter(array_column($units, 29)) == null ? 0 : min(array_filter(array_column($units, 29)));
        $minUnit_hjSep = array_filter(array_column($units, 30)) == null ? 0 : min(array_filter(array_column($units, 30)));
        $minUnit_mgrSep = array_filter(array_column($units, 31)) == null ? 0 : min(array_filter(array_column($units, 31)));

        $maxUnit_hppOkt = array_filter(array_column($units, 32)) == null ? 0 : max(array_filter(array_column($units, 32)));
        $maxUnit_hjOkt = array_filter(array_column($units, 33)) == null ? 0 : max(array_filter(array_column($units, 33)));
        $maxUnit_mgrOkt = array_filter(array_column($units, 34)) == null ? 0 : max(array_filter(array_column($units, 34)));

        $minUnit_hppOkt = array_filter(array_column($units, 32)) == null ? 0 : min(array_filter(array_column($units, 32)));
        $minUnit_hjOkt = array_filter(array_column($units, 33)) == null ? 0 : min(array_filter(array_column($units, 33)));
        $minUnit_mgrOkt = array_filter(array_column($units, 34)) == null ? 0 : min(array_filter(array_column($units, 34)));

        $maxUnit_hppNov = array_filter(array_column($units, 35)) == null ? 0 : max(array_filter(array_column($units, 35)));
        $maxUnit_hjNov = array_filter(array_column($units, 36)) == null ? 0 : max(array_filter(array_column($units, 36)));
        $maxUnit_mgrNov = array_filter(array_column($units, 37)) == null ? 0 : max(array_filter(array_column($units, 37)));

        $minUnit_hppNov = array_filter(array_column($units, 35)) == null ? 0 : min(array_filter(array_column($units, 35)));
        $minUnit_hjNov = array_filter(array_column($units, 36)) == null ? 0 : min(array_filter(array_column($units, 36)));
        $minUnit_mgrNov = array_filter(array_column($units, 37)) == null ? 0 : min(array_filter(array_column($units, 37)));

        $maxUnit_hppDes = array_filter(array_column($units, 38)) == null ? 0 : max(array_filter(array_column($units, 38)));
        $maxUnit_hjDes = array_filter(array_column($units, 39)) == null ? 0 : max(array_filter(array_column($units, 39)));
        $maxUnit_mgrDes = array_filter(array_column($units, 40)) == null ? 0 : max(array_filter(array_column($units, 40)));

        $minUnit_hppDes = array_filter(array_column($units, 38)) == null ? 0 : min(array_filter(array_column($units, 38)));
        $minUnit_hjDes = array_filter(array_column($units, 39)) == null ? 0 : min(array_filter(array_column($units, 39)));
        $minUnit_mgrDes = array_filter(array_column($units, 40)) == null ? 0 : min(array_filter(array_column($units, 40)));

        //$gabMaxUnitHpp = array($maxUnit_hppJan,$maxUnit_hppFeb,$maxUnit_hppMar,$maxUnit_hppApr,$maxUnit_hppMei,$maxUnit_hppJun,$maxUnit_hppJul,$maxUnit_hppAgu,$maxUnit_hppSep,$maxUnit_hppOkt,$maxUnit_hppNov,$maxUnit_hppDes);
        //$gabMaxUnitHj = array($maxUnit_hjJan,$maxUnit_hjFeb,$maxUnit_hjMar,$maxUnit_hjApr,$maxUnit_hjMei,$maxUnit_hjJun,$maxUnit_hjJul,$maxUnit_hjAgu,$maxUnit_hjSep,$maxUnit_hjOkt,$maxUnit_hjNov,$maxUnit_hjDes);
        $gabMaxUnitMgr = array($maxUnit_mgrJan,$maxUnit_mgrFeb,$maxUnit_mgrMar,$maxUnit_mgrApr,$maxUnit_mgrMei,$maxUnit_mgrJun,$maxUnit_mgrJul,$maxUnit_mgrAgu,$maxUnit_mgrSep,$maxUnit_mgrOkt,$maxUnit_mgrNov,$maxUnit_mgrDes);

        //$gabMinUnitHpp = array($minUnit_hppJan,$minUnit_hppFeb,$minUnit_hppMar,$minUnit_hppApr,$minUnit_hppMei,$minUnit_hppJun,$minUnit_hppJul,$minUnit_hppAgu,$minUnit_hppSep,$minUnit_hppOkt,$minUnit_hppNov,$minUnit_hppDes);
        //$gabMinUnitHj = array($minUnit_hjJan,$minUnit_hjFeb,$minUnit_hjMar,$minUnit_hjApr,$minUnit_hjMei,$minUnit_hjJun,$minUnit_hjJul,$minUnit_hjAgu,$minUnit_hjSep,$minUnit_hjOkt,$minUnit_hjNov,$minUnit_hjDes);
        $gabMinUnitMgr = array($minUnit_mgrJan,$minUnit_mgrFeb,$minUnit_mgrMar,$minUnit_mgrApr,$minUnit_mgrMei,$minUnit_mgrJun,$minUnit_mgrJul,$minUnit_mgrAgu,$minUnit_mgrSep,$minUnit_mgrOkt,$minUnit_mgrNov,$minUnit_mgrDes);

        //$maxUnitHpp = max($gabMaxUnitHpp);
        //$maxUnitHj = max($gabMaxUnitHj);
        //$maxUnitMgr = max($gabMaxUnitMgr);

        //$minUnitHpp = min($gabMinUnitHpp);
        //$minUnitHj = min($gabMinUnitHj);
        //$minUnitMgr = min($gabMinUnitMgr);
        //return $maxUnitHpp;


        if($index!=''){
           $region = $reg;
        }
        //return $units;
        $noRegion = 1;
        $marginRegion = DB::select("SELECT unit, region,
                                            (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) AS YtdHpp,
                                            (SUM(jualayamactual)/SUM(cokg)) AS YtdHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END) AS JanHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END) AS JanHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END) AS FebHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END) AS FebHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END) AS MarHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END) AS MarHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END) AS AprHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END) AS AprHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END) AS MeiHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END) AS MeiHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END) AS JunHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END) AS JunHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END) AS JulHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END) AS JulHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END) AS AguHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END) AS AguHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END) AS SepHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END) AS SepHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END) AS OktHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END) AS OktHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END) AS NovHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END) AS NovHj,
                                            (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN valtotbeli ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN nomrhpptotal ELSE 0 END)+SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN hitunguangselisih ELSE 0 END))/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END) AS DesHpp,
                                            SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN valtotbeli ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END) AS DesHj
                                            FROM vrhpp WHERE YEAR(tgldocfinal)= '$tahun' AND region IS NOT NULL GROUP BY region ORDER BY region ASC");

            $regions = array();
            foreach($marginRegion as $data){
                array_push($regions, [
                $koderegion = $data->region,
                $YtdHpp = $data->YtdHpp,
                $YtdHj = $data->YtdHj,
                $YtdMargin = $YtdHj-$YtdHpp,

                $JanHpp = $data->JanHpp,
                $JanHj = $data->JanHj,
                $JanMargin = $JanHj-$JanHpp,

                $FebHpp = $data->FebHpp,
                $FebHj = $data->FebHj,
                $FebMargin = $FebHj-$FebHpp,

                $MarHpp = $data->MarHpp,
                $MarHj = $data->MarHj,
                $MarMargin = $MarHj-$MarHpp,

                $AprHpp = $data->AprHpp,
                $AprHj = $data->AprHj,
                $AprMargin = $AprHj-$AprHpp,

                $MeiHpp = $data->MeiHpp,
                $MeiHj = $data->MeiHj,
                $MeiMargin = $MeiHj-$MeiHpp,

                $JunHpp = $data->JunHpp,
                $JunHj = $data->JunHj,
                $JunMargin = $JunHj-$JunHpp,

                $JulHpp = $data->JulHpp,
                $JulHj = $data->JulHj,
                $JulMargin = $JulHj-$JulHpp,

                $AguHpp = $data->AguHpp,
                $AguHj = $data->AguHj,
                $AguMargin = $AguHj-$AguHpp,

                $SepHpp = $data->SepHpp,
                $SepHj = $data->SepHj,
                $SepMargin = $SepHj-$SepHpp,

                $OktHpp = $data->OktHpp,
                $OktHj = $data->OktHj,
                $OktMargin = $OktHj-$OktHpp,

                $NovHpp = $data->NovHpp,
                $NovHj = $data->NovHj,
                $NovMargin = $NovHj-$NovHpp,

                $DesHpp = $data->DesHpp,
                $DesHj = $data->DesHj,
                $DesMargin = $DesHj-$DesHpp,

                ]);
            }

            $apFoot = DB::select("SELECT ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg)),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'");

                $arrApFoot = array();
                foreach($apFoot as $data){
                    array_push($arrApFoot, [$kodeunit = '',
                    $koderegion = '',
                    $YtdHpp = $data->YtdHpp,
                    $YtdHj = $data->YtdHj,
                    $YtdMargin = $YtdHj-$YtdHpp,

                    $JanHpp = $data->JanHpp,
                    $JanHj = $data->JanHj,
                    $JanMargin = $JanHj-$JanHpp,

                    $FebHpp = $data->FebHpp,
                    $FebHj = $data->FebHj,
                    $FebMargin = $FebHj-$FebHpp,

                    $MarHpp = $data->MarHpp,
                    $MarHj = $data->MarHj,
                    $MarMargin = $MarHj-$MarHpp,

                    $AprHpp = $data->AprHpp,
                    $AprHj = $data->AprHj,
                    $AprMargin = $AprHj-$AprHpp,

                    $MeiHpp = $data->MeiHpp,
                    $MeiHj = $data->MeiHj,
                    $MeiMargin = $MeiHj-$MeiHpp,

                    $JunHpp = $data->JunHpp,
                    $JunHj = $data->JunHj,
                    $JunMargin = $JunHj-$JunHpp,

                    $JulHpp = $data->JulHpp,
                    $JulHj = $data->JulHj,
                    $JulMargin = $JulHj-$JulHpp,

                    $AguHpp = $data->AguHpp,
                    $AguHj = $data->AguHj,
                    $AguMargin = $AguHj-$AguHpp,

                    $SepHpp = $data->SepHpp,
                    $SepHj = $data->SepHj,
                    $SepMargin = $SepHj-$SepHpp,

                    $OktHpp = $data->OktHpp,
                    $OktHj = $data->OktHj,
                    $OktMargin = $OktHj-$OktHpp,

                    $NovHpp = $data->NovHpp,
                    $NovHj = $data->NovHj,
                    $NovMargin = $NovHj-$NovHpp,

                    $DesHpp = $data->DesHpp,
                    $DesHj = $data->DesHj,
                    $DesMargin = $DesHj-$DesHpp,

                    ]);
                }


            $maxReg_hppYtd = array_filter(array_column($regions, 1)) == null ? 0 : max(array_filter(array_column($regions, 1)));
            $maxReg_hjYtd = array_filter(array_column($regions, 2)) == null ? 0 : max(array_filter(array_column($regions, 2)));
            $maxReg_mgrYtd = array_filter(array_column($regions, 3)) == null ? 0 : max(array_filter(array_column($regions, 3)));
            $minReg_mgrYtd = array_filter(array_column($regions, 3)) == null ? 0 : min(array_filter(array_column($regions, 3)));

            $maxReg_hppJan = array_filter(array_column($regions, 4)) == null ? 0 : max(array_filter(array_column($regions, 4)));
            $maxReg_hjJan = array_filter(array_column($regions, 5)) == null ? 0 : max(array_filter(array_column($regions, 5)));
            $maxReg_mgrJan = array_filter(array_column($regions, 6)) == null ? 0 : max(array_filter(array_column($regions, 6)));
            $minReg_mgrJan = array_filter(array_column($regions, 6)) == null ? 0 : min(array_filter(array_column($regions, 6)));

            $maxReg_hppFeb = array_filter(array_column($regions, 7)) == null ? 0 : max(array_filter(array_column($regions, 7)));
            $maxReg_hjFeb = array_filter(array_column($regions, 8)) == null ? 0 : max(array_filter(array_column($regions, 8)));
            $maxReg_mgrFeb = array_filter(array_column($regions, 9)) == null ? 0 : max(array_filter(array_column($regions, 9)));
            $minReg_mgrFeb = array_filter(array_column($regions, 9)) == null ? 0 : min(array_filter(array_column($regions, 9)));

            $maxReg_hppMar = array_filter(array_column($regions, 10)) == null ? 0 : max(array_filter(array_column($regions, 10)));
            $maxReg_hjMar = array_filter(array_column($regions, 11)) == null ? 0 : max(array_filter(array_column($regions, 11)));
            $maxReg_mgrMar = array_filter(array_column($regions, 12)) == null ? 0 : max(array_filter(array_column($regions, 12)));
            $minReg_mgrMar = array_filter(array_column($regions, 12)) == null ? 0 : min(array_filter(array_column($regions, 12)));

            $maxReg_hppApr = array_filter(array_column($regions, 13)) == null ? 0 : max(array_filter(array_column($regions, 13)));
            $maxReg_hjApr = array_filter(array_column($regions, 14)) == null ? 0 : max(array_filter(array_column($regions, 14)));
            $maxReg_mgrApr = array_filter(array_column($regions, 15)) == null ? 0 : max(array_filter(array_column($regions, 15)));
            $minReg_mgrApr = array_filter(array_column($regions, 15)) == null ? 0 : min(array_filter(array_column($regions, 15)));

            $maxReg_hppMei = array_filter(array_column($regions, 16)) == null ? 0 : max(array_filter(array_column($regions, 16)));
            $maxReg_hjMei = array_filter(array_column($regions, 17)) == null ? 0 : max(array_filter(array_column($regions, 17)));
            $maxReg_mgrMei = array_filter(array_column($regions, 18)) == null ? 0 : max(array_filter(array_column($regions, 18)));
            $minReg_mgrMei = array_filter(array_column($regions, 18)) == null ? 0 : min(array_filter(array_column($regions, 18)));

            $maxReg_hppJun = array_filter(array_column($regions, 19)) == null ? 0 : max(array_filter(array_column($regions, 19)));
            $maxReg_hjJun = array_filter(array_column($regions, 20)) == null ? 0 : max(array_filter(array_column($regions, 20)));
            $maxReg_mgrJun = array_filter(array_column($regions, 21)) == null ? 0 : max(array_filter(array_column($regions, 21)));
            $minReg_mgrJun = array_filter(array_column($regions, 21)) == null ? 0 : min(array_filter(array_column($regions, 21)));

            $maxReg_hppJul = array_filter(array_column($regions, 22)) == null ? 0 : max(array_filter(array_column($regions, 22)));
            $maxReg_hjJul = array_filter(array_column($regions, 23)) == null ? 0 : max(array_filter(array_column($regions, 23)));
            $maxReg_mgrJul = array_filter(array_column($regions, 24)) == null ? 0 : max(array_filter(array_column($regions, 24)));
            $minReg_mgrJul = array_filter(array_column($regions, 24)) == null ? 0 : min(array_filter(array_column($regions, 24)));

            $maxReg_hppAgu = array_filter(array_column($regions, 25)) == null ? 0 : max(array_filter(array_column($regions, 25)));
            $maxReg_hjAgu = array_filter(array_column($regions, 26)) == null ? 0 : max(array_filter(array_column($regions, 26)));
            $maxReg_mgrAgu = array_filter(array_column($regions, 27)) == null ? 0 : max(array_filter(array_column($regions, 27)));
            $minReg_mgrAgu = array_filter(array_column($regions, 27)) == null ? 0 : min(array_filter(array_column($regions, 27)));

            $maxReg_hppSep = array_filter(array_column($regions, 28)) == null ? 0 : max(array_filter(array_column($regions, 28)));
            $maxReg_hjSep = array_filter(array_column($regions, 29)) == null ? 0 : max(array_filter(array_column($regions, 29)));
            $maxReg_mgrSep = array_filter(array_column($regions, 30)) == null ? 0 : max(array_filter(array_column($regions, 30)));
            $minReg_mgrSep = array_filter(array_column($regions, 30)) == null ? 0 : min(array_filter(array_column($regions, 30)));

            $maxReg_hppOkt = array_filter(array_column($regions, 31)) == null ? 0 : max(array_filter(array_column($regions, 31)));
            $maxReg_hjOkt = array_filter(array_column($regions, 32)) == null ? 0 : max(array_filter(array_column($regions, 32)));
            $maxReg_mgrOkt = array_filter(array_column($regions, 33)) == null ? 0 : max(array_filter(array_column($regions, 33)));
            $minReg_mgrOkt = array_filter(array_column($regions, 33)) == null ? 0 : min(array_filter(array_column($regions, 33)));

            $maxReg_hppNov = array_filter(array_column($regions, 34)) == null ? 0 : max(array_filter(array_column($regions, 34)));
            $maxReg_hjNov = array_filter(array_column($regions, 35)) == null ? 0 : max(array_filter(array_column($regions, 35)));
            $maxReg_mgrNov = array_filter(array_column($regions, 36)) == null ? 0 : max(array_filter(array_column($regions, 36)));
            $minReg_mgrNov = array_filter(array_column($regions, 36)) == null ? 0 : min(array_filter(array_column($regions, 36)));

            $maxReg_hppDes = array_filter(array_column($regions, 37)) == null ? 0 : max(array_filter(array_column($regions, 37)));
            $maxReg_hjDes = array_filter(array_column($regions, 38)) == null ? 0 : max(array_filter(array_column($regions, 38)));
            $maxReg_mgrDes = array_filter(array_column($regions, 39)) == null ? 0 : max(array_filter(array_column($regions, 39)));
            $minReg_mgrDes = array_filter(array_column($regions, 39)) == null ? 0 : min(array_filter(array_column($regions, 39)));

            //$gabMaxRegHpp = array($maxReg_hppJan,$maxReg_hppFeb,$maxReg_hppMar,$maxReg_hppApr,$maxReg_hppMei,$maxReg_hppJun,$maxReg_hppJul,$maxReg_hppAgu,$maxReg_hppSep,$maxReg_hppOkt,$maxReg_hppNov,$maxReg_hppDes);
            //$gabMaxRegHj = array($maxReg_hjJan,$maxReg_hjFeb,$maxReg_hjMar,$maxReg_hjApr,$maxReg_hjMei,$maxReg_hjJun,$maxReg_hjJul,$maxReg_hjAgu,$maxReg_hjSep,$maxReg_hjOkt,$maxReg_hjNov,$maxReg_hjDes);
            //$gabMaxRegMgr = array($maxReg_mgrJan,$maxReg_mgrFeb,$maxReg_mgrMar,$maxReg_mgrApr,$maxReg_mgrMei,$maxReg_mgrJun,$maxReg_mgrJul,$maxReg_mgrAgu,$maxReg_mgrSep,$maxReg_mgrOkt,$maxReg_mgrNov,$maxReg_mgrDes);

            //$maxRegHpp = max($gabMaxRegHpp);
            //$maxRegHj = max($gabMaxRegHj);
            //$maxRegMgr = max($gabMaxRegMgr);
            if($tab!=2){
                $tabUnit = 'active';
                $tabRegion = '';
                if($sort=='desc'){
                    array_multisort(array_column($units, $index), SORT_DESC, $units);
                    $sort='asc';
                }elseif($sort=='asc'){
                    array_multisort(array_column($units, $index), SORT_ASC, $units);
                    $sort='desc';
                }else{
                    array_multisort(array_column($units, 4), SORT_DESC, $units);
                    $sort='asc';
                }
            }else{
                $tabUnit = '';
                $tabRegion = 'active';
                if($sort=='desc'){
                    array_multisort(array_column($regions, $index), SORT_DESC, $regions);
                    $sort='asc';
                }elseif($sort=='asc'){
                    array_multisort(array_column($regions, $index), SORT_ASC, $regions);
                    $sort='desc';
                }else{
                    array_multisort(array_column($regions, 3), SORT_DESC, $regions);
                    $sort='asc';
                }
            }

        if($tahun==''){
            $tahun='PILIH';
        }
           // array_multisort(array_column($regions, 3), SORT_DESC, $regions);
        return view('dashboard.produksi.marginList',compact('tabUnit','tabRegion','noUnit','units','sort','noRegion','regions','ap','region','arrUnitFoot','arrApFoot','tahun',
            'maxUnit_mgrYtd','maxUnit_mgrJan','maxUnit_mgrFeb','maxUnit_mgrMar','maxUnit_mgrApr','maxUnit_mgrMei','maxUnit_mgrJun','maxUnit_mgrJul','maxUnit_mgrAgu','maxUnit_mgrSep','maxUnit_mgrOkt','maxUnit_mgrNov','maxUnit_mgrDes',
            'minUnit_mgrYtd','minUnit_mgrJan','minUnit_mgrFeb','minUnit_mgrMar','minUnit_mgrApr','minUnit_mgrMei','minUnit_mgrJun','minUnit_mgrJul','minUnit_mgrAgu','minUnit_mgrSep','minUnit_mgrOkt','minUnit_mgrNov','minUnit_mgrDes',
            'maxReg_mgrYtd','maxReg_mgrJan','maxReg_mgrFeb','maxReg_mgrMar','maxReg_mgrApr','maxReg_mgrMei','maxReg_mgrJun','maxReg_mgrJul','maxReg_mgrAgu','maxReg_mgrSep','maxReg_mgrOkt','maxReg_mgrNov','maxReg_mgrDes',
            'minReg_mgrYtd','minReg_mgrJan','minReg_mgrFeb','minReg_mgrMar','minReg_mgrApr','minReg_mgrMei','minReg_mgrJun','minReg_mgrJul','minReg_mgrAgu','minReg_mgrSep','minReg_mgrOkt','minReg_mgrNov','minReg_mgrDes'
        ));
    }

    public function marginRegion(Request $request){
        $region = $request->input('region');
        $tahun = $request->input('tahun');

        $kosong = '';
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");

        /*
        $jabatan = Auth::user()->jabatan;
        $koderegion = Auth::user()->region;
        $kosong = '';
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF QA");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if (in_array($jabatan, $akses)) {
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
        } else {
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $koderegion . '" ORDER BY koderegion ASC');
        }
        */

        //$tahun = date('Y');

        if($region==''){
             $marginUnit = DB::table(DB::raw('units a'))
                    ->select('a.kodeunit','a.region',
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj"))
                    ->orderBy('a.region','ASC')
                    ->get();

        }else{
             $marginUnit = DB::table(DB::raw('units a'))
                    ->select('a.kodeunit','a.region',
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj"),
                    DB::raw("ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp"),
                    DB::raw("ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj"))
                    ->where('a.region', '=', $region)
                    ->orderBy('a.region','ASC')
                    ->get();

        }

        $noUnit = 1;

        $noRegion = 1;
        $marginRegion = DB::table(DB::raw("(SELECT a.region,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM units a) b"))
                    ->select('region',DB::raw('SUM(YtdHpp) AS YtdHpp'),DB::raw('SUM(YtdHj) AS YtdHj'),DB::raw('SUM(JanHpp) AS JanHpp'),DB::raw('SUM(JanHj) AS JanHj'),DB::raw('SUM(FebHpp) AS FebHpp'),DB::raw('SUM(FebHj) AS FebHj'),DB::raw('SUM(MarHpp) AS MarHpp'),DB::raw('SUM(MarHj) AS MarHj'),DB::raw('SUM(AprHpp) AS AprHpp'),DB::raw('SUM(AprHj) AS AprHj'),DB::raw('SUM(MeiHpp) AS MeiHpp'),DB::raw('SUM(MeiHj) AS MeiHj'),DB::raw('SUM(JunHpp) AS JunHpp'),DB::raw('SUM(JunHj) AS JunHj'),DB::raw('SUM(JulHpp) AS JulHpp'),DB::raw('SUM(JulHj) AS JulHj'),DB::raw('SUM(AguHpp) AS AguHpp'),DB::raw('SUM(AguHj) AS AguHj'),DB::raw('SUM(SepHpp) AS SepHpp'),DB::raw('SUM(SepHj) AS SepHj'),DB::raw('SUM(OktHpp) AS OktHpp'),DB::raw('SUM(OktHj) AS OktHj'),DB::raw('SUM(NovHpp) AS NovHpp'),DB::raw('SUM(NovHj) AS NovHj'),DB::raw('SUM(DesHpp) AS DesHpp'),DB::raw('SUM(DesHj) AS DesHj'))
                    ->groupByRaw('region ASC')
                    ->get();

        return view('dashboard.produksi.marginList',compact('noUnit','marginUnit','noRegion','marginRegion','ap','region'));
    }

    public function marginPerZona(Request $request){
        $index = $request->segment(4);
        $sort = $request->segment(5);
        $thn = $request->segment(6);
        $kdzona = $request->segment(7);

        $kodezona = $request->input('zona');
        $tahun = $request->input('tahun');

        if($tahun==''){
            $tahun=$thn;
        }

        if($kodezona==''){
            $kodezona=$kdzona;
        }

        $noUnit = 1;
        $marginUnit = DB::select("SELECT kodeunit, region, zona,
                                    YtdHpp, YtdHj,  (YtdHj-YtdHpp) AS YtdMargin,
                                    JanHpp,JanHj,(JanHj-JanHpp) AS JanMargin,
                                    FebHpp,FebHj,(FebHj-FebHpp) AS FebMargin,
                                    MarHpp,MarHj,(MarHj-MarHpp) AS MarMargin,
                                    AprHpp,AprHj,(AprHj-AprHpp) AS AprMargin,
                                    MeiHpp,MeiHj,(MeiHj-MeiHpp) AS MeiMargin,
                                    JunHpp,JunHj,(JunHj-JunHpp) AS JunMargin,
                                    JulHpp,JulHj,(JulHj-JulHpp) AS JulMargin,
                                    AguHpp,AguHj,(AguHj-AguHpp) AS AguMargin,
                                    SepHpp,SepHj,(SepHj-SepHpp) AS SepMargin,
                                    OktHpp,OktHj,(OktHj-OktHpp) AS OktMargin,
                                    NovHpp,NovHj,(NovHj-NovHpp) AS NovMargin,
                                    DesHpp,DesHj,(DesHj-DesHpp) AS DesMargin
                                FROM
                                (SELECT a.kodeunit, a.region,
                                                    a.lokasi AS zona,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                                                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                                                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                                                    FROM units a
                                                    ORDER BY a.region ASC) c ORDER BY zona ASC");

        $units = array();
        if($kodezona!='SEMUA'){
                foreach($marginUnit as $data){
                    if($data->zona == $kodezona){
                        array_push($units, [$kodeunit = $data->kodeunit,
                        $region = $data->region,
                        $zona = $data->zona,

                        $YtdHpp = $data->YtdHpp,
                        $YtdHj = $data->YtdHj,
                        $YtdMargin = $YtdHj-$YtdHpp,
                        $avgMarginZonaYtd=avgMarginZona($zona,0,$tahun),
                        $diffMarginYtd=$YtdMargin-$avgMarginZonaYtd,
                        $MarginPersenYtd=diffMarginPersen($avgMarginZonaYtd,$diffMarginYtd),

                        $JanHpp = $data->JanHpp,
                        $JanHj = $data->JanHj,
                        $JanMargin = $JanHj-$JanHpp,
                        $avgMarginZonaJan=avgMarginZona($zona,1,$tahun),
                        $diffMarginJan=$JanMargin-$avgMarginZonaJan,
                        $MarginPersenJan=diffMarginPersen($avgMarginZonaJan,$diffMarginJan),

                        $FebHpp = $data->FebHpp,
                        $FebHj = $data->FebHj,
                        $FebMargin = $FebHj-$FebHpp,
                        $avgMarginZonaFeb=avgMarginZona($zona,2,$tahun),
                        $diffMarginFeb=$FebMargin-$avgMarginZonaFeb,
                        $MarginPersenFeb=diffMarginPersen($avgMarginZonaFeb,$diffMarginFeb),

                        $MarHpp = $data->MarHpp,
                        $MarHj = $data->MarHj,
                        $MarMargin = $MarHj-$MarHpp,
                        $avgMarginZonaMar=avgMarginZona($zona,3,$tahun),
                        $diffMarginMar=$MarMargin-$avgMarginZonaMar,
                        $MarginPersenMar=diffMarginPersen($avgMarginZonaMar,$diffMarginMar),

                        $AprHpp = $data->AprHpp,
                        $AprHj = $data->AprHj,
                        $AprMargin = $AprHj-$AprHpp,
                        $avgMarginZonaApr=avgMarginZona($zona,4,$tahun),
                        $diffMarginApr=$AprMargin-$avgMarginZonaApr,
                        $MarginPersenApr=diffMarginPersen($avgMarginZonaApr,$diffMarginApr),

                        $MeiHpp = $data->MeiHpp,
                        $MeiHj = $data->MeiHj,
                        $MeiMargin = $MeiHj-$MeiHpp,
                        $avgMarginZonaMei=avgMarginZona($zona,5,$tahun),
                        $diffMarginMei=$MeiMargin-$avgMarginZonaMei,
                        $MarginPersenMei=diffMarginPersen($avgMarginZonaMei,$diffMarginMei),

                        $JunHpp = $data->JunHpp,
                        $JunHj = $data->JunHj,
                        $JunMargin = $JunHj-$JunHpp,
                        $avgMarginZonaJun=avgMarginZona($zona,6,$tahun),
                        $diffMarginJun=$JunMargin-$avgMarginZonaJun,
                        $MarginPersenJun=diffMarginPersen($avgMarginZonaJun,$diffMarginJun),

                        $JulHpp = $data->JulHpp,
                        $JulHj = $data->JulHj,
                        $JulMargin = $JulHj-$JulHpp,
                        $avgMarginZonaJul=avgMarginZona($zona,7,$tahun),
                        $diffMarginJul=$JulMargin-$avgMarginZonaJul,
                        $MarginPersenJul=diffMarginPersen($avgMarginZonaJul,$diffMarginJul),

                        $AguHpp = $data->AguHpp,
                        $AguHj = $data->AguHj,
                        $AguMargin = $AguHj-$AguHpp,
                        $avgMarginZonaAgu=avgMarginZona($zona,8,$tahun),
                        $diffMarginAgu=$AguMargin-$avgMarginZonaAgu,
                        $MarginPersenAgu=diffMarginPersen($avgMarginZonaAgu,$diffMarginAgu),

                        $SepHpp = $data->SepHpp,
                        $SepHj = $data->SepHj,
                        $SepMargin = $SepHj-$SepHpp,
                        $avgMarginZonaSep=avgMarginZona($zona,9,$tahun),
                        $diffMarginSep=$SepMargin-$avgMarginZonaSep,
                        $MarginPersenSep=diffMarginPersen($avgMarginZonaSep,$diffMarginSep),

                        $OktHpp = $data->OktHpp,
                        $OktHj = $data->OktHj,
                        $OktMargin = $OktHj-$OktHpp,
                        $avgMarginZonaOkt=avgMarginZona($zona,10,$tahun),
                        $diffMarginOkt=$OktMargin-$avgMarginZonaOkt,
                        $MarginPersenOkt=diffMarginPersen($avgMarginZonaOkt,$diffMarginOkt),

                        $NovHpp = $data->NovHpp,
                        $NovHj = $data->NovHj,
                        $NovMargin = $NovHj-$NovHpp,
                        $avgMarginZonaNov=avgMarginZona($zona,11,$tahun),
                        $diffMarginNov=$NovMargin-$avgMarginZonaNov,
                        $MarginPersenNov=diffMarginPersen($avgMarginZonaNov,$diffMarginNov),

                        $DesHpp = $data->DesHpp,
                        $DesHj = $data->DesHj,
                        $DesMargin = $DesHj-$DesHpp,
                        $avgMarginZonaDes=avgMarginZona($zona,12,$tahun),
                        $diffMarginDes=$DesMargin-$avgMarginZonaDes,
                        $MarginPersenDes=diffMarginPersen($avgMarginZonaDes,$diffMarginDes),

                        ]);
                    }
                }
        }else{
                foreach($marginUnit as $data){
                        array_push($units, [$kodeunit = $data->kodeunit,
                        $region = $data->region,
                        $zona = $data->zona,

                        $YtdHpp = $data->YtdHpp,
                        $YtdHj = $data->YtdHj,
                        $YtdMargin = $YtdHj-$YtdHpp,
                        $avgMarginZonaYtd=avgMarginZona($zona,0,$tahun),
                        $diffMarginYtd=$YtdMargin-$avgMarginZonaYtd,
                        $MarginPersenYtd=diffMarginPersen($avgMarginZonaYtd,$diffMarginYtd),

                        $JanHpp = $data->JanHpp,
                        $JanHj = $data->JanHj,
                        $JanMargin = $JanHj-$JanHpp,
                        $avgMarginZonaJan=avgMarginZona($zona,1,$tahun),
                        $diffMarginJan=$JanMargin-$avgMarginZonaJan,
                        $MarginPersenJan=diffMarginPersen($avgMarginZonaJan,$diffMarginJan),

                        $FebHpp = $data->FebHpp,
                        $FebHj = $data->FebHj,
                        $FebMargin = $FebHj-$FebHpp,
                        $avgMarginZonaFeb=avgMarginZona($zona,2,$tahun),
                        $diffMarginFeb=$FebMargin-$avgMarginZonaFeb,
                        $MarginPersenFeb=diffMarginPersen($avgMarginZonaFeb,$diffMarginFeb),

                        $MarHpp = $data->MarHpp,
                        $MarHj = $data->MarHj,
                        $MarMargin = $MarHj-$MarHpp,
                        $avgMarginZonaMar=avgMarginZona($zona,3,$tahun),
                        $diffMarginMar=$MarMargin-$avgMarginZonaMar,
                        $MarginPersenMar=diffMarginPersen($avgMarginZonaMar,$diffMarginMar),

                        $AprHpp = $data->AprHpp,
                        $AprHj = $data->AprHj,
                        $AprMargin = $AprHj-$AprHpp,
                        $avgMarginZonaApr=avgMarginZona($zona,4,$tahun),
                        $diffMarginApr=$AprMargin-$avgMarginZonaApr,
                        $MarginPersenApr=diffMarginPersen($avgMarginZonaApr,$diffMarginApr),

                        $MeiHpp = $data->MeiHpp,
                        $MeiHj = $data->MeiHj,
                        $MeiMargin = $MeiHj-$MeiHpp,
                        $avgMarginZonaMei=avgMarginZona($zona,5,$tahun),
                        $diffMarginMei=$MeiMargin-$avgMarginZonaMei,
                        $MarginPersenMei=diffMarginPersen($avgMarginZonaMei,$diffMarginMei),

                        $JunHpp = $data->JunHpp,
                        $JunHj = $data->JunHj,
                        $JunMargin = $JunHj-$JunHpp,
                        $avgMarginZonaJun=avgMarginZona($zona,6,$tahun),
                        $diffMarginJun=$JunMargin-$avgMarginZonaJun,
                        $MarginPersenJun=diffMarginPersen($avgMarginZonaJun,$diffMarginJun),

                        $JulHpp = $data->JulHpp,
                        $JulHj = $data->JulHj,
                        $JulMargin = $JulHj-$JulHpp,
                        $avgMarginZonaJul=avgMarginZona($zona,7,$tahun),
                        $diffMarginJul=$JulMargin-$avgMarginZonaJul,
                        $MarginPersenJul=diffMarginPersen($avgMarginZonaJul,$diffMarginJul),

                        $AguHpp = $data->AguHpp,
                        $AguHj = $data->AguHj,
                        $AguMargin = $AguHj-$AguHpp,
                        $avgMarginZonaAgu=avgMarginZona($zona,8,$tahun),
                        $diffMarginAgu=$AguMargin-$avgMarginZonaAgu,
                        $MarginPersenAgu=diffMarginPersen($avgMarginZonaAgu,$diffMarginAgu),

                        $SepHpp = $data->SepHpp,
                        $SepHj = $data->SepHj,
                        $SepMargin = $SepHj-$SepHpp,
                        $avgMarginZonaSep=avgMarginZona($zona,9,$tahun),
                        $diffMarginSep=$SepMargin-$avgMarginZonaSep,
                        $MarginPersenSep=diffMarginPersen($avgMarginZonaSep,$diffMarginSep),

                        $OktHpp = $data->OktHpp,
                        $OktHj = $data->OktHj,
                        $OktMargin = $OktHj-$OktHpp,
                        $avgMarginZonaOkt=avgMarginZona($zona,10,$tahun),
                        $diffMarginOkt=$OktMargin-$avgMarginZonaOkt,
                        $MarginPersenOkt=diffMarginPersen($avgMarginZonaOkt,$diffMarginOkt),

                        $NovHpp = $data->NovHpp,
                        $NovHj = $data->NovHj,
                        $NovMargin = $NovHj-$NovHpp,
                        $avgMarginZonaNov=avgMarginZona($zona,11,$tahun),
                        $diffMarginNov=$NovMargin-$avgMarginZonaNov,
                        $MarginPersenNov=diffMarginPersen($avgMarginZonaNov,$diffMarginNov),

                        $DesHpp = $data->DesHpp,
                        $DesHj = $data->DesHj,
                        $DesMargin = $DesHj-$DesHpp,
                        $avgMarginZonaDes=avgMarginZona($zona,12,$tahun),
                        $diffMarginDes=$DesMargin-$avgMarginZonaDes,
                        $MarginPersenDes=diffMarginPersen($avgMarginZonaDes,$diffMarginDes),

                        ]);
                    }
        }

        if($sort=='desc'){
                array_multisort(array_column($units, $index), SORT_DESC, $units);
                $sort='asc';
        }else{
                array_multisort(array_column($units, $index), SORT_ASC, $units);
                $sort='desc';
        }

        if($tahun==''){
            $tahun='PILIH';
        }

        if($kodezona==''){
            $kodezona ='SEMUA';
        }

        return view('dashboard.produksi.marginZonaList',compact('noUnit','units','sort','kodezona','tahun', 'kdzona'));
    }

    public function marginPerZonaOld(Request $request){
        $region = $request->input('region');
        $jabatan = Auth::user()->jabatan;
        $koderegion = Auth::user()->region;
        $kosong = '';
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF QA");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if (in_array($jabatan, $akses)) {
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
        } else {
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $koderegion . '" ORDER BY koderegion ASC');
        }

        $tahun = date('Y');
        $noUnit = 1;

        if($region!=''){
            $marginUnit = DB::table(DB::raw("(SELECT a.kodeunit, a.region,
                    (SELECT area FROM table_rhpp WHERE unit=a.kodeunit LIMIT 1) AS zona,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM units a WHERE a.region='$region'
                    ORDER BY a.region ASC) c ORDER BY zona ASC"))
                    ->select('kodeunit','region','zona','YtdHpp','YtdHj',DB::raw('(YtdHj-YtdHpp) AS YtdMargin'),'JanHpp','JanHj',DB::raw('(JanHj-JanHpp) AS JanMargin'),'FebHpp','FebHj',DB::raw('(FebHj-FebHpp) AS FebMargin'),'MarHpp','MarHj',DB::raw('(MarHj-MarHpp) AS MarMargin'),'AprHpp','AprHj',DB::raw('(AprHj-AprHpp) AS AprMargin'),'MeiHpp','MeiHj',DB::raw('(MeiHj-MeiHpp) AS MeiMargin'),'JunHpp','JunHj',DB::raw('(JunHj-JunHpp) AS JunMargin'),'JulHpp','JulHj',DB::raw('(JulHj-JulHpp) AS JulMargin'),'AguHpp','AguHj',DB::raw('(AguHj-AguHpp) AS AguMargin'),'SepHpp','SepHj',DB::raw('(SepHj-SepHpp) AS SepMargin'),'OktHpp','OktHj',DB::raw('(OktHj-OktHpp) AS OktMargin'),'NovHpp','NovHj',DB::raw('(NovHj-NovHpp) AS NovMargin'),'DesHpp','DesHj',DB::raw('(DesHj-DesHpp) AS DesMargin'))
                    ->get();
        }else{
            $marginUnit = DB::table(DB::raw("(SELECT a.kodeunit, a.region,
                    (SELECT area FROM table_rhpp WHERE unit=a.kodeunit LIMIT 1) AS zona,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM units a
                    ORDER BY a.region ASC) c ORDER BY zona ASC"))
                    ->select('kodeunit','region','zona','YtdHpp','YtdHj',DB::raw('(YtdHj-YtdHpp) AS YtdMargin'),'JanHpp','JanHj',DB::raw('(JanHj-JanHpp) AS JanMargin'),'FebHpp','FebHj',DB::raw('(FebHj-FebHpp) AS FebMargin'),'MarHpp','MarHj',DB::raw('(MarHj-MarHpp) AS MarMargin'),'AprHpp','AprHj',DB::raw('(AprHj-AprHpp) AS AprMargin'),'MeiHpp','MeiHj',DB::raw('(MeiHj-MeiHpp) AS MeiMargin'),'JunHpp','JunHj',DB::raw('(JunHj-JunHpp) AS JunMargin'),'JulHpp','JulHj',DB::raw('(JulHj-JulHpp) AS JulMargin'),'AguHpp','AguHj',DB::raw('(AguHj-AguHpp) AS AguMargin'),'SepHpp','SepHj',DB::raw('(SepHj-SepHpp) AS SepMargin'),'OktHpp','OktHj',DB::raw('(OktHj-OktHpp) AS OktMargin'),'NovHpp','NovHj',DB::raw('(NovHj-NovHpp) AS NovMargin'),'DesHpp','DesHj',DB::raw('(DesHj-DesHpp) AS DesMargin'))
                    ->get();
        }
        return view('dashboard.produksi.marginZonaList',compact('noUnit','marginUnit','ap','region',));
    }

    public function avgZona($items,$filter,$colFilter,$colSum){
        $arrJtm = array_filter($items, function($var) use ($filter,$colFilter){
            return ($var[$colFilter] == $filter);
        });
        $sumJtm = array_sum(array_column($arrJtm, $colSum));
        $countsJtm = count($arrJtm, $colSum);
        return $avgJtm = round($sumJtm/$countsJtm,0);
    }

    public function hppbw(Request $request){
        $region = $request->input('region');

        $kosong = '';
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");

        /*
        $jabatan = Auth::user()->jabatan;
        $koderegion = Auth::user()->region;
        $kosong = '';
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF QA");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if (in_array($jabatan, $akses)) {
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
        } else {
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $koderegion . '" ORDER BY koderegion ASC');
        }
        */
        if ($region=='') {
            $no = 0;
            $noStok = 0;
            $panen = DB::table(DB::raw('units a'))
                ->select('kodeunit', 'region',
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20) AS bw100_120'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.81 AND 3.00) AS bw281_300'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50'))
                ->orderBy('a.kodearca', 'ASC')
                ->get();

            $panenFoot = DB::select("SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) AS bw100_120,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.81 AND 3.00) AS bw281_300,
                                    (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50
                                FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20");

            $valArray = [];
            foreach ($panen as $val) {
                array_push($valArray, $val->kodeunit);
            }
            $jml = count($valArray);
        }else{
            $no = 0;
            $noStok = 0;
            $panen = DB::table(DB::raw('units a'))
                ->select('kodeunit', 'region',
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20) AS bw100_120'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 3.00) AS bw281_300'),
                    DB::raw('(SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50'))
                ->where('a.region', '=', $region)
                ->orderBy('a.kodearca', 'ASC')
                ->get();

                $panenFoot = DB::select("SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) AS bw100_120,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.81 AND 3.00) AS bw281_300,
                                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50
                                    FROM vschpp WHERE region='$region' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20");

            $valArray = [];
            foreach ($panen as $val) {
                array_push($valArray, $val->kodeunit);
            }
            $jml = count($valArray);
        }
        $noregions = 0;
        $regions = Regions::all();
        return view('dashboard.produksi.hppBw', compact('noregions','no','panen','jml','ap','region','regions','panenFoot'));
    }

    public function ipunit(Request $request){
        $index = $request->segment(3);
        $sort = $request->segment(4);
        $reg = $request->segment(5);
        $thn = $request->segment(6);

        $tahun = $request->input('tahun');
        $target = 344;
        $region = $request->input('region');

        if($tahun==''){
            $tahun=$thn;
        }

        if($region==''){
            $region='SEMUA';
        }

        if($index!=''){
           $region = $reg;
        }

        if($index==''){
            $index = 1;
        }
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT(''), 'SEMUA' AS semua FROM regions
                            ORDER BY koderegion ASC");
        $no = 1;

        if($region!='SEMUA'){
            $strWhere = " WHERE region='$region' AND YEAR(tgldocfinal)='$tahun' GROUP BY unit ASC";
        }else{
            $strWhere = " WHERE 1=1 AND YEAR(tgldocfinal)='$tahun' GROUP BY unit ASC";
        }
        $sql = DB::select("SELECT unit AS kodeunit, region,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END)) AS bw1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)) AS fcr1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS dpls1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS umur1,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN coekor ELSE 0 END)) AS bw2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)) AS fcr2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS dpls2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS umur2,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN coekor ELSE 0 END)) AS bw3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)) AS fcr3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS dpls3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS umur3,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN coekor ELSE 0 END)) AS bw4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)) AS fcr4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS dpls4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS umur4,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN coekor ELSE 0 END)) AS bw5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)) AS fcr5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS dpls5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS umur5,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN coekor ELSE 0 END)) AS bw6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)) AS fcr6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS dpls6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS umur6,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN coekor ELSE 0 END)) AS bw7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)) AS fcr7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS dpls7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS umur7,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN coekor ELSE 0 END)) AS bw8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)) AS fcr8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS dpls8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS umur8,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN coekor ELSE 0 END)) AS bw9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)) AS fcr9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS dpls9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS umur9,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN coekor ELSE 0 END)) AS bw10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)) AS fcr10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS dpls10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS umur10,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN coekor ELSE 0 END)) AS bw11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)) AS fcr11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS dpls11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS umur11,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN coekor ELSE 0 END)) AS bw12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)) AS fcr12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS dpls12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS umur12,

                                (SUM(cokg)/SUM(coekor)) AS bwytd,
                                (SUM(feedkgqty)/SUM(cokg)) AS fcrytd,
                                (SUM(rmsbantudpls)/SUM(ciawal)) AS dplsytd,
                                (SUM(rmsbantuumur)/SUM(ciawal)) AS umurytd
                            FROM vrhpp $strWhere");
        $ip = array();
        foreach($sql as $data){
            array_push($ip, [$data->kodeunit,
                $data->region,
                pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd=pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            ]);
        }

        $sqlgab = DB::select("SELECT unit AS kodeunit, region,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END)) AS bw1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)) AS fcr1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS dpls1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS umur1,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN coekor ELSE 0 END)) AS bw2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)) AS fcr2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS dpls2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS umur2,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN coekor ELSE 0 END)) AS bw3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)) AS fcr3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS dpls3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS umur3,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN coekor ELSE 0 END)) AS bw4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)) AS fcr4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS dpls4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS umur4,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN coekor ELSE 0 END)) AS bw5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)) AS fcr5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS dpls5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS umur5,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN coekor ELSE 0 END)) AS bw6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)) AS fcr6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS dpls6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS umur6,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN coekor ELSE 0 END)) AS bw7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)) AS fcr7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS dpls7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS umur7,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN coekor ELSE 0 END)) AS bw8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)) AS fcr8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS dpls8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS umur8,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN coekor ELSE 0 END)) AS bw9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)) AS fcr9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS dpls9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS umur9,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN coekor ELSE 0 END)) AS bw10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)) AS fcr10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS dpls10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS umur10,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN coekor ELSE 0 END)) AS bw11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)) AS fcr11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS dpls11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS umur11,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN coekor ELSE 0 END)) AS bw12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)) AS fcr12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS dpls12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS umur12,

                                (SUM(cokg)/SUM(coekor)) AS bwytd,
                                (SUM(feedkgqty)/SUM(cokg)) AS fcrytd,
                                (SUM(rmsbantudpls)/SUM(ciawal)) AS dplsytd,
                                (SUM(rmsbantuumur)/SUM(ciawal)) AS umurytd
                            FROM vrhpp WHERE YEAR(tgldocfinal)='$tahun'");

        $gab = array();
        $jan=0;
        $feb=0;
        $mar=0;
        $apr=0;
        $mei=0;
        $jun=0;
        $jul=0;
        $agu=0;
        $sep=0;
        $okt=0;
        $nov=0;
        $des=0;
        foreach($sqlgab as $data){
            array_push($gab, [$data->kodeunit,
                $reg = $data->region,
                $jan = pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                $feb = pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                $mar = pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                $apr = pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                $mei = pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                $jun = pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                $jul = pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                $agu = pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                $sep = pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                $okt = pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                $nov = pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                $des = pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd = pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            ]);
        }

        $arrGab = array();
        array_push($arrGab,$jan,$feb,$mar,$apr,$mei,$jun,$jul,$agu,$sep,$okt,$nov,$des);

        $val_chart=(array_filter($arrGab,function ($var){
            return($var > 0);
        }));
        $val_chart = json_encode($val_chart);

        $valArray = [];
        foreach ($ip as $val) {
            array_push($valArray, $val[15]);
        }

        $counts = array_count_values($valArray);
        $excellent = !empty($counts['EXCELLENT']) ? $counts['EXCELLENT'] : '0';
        $baik = !empty($counts['BAIK']) ? $counts['BAIK'] : '0';
        $sedang = !empty($counts['SEDANG']) ? $counts['SEDANG'] : '0';
        $kurang = !empty($counts['KURANG']) ? $counts['KURANG'] : '0';
        $total = count($valArray);

        array_multisort(array_column($ip, 14), SORT_DESC, $ip);
        $arrExcellent = [];
        foreach ($ip as $val) {
            if ($val[15] == 'EXCELLENT'){
                array_push($arrExcellent, [$val[0],$val[14]]);
            }
        }

        $arrBaik = [];
        foreach ($ip as $val) {
            if ($val[15] == 'BAIK'){
                array_push($arrBaik, [$val[0],$val[14]]);
            }
        }

        $arrSedang = [];
        foreach ($ip as $val) {
            if ($val[15] == 'SEDANG'){
                array_push($arrSedang, [$val[0],$val[14]]);
            }
        }

        $arrKurang = [];
        foreach ($ip as $val) {
            if ($val[15] == 'KURANG'){
                array_push($arrKurang, [$val[0],$val[14]]);
            }
        }

        $label =  bulanLabel();
        $content =  bulanContent();

        if($sort=='desc'){
            array_multisort(array_column($ip, $index), SORT_DESC, $ip);
            $sort='asc';
        }else{
            array_multisort(array_column($ip, $index), SORT_ASC, $ip);
            $sort='desc';
        }

        if($tahun==''){
            $tahun='PILIH';
        }

        return view('dashboard.produksi.ipunit', compact('no','ip','gab','target','sort','index','tahun','region','ap','reg','tahun',
                                                        'excellent','baik','sedang','kurang','total','label','content','val_chart',
                                                        'arrExcellent','arrBaik','arrSedang','arrKurang'));
    }

    public function ipunitExcel($region, $tahun){
        $target = 354;

        if($region!='SEMUA'){
            $sql = DB::select("SELECT a.kodeunit, a.region,
                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS bw1,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS fcr1,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS dpls1,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS umur1,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS bw2,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS fcr2,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS dpls2,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS umur2,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS bw3,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS fcr3,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS dpls3,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS umur3,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS bw4,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS fcr4,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS dpls4,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS umur4,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS bw5,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS fcr5,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS dpls5,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS umur5,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS bw6,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS fcr6,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS dpls6,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS umur6,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS bw7,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS fcr7,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS dpls7,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS umur7,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS bw8,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS fcr8,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS dpls8,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS umur8,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS bw9,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS fcr9,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS dpls9,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS umur9,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS bw10,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS fcr10,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS dpls10,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS umur10,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS bw11,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS fcr11,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS dpls11,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS umur11,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS bw12,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS fcr12,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS dpls12,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS umur12,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS bwytd,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS fcrytd,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS dplsytd,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS umurytd
                    FROM units a WHERE a.region='$region' ORDER BY a.region ASC");


        }else{
            $sql = DB::select("SELECT a.kodeunit, a.region,
                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS bw1,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS fcr1,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS dpls1,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS umur1,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS bw2,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS fcr2,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS dpls2,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS umur2,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS bw3,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS fcr3,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS dpls3,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS umur3,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS bw4,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS fcr4,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS dpls4,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS umur4,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS bw5,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS fcr5,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS dpls5,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS umur5,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS bw6,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS fcr6,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS dpls6,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS umur6,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS bw7,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS fcr7,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS dpls7,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS umur7,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS bw8,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS fcr8,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS dpls8,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS umur8,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS bw9,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS fcr9,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS dpls9,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS umur9,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS bw10,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS fcr10,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS dpls10,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS umur10,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS bw11,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS fcr11,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS dpls11,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS umur11,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS bw12,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS fcr12,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS dpls12,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS umur12,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS bwytd,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS fcrytd,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS dplsytd,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS umurytd
                    FROM units a ORDER BY a.region ASC");
        }

        $ip = array();
        foreach($sql as $data){
            array_push($ip, [$data->kodeunit,
                $data->region,
                pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd=pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:T1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'IP PER UNIT');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:T3')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'UNIT');
        $sheet->setCellValue('C3', 'AP');
        $sheet->setCellValue('D3', 'JAN ');
        $sheet->setCellValue('E3', 'FEB ');
        $sheet->setCellValue('F3', 'MAR ');
        $sheet->setCellValue('G3', 'APR ');
        $sheet->setCellValue('H3', 'MEI');
        $sheet->setCellValue('I3', 'JUN');
        $sheet->setCellValue('J3', 'JUL');
        $sheet->setCellValue('K3', 'AGU');
        $sheet->setCellValue('L3', 'SEP');
        $sheet->setCellValue('M3', 'OKT');
        $sheet->setCellValue('N3', 'NOV');
        $sheet->setCellValue('O3', 'DES');
        $sheet->setCellValue('P3', 'YTD');
        $sheet->setCellValue('Q3', 'NILAI');
        $sheet->setCellValue('R3', 'TARGET');
        $sheet->setCellValue('S3', 'DIFF');
        $sheet->setCellValue('T3', 'EVALUASI ');

        $rows = 4;
        $no = 1;

        foreach ($ip as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data[0]);
            $sheet->setCellValue('C' . $rows, $data[1]);
            $sheet->setCellValue('D' . $rows, number_indo_excel($data[2]));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data[3]));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data[4]));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data[5]));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data[6]));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data[7]));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data[8]));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data[9]));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data[10]));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data[11]));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data[12]));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data[13]));
            $sheet->setCellValue('P' . $rows, number_indo_excel($data[14]));
            $sheet->setCellValue('Q' . $rows, $data[15]);
            $sheet->setCellValue('R' . $rows, number_indo_excel($data[16]));
            $sheet->setCellValue('S' . $rows, number_indo_excel($data[17]));
            $sheet->setCellValue('T' . $rows, $data[18]);


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('15');
            $sheet->getColumnDimension('C')->setWidth('15');
            foreach (range('D', 'P') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $sqlgab = DB::select("SELECT a.kodeunit, a.region,
                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=1) AS bw1,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=1) AS fcr1,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=1) AS dpls1,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=1) AS umur1,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=2) AS bw2,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=2) AS fcr2,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=2) AS dpls2,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=2) AS umur2,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=3) AS bw3,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=3) AS fcr3,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=3) AS dpls3,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=3) AS umur3,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=4) AS bw4,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=4) AS fcr4,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=4) AS dpls4,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=4) AS umur4,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=5) AS bw5,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=5) AS fcr5,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=5) AS dpls5,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=5) AS umur5,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=6) AS bw6,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=6) AS fcr6,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=6) AS dpls6,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=6) AS umur6,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=7) AS bw7,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=7) AS fcr7,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=7) AS dpls7,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=7) AS umur7,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=8) AS bw8,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=8) AS fcr8,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=8) AS dpls8,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=8) AS umur8,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=9) AS bw9,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=9) AS fcr9,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=9) AS dpls9,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=9) AS umur9,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=10) AS bw10,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=10) AS fcr10,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=10) AS dpls10,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=10) AS umur10,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=11) AS bw11,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=11) AS fcr11,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=11) AS dpls11,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=11) AS umur11,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=12) AS bw12,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=12) AS fcr12,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=12) AS dpls12,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=12) AS umur12,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS bwytd,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS fcrytd,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS dplsytd,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS umurytd
                    FROM units a GROUP BY a.region ASC LIMIT 1");

        $gab = array();
        foreach($sqlgab as $data){
            array_push($gab, $data->kodeunit,
                $data->region,
                pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd=pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            );
        }

      // return $gab;
            $sheet->setCellValue('A' . $rows, '');
            $sheet->setCellValue('B' . $rows, 'GAB');
            $sheet->setCellValue('C' . $rows, '');
            $sheet->setCellValue('D' . $rows, number_indo_excel($gab[2]));
            $sheet->setCellValue('E' . $rows, number_indo_excel($gab[3]));
            $sheet->setCellValue('F' . $rows, number_indo_excel($gab[4]));
            $sheet->setCellValue('G' . $rows, number_indo_excel($gab[5]));
            $sheet->setCellValue('H' . $rows, number_indo_excel($gab[6]));
            $sheet->setCellValue('I' . $rows, number_indo_excel($gab[7]));
            $sheet->setCellValue('J' . $rows, number_indo_excel($gab[8]));
            $sheet->setCellValue('K' . $rows, number_indo_excel($gab[9]));
            $sheet->setCellValue('L' . $rows, number_indo_excel($gab[10]));
            $sheet->setCellValue('M' . $rows, number_indo_excel($gab[11]));
            $sheet->setCellValue('N' . $rows, number_indo_excel($gab[12]));
            $sheet->setCellValue('O' . $rows, number_indo_excel($gab[13]));
            $sheet->setCellValue('P' . $rows, number_indo_excel($gab[14]));
            $sheet->setCellValue('Q' . $rows, $gab[15]);
            $sheet->setCellValue('R' . $rows, number_indo_excel($gab[16]));
            $sheet->setCellValue('S' . $rows, number_indo_excel($gab[17]));
            $sheet->setCellValue('T' . $rows, $gab[18]);


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('15');
            $sheet->getColumnDimension('C')->setWidth('15');
            foreach (range('D', 'P') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;


        $fileName = "IP_PER_UNIT.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));

    }

    public function ipapExcel($tahun){
        $target = 344;

        $sql = DB::select("SELECT a.koderegion, a.namaregion,
                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS bw1,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS fcr1,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS dpls1,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS umur1,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS bw2,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS fcr2,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS dpls2,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS umur2,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS bw3,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS fcr3,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS dpls3,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS umur3,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS bw4,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS fcr4,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS dpls4,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS umur4,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS bw5,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS fcr5,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS dpls5,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS umur5,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS bw6,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS fcr6,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS dpls6,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS umur6,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS bw7,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS fcr7,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS dpls7,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS umur7,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS bw8,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS fcr8,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS dpls8,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS umur8,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS bw9,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS fcr9,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS dpls9,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS umur9,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS bw10,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS fcr10,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS dpls10,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS umur10,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS bw11,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS fcr11,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS dpls11,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS umur11,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS bw12,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS fcr12,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS dpls12,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS umur12,

                    (SELECT SUM(cokg)/SUM(coekor) FROM vrhpp WHERE region=a.koderegion AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS bwytd,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM vrhpp WHERE region=a.koderegion AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS fcrytd,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS dplsytd,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS umurytd
                    FROM regions a ORDER BY a.koderegion ASC");

        $ip = array();
        foreach($sql as $data){
            array_push($ip, [$data->koderegion,
                $data->koderegion,
                pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd=pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:T1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'IP PER AP');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:T3')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'AP');
        $sheet->setCellValue('C3', 'JAN ');
        $sheet->setCellValue('D3', 'FEB ');
        $sheet->setCellValue('E3', 'MAR ');
        $sheet->setCellValue('F3', 'APR ');
        $sheet->setCellValue('G3', 'MEI');
        $sheet->setCellValue('H3', 'JUN');
        $sheet->setCellValue('I3', 'JUL');
        $sheet->setCellValue('J3', 'AGU');
        $sheet->setCellValue('K3', 'SEP');
        $sheet->setCellValue('L3', 'OKT');
        $sheet->setCellValue('M3', 'NOV');
        $sheet->setCellValue('N3', 'DES');
        $sheet->setCellValue('O3', 'YTD');
        $sheet->setCellValue('P3', 'NILAI');
        $sheet->setCellValue('Q3', 'TARGET');
        $sheet->setCellValue('R3', 'DIFF');
        $sheet->setCellValue('S3', 'EVALUASI ');

        $rows = 4;
        $no = 1;

        foreach ($ip as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data[1]);
            $sheet->setCellValue('C' . $rows, number_indo_excel($data[2]));
            $sheet->setCellValue('D' . $rows, number_indo_excel($data[3]));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data[4]));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data[5]));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data[6]));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data[7]));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data[8]));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data[9]));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data[10]));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data[11]));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data[12]));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data[13]));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data[14]));
            $sheet->setCellValue('P' . $rows, $data[15]);
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data[16]));
            $sheet->setCellValue('R' . $rows, number_indo_excel($data[17]));
            $sheet->setCellValue('S' . $rows, $data[18]);


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('15');
            $sheet->getColumnDimension('C')->setWidth('15');
            foreach (range('D', 'P') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $sqlgab = DB::select("SELECT a.kodeunit, a.region,
                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS bw1,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS fcr1,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS dpls1,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun') AS umur1,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS bw2,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS fcr2,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS dpls2,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun') AS umur2,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS bw3,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS fcr3,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS dpls3,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun') AS umur3,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS bw4,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS fcr4,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS dpls4,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun') AS umur4,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS bw5,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS fcr5,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS dpls5,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun') AS umur5,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS bw6,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS fcr6,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS dpls6,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun') AS umur6,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS bw7,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS fcr7,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS dpls7,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun') AS umur7,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS bw8,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS fcr8,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS dpls8,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun') AS umur8,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS bw9,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS fcr9,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS dpls9,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun') AS umur9,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS bw10,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS fcr10,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS dpls10,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun') AS umur10,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS bw11,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS fcr11,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS dpls11,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun') AS umur11,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS bw12,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS fcr12,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS dpls12,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun') AS umur12,

                    (SELECT SUM(cokg)/SUM(coekor) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS bwytd,
                    (SELECT SUM(feedkgqty)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS fcrytd,
                    (SELECT SUM(rmsbantudpls)/SUM(ciawal) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS dplsytd,
                    (SELECT SUM(rmsbantuumur)/SUM(ciawal) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31') AS umurytd
                    FROM units a GROUP BY a.region ASC LIMIT 1");

        $gab = array();
        foreach($sqlgab as $data){
            array_push($gab, $data->kodeunit,
                $data->region,
                pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd=pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            );
        }

      // return $gab;
            $sheet->setCellValue('A' . $rows, '');
            $sheet->setCellValue('B' . $rows, 'GAB');
            $sheet->setCellValue('C' . $rows, number_indo_excel($gab[2]));
            $sheet->setCellValue('D' . $rows, number_indo_excel($gab[3]));
            $sheet->setCellValue('E' . $rows, number_indo_excel($gab[4]));
            $sheet->setCellValue('F' . $rows, number_indo_excel($gab[5]));
            $sheet->setCellValue('G' . $rows, number_indo_excel($gab[6]));
            $sheet->setCellValue('H' . $rows, number_indo_excel($gab[7]));
            $sheet->setCellValue('I' . $rows, number_indo_excel($gab[8]));
            $sheet->setCellValue('J' . $rows, number_indo_excel($gab[9]));
            $sheet->setCellValue('K' . $rows, number_indo_excel($gab[10]));
            $sheet->setCellValue('L' . $rows, number_indo_excel($gab[11]));
            $sheet->setCellValue('M' . $rows, number_indo_excel($gab[12]));
            $sheet->setCellValue('N' . $rows, number_indo_excel($gab[13]));
            $sheet->setCellValue('O' . $rows, number_indo_excel($gab[14]));
            $sheet->setCellValue('P' . $rows, $gab[15]);
            $sheet->setCellValue('Q' . $rows, number_indo_excel($gab[16]));
            $sheet->setCellValue('R' . $rows, number_indo_excel($gab[17]));
            $sheet->setCellValue('S' . $rows, $gab[18]);


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('15');
            $sheet->getColumnDimension('C')->setWidth('15');
            foreach (range('D', 'P') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;


        $fileName = "IP_PER_AP.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));

    }

    public function ipap(Request $request){
        $index = $request->segment(3);
        $sort = $request->segment(4);
        $reg = $request->segment(5);
        $thn = $request->segment(6);

        $tahun = $request->input('tahun');
        $target = 344;
        $region = $request->input('region');

        if($tahun==''){
            $tahun=$thn;
        }

        if($region==''){
            $region='SEMUA';
        }

        if($index!=''){
           $region = $reg;
        }

        if($index==''){
            $index = 1;
        }
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT(''), 'SEMUA' AS semua FROM regions
                            ORDER BY koderegion ASC");
        $no = 1;

        $sql = DB::select("SELECT region AS koderegion, region AS namaregion,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END)) AS bw1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)) AS fcr1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS dpls1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS umur1,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN coekor ELSE 0 END)) AS bw2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)) AS fcr2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS dpls2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS umur2,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN coekor ELSE 0 END)) AS bw3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)) AS fcr3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS dpls3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS umur3,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN coekor ELSE 0 END)) AS bw4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)) AS fcr4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS dpls4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS umur4,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN coekor ELSE 0 END)) AS bw5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)) AS fcr5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS dpls5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS umur5,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN coekor ELSE 0 END)) AS bw6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)) AS fcr6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS dpls6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS umur6,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN coekor ELSE 0 END)) AS bw7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)) AS fcr7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS dpls7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS umur7,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN coekor ELSE 0 END)) AS bw8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)) AS fcr8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS dpls8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS umur8,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN coekor ELSE 0 END)) AS bw9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)) AS fcr9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS dpls9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS umur9,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN coekor ELSE 0 END)) AS bw10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)) AS fcr10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS dpls10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS umur10,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN coekor ELSE 0 END)) AS bw11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)) AS fcr11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS dpls11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS umur11,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN coekor ELSE 0 END)) AS bw12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)) AS fcr12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS dpls12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS umur12,

                                (SUM(cokg)/SUM(coekor)) AS bwytd,
                                (SUM(feedkgqty)/SUM(cokg)) AS fcrytd,
                                (SUM(rmsbantudpls)/SUM(ciawal)) AS dplsytd,
                                (SUM(rmsbantuumur)/SUM(ciawal)) AS umurytd
                            FROM vrhpp WHERE YEAR(tgldocfinal)='$tahun' GROUP BY region ASC");

        $ip = array();
        foreach($sql as $data){
            array_push($ip, [$data->koderegion,
                $data->koderegion,
                pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd=pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            ]);
        }

        $sqlgab = DB::select("SELECT unit AS kodeunit, region,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END)) AS bw1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)) AS fcr1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS dpls1,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=1 THEN ciawal ELSE 0 END)) AS umur1,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN coekor ELSE 0 END)) AS bw2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)) AS fcr2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS dpls2,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=2 THEN ciawal ELSE 0 END)) AS umur2,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN coekor ELSE 0 END)) AS bw3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)) AS fcr3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS dpls3,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=3 THEN ciawal ELSE 0 END)) AS umur3,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN coekor ELSE 0 END)) AS bw4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)) AS fcr4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS dpls4,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=4 THEN ciawal ELSE 0 END)) AS umur4,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN coekor ELSE 0 END)) AS bw5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)) AS fcr5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS dpls5,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=5 THEN ciawal ELSE 0 END)) AS umur5,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN coekor ELSE 0 END)) AS bw6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)) AS fcr6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS dpls6,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=6 THEN ciawal ELSE 0 END)) AS umur6,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN coekor ELSE 0 END)) AS bw7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)) AS fcr7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS dpls7,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=7 THEN ciawal ELSE 0 END)) AS umur7,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN coekor ELSE 0 END)) AS bw8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)) AS fcr8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS dpls8,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=8 THEN ciawal ELSE 0 END)) AS umur8,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN coekor ELSE 0 END)) AS bw9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)) AS fcr9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS dpls9,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=9 THEN ciawal ELSE 0 END)) AS umur9,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN coekor ELSE 0 END)) AS bw10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)) AS fcr10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS dpls10,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=10 THEN ciawal ELSE 0 END)) AS umur10,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN coekor ELSE 0 END)) AS bw11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)) AS fcr11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS dpls11,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=11 THEN ciawal ELSE 0 END)) AS umur11,

                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN coekor ELSE 0 END)) AS bw12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)) AS fcr12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantudpls ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS dpls12,
                                (SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN rmsbantuumur ELSE 0 END)/SUM(CASE WHEN MONTH(tgldocfinal)=12 THEN ciawal ELSE 0 END)) AS umur12,

                                (SUM(cokg)/SUM(coekor)) AS bwytd,
                                (SUM(feedkgqty)/SUM(cokg)) AS fcrytd,
                                (SUM(rmsbantudpls)/SUM(ciawal)) AS dplsytd,
                                (SUM(rmsbantuumur)/SUM(ciawal)) AS umurytd
                            FROM vrhpp WHERE YEAR(tgldocfinal)='$tahun'");

        $gab = array();
        $jan=0;
        $feb=0;
        $mar=0;
        $apr=0;
        $mei=0;
        $jun=0;
        $jul=0;
        $agu=0;
        $sep=0;
        $okt=0;
        $nov=0;
        $des=0;
        foreach($sqlgab as $data){
            array_push($gab, [$data->kodeunit,
                $data->region,
                $jan = pencapaian_ipunit($data->bw1,$data->fcr1,$data->dpls1,$data->umur1),
                $feb = pencapaian_ipunit($data->bw2,$data->fcr2,$data->dpls2,$data->umur2),
                $mar = pencapaian_ipunit($data->bw3,$data->fcr3,$data->dpls3,$data->umur3),
                $apr = pencapaian_ipunit($data->bw4,$data->fcr4,$data->dpls4,$data->umur4),
                $mei = pencapaian_ipunit($data->bw5,$data->fcr5,$data->dpls5,$data->umur5),
                $jun = pencapaian_ipunit($data->bw6,$data->fcr6,$data->dpls6,$data->umur6),
                $jul = pencapaian_ipunit($data->bw7,$data->fcr7,$data->dpls7,$data->umur7),
                $agu = pencapaian_ipunit($data->bw8,$data->fcr8,$data->dpls8,$data->umur8),
                $sep = pencapaian_ipunit($data->bw9,$data->fcr9,$data->dpls9,$data->umur9),
                $okt = pencapaian_ipunit($data->bw10,$data->fcr10,$data->dpls10,$data->umur10),
                $nov = pencapaian_ipunit($data->bw11,$data->fcr11,$data->dpls11,$data->umur11),
                $des = pencapaian_ipunit($data->bw12,$data->fcr12,$data->dpls12,$data->umur12),
                $ytd=pencapaian_ipunit($data->bwytd,$data->fcrytd,$data->dplsytd,$data->umurytd),
                NilaiIp($ytd),
                $target,
                $ytd-$target,
                EvaluasiIp($ytd,$target),
            ]);
        }

        $arrGab = array();
        array_push($arrGab,$jan,$feb,$mar,$apr,$mei,$jun,$jul,$agu,$sep,$okt,$nov,$des);

        $val_chart=(array_filter($arrGab,function ($var){
            return($var > 0);
        }));
        $val_chart = json_encode($val_chart);

        $valArray = [];
        foreach ($ip as $val) {
            array_push($valArray, $val[15]);
        }

        $counts = array_count_values($valArray);
        $excellent = !empty($counts['EXCELLENT']) ? $counts['EXCELLENT'] : '0';
        $baik = !empty($counts['BAIK']) ? $counts['BAIK'] : '0';
        $sedang = !empty($counts['SEDANG']) ? $counts['SEDANG'] : '0';
        $kurang = !empty($counts['KURANG']) ? $counts['KURANG'] : '0';
        $total = count($valArray);

        array_multisort(array_column($ip, 14), SORT_DESC, $ip);
        $arrExcellent = [];
        foreach ($ip as $val) {
            if ($val[15] == 'EXCELLENT'){
                array_push($arrExcellent, [$val[0],$val[14]]);
            }
        }

        $arrBaik = [];
        foreach ($ip as $val) {
            if ($val[15] == 'BAIK'){
                array_push($arrBaik, [$val[0],$val[14]]);
            }
        }

        $arrSedang = [];
        foreach ($ip as $val) {
            if ($val[15] == 'SEDANG'){
                array_push($arrSedang, [$val[0],$val[14]]);
            }
        }

        $arrKurang = [];
        foreach ($ip as $val) {
            if ($val[15] == 'KURANG'){
                array_push($arrKurang, [$val[0],$val[14]]);
            }
        }

        $label =  bulanLabel();
        $content =  bulanContent();

        if($sort=='desc'){
            array_multisort(array_column($ip, $index), SORT_DESC, $ip);
            $sort='asc';
        }else{
            array_multisort(array_column($ip, $index), SORT_ASC, $ip);
            $sort='desc';
        }

        if($tahun==''){
            $tahun='PILIH';
        }

        return view('dashboard.produksi.ipap', compact('no','ip','gab','target','sort','index','tahun','region','ap','reg','tahun',
                                                        'excellent','baik','sedang','kurang','total','label','content','val_chart',
                                                        'arrExcellent','arrBaik','arrSedang','arrKurang'));
    }

    public function marginPerZonaExcel($tahun){
            $noUnit = 1;
            $marginUnit = DB::table(DB::raw("(SELECT a.kodeunit, a.region,
                    (SELECT area FROM table_rhpp WHERE unit=a.kodeunit LIMIT 1) AS zona,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM units a
                    ORDER BY a.region ASC) c ORDER BY zona ASC"))
                    ->select('kodeunit','region','zona','YtdHpp','YtdHj',DB::raw('(YtdHj-YtdHpp) AS YtdMargin'),'JanHpp','JanHj',DB::raw('(JanHj-JanHpp) AS JanMargin'),'FebHpp','FebHj',DB::raw('(FebHj-FebHpp) AS FebMargin'),'MarHpp','MarHj',DB::raw('(MarHj-MarHpp) AS MarMargin'),'AprHpp','AprHj',DB::raw('(AprHj-AprHpp) AS AprMargin'),'MeiHpp','MeiHj',DB::raw('(MeiHj-MeiHpp) AS MeiMargin'),'JunHpp','JunHj',DB::raw('(JunHj-JunHpp) AS JunMargin'),'JulHpp','JulHj',DB::raw('(JulHj-JulHpp) AS JulMargin'),'AguHpp','AguHj',DB::raw('(AguHj-AguHpp) AS AguMargin'),'SepHpp','SepHj',DB::raw('(SepHj-SepHpp) AS SepMargin'),'OktHpp','OktHj',DB::raw('(OktHj-OktHpp) AS OktMargin'),'NovHpp','NovHj',DB::raw('(NovHj-NovHpp) AS NovMargin'),'DesHpp','DesHj',DB::raw('(DesHj-DesHpp) AS DesMargin'))
                    ->get();

            $units = array();
            foreach($marginUnit as $data){
                array_push($units, [$kodeunit = $data->kodeunit,
                $region = $data->region,
                $zona = $data->zona,

                $YtdHpp = $data->YtdHpp,
                $YtdHj = $data->YtdHj,
                $YtdMargin = $YtdHj-$YtdHpp,
                $avgMarginZonaYtd=avgMarginZona($zona,0),
                $diffMarginYtd=$YtdMargin-$avgMarginZonaYtd,
                $MarginPersenYtd=diffMarginPersen($avgMarginZonaYtd,$diffMarginYtd),

                $JanHpp = $data->JanHpp,
                $JanHj = $data->JanHj,
                $JanMargin = $JanHj-$JanHpp,
                $avgMarginZonaJan=avgMarginZona($zona,1),
                $diffMarginJan=$JanMargin-$avgMarginZonaJan,
                $MarginPersenJan=diffMarginPersen($avgMarginZonaJan,$diffMarginJan),

                $FebHpp = $data->FebHpp,
                $FebHj = $data->FebHj,
                $FebMargin = $FebHj-$FebHpp,
                $avgMarginZonaFeb=avgMarginZona($zona,2),
                $diffMarginFeb=$FebMargin-$avgMarginZonaFeb,
                $MarginPersenFeb=diffMarginPersen($avgMarginZonaFeb,$diffMarginFeb),

                $MarHpp = $data->MarHpp,
                $MarHj = $data->MarHj,
                $MarMargin = $MarHj-$MarHpp,
                $avgMarginZonaMar=avgMarginZona($zona,3),
                $diffMarginMar=$MarMargin-$avgMarginZonaMar,
                $MarginPersenMar=diffMarginPersen($avgMarginZonaMar,$diffMarginMar),

                $AprHpp = $data->AprHpp,
                $AprHj = $data->AprHj,
                $AprMargin = $AprHj-$AprHpp,
                $avgMarginZonaApr=avgMarginZona($zona,4),
                $diffMarginApr=$AprMargin-$avgMarginZonaApr,
                $MarginPersenApr=diffMarginPersen($avgMarginZonaApr,$diffMarginApr),

                $MeiHpp = $data->MeiHpp,
                $MeiHj = $data->MeiHj,
                $MeiMargin = $MeiHj-$MeiHpp,
                $avgMarginZonaMei=avgMarginZona($zona,5),
                $diffMarginMei=$MeiMargin-$avgMarginZonaMei,
                $MarginPersenMei=diffMarginPersen($avgMarginZonaMei,$diffMarginMei),

                $JunHpp = $data->JunHpp,
                $JunHj = $data->JunHj,
                $JunMargin = $JunHj-$JunHpp,
                $avgMarginZonaJun=avgMarginZona($zona,6),
                $diffMarginJun=$JunMargin-$avgMarginZonaJun,
                $MarginPersenJun=diffMarginPersen($avgMarginZonaJun,$diffMarginJun),

                $JulHpp = $data->JulHpp,
                $JulHj = $data->JulHj,
                $JulMargin = $JulHj-$JulHpp,
                $avgMarginZonaJul=avgMarginZona($zona,7),
                $diffMarginJul=$JulMargin-$avgMarginZonaJul,
                $MarginPersenJul=diffMarginPersen($avgMarginZonaJul,$diffMarginJul),

                $AguHpp = $data->AguHpp,
                $AguHj = $data->AguHj,
                $AguMargin = $AguHj-$AguHpp,
                $avgMarginZonaAgu=avgMarginZona($zona,8),
                $diffMarginAgu=$AguMargin-$avgMarginZonaAgu,
                $MarginPersenAgu=diffMarginPersen($avgMarginZonaAgu,$diffMarginAgu),

                $SepHpp = $data->SepHpp,
                $SepHj = $data->SepHj,
                $SepMargin = $SepHj-$SepHpp,
                $avgMarginZonaSep=avgMarginZona($zona,9),
                $diffMarginSep=$SepMargin-$avgMarginZonaSep,
                $MarginPersenSep=diffMarginPersen($avgMarginZonaSep,$diffMarginSep),

                $OktHpp = $data->OktHpp,
                $OktHj = $data->OktHj,
                $OktMargin = $OktHj-$OktHpp,
                $avgMarginZonaOkt=avgMarginZona($zona,10),
                $diffMarginOkt=$OktMargin-$avgMarginZonaOkt,
                $MarginPersenOkt=diffMarginPersen($avgMarginZonaOkt,$diffMarginOkt),

                $NovHpp = $data->NovHpp,
                $NovHj = $data->NovHj,
                $NovMargin = $NovHj-$NovHpp,
                $avgMarginZonaNov=avgMarginZona($zona,11),
                $diffMarginNov=$NovMargin-$avgMarginZonaNov,
                $MarginPersenNov=diffMarginPersen($avgMarginZonaNov,$diffMarginNov),

                $DesHpp = $data->DesHpp,
                $DesHj = $data->DesHj,
                $DesMargin = $DesHj-$DesHpp,
                $avgMarginZonaDes=avgMarginZona($zona,12),
                $diffMarginDes=$DesMargin-$avgMarginZonaDes,
                $MarginPersenDes=diffMarginPersen($avgMarginZonaDes,$diffMarginDes),

                ]);
            }
        array_multisort(array_column($units, 0), SORT_ASC, $units);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:CD1');
        $spreadsheet->getActiveSheet()->setCellValue('A1','MARGIN PERZONA');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:CD4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', 'ZONA');
        $spreadsheet->getActiveSheet()->mergeCells('D3:D4');

        $sheet->setCellValue('E3', 'YTD 2022');
        $spreadsheet->getActiveSheet()->mergeCells('E3:J3');
        $sheet->setCellValue('E4', 'HPP');
        $sheet->setCellValue('F4', 'HJ');
        $sheet->setCellValue('G4', 'MRG KG');
        $sheet->setCellValue('H4', 'MRG ZONA');
        $sheet->setCellValue('I4', 'DIFF MRG');
        $sheet->setCellValue('J4', 'DIFF MRG');

        $sheet->setCellValue('K3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('K3:P3');
        $sheet->setCellValue('K4', 'HPP');
        $sheet->setCellValue('L4', 'HJ');
        $sheet->setCellValue('M4', 'MRG KG');
        $sheet->setCellValue('N4', 'MRG ZONA');
        $sheet->setCellValue('O4', 'DIFF MRG');
        $sheet->setCellValue('P4', 'DIFF MRG');

        $sheet->setCellValue('Q3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('Q3:V3');
        $sheet->setCellValue('Q4', 'HPP');
        $sheet->setCellValue('R4', 'HJ');
        $sheet->setCellValue('S4', 'MRG KG');
        $sheet->setCellValue('T4', 'MRG ZONA');
        $sheet->setCellValue('U4', 'DIFF MRG');
        $sheet->setCellValue('V4', 'DIFF MRG');

        $sheet->setCellValue('W3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('W3:AB3');
        $sheet->setCellValue('W4', 'HPP');
        $sheet->setCellValue('X4', 'HJ');
        $sheet->setCellValue('Z4', 'MRG KG');
        $sheet->setCellValue('Y4', 'MRG ZONA');
        $sheet->setCellValue('AA4', 'DIFF MRG');
        $sheet->setCellValue('AB4', 'DIFF MRG');

        $sheet->setCellValue('AC3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('AC3:AH3');
        $sheet->setCellValue('AC4', 'HPP');
        $sheet->setCellValue('AD4', 'HJ');
        $sheet->setCellValue('AE4', 'MRG KG');
        $sheet->setCellValue('AF4', 'MRG ZONA');
        $sheet->setCellValue('AG4', 'DIFF MRG');
        $sheet->setCellValue('AH4', 'DIFF MRG');

        $sheet->setCellValue('AI3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('AI3:AN3');
        $sheet->setCellValue('AI4', 'HPP');
        $sheet->setCellValue('AJ4', 'HJ');
        $sheet->setCellValue('AK4', 'MRG KG');
        $sheet->setCellValue('AL4', 'MRG ZONA');
        $sheet->setCellValue('AM4', 'DIFF MRG');
        $sheet->setCellValue('AN4', 'DIFF MRG');

        $sheet->setCellValue('AO3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('AO3:AT3');
        $sheet->setCellValue('AO4', 'HPP');
        $sheet->setCellValue('AP4', 'HJ');
        $sheet->setCellValue('AQ4', 'MRG KG');
        $sheet->setCellValue('AR4', 'MRG ZONA');
        $sheet->setCellValue('AS4', 'DIFF MRG');
        $sheet->setCellValue('AT4', 'DIFF MRG');

        $sheet->setCellValue('AU3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('AU3:AZ3');
        $sheet->setCellValue('AU4', 'HPP');
        $sheet->setCellValue('AV4', 'HJ');
        $sheet->setCellValue('AW4', 'MRG KG');
        $sheet->setCellValue('AX4', 'MRG ZONA');
        $sheet->setCellValue('AY4', 'DIFF MRG');
        $sheet->setCellValue('AZ4', 'DIFF MRG');

        $sheet->setCellValue('BA3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('BA3:BF3');
        $sheet->setCellValue('BA4', 'HPP');
        $sheet->setCellValue('BB4', 'HJ');
        $sheet->setCellValue('BC4', 'MRG KG');
        $sheet->setCellValue('BD4', 'MRG ZONA');
        $sheet->setCellValue('BE4', 'DIFF MRG');
        $sheet->setCellValue('BF4', 'DIFF MRG');

        $sheet->setCellValue('BG3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('BG3:BL3');
        $sheet->setCellValue('BG4', 'HPP');
        $sheet->setCellValue('BH4', 'HJ');
        $sheet->setCellValue('BI4', 'MRG KG');
        $sheet->setCellValue('BJ4', 'MRG ZONA');
        $sheet->setCellValue('BK4', 'DIFF MRG');
        $sheet->setCellValue('BL4', 'DIFF MRG');

        $sheet->setCellValue('BM3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('BM3:BR3');
        $sheet->setCellValue('BM4', 'HPP');
        $sheet->setCellValue('BN4', 'HJ');
        $sheet->setCellValue('BO4', 'MRG KG');
        $sheet->setCellValue('BP4', 'MRG ZONA');
        $sheet->setCellValue('BQ4', 'DIFF MRG');
        $sheet->setCellValue('BR4', 'DIFF MRG');

        $sheet->setCellValue('BS3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('BS3:BX3');
        $sheet->setCellValue('BS4', 'HPP');
        $sheet->setCellValue('BT4', 'HJ');
        $sheet->setCellValue('BU4', 'MRG KG');
        $sheet->setCellValue('BF4', 'MRG ZONA');
        $sheet->setCellValue('BW4', 'DIFF MRG');
        $sheet->setCellValue('BX4', 'DIFF MRG');

        $sheet->setCellValue('BY3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('BY3:CD3');
        $sheet->setCellValue('BY4', 'HPP');
        $sheet->setCellValue('BZ4', 'HJ');
        $sheet->setCellValue('CA4', 'MRG KG');
        $sheet->setCellValue('CB4', 'MRG ZONA');
        $sheet->setCellValue('CC4', 'DIFF MRG');
        $sheet->setCellValue('CD4', 'DIFF MRG');

        $rows = 5;
        $no = 1;

        foreach ($units as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data[0]);
            $sheet->setCellValue('C' . $rows, $data[1]);
            $sheet->setCellValue('D' . $rows, $zona = $data[2]);
            $sheet->setCellValue('E' . $rows, number_indo_excel($data[3]));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data[4]));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data[5]));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data[6]));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data[7]));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data[8]).'%');
            $sheet->setCellValue('K' . $rows, number_indo_excel($data[9]));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data[10]));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data[11]));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data[12]));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data[13]));
            $sheet->setCellValue('P' . $rows, number_indo_excel($data[14]).'%');
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data[15]));
            $sheet->setCellValue('R' . $rows, number_indo_excel($data[16]));
            $sheet->setCellValue('S' . $rows, number_indo_excel($data[17]));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data[18]));
            $sheet->setCellValue('U' . $rows, number_indo_excel($data[19]));
            $sheet->setCellValue('V' . $rows, number_indo_excel($data[20]).'%');
            $sheet->setCellValue('W' . $rows, number_indo_excel($data[21]));
            $sheet->setCellValue('X' . $rows, number_indo_excel($data[22]));
            $sheet->setCellValue('Y' . $rows, number_indo_excel($data[23]));
            $sheet->setCellValue('Z' . $rows, number_indo_excel($data[24]));
            $sheet->setCellValue('AA' . $rows, number_indo_excel($data[25]));
            $sheet->setCellValue('AB' . $rows, number_indo_excel($data[26]).'%');
            $sheet->setCellValue('AC' . $rows, number_indo_excel($data[27]));
            $sheet->setCellValue('AD' . $rows, number_indo_excel($data[28]));
            $sheet->setCellValue('AE' . $rows, number_indo_excel($data[29]));
            $sheet->setCellValue('AF' . $rows, number_indo_excel($data[30]));
            $sheet->setCellValue('AG' . $rows, number_indo_excel($data[31]));
            $sheet->setCellValue('AH' . $rows, number_indo_excel($data[32]).'%');
            $sheet->setCellValue('AI' . $rows, number_indo_excel($data[33]));
            $sheet->setCellValue('AJ' . $rows, number_indo_excel($data[34]));
            $sheet->setCellValue('AK' . $rows, number_indo_excel($data[35]));
            $sheet->setCellValue('AL' . $rows, number_indo_excel($data[36]));
            $sheet->setCellValue('AM' . $rows, number_indo_excel($data[37]));
            $sheet->setCellValue('AN' . $rows, number_indo_excel($data[38]).'%');
            $sheet->setCellValue('AO' . $rows, number_indo_excel($data[39]));
            $sheet->setCellValue('AP' . $rows, number_indo_excel($data[40]));
            $sheet->setCellValue('AQ' . $rows, number_indo_excel($data[41]));
            $sheet->setCellValue('AR' . $rows, number_indo_excel($data[42]));
            $sheet->setCellValue('AS' . $rows, number_indo_excel($data[43]));
            $sheet->setCellValue('AT' . $rows, number_indo_excel($data[44]).'%');
            $sheet->setCellValue('AU' . $rows, number_indo_excel($data[45]));
            $sheet->setCellValue('AV' . $rows, number_indo_excel($data[46]));
            $sheet->setCellValue('AW' . $rows, number_indo_excel($data[47]));
            $sheet->setCellValue('AX' . $rows, number_indo_excel($data[48]));
            $sheet->setCellValue('AY' . $rows, number_indo_excel($data[49]));
            $sheet->setCellValue('AZ' . $rows, number_indo_excel($data[50]).'%');
            $sheet->setCellValue('BA' . $rows, number_indo_excel($data[51]));
            $sheet->setCellValue('BB' . $rows, number_indo_excel($data[52]));
            $sheet->setCellValue('BC' . $rows, number_indo_excel($data[53]));
            $sheet->setCellValue('BD' . $rows, number_indo_excel($data[54]));
            $sheet->setCellValue('BE' . $rows, number_indo_excel($data[55]));
            $sheet->setCellValue('BF' . $rows, number_indo_excel($data[56]).'%');
            $sheet->setCellValue('BG' . $rows, number_indo_excel($data[57]));
            $sheet->setCellValue('BH' . $rows, number_indo_excel($data[58]));
            $sheet->setCellValue('BI' . $rows, number_indo_excel($data[59]));
            $sheet->setCellValue('BJ' . $rows, number_indo_excel($data[60]));
            $sheet->setCellValue('BK' . $rows, number_indo_excel($data[61]));
            $sheet->setCellValue('BL' . $rows, number_indo_excel($data[62]).'%');
            $sheet->setCellValue('BM' . $rows, number_indo_excel($data[63]));
            $sheet->setCellValue('BN' . $rows, number_indo_excel($data[64]));
            $sheet->setCellValue('BO' . $rows, number_indo_excel($data[65]));
            $sheet->setCellValue('BP' . $rows, number_indo_excel($data[66]));
            $sheet->setCellValue('BQ' . $rows, number_indo_excel($data[67]));
            $sheet->setCellValue('BR' . $rows, number_indo_excel($data[68]).'%');
            $sheet->setCellValue('BS' . $rows, number_indo_excel($data[69]));
            $sheet->setCellValue('BT' . $rows, number_indo_excel($data[70]));
            $sheet->setCellValue('BU' . $rows, number_indo_excel($data[71]));
            $sheet->setCellValue('BV' . $rows, number_indo_excel($data[72]));
            $sheet->setCellValue('BW' . $rows, number_indo_excel($data[73]));
            $sheet->setCellValue('BX' . $rows, number_indo_excel($data[74]).'%');
            $sheet->setCellValue('BY' . $rows, number_indo_excel($data[75]));
            $sheet->setCellValue('BZ' . $rows, number_indo_excel($data[76]));
            $sheet->setCellValue('CA' . $rows, number_indo_excel($data[77]));
            $sheet->setCellValue('CB' . $rows, number_indo_excel($data[78]));
            $sheet->setCellValue('CC' . $rows, number_indo_excel($data[79]));
            $sheet->setCellValue('CD' . $rows, number_indo_excel($data[80]).'%');


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('AI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AL' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('AO' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AP' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AQ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AR' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AS' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AT' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AT' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('AU' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AV' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AW' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AX' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AY' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AZ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AZ' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('BA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BF' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('BG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BL' . $rows)->getNumberFormat()
    ->setFormatCode('[Blue][>=3000]$#,##0;[Red][<0]$#,##0;$#,##0')->applyFromArray(setBody());
            $sheet->getStyle('BL' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('BM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BO' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BP' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BQ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BR' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BR' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('BS' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BT' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BU' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BV' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BW' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BX' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BX' . $rows)->applyFromArray(setBorderRight());
            $sheet->getStyle('BY' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('BZ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('CA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('CB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('CC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('CD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('CD' . $rows)->applyFromArray(setBorderRight());


            foreach (range('A', 'CE') as $columnID) {
                $sheet->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            $rows++;
        }

        $fileName = "MARGIN PER ZONA.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function rugiProduksiExcel($tahun){
         //$tahun = date('Y');
         $sql = DB::select("SELECT kodeunit, region,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun'),'') AS jan,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun'),'') AS feb,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun'),'') AS mar,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun'),'') AS apr,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun'),'') AS mei,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun'),'') AS jun,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun'),'') AS jul,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun'),'') AS agu,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun'),'') AS sep,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun'),'') AS okt,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun'),'') AS nov,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun'),'') AS des,
                        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND YEAR(tgldocfinal)='$tahun'),'') AS cum,
                        IFNULL((SELECT COUNT(nomrhpprugi) FROM table_rhpp WHERE unit=a.kodeunit AND nomrhpprugi > 0 AND YEAR(tgldocfinal)='$tahun'),0)/(SELECT COUNT(ciawal) FROM table_rhpp WHERE unit=a.kodeunit AND ciawal > 0 AND YEAR(tgldocfinal)='$tahun')*100 AS flok_rugi
                        FROM units a");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:R1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', ' PENCAPAIAN RUGI PRODUKSI PER BULAN');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:R4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', 'RUGI PROD PER BULAN');
        $spreadsheet->getActiveSheet()->mergeCells('D3:O3');
        $sheet->setCellValue('D4', 'JAN');
        $sheet->setCellValue('E4', 'FEB');
        $sheet->setCellValue('F4', 'MAR');
        $sheet->setCellValue('G4', 'APR');
        $sheet->setCellValue('H4', 'MEI');
        $sheet->setCellValue('I4', 'JUN');
        $sheet->setCellValue('J4', 'JUL');
        $sheet->setCellValue('K4', 'AGU');
        $sheet->setCellValue('L4', 'SEP');
        $sheet->setCellValue('M4', 'OKT');
        $sheet->setCellValue('N4', 'NOV');
        $sheet->setCellValue('O4', 'DES');

        $sheet->setCellValue('P3', 'YEAR TO DATE '.$tahun);
        $spreadsheet->getActiveSheet()->mergeCells('P3:R3');
        $sheet->setCellValue('P4', 'RUGI PROD ');
        $sheet->setCellValue('Q4', '% FLOK RUGI ');
        $sheet->setCellValue('R4', 'EVALUASI');

        $rows = 5;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->kodeunit);
            $sheet->setCellValue('C' . $rows, $data->region);
            $sheet->setCellValue('D' . $rows, number_indo_excel($data->jan));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data->feb));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data->mar));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data->apr));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data->mei));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data->jun));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data->jul));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data->agu));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data->sep));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data->okt));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data->nov));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data->des));
            $sheet->setCellValue('P' . $rows, number_indo_excel($data->cum));
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data->flok_rugi));
            $sheet->setCellValue('R' . $rows, evaluasiProduksi($data->cum));

            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('C')->setWidth('15');
            $sheet->getColumnDimension('P')->setWidth('15');
            $sheet->getColumnDimension('Q')->setWidth('15');
            $sheet->getColumnDimension('R')->setWidth('15');
            foreach (range('D', 'O') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $rugifoot = DB::select("SELECT kode, val FROM (SELECT MONTH(tgldocfinal) AS bln, round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                                FROM table_rhpp  WHERE YEAR(tgldocfinal)='$tahun' GROUP BY MONTH(tgldocfinal) ) a
                                RIGHT JOIN ( SELECT kode FROM bulan) b ON a.bln = b.kode
                                UNION ALL
                                SELECT 'ALL', round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                                FROM table_rhpp WHERE YEAR(tgldocfinal)='$tahun'");

        $arrfoot = [];
        foreach ($rugifoot as $val) {
            array_push($arrfoot,$val->val);
        }
            $sheet->setCellValue('A' . $rows, '');
            $sheet->setCellValue('B' . $rows, '');
            $sheet->setCellValue('C' . $rows, 'CUM');
            $sheet->setCellValue('D' . $rows, number_indo_excel($arrfoot[0]));
            $sheet->setCellValue('E' . $rows, number_indo_excel($arrfoot[1]));
            $sheet->setCellValue('F' . $rows, number_indo_excel($arrfoot[2]));
            $sheet->setCellValue('G' . $rows, number_indo_excel($arrfoot[3]));
            $sheet->setCellValue('H' . $rows, number_indo_excel($arrfoot[4]));
            $sheet->setCellValue('I' . $rows, number_indo_excel($arrfoot[5]));
            $sheet->setCellValue('J' . $rows, number_indo_excel($arrfoot[6]));
            $sheet->setCellValue('K' . $rows, number_indo_excel($arrfoot[7]));
            $sheet->setCellValue('L' . $rows, number_indo_excel($arrfoot[8]));
            $sheet->setCellValue('M' . $rows, number_indo_excel($arrfoot[9]));
            $sheet->setCellValue('N' . $rows, number_indo_excel($arrfoot[10]));
            $sheet->setCellValue('O' . $rows, number_indo_excel($arrfoot[11]));
            $sheet->setCellValue('P' . $rows, number_indo_excel($arrfoot[12]));
            $sheet->setCellValue('Q' . $rows, '');
            $sheet->setCellValue('R' . $rows, '');

            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());

        $fileName = "RUGI_PRODUKSI.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function rugiProduksiExcelAp($tahun){
        //$tahun = date('Y');
        $sql = DB::select("SELECT id, koderegion,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=1 AND YEAR(tgldocfinal)='$tahun'),'') AS jan,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=2 AND YEAR(tgldocfinal)='$tahun'),'') AS feb,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=3 AND YEAR(tgldocfinal)='$tahun'),'') AS mar,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=4 AND YEAR(tgldocfinal)='$tahun'),'') AS apr,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=5 AND YEAR(tgldocfinal)='$tahun'),'') AS mei,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=6 AND YEAR(tgldocfinal)='$tahun'),'') AS jun,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=7 AND YEAR(tgldocfinal)='$tahun'),'') AS jul,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=8 AND YEAR(tgldocfinal)='$tahun'),'') AS agu,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=9 AND YEAR(tgldocfinal)='$tahun'),'') AS sep,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=10 AND YEAR(tgldocfinal)='$tahun'),'') AS okt,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=11 AND YEAR(tgldocfinal)='$tahun'),'') AS nov,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND MONTH(tgldocfinal)=12 AND YEAR(tgldocfinal)='$tahun'),'') AS des,
        IFNULL((SELECT SUM(nomrhpprugi)/SUM(ciawal) FROM vrhpp WHERE region=a.koderegion AND YEAR(tgldocfinal)='$tahun'),'') AS cum,
        IFNULL((SELECT COUNT(nomrhpprugi) FROM vrhpp WHERE region=a.koderegion AND nomrhpprugi > 0 AND YEAR(tgldocfinal)='$tahun'),0)/(SELECT COUNT(ciawal) FROM vrhpp WHERE region=a.koderegion AND ciawal > 0 AND YEAR(tgldocfinal)='$tahun')*100 AS flok_rugi
    FROM regions a");

       $spreadsheet = new Spreadsheet();
       $spreadsheet->getActiveSheet()->mergeCells('A1:Q1');
       $spreadsheet->getActiveSheet()->setCellValue('A1', ' PENCAPAIAN RUGI PRODUKSI PER BULAN TAHUN '.$tahun);
       $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

       $spreadsheet->getActiveSheet()->getStyle('A3:Q4')->applyFromArray(setHeader());
       $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

       $sheet = $spreadsheet->getActiveSheet();
       $sheet->setCellValue('A3', 'NO');
       $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

       $sheet->setCellValue('B3', 'AP');
       $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

       $sheet->setCellValue('C3', 'RUGI PROD PER BULAN');
       $spreadsheet->getActiveSheet()->mergeCells('C3:N3');
       $sheet->setCellValue('C4', 'JAN');
       $sheet->setCellValue('D4', 'FEB');
       $sheet->setCellValue('E4', 'MAR');
       $sheet->setCellValue('F4', 'APR');
       $sheet->setCellValue('G4', 'MEI');
       $sheet->setCellValue('H4', 'JUN');
       $sheet->setCellValue('I4', 'JUL');
       $sheet->setCellValue('J4', 'AGU');
       $sheet->setCellValue('K4', 'SEP');
       $sheet->setCellValue('L4', 'OKT');
       $sheet->setCellValue('M4', 'NOV');
       $sheet->setCellValue('N4', 'DES');

       $sheet->setCellValue('O3', 'YEAR TO DATE '.$tahun);
       $spreadsheet->getActiveSheet()->mergeCells('O3:Q3');
       $sheet->setCellValue('O4', 'RUGI PROD ');
       $sheet->setCellValue('P4', '% FLOK RUGI ');
       $sheet->setCellValue('Q4', 'EVALUASI');

       $rows = 5;
       $no = 1;

       foreach ($sql as $data) {
           $sheet->setCellValue('A' . $rows, $no++);
           $sheet->setCellValue('B' . $rows, $data->koderegion);
           $sheet->setCellValue('C' . $rows, number_indo_excel($data->jan));
           $sheet->setCellValue('D' . $rows, number_indo_excel($data->feb));
           $sheet->setCellValue('E' . $rows, number_indo_excel($data->mar));
           $sheet->setCellValue('F' . $rows, number_indo_excel($data->apr));
           $sheet->setCellValue('G' . $rows, number_indo_excel($data->mei));
           $sheet->setCellValue('H' . $rows, number_indo_excel($data->jun));
           $sheet->setCellValue('I' . $rows, number_indo_excel($data->jul));
           $sheet->setCellValue('J' . $rows, number_indo_excel($data->agu));
           $sheet->setCellValue('K' . $rows, number_indo_excel($data->sep));
           $sheet->setCellValue('L' . $rows, number_indo_excel($data->okt));
           $sheet->setCellValue('M' . $rows, number_indo_excel($data->nov));
           $sheet->setCellValue('N' . $rows, number_indo_excel($data->des));
           $sheet->setCellValue('O' . $rows, number_indo_excel($data->cum));
           $sheet->setCellValue('P' . $rows, number_indo_excel($data->flok_rugi));
           $sheet->setCellValue('Q' . $rows, evaluasiProduksi($data->cum));

           $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());

           $sheet->getColumnDimension('A')->setWidth('5');
           $sheet->getColumnDimension('C')->setWidth('15');
           $sheet->getColumnDimension('P')->setWidth('15');
           $sheet->getColumnDimension('Q')->setWidth('15');
           $sheet->getColumnDimension('R')->setWidth('15');
           foreach (range('D', 'O') as $columnID) {
               $sheet->getColumnDimension($columnID)->setWidth('10');
               $sheet->getColumnDimension($columnID)->setAutoSize(false);
           }
           $rows++;
       }

       $rugifoot = DB::select("SELECT kode, val FROM (SELECT MONTH(tgldocfinal) AS bln, round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                               FROM table_rhpp WHERE YEAR(tgldocfinal)='$tahun' GROUP BY MONTH(tgldocfinal) ) a
                               RIGHT JOIN ( SELECT kode FROM bulan) b ON a.bln = b.kode
                               UNION ALL
                               SELECT 'ALL', round(SUM(nomrhpprugi)/SUM(ciawal),0) AS val
                               FROM table_rhpp WHERE YEAR(tgldocfinal)='$tahun'");

       $arrfoot = [];
       foreach ($rugifoot as $val) {
           array_push($arrfoot,$val->val);
       }
           $sheet->setCellValue('A' . $rows, '');
           $sheet->setCellValue('B' . $rows, 'CUM');
           $sheet->setCellValue('C' . $rows, number_indo_excel($arrfoot[0]));
           $sheet->setCellValue('D' . $rows, number_indo_excel($arrfoot[1]));
           $sheet->setCellValue('E' . $rows, number_indo_excel($arrfoot[2]));
           $sheet->setCellValue('F' . $rows, number_indo_excel($arrfoot[3]));
           $sheet->setCellValue('G' . $rows, number_indo_excel($arrfoot[4]));
           $sheet->setCellValue('H' . $rows, number_indo_excel($arrfoot[5]));
           $sheet->setCellValue('I' . $rows, number_indo_excel($arrfoot[6]));
           $sheet->setCellValue('J' . $rows, number_indo_excel($arrfoot[7]));
           $sheet->setCellValue('K' . $rows, number_indo_excel($arrfoot[8]));
           $sheet->setCellValue('L' . $rows, number_indo_excel($arrfoot[9]));
           $sheet->setCellValue('M' . $rows, number_indo_excel($arrfoot[10]));
           $sheet->setCellValue('N' . $rows, number_indo_excel($arrfoot[11]));
           $sheet->setCellValue('O' . $rows, number_indo_excel($arrfoot[12]));
           $sheet->setCellValue('P' . $rows, '');
           $sheet->setCellValue('Q' . $rows, '');

           $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
           $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());

       $fileName = "RUGI_PRODUKSI_AP.xlsx";
       $writer = new Xlsx($spreadsheet);
       $writer->save("export/" . $fileName);
       header("Content-Type: application/vnd.ms-excel");
       return redirect(url('/export/' . $fileName));
   }

    public function marginUnitExcel($ap, $tahun){
        //$tahun = date('Y');
         if ($ap != 'SEMUA') {
            $sql = DB::select("SELECT a.kodeunit, a.region,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM units a WHERE a.region='$ap' ORDER BY a.region ASC");

            $sqlFoot = DB::select("SELECT ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg)),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                FROM vrhpp WHERE region='$ap' AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'");
        } else {
            $sql = DB::select("SELECT a.kodeunit, a.region,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE unit=a.kodeunit AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM units a ORDER BY a.region ASC");

            $sqlFoot = DB::select("SELECT ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg)),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'");
        }


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AP1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', ' MARGIN UNIT PER BULAN');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AP4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', 'YTD '.$tahun);
        $spreadsheet->getActiveSheet()->mergeCells('D3:F3');
        $sheet->setCellValue('D4', 'HPP ');
        $sheet->setCellValue('E4', 'HJ ');
        $sheet->setCellValue('F4', 'MRG ');

        $sheet->setCellValue('G3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('G3:I3');
        $sheet->setCellValue('G4', 'HPP ');
        $sheet->setCellValue('H4', 'HJ ');
        $sheet->setCellValue('I4', 'MRG ');

        $sheet->setCellValue('J3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('J3:L3');
        $sheet->setCellValue('J4', 'HPP ');
        $sheet->setCellValue('K4', 'HJ');
        $sheet->setCellValue('L4', 'MRG ');

        $sheet->setCellValue('M3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('M3:O3');
        $sheet->setCellValue('M4', 'HPP');
        $sheet->setCellValue('N4', 'HJ');
        $sheet->setCellValue('O4', 'MRG ');

        $sheet->setCellValue('P3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('P3:R3');
        $sheet->setCellValue('P4', 'HPP');
        $sheet->setCellValue('Q4', 'HJ');
        $sheet->setCellValue('R4', 'MRG ');

        $sheet->setCellValue('S3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('S3:U3');
        $sheet->setCellValue('S4', 'HPP');
        $sheet->setCellValue('T4', 'HJ');
        $sheet->setCellValue('U4', 'MRG ');

        $sheet->setCellValue('V3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('V3:X3');
        $sheet->setCellValue('V4', 'HPP');
        $sheet->setCellValue('W4', 'HJ');
        $sheet->setCellValue('X4', 'MRG ');

        $sheet->setCellValue('Y3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('Y3:AA3');
        $sheet->setCellValue('Y4', 'HPP');
        $sheet->setCellValue('Z4', 'HJ');
        $sheet->setCellValue('AA4', 'MRG ');

        $sheet->setCellValue('AB3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('AB3:AD3');
        $sheet->setCellValue('AB4', 'HPP');
        $sheet->setCellValue('AC4', 'HJ');
        $sheet->setCellValue('AD4', 'MRG ');

        $sheet->setCellValue('AE3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('AE3:AG3');
        $sheet->setCellValue('AE4', 'HPP');
        $sheet->setCellValue('AF4', 'HJ');
        $sheet->setCellValue('AG4', 'MRG ');

        $sheet->setCellValue('AH3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('AH3:AJ3');
        $sheet->setCellValue('AH4', 'HPP');
        $sheet->setCellValue('AI4', 'HJ');
        $sheet->setCellValue('AJ4', 'MRG ');

        $sheet->setCellValue('AK3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('AK3:AM3');
        $sheet->setCellValue('AK4', 'HPP');
        $sheet->setCellValue('AL4', 'HJ');
        $sheet->setCellValue('AM4', 'MRG ');

        $sheet->setCellValue('AN3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('AN3:AP3');
        $sheet->setCellValue('AN4', 'HPP');
        $sheet->setCellValue('AO4', 'HJ');
        $sheet->setCellValue('AP4', 'MRG ');



        $rows = 5;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->kodeunit);
            $sheet->setCellValue('C' . $rows, $data->region);
            $sheet->setCellValue('D' . $rows, number_indo_excel($data->YtdHpp));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data->YtdHj));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data->YtdHj-$data->YtdHpp));

            $sheet->setCellValue('G' . $rows, number_indo_excel($data->JanHpp));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data->JanHj));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data->JanHj-$data->JanHpp));

            $sheet->setCellValue('J' . $rows, number_indo_excel($data->FebHpp));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data->FebHj));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data->FebHj-$data->FebHpp));

            $sheet->setCellValue('M' . $rows, number_indo_excel($data->MarHpp));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data->MarHj));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data->MarHj-$data->MarHpp));

            $sheet->setCellValue('P' . $rows, number_indo_excel($data->AprHpp));
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data->AprHj));
            $sheet->setCellValue('R' . $rows, number_indo_excel($data->AprHj-$data->AprHpp));

            $sheet->setCellValue('S' . $rows, number_indo_excel($data->MeiHpp));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data->MeiHj));
            $sheet->setCellValue('U' . $rows, number_indo_excel($data->MeiHj-$data->MeiHpp));

            $sheet->setCellValue('V' . $rows, number_indo_excel($data->JunHpp));
            $sheet->setCellValue('W' . $rows, number_indo_excel($data->JunHj));
            $sheet->setCellValue('X' . $rows, number_indo_excel($data->JunHj-$data->JunHpp));

            $sheet->setCellValue('Y' . $rows, number_indo_excel($data->JulHpp));
            $sheet->setCellValue('Z' . $rows, number_indo_excel($data->JulHj));
            $sheet->setCellValue('AA' . $rows, number_indo_excel($data->JulHj-$data->JulHpp));

            $sheet->setCellValue('AB' . $rows, number_indo_excel($data->AguHpp));
            $sheet->setCellValue('AC' . $rows, number_indo_excel($data->AguHj));
            $sheet->setCellValue('AD' . $rows, number_indo_excel($data->AguHj-$data->AguHpp));

            $sheet->setCellValue('AE' . $rows, number_indo_excel($data->SepHpp));
            $sheet->setCellValue('AF' . $rows, number_indo_excel($data->SepHj));
            $sheet->setCellValue('AG' . $rows, number_indo_excel($data->SepHj-$data->SepHpp));

            $sheet->setCellValue('AH' . $rows, number_indo_excel($data->OktHpp));
            $sheet->setCellValue('AI' . $rows, number_indo_excel($data->OktHj));
            $sheet->setCellValue('AJ' . $rows, number_indo_excel($data->OktHj-$data->OktHpp));

            $sheet->setCellValue('AK' . $rows, number_indo_excel($data->NovHpp));
            $sheet->setCellValue('AL' . $rows, number_indo_excel($data->NovHj));
            $sheet->setCellValue('AM' . $rows, number_indo_excel($data->NovHj-$data->NovHpp));

            $sheet->setCellValue('AN' . $rows, number_indo_excel($data->DesHpp));
            $sheet->setCellValue('AO' . $rows, number_indo_excel($data->DesHj));
            $sheet->setCellValue('AP' . $rows, number_indo_excel($data->DesHj-$data->DesHpp));


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AL' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AO' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AP' . $rows)->applyFromArray(setBody());

            foreach (range('D', 'AP') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10')->setAutoSize(false);
            }
            $sheet->getColumnDimension('A')->setWidth('5')->setAutoSize(false);
            $sheet->getColumnDimension('B')->setWidth('8')->setAutoSize(false);
            $sheet->getColumnDimension('C')->setWidth('8')->setAutoSize(false);
            $rows++;
        }

        foreach ($sqlFoot as $data) {
            $sheet->setCellValue('A' . $rows, '');
            $sheet->setCellValue('B' . $rows, 'AVG');
            $sheet->setCellValue('C' . $rows, '');
            $sheet->setCellValue('D' . $rows, number_indo_excel($data->YtdHpp));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data->YtdHj));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data->YtdHj-$data->YtdHpp));

            $sheet->setCellValue('G' . $rows, number_indo_excel($data->JanHpp));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data->JanHj));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data->JanHj-$data->JanHpp));

            $sheet->setCellValue('J' . $rows, number_indo_excel($data->FebHpp));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data->FebHj));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data->FebHj-$data->FebHpp));

            $sheet->setCellValue('M' . $rows, number_indo_excel($data->MarHpp));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data->MarHj));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data->MarHj-$data->MarHpp));

            $sheet->setCellValue('P' . $rows, number_indo_excel($data->AprHpp));
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data->AprHj));
            $sheet->setCellValue('R' . $rows, number_indo_excel($data->AprHj-$data->AprHpp));

            $sheet->setCellValue('S' . $rows, number_indo_excel($data->MeiHpp));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data->MeiHj));
            $sheet->setCellValue('U' . $rows, number_indo_excel($data->MeiHj-$data->MeiHpp));

            $sheet->setCellValue('V' . $rows, number_indo_excel($data->JunHpp));
            $sheet->setCellValue('W' . $rows, number_indo_excel($data->JunHj));
            $sheet->setCellValue('X' . $rows, number_indo_excel($data->JunHj-$data->JunHpp));

            $sheet->setCellValue('Y' . $rows, number_indo_excel($data->JulHpp));
            $sheet->setCellValue('Z' . $rows, number_indo_excel($data->JulHj));
            $sheet->setCellValue('AA' . $rows, number_indo_excel($data->JulHj-$data->JulHpp));

            $sheet->setCellValue('AB' . $rows, number_indo_excel($data->AguHpp));
            $sheet->setCellValue('AC' . $rows, number_indo_excel($data->AguHj));
            $sheet->setCellValue('AD' . $rows, number_indo_excel($data->AguHj-$data->AguHpp));

            $sheet->setCellValue('AE' . $rows, number_indo_excel($data->SepHpp));
            $sheet->setCellValue('AF' . $rows, number_indo_excel($data->SepHj));
            $sheet->setCellValue('AG' . $rows, number_indo_excel($data->SepHj-$data->SepHpp));

            $sheet->setCellValue('AH' . $rows, number_indo_excel($data->OktHpp));
            $sheet->setCellValue('AI' . $rows, number_indo_excel($data->OktHj));
            $sheet->setCellValue('AJ' . $rows, number_indo_excel($data->OktHj-$data->OktHpp));

            $sheet->setCellValue('AK' . $rows, number_indo_excel($data->NovHpp));
            $sheet->setCellValue('AL' . $rows, number_indo_excel($data->NovHj));
            $sheet->setCellValue('AM' . $rows, number_indo_excel($data->NovHj-$data->NovHpp));

            $sheet->setCellValue('AN' . $rows, number_indo_excel($data->DesHpp));
            $sheet->setCellValue('AO' . $rows, number_indo_excel($data->DesHj));
            $sheet->setCellValue('AP' . $rows, number_indo_excel($data->DesHj-$data->DesHpp));


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AL' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AO' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AP' . $rows)->applyFromArray(setBody());

            foreach (range('D', 'AP') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10')->setAutoSize(false);
            }
            $sheet->getColumnDimension('A')->setWidth('5')->setAutoSize(false);
            $sheet->getColumnDimension('B')->setWidth('8')->setAutoSize(false);
            $sheet->getColumnDimension('C')->setWidth('8')->setAutoSize(false);
            $rows++;
        }
        $spreadsheet->getActiveSheet()->getStyle('A'.($rows-1).':AP'.($rows-1))->applyFromArray(setHeader());
        $sheet->getStyle('D5:AP'.$rows++)->getNumberFormat()->setFormatCode('[Blue][>=30000]#,##0;[Red][<0]#,##0;#,##0')->applyFromArray(setBody());
        $fileName = "MARGIN_UNIT.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function marginApExcel(){
        $tahun = date('Y');
        $sql = DB::select("SELECT a.region AS region,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM vrhpp WHERE region=a.region AND tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM units a GROUP BY region ASC");

        $sqlFoot = DB::select("SELECT ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg)),0) AS YtdHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'),0) AS YtdHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-01-31'),0) AS JanHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-02-01' AND '$tahun-02-31'),0) AS FebHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-03-01' AND '$tahun-03-31'),0) AS MarHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-04-01' AND '$tahun-04-31'),0) AS AprHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-05-01' AND '$tahun-05-31'),0) AS MeiHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-06-01' AND '$tahun-06-31'),0) AS JunHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-07-01' AND '$tahun-07-31'),0) AS JulHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-08-01' AND '$tahun-08-31'),0) AS AguHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-09-01' AND '$tahun-09-31'),0) AS SepHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-10-01' AND '$tahun-10-31'),0) AS OktHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-11-01' AND '$tahun-11-31'),0) AS NovHj,
                    ROUND((SELECT (SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHpp,
                    ROUND((SELECT SUM(jualayamactual)/SUM(cokg) FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-12-01' AND '$tahun-12-31'),0) AS DesHj
                    FROM table_rhpp WHERE tgldocfinal BETWEEN '$tahun-01-01' AND '$tahun-12-31'");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AO1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', ' MARGIN REGION PER BULAN');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AO4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'YTD '.$tahun);
        $spreadsheet->getActiveSheet()->mergeCells('C3:E3');
        $sheet->setCellValue('C4', 'HPP ');
        $sheet->setCellValue('D4', 'HJ ');
        $sheet->setCellValue('E4', 'MRG ');

        $sheet->setCellValue('F3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('F3:H3');
        $sheet->setCellValue('F4', 'HPP ');
        $sheet->setCellValue('G4', 'HJ ');
        $sheet->setCellValue('H4', 'MRG ');

        $sheet->setCellValue('I3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('I3:K3');
        $sheet->setCellValue('I4', 'HPP ');
        $sheet->setCellValue('J4', 'HJ');
        $sheet->setCellValue('K4', 'MRG ');

        $sheet->setCellValue('L3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('L3:N3');
        $sheet->setCellValue('L4', 'HPP');
        $sheet->setCellValue('M4', 'HJ');
        $sheet->setCellValue('N4', 'MRG ');

        $sheet->setCellValue('O3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('O3:Q3');
        $sheet->setCellValue('O4', 'HPP');
        $sheet->setCellValue('P4', 'HJ');
        $sheet->setCellValue('Q4', 'MRG ');

        $sheet->setCellValue('R3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('R3:T3');
        $sheet->setCellValue('R4', 'HPP');
        $sheet->setCellValue('S4', 'HJ');
        $sheet->setCellValue('T4', 'MRG ');

        $sheet->setCellValue('U3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('U3:W3');
        $sheet->setCellValue('U4', 'HPP');
        $sheet->setCellValue('V4', 'HJ');
        $sheet->setCellValue('W4', 'MRG ');

        $sheet->setCellValue('X3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('X3:Z3');
        $sheet->setCellValue('X4', 'HPP');
        $sheet->setCellValue('Y4', 'HJ');
        $sheet->setCellValue('Z4', 'MRG ');

        $sheet->setCellValue('AA3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('AA3:AC3');
        $sheet->setCellValue('AA4', 'HPP');
        $sheet->setCellValue('AB4', 'HJ');
        $sheet->setCellValue('AC4', 'MRG ');

        $sheet->setCellValue('AD3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('AD3:AF3');
        $sheet->setCellValue('AD4', 'HPP');
        $sheet->setCellValue('AE4', 'HJ');
        $sheet->setCellValue('AF4', 'MRG ');

        $sheet->setCellValue('AG3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('AG3:AI3');
        $sheet->setCellValue('AG4', 'HPP');
        $sheet->setCellValue('AH4', 'HJ');
        $sheet->setCellValue('AI4', 'MRG ');

        $sheet->setCellValue('AJ3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('AJ3:AL3');
        $sheet->setCellValue('AJ4', 'HPP');
        $sheet->setCellValue('AK4', 'HJ');
        $sheet->setCellValue('AL4', 'MRG ');

        $sheet->setCellValue('AM3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('AM3:AO3');
        $sheet->setCellValue('AM4', 'HPP');
        $sheet->setCellValue('AN4', 'HJ');
        $sheet->setCellValue('AO4', 'MRG ');



        $rows = 5;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->region);
            $sheet->setCellValue('C' . $rows, number_indo_excel($data->YtdHpp));
            $sheet->setCellValue('D' . $rows, number_indo_excel($data->YtdHj));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data->YtdHj-$data->YtdHpp));

            $sheet->setCellValue('F' . $rows, number_indo_excel($data->JanHpp));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data->JanHj));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data->JanHj-$data->JanHpp));

            $sheet->setCellValue('I' . $rows, number_indo_excel($data->FebHpp));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data->FebHj));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data->FebHj-$data->FebHpp));

            $sheet->setCellValue('L' . $rows, number_indo_excel($data->MarHpp));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data->MarHj));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data->MarHj-$data->MarHpp));

            $sheet->setCellValue('O' . $rows, number_indo_excel($data->AprHpp));
            $sheet->setCellValue('P' . $rows, number_indo_excel($data->AprHj));
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data->AprHj-$data->AprHpp));

            $sheet->setCellValue('R' . $rows, number_indo_excel($data->MeiHpp));
            $sheet->setCellValue('S' . $rows, number_indo_excel($data->MeiHj));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data->MeiHj-$data->MeiHpp));

            $sheet->setCellValue('U' . $rows, number_indo_excel($data->JunHpp));
            $sheet->setCellValue('V' . $rows, number_indo_excel($data->JunHj));
            $sheet->setCellValue('W' . $rows, number_indo_excel($data->JunHj-$data->JunHpp));

            $sheet->setCellValue('X' . $rows, number_indo_excel($data->JulHpp));
            $sheet->setCellValue('Y' . $rows, number_indo_excel($data->JulHj));
            $sheet->setCellValue('Z' . $rows, number_indo_excel($data->JulHj-$data->JulHpp));

            $sheet->setCellValue('AA' . $rows, number_indo_excel($data->AguHpp));
            $sheet->setCellValue('AB' . $rows, number_indo_excel($data->AguHj));
            $sheet->setCellValue('AC' . $rows, number_indo_excel($data->AguHj-$data->AguHpp));

            $sheet->setCellValue('AD' . $rows, number_indo_excel($data->SepHpp));
            $sheet->setCellValue('AE' . $rows, number_indo_excel($data->SepHj));
            $sheet->setCellValue('AF' . $rows, number_indo_excel($data->SepHj-$data->SepHpp));

            $sheet->setCellValue('AG' . $rows, number_indo_excel($data->OktHpp));
            $sheet->setCellValue('AH' . $rows, number_indo_excel($data->OktHj));
            $sheet->setCellValue('AI' . $rows, number_indo_excel($data->OktHj-$data->OktHpp));

            $sheet->setCellValue('AJ' . $rows, number_indo_excel($data->NovHpp));
            $sheet->setCellValue('AK' . $rows, number_indo_excel($data->NovHj));
            $sheet->setCellValue('AL' . $rows, number_indo_excel($data->NovHj-$data->NovHpp));

            $sheet->setCellValue('AM' . $rows, number_indo_excel($data->DesHpp));
            $sheet->setCellValue('AN' . $rows, number_indo_excel($data->DesHj));
            $sheet->setCellValue('AO' . $rows, number_indo_excel($data->DesHj-$data->DesHpp));


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AL' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AO' . $rows)->applyFromArray(setBody());

            foreach (range('C', 'AO') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10')->setAutoSize(false);
            }
            $sheet->getColumnDimension('A')->setWidth('5')->setAutoSize(false);
            $sheet->getColumnDimension('B')->setWidth('8')->setAutoSize(false);
            $rows++;
        }

        foreach ($sqlFoot as $data) {
            $sheet->setCellValue('A' . $rows, '');
            $sheet->setCellValue('B' . $rows, 'AVG');
            $sheet->setCellValue('C' . $rows, number_indo_excel($data->YtdHpp));
            $sheet->setCellValue('D' . $rows, number_indo_excel($data->YtdHj));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data->YtdHj-$data->YtdHpp));

            $sheet->setCellValue('F' . $rows, number_indo_excel($data->JanHpp));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data->JanHj));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data->JanHj-$data->JanHpp));

            $sheet->setCellValue('I' . $rows, number_indo_excel($data->FebHpp));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data->FebHj));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data->FebHj-$data->FebHpp));

            $sheet->setCellValue('L' . $rows, number_indo_excel($data->MarHpp));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data->MarHj));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data->MarHj-$data->MarHpp));

            $sheet->setCellValue('O' . $rows, number_indo_excel($data->AprHpp));
            $sheet->setCellValue('P' . $rows, number_indo_excel($data->AprHj));
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data->AprHj-$data->AprHpp));

            $sheet->setCellValue('R' . $rows, number_indo_excel($data->MeiHpp));
            $sheet->setCellValue('S' . $rows, number_indo_excel($data->MeiHj));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data->MeiHj-$data->MeiHpp));

            $sheet->setCellValue('U' . $rows, number_indo_excel($data->JunHpp));
            $sheet->setCellValue('V' . $rows, number_indo_excel($data->JunHj));
            $sheet->setCellValue('W' . $rows, number_indo_excel($data->JunHj-$data->JunHpp));

            $sheet->setCellValue('X' . $rows, number_indo_excel($data->JulHpp));
            $sheet->setCellValue('Y' . $rows, number_indo_excel($data->JulHj));
            $sheet->setCellValue('Z' . $rows, number_indo_excel($data->JulHj-$data->JulHpp));

            $sheet->setCellValue('AA' . $rows, number_indo_excel($data->AguHpp));
            $sheet->setCellValue('AB' . $rows, number_indo_excel($data->AguHj));
            $sheet->setCellValue('AC' . $rows, number_indo_excel($data->AguHj-$data->AguHpp));

            $sheet->setCellValue('AD' . $rows, number_indo_excel($data->SepHpp));
            $sheet->setCellValue('AE' . $rows, number_indo_excel($data->SepHj));
            $sheet->setCellValue('AF' . $rows, number_indo_excel($data->SepHj-$data->SepHpp));

            $sheet->setCellValue('AG' . $rows, number_indo_excel($data->OktHpp));
            $sheet->setCellValue('AH' . $rows, number_indo_excel($data->OktHj));
            $sheet->setCellValue('AI' . $rows, number_indo_excel($data->OktHj-$data->OktHpp));

            $sheet->setCellValue('AJ' . $rows, number_indo_excel($data->NovHpp));
            $sheet->setCellValue('AK' . $rows, number_indo_excel($data->NovHj));
            $sheet->setCellValue('AL' . $rows, number_indo_excel($data->NovHj-$data->NovHpp));

            $sheet->setCellValue('AM' . $rows, number_indo_excel($data->DesHpp));
            $sheet->setCellValue('AN' . $rows, number_indo_excel($data->DesHj));
            $sheet->setCellValue('AO' . $rows, number_indo_excel($data->DesHj-$data->DesHpp));


            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AL' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AO' . $rows)->applyFromArray(setBody());

            foreach (range('C', 'AO') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10')->setAutoSize(false);
            }
            $sheet->getColumnDimension('A')->setWidth('5')->setAutoSize(false);
            $sheet->getColumnDimension('B')->setWidth('8')->setAutoSize(false);
            $rows++;
        }
        $spreadsheet->getActiveSheet()->getStyle('A'.($rows-1).':AO'.($rows-1))->applyFromArray(setHeader());
        $sheet->getStyle('C5:AO'.$rows++)->getNumberFormat()->setFormatCode('[Blue][>=30000]#,##0;[Red][<0]#,##0;#,##0')->applyFromArray(setBody());
        $fileName = "MARGIN_REGION.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function hppbwExcel($ap){
        if ($ap != 'SEMUA') {
            $sql = DB::select("SELECT kodeunit, region,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20) AS bw100_120,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.81 AND 3.00) AS bw281_300,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50
                        FROM units a WHERE region = '$ap' ORDER BY a.kodearca ASC");

            $sqlFoot = DB::select("SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) AS bw100_120,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.81 AND 3.00) AS bw281_300,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50
                    FROM vschpp WHERE region='$ap' AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20");
        } else {
            $sql = DB::select("SELECT kodeunit, region,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20) AS bw100_120,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.81 AND 3.00) AS bw281_300,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),1) FROM vschpp WHERE kodeunit = a.kodearca AND rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50
                        FROM units a ORDER BY a.kodearca ASC");

            $sqlFoot = DB::select("SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) AS bw100_120,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.21 AND 1.40) AS bw121_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.41 AND 1.60) AS bw141_160,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.61 AND 1.80) AS bw161_180,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.81 AND 2.00) AS bw181_200,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.01 AND 2.20) AS bw201_220,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.21 AND 2.40) AS bw221_240,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.41 AND 2.60) AS bw241_260,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.61 AND 2.80) AS bw261_280,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 2.81 AND 3.00) AS bw281_300,
                        (SELECT round(SUM(rmsy)/SUM(pfmc_ci),0) FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 3.01 AND 5.00) AS bw50
                        FROM vschpp WHERE rhpp_ek >=-1000 AND pfmc_bw BETWEEN 1.00 AND 1.20");
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:N1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'ESTIMASI HPP PER SEGMEN BW / DALAM RIBUAN (DARI RECORDING ARCA)');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:N4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', '1');
        $sheet->setCellValue('E3', '1,21');
        $sheet->setCellValue('F3', '1,41');
        $sheet->setCellValue('G3', '1,61');
        $sheet->setCellValue('H3', '1,81');
        $sheet->setCellValue('I3', '2,01');
        $sheet->setCellValue('J3', '2,21');
        $sheet->setCellValue('K3', '2,41');
        $sheet->setCellValue('L3', '2,61');
        $sheet->setCellValue('M3', '2,81');
        $sheet->setCellValue('N3', '>');

        $sheet->setCellValue('D4', '1,2');
        $sheet->setCellValue('E4', '1,4');
        $sheet->setCellValue('F4', '1,6');
        $sheet->setCellValue('G4', '1,8');
        $sheet->setCellValue('H4', '2');
        $sheet->setCellValue('I4', '2,2');
        $sheet->setCellValue('J4', '2,4');
        $sheet->setCellValue('K4', '2,6');
        $sheet->setCellValue('L4', '2,8');
        $sheet->setCellValue('M4', '3');
        $sheet->setCellValue('N4', '3');

        $rows = 5;
        $no = 1;

    /*  $bw12 = 0;
        $bw14 = 0;
        $bw16 = 0;
        $bw18 = 0;
        $bw20 = 0;
        $bw22 = 0;
        $bw24 = 0;
        $bw26 = 0;
        $bw28 = 0;
        $bw30 = 0;
        $bw50 = 0;
    */
        foreach ($sql as $data) {
        /*  $bw12 += number_indo_1000_koma_kosong_excel($data->bw100_120);
            $bw14 += number_indo_1000_koma_kosong_excel($data->bw121_240);
            $bw16 += number_indo_1000_koma_kosong_excel($data->bw141_160);
            $bw18 += number_indo_1000_koma_kosong_excel($data->bw161_180);
            $bw20 += number_indo_1000_koma_kosong_excel($data->bw181_200);
            $bw22 += number_indo_1000_koma_kosong_excel($data->bw201_220);
            $bw24 += number_indo_1000_koma_kosong_excel($data->bw221_240);
            $bw26 += number_indo_1000_koma_kosong_excel($data->bw241_260);
            $bw28 += number_indo_1000_koma_kosong_excel($data->bw261_280);
            $bw30 += number_indo_1000_koma_kosong_excel($data->bw281_300);
            $bw50 += number_indo_1000_koma_kosong_excel($data->bw50);
        */
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->kodeunit);
            $sheet->setCellValue('C' . $rows, $data->region);
            $sheet->setCellValue('D' . $rows, number_indo_1000_koma_kosong_excel($data->bw100_120));
            $sheet->setCellValue('E' . $rows, number_indo_1000_koma_kosong_excel($data->bw121_240));
            $sheet->setCellValue('F' . $rows, number_indo_1000_koma_kosong_excel($data->bw141_160));
            $sheet->setCellValue('G' . $rows, number_indo_1000_koma_kosong_excel($data->bw161_180));
            $sheet->setCellValue('H' . $rows, number_indo_1000_koma_kosong_excel($data->bw181_200));
            $sheet->setCellValue('I' . $rows, number_indo_1000_koma_kosong_excel($data->bw201_220));
            $sheet->setCellValue('J' . $rows, number_indo_1000_koma_kosong_excel($data->bw221_240));
            $sheet->setCellValue('K' . $rows, number_indo_1000_koma_kosong_excel($data->bw241_260));
            $sheet->setCellValue('L' . $rows, number_indo_1000_koma_kosong_excel($data->bw261_280));
            $sheet->setCellValue('M' . $rows, number_indo_1000_koma_kosong_excel($data->bw281_300));
            $sheet->setCellValue('N' . $rows, number_indo_1000_koma_kosong_excel($data->bw50));

            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());

            foreach (range('D', 'N') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        foreach ($sqlFoot as $data) {
            /*  $bw12 += number_indo_1000_koma_kosong_excel($data->bw100_120);
                $bw14 += number_indo_1000_koma_kosong_excel($data->bw121_240);
                $bw16 += number_indo_1000_koma_kosong_excel($data->bw141_160);
                $bw18 += number_indo_1000_koma_kosong_excel($data->bw161_180);
                $bw20 += number_indo_1000_koma_kosong_excel($data->bw181_200);
                $bw22 += number_indo_1000_koma_kosong_excel($data->bw201_220);
                $bw24 += number_indo_1000_koma_kosong_excel($data->bw221_240);
                $bw26 += number_indo_1000_koma_kosong_excel($data->bw241_260);
                $bw28 += number_indo_1000_koma_kosong_excel($data->bw261_280);
                $bw30 += number_indo_1000_koma_kosong_excel($data->bw281_300);
                $bw50 += number_indo_1000_koma_kosong_excel($data->bw50);
            */
                $sheet->setCellValue('A' . $rows, '');
                $sheet->setCellValue('B' . $rows, 'AVG');
                $sheet->setCellValue('C' . $rows, '');
                $sheet->setCellValue('D' . $rows, number_indo_1000_koma_kosong_excel($data->bw100_120));
                $sheet->setCellValue('E' . $rows, number_indo_1000_koma_kosong_excel($data->bw121_240));
                $sheet->setCellValue('F' . $rows, number_indo_1000_koma_kosong_excel($data->bw141_160));
                $sheet->setCellValue('G' . $rows, number_indo_1000_koma_kosong_excel($data->bw161_180));
                $sheet->setCellValue('H' . $rows, number_indo_1000_koma_kosong_excel($data->bw181_200));
                $sheet->setCellValue('I' . $rows, number_indo_1000_koma_kosong_excel($data->bw201_220));
                $sheet->setCellValue('J' . $rows, number_indo_1000_koma_kosong_excel($data->bw221_240));
                $sheet->setCellValue('K' . $rows, number_indo_1000_koma_kosong_excel($data->bw241_260));
                $sheet->setCellValue('L' . $rows, number_indo_1000_koma_kosong_excel($data->bw261_280));
                $sheet->setCellValue('M' . $rows, number_indo_1000_koma_kosong_excel($data->bw281_300));
                $sheet->setCellValue('N' . $rows, number_indo_1000_koma_kosong_excel($data->bw50));

                $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
                $sheet->getStyle('N' . $rows)->applyFromArray(setBody());

                foreach (range('D', 'N') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setWidth('10');
                    $sheet->getColumnDimension($columnID)->setAutoSize(false);
                }
                $rows++;
            }
        /*  $sheet->setCellValue('A' . $rows, '');
            $sheet->setCellValue('B' . $rows, 'TOTAL');
            $sheet->setCellValue('C' . $rows, '');
            $sheet->setCellValue('D' . $rows, $bw12);
            $sheet->setCellValue('E' . $rows, $bw14);
            $sheet->setCellValue('F' . $rows, $bw16);
            $sheet->setCellValue('G' . $rows, $bw18);
            $sheet->setCellValue('H' . $rows, $bw20);
            $sheet->setCellValue('I' . $rows, $bw22);
            $sheet->setCellValue('J' . $rows, $bw24);
            $sheet->setCellValue('K' . $rows, $bw26);
            $sheet->setCellValue('L' . $rows, $bw28);
            $sheet->setCellValue('M' . $rows, $bw30);
            $sheet->setCellValue('N' . $rows, $bw50);

            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
        */
        $spreadsheet->getActiveSheet()->getStyle('A'.($rows-1).':N'.($rows-1))->applyFromArray(setHeader());
        $fileName = "ESTIMASI_HPP_PER_SEGMENT_BW.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function perbandingan_kontrak(){
        $no=1;
        $pengguna = Auth::user()->nik;
        $region = Auth::user()->region;
        $roles = Auth::user()->roles;
        if($roles=='sr'){
            $sql = DB::select("SELECT kodeunit FROM units WHERE aktif='Y' AND region ='$region' ORDER BY kodeunit ASC");
        }else{
            $sql = DB::select("SELECT kodeunit FROM units WHERE aktif='Y' ORDER BY kodeunit ASC");
        }
        $sqlRhpp = DB::select("SELECT bw, total_rhpp FROM table_kontrak_rhpp ORDER BY bw ASC");
        $sqlHpp = DB::select("SELECT bw, hpp_inti FROM table_kontrak_rhpp ORDER BY bw ASC");
        $sqlSapronak = DB::select("SELECT kodeunit FROM units ORDER BY kodeunit ASC");
        $sqlResume = DB::select("SELECT a.id, a.nama, a.unit, b.region, a.tanggal, c.min_bw, c.max_bw, a.doc_hb_franco, a.doc_hj, a.doc_margin, a.tot_pakan_hb_franco, a.tot_pakan_hj, (a.tot_pakan_hj-a.tot_pakan_hb_franco) AS pakan_margin, c.hpp, c.rhpp FROM table_kontrak_sapronak a
                                    INNER JOIN units b ON b.kodeunit=a.unit
                                    LEFT JOIN table_kontrak_resume c ON c.id_kontrak=a.id");

        $kosong = '';
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");

        return view('dashboard.produksi.perbandinganKontrak',compact('no','sql','sqlSapronak','sqlRhpp','sqlHpp', 'pengguna','sqlResume','ap'));
    }

    public function perbandingan_kontrak_get_resume($ap, $unit, Request $request){
        if ($request -> ajax()) {
            if($unit!='SEMUA'){
                $strWhere = " WHERE region='$ap' AND unit='$unit'";
            }else{
                if($ap!='SEMUA'){
                    $strWhere = " WHERE region='$ap'";
                }else{
                    $strWhere = "";
                }
            }
            $data = DB::select("SELECT a.id, a.kode, a.nama, a.unit, b.region, a.tanggal, c.min_bw, c.max_bw, a.doc_hb_franco, a.doc_hj, a.doc_margin,
                                    a.tot_pakan_hb_franco, a.tot_pakan_hj, (a.tot_pakan_hj-a.tot_pakan_hb_franco) AS pakan_margin,
                                    ROUND((SELECT AVG(hpp_inti) FROM table_kontrak_rhpp WHERE kode=a.kode AND bw BETWEEN c.min_bw AND c.max_bw),0) AS hpp,
                                    ROUND((SELECT AVG(total_rhpp) FROM table_kontrak_rhpp WHERE kode=a.kode AND bw BETWEEN c.min_bw AND c.max_bw),0) AS rhpp FROM table_kontrak_sapronak a
                                    INNER JOIN units b ON b.kodeunit=a.unit
                                    LEFT JOIN table_kontrak_resume c ON c.id_kontrak=a.id $strWhere
                                    ORDER BY a.unit ASC");
            return response() -> json(['success' => true, 'data' => $data]);
        }
    }

    public function perbandingan_kontrak_create(Request $request){
        $hargadoc = 0;
        $unit = $request->input('unit');
        $kontrak = $request->input('nama_kontrak');
        $jenis_kontrak = $request->input('jenis_kontrak');
        $strKode = $unit.$kontrak;
        $kode = str_replace(' ','0',$strKode);
        $sqlCek = DB::select("SELECT id FROM table_kontrak_sapronak WHERE unit='$unit' AND nama='$kontrak'");
        $sqlHargaDoc = DB::select("SELECT hargadoc FROM table_var_statik WHERE id=1");
        foreach($sqlHargaDoc as $data){
            $hargadoc = $data->hargadoc;
        }
        if($sqlCek){
            return $this->perbandingan_kontrak_unit(getId($unit));
        }else{
            $tgl_kontrak = $request->input('tgl_kontrak');
            $jenis_kontrak = $request->input('jenis_kontrak');
            $create_sapronak = DB::statement("INSERT INTO table_kontrak_sapronak (kode, tanggal, unit, nama, doc_hb_franco, jenis_kontrak) VALUES ('$kode','$tgl_kontrak','$unit','$kontrak','$hargadoc','$jenis_kontrak')");
            if($create_sapronak){
                $create_bonus = DB::statement("INSERT INTO table_kontrak_bonus (kode, tanggal, unit, nama) VALUES ('$kode','$tgl_kontrak','$unit','$kontrak')");
                if($create_bonus){
                    $create_adj = DB::statement("INSERT INTO table_kontrak_adj (kode, tanggal, unit, nama) VALUES ('$kode','$tgl_kontrak','$unit','$kontrak')");
                }
            }

            $no=1;
            $sql = DB::select("SELECT kodeunit FROM units ORDER BY kodeunit ASC");
            $pakan = DB::select("SELECT pakan, harga FROM master_pakan ORDER BY pakan ASC");
            $tgl_update = kontrak_tgl_update($unit, $kontrak);
            return view('dashboard.produksi.perbandinganKontrakUnit',compact('no','unit','pakan','tgl_update','jenis_kontrak','kontrak'));
        }
    }

    public function perbandingan_kontrak_unit($unit){
        $unit = setId($unit);
        $no=1;
        $sql = DB::select("SELECT tanggal, nama, jenis_kontrak FROM table_kontrak_sapronak WHERE unit='$unit' ORDER BY tanggal DESC");
        return view('dashboard.produksi.perbandinganKontrakUnitList',compact('no','unit','sql'));
    }

    public function perbandingan_kontrak_delete($unit, $nama){
        $delSapronak = DB::delete("DELETE FROM table_kontrak_sapronak WHERE unit='$unit' AND nama='$nama'");
        if($delSapronak){
            $delBonus = DB::delete("DELETE FROM table_kontrak_bonus WHERE unit='$unit' AND nama='$nama'");
            if($delBonus){
                $delAdj = DB::delete("DELETE FROM table_kontrak_adj WHERE unit='$unit' AND nama='$nama'");
                if($delAdj){
                    $delRhpp = DB::delete("DELETE FROM table_kontrak_rhpp WHERE unit='$unit' AND nama='$nama'");
                }
            }
        }
        $unit = getId($unit);
        return $this->perbandingan_kontrak_unit($unit);
    }

    public function perbandingan_kontrak_edit($unit, $kontrak, $jenis_kontrak){
        $unit = setId($unit);
        $no=1;
        $sql = DB::select("SELECT kodeunit FROM units ORDER BY kodeunit ASC");
        $pakan = DB::select("SELECT pakan, harga FROM master_pakan ORDER BY pakan ASC");
        $tgl_update = kontrak_tgl_update($unit, $kontrak);
        return view('dashboard.produksi.perbandinganKontrakUnit',compact('no','unit','pakan','tgl_update','kontrak','jenis_kontrak'));
    }

    public function perbandingan_kontrak_pakan(Request $request) {
        $pakan = $request->input('pakan');
        $sql = DB::select("SELECT harga FROM master_pakan WHERE pakan ='$pakan'");
        return response()->json($sql);
    }

    public function perbandingan_kontrak_pakan_update(Request $request, $unit, $nama){
        $unit = setId($unit);
        $sapronak = KontrakSapronak::where('unit','=',$unit)->where('nama','=',$nama)->first();
        $sapronak->tanggal = date('Y-m-d');
        $sapronak->unit = $unit;
        $sapronak->doc_hb_franco = $request->doc_beli_frc;
        $sapronak->doc_hj = $request->doc_harga_jual;
        $sapronak->doc_margin = $request->doc_margin_jual;
        $sapronak->ovk_hb_franco = $request->ovk_beli_frc;
        $sapronak->ovk_hj = $request->ovk_harga_jual;
        $sapronak->ovk_margin = $request->ovk_margin_jual;
        $sapronak->tot_pakan_hb_franco = $request->tot_beli_pakan;
        $sapronak->tot_pakan_hj = $request->tot_jual_pakan;
        $sapronak->tot_pakan_pemakaian = $request->tot_pemakaian_pakan;


        $sapronak->pakan1 = $request->pakan1;
        $sapronak->pakan1_hb_locco = $request->pakan1_harga_beli_locco;
        $sapronak->pakan1_oa = $request->pakan1_oa;
        $sapronak->pakan1_hb_franco = $request->pakan1_harga_beli_franco;
        $sapronak->pakan1_hj = $request->pakan1_harga_jual;
        $sapronak->pakan1_pemakaian = $request->pakan1_pemakaian;
        $sapronak->pakan1_margin = $request->pakan1_margin_jual;

        $sapronak->pakan2 = $request->pakan2;
        $sapronak->pakan2_hb_locco = $request->pakan2_harga_beli_locco;
        $sapronak->pakan2_oa = $request->pakan2_oa;
        $sapronak->pakan2_hb_franco = $request->pakan2_harga_beli_franco;
        $sapronak->pakan2_hj = $request->pakan2_harga_jual;
        $sapronak->pakan2_pemakaian = $request->pakan2_pemakaian;
        $sapronak->pakan2_margin = $request->pakan2_margin_jual;

        $sapronak->pakan3 = $request->pakan3;
        $sapronak->pakan3_hb_locco = $request->pakan3_harga_beli_locco;
        $sapronak->pakan3_oa = $request->pakan3_oa;
        $sapronak->pakan3_hb_franco = $request->pakan3_harga_beli_franco;
        $sapronak->pakan3_hj = $request->pakan3_harga_jual;
        $sapronak->pakan3_pemakaian = $request->pakan3_pemakaian;
        $sapronak->pakan3_margin = $request->pakan3_margin_jual;

        $sapronak->pakan4 = $request->pakan4;
        $sapronak->pakan4_hb_locco = $request->pakan4_harga_beli_locco;
        $sapronak->pakan4_oa = $request->pakan4_oa;
        $sapronak->pakan4_hb_franco = $request->pakan4_harga_beli_franco;
        $sapronak->pakan4_hj = $request->pakan4_harga_jual;
        $sapronak->pakan4_pemakaian = $request->pakan4_pemakaian;
        $sapronak->pakan4_margin = $request->pakan4_margin_jual;

        $sapronak->pakan5 = $request->pakan5;
        $sapronak->pakan5_hb_locco = $request->pakan5_harga_beli_locco;
        $sapronak->pakan5_oa = $request->pakan5_oa;
        $sapronak->pakan5_hb_franco = $request->pakan5_harga_beli_franco;
        $sapronak->pakan5_hj = $request->pakan5_harga_jual;
        $sapronak->pakan5_pemakaian = $request->pakan5_pemakaian;
        $sapronak->pakan5_margin = $request->pakan5_margin_jual;

        $sapronak->pakan6 = $request->pakan6;
        $sapronak->pakan6_hb_locco = $request->pakan6_harga_beli_locco;
        $sapronak->pakan6_oa = $request->pakan6_oa;
        $sapronak->pakan6_hb_franco = $request->pakan6_harga_beli_franco;
        $sapronak->pakan6_hj = $request->pakan6_harga_jual;
        $sapronak->pakan6_pemakaian = $request->pakan6_pemakaian;
        $sapronak->pakan6_margin = $request->pakan6_margin_jual;

        $sapronak->pakan7 = $request->pakan7;
        $sapronak->pakan7_hb_locco = $request->pakan7_harga_beli_locco;
        $sapronak->pakan7_oa = $request->pakan7_oa;
        $sapronak->pakan7_hb_franco = $request->pakan7_harga_beli_franco;
        $sapronak->pakan7_hj = $request->pakan7_harga_jual;
        $sapronak->pakan7_pemakaian = $request->pakan7_pemakaian;
        $sapronak->pakan7_margin = $request->pakan7_margin_jual;

        $sapronak->pakan8 = $request->pakan8;
        $sapronak->pakan8_hb_locco = $request->pakan8_harga_beli_locco;
        $sapronak->pakan8_oa = $request->pakan8_oa;
        $sapronak->pakan8_hb_franco = $request->pakan8_harga_beli_franco;
        $sapronak->pakan8_hj = $request->pakan8_harga_jual;
        $sapronak->pakan8_pemakaian = $request->pakan8_pemakaian;
        $sapronak->pakan8_margin = $request->pakan8_margin_jual;

        $sapronak->update();
        echo "Data berhasil diupdate";
    }

    public function perbandingan_kontrak_bonus_update(Request $request, $unit, $nama){
        $unit = setId($unit);
        $bonus = KontrakBonus::where('unit','=',$unit)->where('nama','=',$nama)->first();
        $bonus->tanggal = date('Y-m-d');
        $bonus->unit = $unit;
        $bonus->bw_09 = $request->bw_09;
        $bonus->bw_13 = $request->bw_13;
        $bonus->bw_14 = $request->bw_14;
        $bonus->bw_15 = $request->bw_15;
        $bonus->bw_16 = $request->bw_16;
        $bonus->bw_18 = $request->bw_18;
        $bonus->bw_19 = $request->bw_19;
        $bonus->bw_20 = $request->bw_20;
        $bonus->bw_22 = $request->bw_22;
        $bonus->bw_24 = $request->bw_24;
        $bonus->bw_26 = $request->bw_26;
        $bonus->bw_27 = $request->bw_27;
        $bonus->bw_28 = $request->bw_28;
        $bonus->bw_29 = $request->bw_29;
        $bonus->bw_30 = $request->bw_30;
        $bonus->bns_hrg_pasar = $request->bns_hrg_pasar;
        $bonus->bns_op = $request->bns_op;
        $bonus->bns_ip_35 = $request->bns_ip_35;
        $bonus->bns_ip_37 = $request->bns_ip_37;
        $bonus->bns_ip_40 = $request->bns_ip_40;
        $bonus->bns_fcr_1 = $request->bns_fcr_1;
        $bonus->bns_fcr_2 = $request->bns_fcr_2;
        $bonus->bns_fcr_3 = $request->bns_fcr_3;
        $bonus->bns_fcr_4 = $request->bns_fcr_4;
        $bonus->bns_dpls = $request->bns_dpls;
        $bonus->bns_bw_1 = $request->bns_bw_1;
        $bonus->bns_bw_2 = $request->bns_bw_2;
        $bonus->bns_bw_3 = $request->bns_bw_3;
        $bonus->bns_bw_4 = $request->bns_bw_4;
        $bonus->val_bns_ip_35 = $request->val_bns_ip_35;
        $bonus->val_bns_ip_37 = $request->val_bns_ip_37;
        $bonus->val_bns_ip_40 = $request->val_bns_ip_40;
        $bonus->val_bns_fcr_1 = $request->val_bns_fcr_1;
        $bonus->val_bns_fcr_2 = $request->val_bns_fcr_2;
        $bonus->val_bns_fcr_3 = $request->val_bns_fcr_3;
        $bonus->update();
        echo "Data berhasil diupdate";
    }

    public function perbandingan_kontrak_adj_update(Request $request, $unit, $nama){
        $unit = setId($unit);
        $bonus = KontrakAdj::where('unit','=',$unit)->where('nama','=',$nama)->first();
        $bonus->tanggal = date('Y-m-d');
        $bonus->unit = $unit;
        $bonus->adj_dpls = $request->adj_dpls;
        $bonus->adj_fcr = $request->adj_fcr;
        $bonus->adj_bonus_pasar = $request->adj_bonus_pasar;
        $bonus->update();
        echo "Data berhasil diupdate";
    }

    public function perbandingan_kontrak_pakan_get($unit, $nama){
        $unit = setId($unit);
        $sql = DB::select("SELECT * FROM vkontrak_sapronak WHERE unit='$unit' AND nama='$nama'");
        return response()->json($sql);
    }

    public function perbandingan_kontrak_bonus_get($unit, $nama){
        $unit = setId($unit);
        $sql = DB::select("SELECT * FROM table_kontrak_bonus WHERE unit='$unit' AND nama='$nama'");
        return response()->json($sql);
    }

    public function perbandingan_kontrak_adj_get($unit, $nama){
        $unit = setId($unit);
        $sql = DB::select("SELECT * FROM table_kontrak_adj WHERE unit='$unit' AND nama='$nama'");
        return response()->json($sql);
    }

    public function perbandingan_kontrak_hasil($unit, $nama){
        $unit = setId($unit);
        //return $unit;
        $sqlFcr = DB::select("SELECT adj_fcr, adj_dpls FROM table_kontrak_adj WHERE unit='$unit' AND nama='$nama'");
        foreach($sqlFcr as $data){
            $adj_fcr = $data->adj_fcr;
            $adj_dpls = $data->adj_dpls;
        }

        $sql = DB::select("SELECT a.bw, (CAST(b.dpls as DECIMAL(10,2))+$adj_dpls) AS dpls, (b.fcr+$adj_fcr) AS fcr, b.umur,
                            (a.bw*((100-(CAST(b.dpls as DECIMAL(10,2))+$adj_dpls))/100)*1000000/100/b.umur/(b.fcr+$adj_fcr)) AS ip,
                            (a.bw* (b.fcr+$adj_fcr)*(100-b.dpls)/100) AS fi,
                            (SELECT bns_op FROM table_kontrak_bonus WHERE unit='$unit' AND nama='$nama') AS bonus_op
                            FROM table_kontrak_bw a
                            LEFT JOIN table_std_pfmc b ON b.bw=a.bw ORDER BY a.bw ASC
                        ");
        $arrSql = array();
        foreach ($sql as $data) {
            array_push($arrSql,array(
                'kode' => str_replace(' ','0',$unit.$nama),
                'bw' => $data->bw,
                'dpls' => $data->dpls,
                'fcr' => $data->fcr,
                'ip' => ROUND($data->ip,0),
                'umur' => ROUND($data->umur,0),
                'fi' => $data->fi,
                'kontrak_lb' => $kontrak_lb = kontrak_lb($unit, $data->bw, $nama),
                'pfmc' => $kontrak_pfmc = kontrak_pfmc($unit, $data->bw, $data->dpls, $data->fcr, $kontrak_lb, $nama),
                'bonus_op' => $bonus_op = $data->bonus_op,
                'insentif' => $pfmc_insentif =  pfmc_insentif($unit, $data->ip, $adj_fcr, $nama),
                'bonus_pasar' => $bonus_pasar = bonus_pasar($unit, $kontrak_lb, $data->bw, $data->dpls, $nama),
                'total_rhpp' => $total_rhpp = ROUND($kontrak_pfmc+$bonus_op+$pfmc_insentif+$bonus_pasar,0),
                'hpp_inti' => kontrak_hpp_inti($unit, $total_rhpp, $data->bw, $data->dpls, $data->fcr, $nama),
                'unit' => $unit,
                'nama' => $nama,
            ));
        }
        $hapus = DB::delete("DELETE FROM table_kontrak_rhpp WHERE unit='$unit' AND nama='$nama'");
        if($hapus){
            $insert_rhpp = collect($arrSql);
        }else{
            $insert_rhpp = collect($arrSql);
        }
        DB::table('table_kontrak_rhpp')->insert($insert_rhpp->toArray());
        return Datatables::of($arrSql)->addIndexColumn()->make(true);
    }

    public function load_kontrak(){
        $sql = DB::select("SELECT a.id, a.unit, nama, CONCAT(a.unit,' | ',a.nama) AS kontrak, a.kode, b.region FROM table_kontrak_adj a
                            INNER JOIN units b ON b.kodeunit = a.unit ORDER BY a.unit ASC");
        return Datatables::of($sql)
            ->addIndexColumn()
            ->make(true);
    }

    public function load_kontrak_filter($pengguna){
        $sql = DB::select("SELECT a.id, a.unit, nama, CONCAT(a.unit,' | ',a.nama) AS kontrak, a.kode, b.region FROM table_kontrak_filter a
                            INNER JOIN units b ON b.kodeunit = a.unit WHERE a.pengguna='$pengguna' GROUP BY a.kode ASC");
        return Datatables::of($sql)
            ->addIndexColumn()
            ->make(true);
    }

    public function move_id($id){
        $pengguna = Auth::user()->nik;
        DB::statement("INSERT INTO table_kontrak_filter (kode,unit,nama,pengguna) SELECT REPLACE(CONCAT(unit,nama),' ','0') AS kode, unit, nama, '$pengguna' FROM table_kontrak_adj WHERE id='$id'");
    }

    public function hapus_id_filter($id){
        $pengguna = Auth::user()->nik;
        DB::delete("DELETE FROM table_kontrak_filter WHERE id='$id' AND pengguna='$pengguna'");
    }

    public function perbandingan_kontrak_resume(){
        $pengguna = Auth::user()->nik;
        $sql = DB::select("SELECT * FROM (SELECT tanggal,kode, unit AS kodeunit, nama FROM table_kontrak_rhpp
                                WHERE kode IN (SELECT kode FROM table_kontrak_filter WHERE pengguna='$pengguna')
                            )a JOIN (
                                SELECT MAX(tanggal) AS tgl_last, unit FROM table_kontrak_rhpp GROUP BY unit ASC
                            )b ON b.unit=a.kodeunit GROUP BY kode ASC");
        return view('dashboard.produksi.perbandinganKontrakResume',compact('sql'));
    }

    public function perbandingan_kontrak_resume_all(){
        $sql = DB::select("SELECT * FROM (SELECT tanggal,kode, unit AS kodeunit, nama FROM table_kontrak_rhpp
                                WHERE kode IN (SELECT kode FROM table_kontrak_adj) GROUP BY kode ASC)a
                            JOIN (
                                SELECT MAX(tanggal) AS tgl_last, unit FROM table_kontrak_rhpp GROUP BY unit ASC
                            )b ON b.unit=a.kodeunit GROUP BY kode ASC");
        return view('dashboard.produksi.perbandinganKontrakResume',compact('sql'));
    }

    public function perbandingan_kontrak_edit_tgl(Request $request){
        $unit = $request->input('strUnit');
        $nama_kontrak = $request->input('nama_kontrak');
        $jenis_kontrak = $request->input('jenis_kontrak');
        $kodeUnit = setId($unit);
        $kontrak = $request->input('strKontrak');
        $tglkontrak = $request->input('tgl_kontrak');
        $strKode = $kodeUnit.$nama_kontrak;
        $kode = str_replace(' ','0',$strKode);
        $sapronak = DB::statement("UPDATE table_kontrak_sapronak SET tanggal='$tglkontrak', nama='$nama_kontrak', jenis_kontrak='$jenis_kontrak', kode='$kode' WHERE unit='$kodeUnit' AND nama='$kontrak'");
        if($sapronak){
            $bonus = DB::statement("UPDATE table_kontrak_bonus SET tanggal='$tglkontrak', nama='$nama_kontrak', kode='$kode' WHERE unit='$kodeUnit' AND nama='$kontrak'");
            if($bonus){
                $adj = DB::statement("UPDATE table_kontrak_adj SET tanggal='$tglkontrak', nama='$nama_kontrak', kode='$kode' WHERE unit='$kodeUnit' AND nama='$kontrak'");
                if($adj) {
                    Alert::toast('Tanggal berhasil diupdate', 'success');
                } else {
                    Alert::toast('Tanggal gagal diupdate!', 'danger');
                }
            }
        }

        return $this->perbandingan_kontrak_edit($unit, $nama_kontrak, $jenis_kontrak);
    }

    public function perbandingan_kontrak_resume_edit($id, $unit){
        $sqlSetting = DB::select("SELECT a.id, a.kode, a.nama, a.unit, b.region, a.tanggal, c.min_bw, c.max_bw, a.doc_hb_franco, a.doc_hj, a.doc_margin, a.tot_pakan_hb_franco, a.tot_pakan_hj, (a.tot_pakan_hj-a.tot_pakan_hb_franco) AS pakan_margin, c.hpp, c.rhpp FROM table_kontrak_sapronak a
                                    INNER JOIN units b ON b.kodeunit=a.unit
                                    LEFT JOIN table_kontrak_resume c ON c.id_kontrak=a.id
                                    WHERE a.id='$id'");
        // $sqlMinBw = DB::select("SELECT * ");
        // $sqlMaxBw = DB::select("");
        return view('dashboard.produksi.perbandinganKontrakSetting',compact('sqlSetting'));
    }

    public function get_edit_kontrak(Request $request) {
        $id = $request->input('id');
        $count = getRows('table_kontrak_resume', 'id_kontrak', $id);
        if($count > 0){
            $data = DB::select("SELECT * FROM table_kontrak_resume WHERE id_kontrak='$id'");
        }else{
            $data = DB::select("SELECT a.id, a.kode, a.nama, a.unit, b.region, a.tanggal, c.min_bw, c.max_bw, a.doc_hb_franco, a.doc_hj, a.doc_margin, a.tot_pakan_hb_franco, a.tot_pakan_hj, (a.tot_pakan_hj-a.tot_pakan_hb_franco) AS pakan_margin, c.hpp, c.rhpp FROM table_kontrak_sapronak a
                                    INNER JOIN units b ON b.kodeunit=a.unit
                                    LEFT JOIN table_kontrak_resume c ON c.id_kontrak=a.id
                                    WHERE a.id='$id'");
        }
        return response()->json($data);
    }

    public function get_edit_kontrak_simpan(Request $request){
        $id = $request->input('id');
        $count = getRows('table_kontrak_resume', 'id_kontrak', $id);
        if($count > 0){
             DB::table('table_kontrak_resume')->where('id_kontrak', $id)->update([
                'min_bw' => $request->input('min_bw'),
                'max_bw' => $request->input('max_bw'),
            ]);
        }else{
             DB::table('table_kontrak_resume')->insert([
                'id_kontrak' => $id,
                'min_bw' => $request->input('min_bw'),
                'max_bw' => $request->input('max_bw'),
            ]);
        }
        return response()->json(
            [
            'success' => true,
            'message' => 'Data berhasil disimpan'
            ]
        );
    }

    public function perbandingan_kontrak_setting_simpan(Request $request){
        $id_kontrak = $request->input('id_kontrak');
        $kode = $request->input('kode');
        $unit = $request->input('unit');
        $max_bw = $request->input('max_bw');
        $min_bw = $request->input('min_bw');
        return redirect()->back()->with('success', 'Berhasil disimpan');
    }

    public function diff_fcr(Request $request){
        $tahun = $request->input('tahun');
        $region = $request->input('region');

        $kosong = '';
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");

        /*
        $jabatan = Auth::user()->jabatan;
        $koderegion = Auth::user()->region;
        $kosong = '';
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF QA");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if (in_array($jabatan, $akses)) {
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
        } else {
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $koderegion . '" ORDER BY koderegion ASC');
        }
        */

        $noUnit = 1;

        if($region!=''){
            $strWhere = " WHERE YEAR(tgldocfinal)='$tahun' AND region = '$region' ORDER BY region, kodeunit ASC";
        }else{
            $strWhere = "WHERE YEAR(tgldocfinal)='$tahun' ORDER BY region, kodeunit ASC";
        }

        $sqlUnit = DB::select("SELECT unit as kodeunit, region,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN coekor ELSE 0 END),2) AS gab_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END),3) AS gab_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END),2) AS jan_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END),3) AS jan_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN coekor ELSE 0 END),2) AS feb_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END),3) AS feb_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN coekor ELSE 0 END),2) AS mar_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END),3) AS mar_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN coekor ELSE 0 END),2) AS apr_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END),3) AS apr_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN coekor ELSE 0 END),2) AS mei_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END),3) AS mei_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN coekor ELSE 0 END),2) AS jun_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END),3) AS jun_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN coekor ELSE 0 END),2) AS jul_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END),3) AS jul_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN coekor ELSE 0 END),2) AS agu_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END),3) AS agu_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN coekor ELSE 0 END),2) AS sep_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END),3) AS sep_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN coekor ELSE 0 END),2) AS okt_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END),3) AS okt_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN coekor ELSE 0 END),2) AS nov_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=11 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END),3) AS nov_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END),2) AS des_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END),3) AS des_fcr
                                    FROM vrhpp $strWhere");
        $units = array();
        foreach($sqlUnit as $data){
            array_push($units, [$kodeunit = $data->kodeunit,
            $koderegion = $data->region,

            $gab_bw = $data->gab_bw,
            $gab_fcr = $data->gab_fcr,
            $gab_diff = fcr_std_bw($data->gab_bw, $data->gab_fcr),
            fcr_ket($gab_diff),

            $jan_bw = $data->jan_bw,
            $jan_fcr = $data->jan_fcr,
            fcr_std_bw($data->jan_bw, $data->jan_fcr),

            $feb_bw = $data->feb_bw,
            $feb_fcr = $data->feb_fcr,
            fcr_std_bw($data->feb_bw, $data->feb_fcr),

            $mar_bw = $data->mar_bw,
            $mar_fcr = $data->mar_fcr,
            fcr_std_bw($data->mar_bw, $data->mar_fcr),

            $apr_bw = $data->apr_bw,
            $apr_fcr = $data->apr_fcr,
            fcr_std_bw($data->apr_bw, $data->apr_fcr),

            $mei_bw = $data->mei_bw,
            $mei_fcr = $data->mei_fcr,
            fcr_std_bw($data->mei_bw, $data->mei_fcr),

            $jun_bw = $data->jun_bw,
            $jun_fcr = $data->jun_fcr,
            fcr_std_bw($data->jun_bw, $data->jun_fcr),

            $jul_bw = $data->jul_bw,
            $jul_fcr = $data->jul_fcr,
            fcr_std_bw($data->jul_bw, $data->jul_fcr),

            $agu_bw = $data->agu_bw,
            $agu_fcr = $data->agu_fcr,
            fcr_std_bw($data->agu_bw, $data->agu_fcr),

            $sep_bw = $data->sep_bw,
            $sep_fcr = $data->sep_fcr,
            fcr_std_bw($data->sep_bw, $data->sep_fcr),

            $okt_bw = $data->okt_bw,
            $okt_fcr = $data->okt_fcr,
            fcr_std_bw($data->okt_bw, $data->okt_fcr),

            $nov_bw = $data->nov_bw,
            $nov_fcr = $data->nov_fcr,
            fcr_std_bw($data->nov_bw, $data->nov_fcr),

            $des_bw = $data->des_bw,
            $des_fcr = $data->des_fcr,
            fcr_std_bw($data->des_bw, $data->des_fcr),

            ]);
        }

        $max_gab = array_filter(array_column($units, 4)) == null ? 0 : max(array_column($units, 4));
        $max_jan = array_filter(array_column($units, 8)) == null ? 0 : max(array_column($units, 8));
        $max_feb = array_filter(array_column($units, 11)) == null ? 0 : max(array_column($units, 11));
        $max_mar = array_filter(array_column($units, 14)) == null ? 0 : max(array_column($units, 14));
        $max_apr = array_filter(array_column($units, 17)) == null ? 0 : max(array_column($units, 17));
        $max_mei = array_filter(array_column($units, 20)) == null ? 0 : max(array_column($units, 20));
        $max_jun = array_filter(array_column($units, 23)) == null ? 0 : max(array_column($units, 23));
        $max_jul = array_filter(array_column($units, 26)) == null ? 0 : max(array_column($units, 26));
        $max_agu = array_filter(array_column($units, 29)) == null ? 0 : max(array_column($units, 29));
        $max_sep = array_filter(array_column($units, 32)) == null ? 0 : max(array_column($units, 32));
        $max_okt = array_filter(array_column($units, 35)) == null ? 0 : max(array_column($units, 35));
        $max_nov = array_filter(array_column($units, 38)) == null ? 0 : max(array_column($units, 38));
        $max_des = array_filter(array_column($units, 40)) == null ? 0 : max(array_column($units, 40));

        $min_gab = array_filter(array_column($units, 4)) == null ? 0 : min(array_filter(array_column($units, 4)));
        $min_jan = array_filter(array_column($units, 8)) == null ? 0 : min(array_filter(array_column($units, 8)));
        $min_feb = array_filter(array_column($units, 11)) == null ? 0 : min(array_filter(array_column($units, 11)));
        $min_mar = array_filter(array_column($units, 14)) == null ? 0 : min(array_filter(array_column($units, 14)));
        $min_apr = array_filter(array_column($units, 17)) == null ? 0 : min(array_filter(array_column($units, 17)));
        $min_mei = array_filter(array_column($units, 20)) == null ? 0 : min(array_filter(array_column($units, 20)));
        $min_jun = array_filter(array_column($units, 23)) == null ? 0 : min(array_filter(array_column($units, 23)));
        $min_jul = array_filter(array_column($units, 26)) == null ? 0 : min(array_filter(array_column($units, 26)));
        $min_agu = array_filter(array_column($units, 29)) == null ? 0 : min(array_filter(array_column($units, 29)));
        $min_sep = array_filter(array_column($units, 32)) == null ? 0 : min(array_filter(array_column($units, 32)));
        $min_okt = array_filter(array_column($units, 35)) == null ? 0 : min(array_filter(array_column($units, 35)));
        $min_nov = array_filter(array_column($units, 38)) == null ? 0 : min(array_filter(array_column($units, 38)));
        $min_des = array_filter(array_column($units, 40)) == null ? 0 : min(array_filter(array_column($units, 40)));

        $sqlRegion = DB::select("SELECT region,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN coekor ELSE 0 END),2) AS gab_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 THEN cokg ELSE 0 END),3) AS gab_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END),2) AS jan_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END),3) AS jan_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN coekor ELSE 0 END),2) AS feb_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=2 THEN cokg ELSE 0 END),3) AS feb_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN coekor ELSE 0 END),2) AS mar_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=3 THEN cokg ELSE 0 END),3) AS mar_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN coekor ELSE 0 END),2) AS apr_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=4 THEN cokg ELSE 0 END),3) AS apr_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN coekor ELSE 0 END),2) AS mei_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=5 THEN cokg ELSE 0 END),3) AS mei_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN coekor ELSE 0 END),2) AS jun_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=6 THEN cokg ELSE 0 END),3) AS jun_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN coekor ELSE 0 END),2) AS jul_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=7 THEN cokg ELSE 0 END),3) AS jul_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN coekor ELSE 0 END),2) AS agu_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=8 THEN cokg ELSE 0 END),3) AS agu_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN coekor ELSE 0 END),2) AS sep_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=9 THEN cokg ELSE 0 END),3) AS sep_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN coekor ELSE 0 END),2) AS okt_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=10 THEN cokg ELSE 0 END),3) AS okt_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=11 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN coekor ELSE 0 END),2) AS nov_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=11 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END),3) AS nov_fcr,

                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN cokg ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN coekor ELSE 0 END),2) AS des_bw,
                                    ROUND(SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=12 THEN feedkgqty ELSE 0 END)/SUM(CASE WHEN rhpprugiproduksi <= 0 AND MONTH(tgldocfinal)=1 THEN cokg ELSE 0 END),3) AS des_fcr
                                    FROM vrhpp ORDER BY region ASC");

        $arrRegion = array();
        foreach($sqlRegion as $data){
            array_push($arrRegion, [$koderegion = $data->koderegion,
            $koderegion = $data->koderegion,

            $gab_bw = $data->gab_bw,
            $gab_fcr = $data->gab_fcr,
            $gab_diff = fcr_std_bw($data->gab_bw, $data->gab_fcr),
            fcr_ket($gab_diff),

            $jan_bw = $data->jan_bw,
            $jan_fcr = $data->jan_fcr,
            fcr_std_bw($data->jan_bw, $data->jan_fcr),

            $feb_bw = $data->feb_bw,
            $feb_fcr = $data->feb_fcr,
            fcr_std_bw($data->feb_bw, $data->feb_fcr),

            $mar_bw = $data->mar_bw,
            $mar_fcr = $data->mar_fcr,
            fcr_std_bw($data->mar_bw, $data->mar_fcr),

            $apr_bw = $data->apr_bw,
            $apr_fcr = $data->apr_fcr,
            fcr_std_bw($data->apr_bw, $data->apr_fcr),

            $mei_bw = $data->mei_bw,
            $mei_fcr = $data->mei_fcr,
            fcr_std_bw($data->mei_bw, $data->mei_fcr),

            $jun_bw = $data->jun_bw,
            $jun_fcr = $data->jun_fcr,
            fcr_std_bw($data->jun_bw, $data->jun_fcr),

            $jul_bw = $data->jul_bw,
            $jul_fcr = $data->jul_fcr,
            fcr_std_bw($data->jul_bw, $data->jul_fcr),

            $agu_bw = $data->agu_bw,
            $agu_fcr = $data->agu_fcr,
            fcr_std_bw($data->agu_bw, $data->agu_fcr),

            $sep_bw = $data->sep_bw,
            $sep_fcr = $data->sep_fcr,
            fcr_std_bw($data->sep_bw, $data->sep_fcr),

            $okt_bw = $data->okt_bw,
            $okt_fcr = $data->okt_fcr,
            fcr_std_bw($data->okt_bw, $data->okt_fcr),

            $nov_bw = $data->nov_bw,
            $nov_fcr = $data->nov_fcr,
            fcr_std_bw($data->nov_bw, $data->nov_fcr),

            $des_bw = $data->des_bw,
            $des_fcr = $data->des_fcr,
            fcr_std_bw($data->des_bw, $data->des_fcr),

            ]);
        }

        $max_gab_reg = array_filter(array_column($arrRegion, 4)) == null ? 0 : max(array_column($arrRegion, 4));
        $max_jan_reg = array_filter(array_column($arrRegion, 8)) == null ? 0 : max(array_column($arrRegion, 8));
        $max_feb_reg = array_filter(array_column($arrRegion, 11)) == null ? 0 : max(array_column($arrRegion, 11));
        $max_mar_reg = array_filter(array_column($arrRegion, 14)) == null ? 0 : max(array_column($arrRegion, 14));
        $max_apr_reg = array_filter(array_column($arrRegion, 17)) == null ? 0 : max(array_column($arrRegion, 17));
        $max_mei_reg = array_filter(array_column($arrRegion, 20)) == null ? 0 : max(array_column($arrRegion, 20));
        $max_jun_reg = array_filter(array_column($arrRegion, 23)) == null ? 0 : max(array_column($arrRegion, 23));
        $max_jul_reg = array_filter(array_column($arrRegion, 26)) == null ? 0 : max(array_column($arrRegion, 26));
        $max_agu_reg = array_filter(array_column($arrRegion, 29)) == null ? 0 : max(array_column($arrRegion, 29));
        $max_sep_reg = array_filter(array_column($arrRegion, 32)) == null ? 0 : max(array_column($arrRegion, 32));
        $max_okt_reg = array_filter(array_column($arrRegion, 35)) == null ? 0 : max(array_column($arrRegion, 35));
        $max_nov_reg = array_filter(array_column($arrRegion, 38)) == null ? 0 : max(array_column($arrRegion, 38));
        $max_des_reg = array_filter(array_column($arrRegion, 40)) == null ? 0 : max(array_column($arrRegion, 40));

        $min_gab_reg = array_filter(array_column($arrRegion, 4)) == null ? 0 : min(array_filter(array_column($arrRegion, 4)));
        $min_jan_reg = array_filter(array_column($arrRegion, 8)) == null ? 0 : min(array_filter(array_column($arrRegion, 8)));
        $min_feb_reg = array_filter(array_column($arrRegion, 11)) == null ? 0 : min(array_filter(array_column($arrRegion, 11)));
        $min_mar_reg = array_filter(array_column($arrRegion, 14)) == null ? 0 : min(array_filter(array_column($arrRegion, 14)));
        $min_apr_reg = array_filter(array_column($arrRegion, 17)) == null ? 0 : min(array_filter(array_column($arrRegion, 17)));
        $min_mei_reg = array_filter(array_column($arrRegion, 20)) == null ? 0 : min(array_filter(array_column($arrRegion, 20)));
        $min_jun_reg = array_filter(array_column($arrRegion, 23)) == null ? 0 : min(array_filter(array_column($arrRegion, 23)));
        $min_jul_reg = array_filter(array_column($arrRegion, 26)) == null ? 0 : min(array_filter(array_column($arrRegion, 26)));
        $min_agu_reg = array_filter(array_column($arrRegion, 29)) == null ? 0 : min(array_filter(array_column($arrRegion, 29)));
        $min_sep_reg = array_filter(array_column($arrRegion, 32)) == null ? 0 : min(array_filter(array_column($arrRegion, 32)));
        $min_okt_reg = array_filter(array_column($arrRegion, 35)) == null ? 0 : min(array_filter(array_column($arrRegion, 35)));
        $min_nov_reg = array_filter(array_column($arrRegion, 38)) == null ? 0 : min(array_filter(array_column($arrRegion, 38)));
        $min_des_reg = array_filter(array_column($arrRegion, 40)) == null ? 0 : min(array_filter(array_column($arrRegion, 40)));

        $noRegion = 1;
        if($tahun==''){
            $tahun='PILIH';
        }
        return view('dashboard.produksi.diff_fcr',compact('noUnit','sqlUnit','noRegion','sqlRegion','ap','region','units','arrRegion','tahun',
            'max_gab', 'max_jan', 'max_feb', 'max_mar', 'max_apr', 'max_mei', 'max_jun', 'max_jul', 'max_agu', 'max_sep', 'max_okt', 'max_nov', 'max_des',
            'min_gab', 'min_jan', 'min_feb', 'min_mar', 'min_apr', 'min_mei', 'min_jun', 'min_jul', 'min_agu', 'min_sep', 'min_okt', 'min_nov', 'min_des',
            'max_gab_reg', 'max_jan_reg', 'max_feb_reg', 'max_mar_reg', 'max_apr_reg', 'max_mei_reg', 'max_jun_reg', 'max_jul_reg', 'max_agu_reg', 'max_sep_reg', 'max_okt_reg', 'max_nov_reg', 'max_des_reg',
            'min_gab_reg', 'min_jan_reg', 'min_feb_reg', 'min_mar_reg', 'min_apr_reg', 'min_mei_reg', 'min_jun_reg', 'min_jul_reg', 'min_agu_reg', 'min_sep_reg', 'min_okt_reg', 'min_nov_reg', 'min_des_reg'
        ));
    }

    public function diff_fcr_unit_excel($region, $tahun){
        if($region!='SEMUA'){
            $sql = DB::select("SELECT a.kodeunit, a.region,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun') AS gab_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun') AS gab_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=1) AS jan_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=1) AS jan_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=2) AS feb_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=2) AS feb_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=3) AS mar_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=3) AS mar_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=4) AS apr_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=4) AS apr_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=5) AS mei_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=5) AS mei_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=6) AS jun_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=6) AS jun_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=7) AS jul_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=7) AS jul_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=8) AS agu_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=8) AS agu_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=9) AS sep_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=9) AS sep_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=10) AS okt_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=10) AS okt_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=11) AS nov_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=11) AS nov_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=12) AS des_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=12) AS des_fcr
                    FROM units a WHERE a.region = '$region' ORDER BY a.region, a.kodeunit ASC");
        }else{
            $sql = DB::select("SELECT a.kodeunit, a.region,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun') AS gab_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun') AS gab_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=1) AS jan_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=1) AS jan_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=2) AS feb_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=2) AS feb_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=3) AS mar_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=3) AS mar_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=4) AS apr_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=4) AS apr_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=5) AS mei_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=5) AS mei_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=6) AS jun_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=6) AS jun_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=7) AS jul_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=7) AS jul_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=8) AS agu_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=8) AS agu_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=9) AS sep_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=9) AS sep_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=10) AS okt_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=10) AS okt_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=11) AS nov_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=11) AS nov_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=12) AS des_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE unit = a.kodeunit AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=12) AS des_fcr
                    FROM units a ORDER BY a.region, a.kodeunit ASC");
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AQ1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', ' DIFF FCR UNIT '.$tahun);
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AQ4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', 'GAB');
        $spreadsheet->getActiveSheet()->mergeCells('D3:G3');
        $sheet->setCellValue('D4', 'BW');
        $sheet->setCellValue('E4', 'FCR');
        $sheet->setCellValue('F4', 'DIFF');
        $sheet->setCellValue('G4', 'KETERANGAN');

        $sheet->setCellValue('H3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('H3:J3');
        $sheet->setCellValue('H4', 'BW');
        $sheet->setCellValue('I4', 'FCR');
        $sheet->setCellValue('J4', 'DIF');

        $sheet->setCellValue('K3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('K3:M3');
        $sheet->setCellValue('K4', 'BW');
        $sheet->setCellValue('L4', 'FCR');
        $sheet->setCellValue('M4', 'DIF');

        $sheet->setCellValue('N3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('N3:P3');
        $sheet->setCellValue('N4', 'BW');
        $sheet->setCellValue('O4', 'FCR');
        $sheet->setCellValue('P4', 'DIF');

        $sheet->setCellValue('Q3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('Q3:S3');
        $sheet->setCellValue('Q4', 'BW');
        $sheet->setCellValue('R4', 'FCR');
        $sheet->setCellValue('S4', 'DIF');

        $sheet->setCellValue('T3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('T3:V3');
        $sheet->setCellValue('T4', 'BW');
        $sheet->setCellValue('U4', 'FCR');
        $sheet->setCellValue('V4', 'DIF');

        $sheet->setCellValue('W3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('W3:Y3');
        $sheet->setCellValue('W4', 'BW');
        $sheet->setCellValue('X4', 'FCR');
        $sheet->setCellValue('Y4', 'DIF');

        $sheet->setCellValue('Z3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('Z3:AB3');
        $sheet->setCellValue('Z4', 'BW');
        $sheet->setCellValue('AA4', 'FCR');
        $sheet->setCellValue('AB4', 'DIF');

        $sheet->setCellValue('AC3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('AC3:AE3');
        $sheet->setCellValue('AC4', 'BW');
        $sheet->setCellValue('AD4', 'FCR');
        $sheet->setCellValue('AE4', 'DIF');

        $sheet->setCellValue('AF3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('AF3:AH3');
        $sheet->setCellValue('AF4', 'BW');
        $sheet->setCellValue('AG4', 'FCR');
        $sheet->setCellValue('AH4', 'DIF');

        $sheet->setCellValue('AI3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('AI3:AK3');
        $sheet->setCellValue('AI4', 'BW');
        $sheet->setCellValue('AJ4', 'FCR');
        $sheet->setCellValue('AK4', 'DIF');

        $sheet->setCellValue('AL3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('AL3:AN3');
        $sheet->setCellValue('AL4', 'BW');
        $sheet->setCellValue('AM4', 'FCR');
        $sheet->setCellValue('AN4', 'DIF');

        $sheet->setCellValue('AO3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('AO3:AQ3');
        $sheet->setCellValue('AO4', 'BW');
        $sheet->setCellValue('AP4', 'FCR');
        $sheet->setCellValue('AQ4', 'DIF');

        $rows = 5;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->kodeunit);
            $sheet->setCellValue('C' . $rows, $data->region);

            $sheet->setCellValue('D' . $rows, number_indo_excel_koma($data->gab_bw));
            $sheet->setCellValue('E' . $rows, number_indo_excel_koma3($data->gab_fcr));
            $sheet->setCellValue('F' . $rows, number_indo_excel_koma1($gab_diff = fcr_std_bw($data->gab_bw, $data->gab_fcr)));
            $sheet->setCellValue('G' . $rows, fcr_ket($gab_diff));

            $sheet->setCellValue('H' . $rows, number_indo_excel_koma($data->jan_bw));
            $sheet->setCellValue('I' . $rows, number_indo_excel_koma3($data->jan_fcr));
            $sheet->setCellValue('J' . $rows, number_indo_excel_koma1(fcr_std_bw($data->jan_bw, $data->jan_fcr)));

            $sheet->setCellValue('K' . $rows, number_indo_excel_koma($data->feb_bw));
            $sheet->setCellValue('L' . $rows, number_indo_excel_koma3($data->feb_fcr));
            $sheet->setCellValue('M' . $rows, number_indo_excel_koma1(fcr_std_bw($data->feb_bw, $data->feb_fcr)));

            $sheet->setCellValue('N' . $rows, number_indo_excel_koma($data->mar_bw));
            $sheet->setCellValue('O' . $rows, number_indo_excel_koma3($data->mar_fcr));
            $sheet->setCellValue('P' . $rows, number_indo_excel_koma1(fcr_std_bw($data->mar_bw, $data->mar_fcr)));

            $sheet->setCellValue('Q' . $rows, number_indo_excel_koma($data->apr_bw));
            $sheet->setCellValue('R' . $rows, number_indo_excel_koma3($data->apr_fcr));
            $sheet->setCellValue('S' . $rows, number_indo_excel_koma1(fcr_std_bw($data->apr_bw, $data->apr_fcr)));

            $sheet->setCellValue('T' . $rows, number_indo_excel_koma($data->mei_bw));
            $sheet->setCellValue('U' . $rows, number_indo_excel_koma3($data->mei_fcr));
            $sheet->setCellValue('V' . $rows, number_indo_excel_koma1(fcr_std_bw($data->mei_bw, $data->mei_fcr)));

            $sheet->setCellValue('W' . $rows, number_indo_excel_koma($data->jun_bw));
            $sheet->setCellValue('X' . $rows, number_indo_excel_koma3($data->jun_fcr));
            $sheet->setCellValue('Y' . $rows, number_indo_excel_koma1(fcr_std_bw($data->jun_bw, $data->jun_fcr)));

            $sheet->setCellValue('Z' . $rows, number_indo_excel_koma($data->jul_bw));
            $sheet->setCellValue('AA' . $rows, number_indo_excel_koma3($data->jul_fcr));
            $sheet->setCellValue('AB' . $rows, number_indo_excel_koma1(fcr_std_bw($data->jul_bw, $data->jul_fcr)));

            $sheet->setCellValue('AC' . $rows, number_indo_excel_koma($data->agu_bw));
            $sheet->setCellValue('AD' . $rows, number_indo_excel_koma3($data->agu_fcr));
            $sheet->setCellValue('AE' . $rows, number_indo_excel_koma1(fcr_std_bw($data->agu_bw, $data->agu_fcr)));

            $sheet->setCellValue('AF' . $rows, number_indo_excel_koma($data->sep_bw));
            $sheet->setCellValue('AG' . $rows, number_indo_excel_koma3($data->sep_fcr));
            $sheet->setCellValue('AH' . $rows, number_indo_excel_koma1(fcr_std_bw($data->sep_bw, $data->sep_fcr)));

            $sheet->setCellValue('AI' . $rows, number_indo_excel_koma($data->okt_bw));
            $sheet->setCellValue('AJ' . $rows, number_indo_excel_koma3($data->okt_fcr));
            $sheet->setCellValue('AK' . $rows, number_indo_excel_koma1(fcr_std_bw($data->okt_bw, $data->okt_fcr)));

            $sheet->setCellValue('AL' . $rows, number_indo_excel_koma($data->nov_bw));
            $sheet->setCellValue('AM' . $rows, number_indo_excel_koma3($data->nov_fcr));
            $sheet->setCellValue('AN' . $rows, number_indo_excel_koma1(fcr_std_bw($data->nov_bw, $data->nov_fcr)));

            $sheet->setCellValue('AO' . $rows, number_indo_excel_koma($data->des_bw));
            $sheet->setCellValue('AP' . $rows, number_indo_excel_koma3($data->des_fcr));
            $sheet->setCellValue('AQ' . $rows, number_indo_excel_koma1(fcr_std_bw($data->des_bw, $data->des_fcr)));

            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AL' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AO' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AP' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AQ' . $rows)->applyFromArray(setBody());

            foreach (range('C', 'AQ') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10')->setAutoSize(false);
            }
            $sheet->getColumnDimension('A')->setWidth('5')->setAutoSize(false);
            $sheet->getColumnDimension('B')->setWidth('8')->setAutoSize(false);
            $rows++;
        }

        $fileName = "DIFF_FCR_UNIT.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function diff_fcr_region_excel($tahun){
        $sql = DB::select("SELECT koderegion,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun') AS gab_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun') AS gab_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=1) AS jan_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=1) AS jan_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=2) AS feb_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=2) AS feb_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=3) AS mar_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=3) AS mar_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=4) AS apr_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=4) AS apr_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=5) AS mei_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=5) AS mei_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=6) AS jun_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=6) AS jun_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=7) AS jul_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=7) AS jul_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=8) AS agu_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=8) AS agu_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=9) AS sep_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=9) AS sep_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=10) AS okt_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=10) AS okt_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=11) AS nov_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=11) AS nov_fcr,
                    (SELECT ROUND(SUM(cokg)/SUM(coekor),2) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=12) AS des_bw,
                    (SELECT ROUND(SUM(feedkgqty)/SUM(cokg),3) FROM vrhpp WHERE region = a.koderegion AND rhpprugiproduksi <= 0 AND YEAR(tgldocfinal)='$tahun' AND MONTH(tgldocfinal)=12) AS des_fcr
                    FROM regions a ORDER BY a.koderegion ASC");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AP1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', ' DIFF FCR AP '.$tahun);
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AP4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'GAB');
        $spreadsheet->getActiveSheet()->mergeCells('C3:F3');
        $sheet->setCellValue('C4', 'BW');
        $sheet->setCellValue('D4', 'FCR');
        $sheet->setCellValue('E4', 'DIFF');
        $sheet->setCellValue('F4', 'KETERANGAN');

        $sheet->setCellValue('G3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('G3:I3');
        $sheet->setCellValue('G4', 'BW');
        $sheet->setCellValue('H4', 'FCR');
        $sheet->setCellValue('I4', 'DIF');

        $sheet->setCellValue('J3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('J3:L3');
        $sheet->setCellValue('J4', 'BW');
        $sheet->setCellValue('K4', 'FCR');
        $sheet->setCellValue('L4', 'DIF');

        $sheet->setCellValue('M3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('M3:O3');
        $sheet->setCellValue('M4', 'BW');
        $sheet->setCellValue('N4', 'FCR');
        $sheet->setCellValue('O4', 'DIF');

        $sheet->setCellValue('P3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('P3:R3');
        $sheet->setCellValue('P4', 'BW');
        $sheet->setCellValue('Q4', 'FCR');
        $sheet->setCellValue('R4', 'DIF');

        $sheet->setCellValue('S3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('S3:U3');
        $sheet->setCellValue('S4', 'BW');
        $sheet->setCellValue('T4', 'FCR');
        $sheet->setCellValue('U4', 'DIF');

        $sheet->setCellValue('V3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('V3:X3');
        $sheet->setCellValue('V4', 'BW');
        $sheet->setCellValue('W4', 'FCR');
        $sheet->setCellValue('X4', 'DIF');

        $sheet->setCellValue('Y3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('Y3:AA3');
        $sheet->setCellValue('Y4', 'BW');
        $sheet->setCellValue('Z4', 'FCR');
        $sheet->setCellValue('AA4', 'DIF');

        $sheet->setCellValue('AB3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('AB3:AD3');
        $sheet->setCellValue('AB4', 'BW');
        $sheet->setCellValue('AC4', 'FCR');
        $sheet->setCellValue('AD4', 'DIF');

        $sheet->setCellValue('AE3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('AE3:AG3');
        $sheet->setCellValue('AE4', 'BW');
        $sheet->setCellValue('AF4', 'FCR');
        $sheet->setCellValue('AG4', 'DIF');

        $sheet->setCellValue('AH3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('AH3:AJ3');
        $sheet->setCellValue('AH4', 'BW');
        $sheet->setCellValue('AI4', 'FCR');
        $sheet->setCellValue('AJ4', 'DIF');

        $sheet->setCellValue('AK3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('AK3:AM3');
        $sheet->setCellValue('AK4', 'BW');
        $sheet->setCellValue('AL4', 'FCR');
        $sheet->setCellValue('AM4', 'DIF');

        $sheet->setCellValue('AN3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('AN3:AP3');
        $sheet->setCellValue('AN4', 'BW');
        $sheet->setCellValue('AO4', 'FCR');
        $sheet->setCellValue('AP4', 'DIF');

        $rows = 5;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->koderegion);

            $sheet->setCellValue('C' . $rows, number_indo_excel_koma($data->gab_bw));
            $sheet->setCellValue('D' . $rows, number_indo_excel_koma3($data->gab_fcr));
            $sheet->setCellValue('E' . $rows, number_indo_excel_koma1($gab_diff = fcr_std_bw($data->gab_bw, $data->gab_fcr)));
            $sheet->setCellValue('F' . $rows, fcr_ket($gab_diff));

            $sheet->setCellValue('G' . $rows, number_indo_excel_koma($data->jan_bw));
            $sheet->setCellValue('H' . $rows, number_indo_excel_koma3($data->jan_fcr));
            $sheet->setCellValue('I' . $rows, number_indo_excel_koma1(fcr_std_bw($data->jan_bw, $data->jan_fcr)));

            $sheet->setCellValue('J' . $rows, number_indo_excel_koma($data->feb_bw));
            $sheet->setCellValue('K' . $rows, number_indo_excel_koma3($data->feb_fcr));
            $sheet->setCellValue('L' . $rows, number_indo_excel_koma1(fcr_std_bw($data->feb_bw, $data->feb_fcr)));

            $sheet->setCellValue('M' . $rows, number_indo_excel_koma($data->mar_bw));
            $sheet->setCellValue('N' . $rows, number_indo_excel_koma3($data->mar_fcr));
            $sheet->setCellValue('O' . $rows, number_indo_excel_koma1(fcr_std_bw($data->mar_bw, $data->mar_fcr)));

            $sheet->setCellValue('P' . $rows, number_indo_excel_koma($data->apr_bw));
            $sheet->setCellValue('Q' . $rows, number_indo_excel_koma3($data->apr_fcr));
            $sheet->setCellValue('R' . $rows, number_indo_excel_koma1(fcr_std_bw($data->apr_bw, $data->apr_fcr)));

            $sheet->setCellValue('S' . $rows, number_indo_excel_koma($data->mei_bw));
            $sheet->setCellValue('T' . $rows, number_indo_excel_koma3($data->mei_fcr));
            $sheet->setCellValue('U' . $rows, number_indo_excel_koma1(fcr_std_bw($data->mei_bw, $data->mei_fcr)));

            $sheet->setCellValue('V' . $rows, number_indo_excel_koma($data->jun_bw));
            $sheet->setCellValue('W' . $rows, number_indo_excel_koma3($data->jun_fcr));
            $sheet->setCellValue('X' . $rows, number_indo_excel_koma1(fcr_std_bw($data->jun_bw, $data->jun_fcr)));

            $sheet->setCellValue('Y' . $rows, number_indo_excel_koma($data->jul_bw));
            $sheet->setCellValue('Z' . $rows, number_indo_excel_koma3($data->jul_fcr));
            $sheet->setCellValue('AA' . $rows, number_indo_excel_koma1(fcr_std_bw($data->jul_bw, $data->jul_fcr)));

            $sheet->setCellValue('AB' . $rows, number_indo_excel_koma($data->agu_bw));
            $sheet->setCellValue('AC' . $rows, number_indo_excel_koma3($data->agu_fcr));
            $sheet->setCellValue('AD' . $rows, number_indo_excel_koma1(fcr_std_bw($data->agu_bw, $data->agu_fcr)));

            $sheet->setCellValue('AE' . $rows, number_indo_excel_koma($data->sep_bw));
            $sheet->setCellValue('AF' . $rows, number_indo_excel_koma3($data->sep_fcr));
            $sheet->setCellValue('AG' . $rows, number_indo_excel_koma1(fcr_std_bw($data->sep_bw, $data->sep_fcr)));

            $sheet->setCellValue('AH' . $rows, number_indo_excel_koma($data->okt_bw));
            $sheet->setCellValue('AI' . $rows, number_indo_excel_koma3($data->okt_fcr));
            $sheet->setCellValue('AJ' . $rows, number_indo_excel_koma1(fcr_std_bw($data->okt_bw, $data->okt_fcr)));

            $sheet->setCellValue('AK' . $rows, number_indo_excel_koma($data->nov_bw));
            $sheet->setCellValue('AL' . $rows, number_indo_excel_koma3($data->nov_fcr));
            $sheet->setCellValue('AM' . $rows, number_indo_excel_koma1(fcr_std_bw($data->nov_bw, $data->nov_fcr)));

            $sheet->setCellValue('AN' . $rows, number_indo_excel_koma($data->des_bw));
            $sheet->setCellValue('AO' . $rows, number_indo_excel_koma3($data->des_fcr));
            $sheet->setCellValue('AP' . $rows, number_indo_excel_koma1(fcr_std_bw($data->des_bw, $data->des_fcr)));

            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('G' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('H' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('I' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('J' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('K' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('L' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('M' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('N' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('O' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('P' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Q' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('R' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('S' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('T' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('U' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('V' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('W' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('X' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Y' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('Z' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AA' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AB' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AC' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AD' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AE' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AF' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AG' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AH' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AI' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AJ' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AK' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AL' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AM' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AN' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AO' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('AP' . $rows)->applyFromArray(setBody());

            foreach (range('C', 'AO') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10')->setAutoSize(false);
            }
            $sheet->getColumnDimension('A')->setWidth('5')->setAutoSize(false);
            $sheet->getColumnDimension('B')->setWidth('8')->setAutoSize(false);
            $rows++;
        }

        $fileName = "DIFF_FCR_AP.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function feed_cost(Request $request){
        // $tglawal = $request->input('tglawal');
        // $tglakhir = $request->input('tglakhir');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        $bulan2 = $request->input('bulan2');
        $tahun2 = $request->input('tahun2');

        $tglawal = $tahun.'-'.$bulan.'-01';
        $tglakhir = $tahun2.'-'.$bulan2.'-31';

        $region = $request->input('region');
        if($region==''){
            $region='SEMUA';
        }
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT(''), 'SEMUA' AS semua FROM regions
                            ORDER BY koderegion ASC");

        $noUnit = 1;
        $sqlUnit = DB::select("SELECT a.*, (a.fcr-(SELECT CAST(fcr AS DOUBLE) FROM table_std_pfmc WHERE CAST(bw AS DOUBLE)=ROUND(a.bw,2)))*100 AS diff_fcr
                                FROM (
                                    SELECT unit, region,
                                    ROUND((SUM(cokg)/SUM(coekor)),2) AS bw,
                                    ROUND((SUM(feedkgqty)/SUM(cokg)),3) AS fcr,
                                    (SUM(valbbbelifeed)/SUM(feedkgqty)) AS hrg_pakan
                                    FROM vrhpp
                                    WHERE unit IS NOT NULL
                                    AND rhpprugiproduksi <=0 AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                                    GROUP BY unit ASC
                                ) a
                            ");

        // $sqlUnit = DB::select("SELECT a.*, (a.fcr-(SELECT CAST(fcr AS DOUBLE) FROM table_std_pfmc WHERE CAST(bw AS DOUBLE)=ROUND(a.bw,2)))*100 AS diff_fcr
        //                         FROM (
        //                             SELECT unit, region,
        //                             ROUND((SUM(cokg)/SUM(coekor)),2) AS bw,
        //                             ROUND((SUM(feedkgqty)/SUM(cokg)),3) AS fcr,
        //                             (SUM(valbbbelifeed)/SUM(feedkgqty)) AS hrg_pakan
        //                             FROM vrhpp
        //                             WHERE unit IS NOT NULL
        //                             AND rhpprugiproduksi <=0 AND MONTH(tgldocfinal) = '$bulan' AND YEAR(tgldocfinal) = '$tahun'
        //                             GROUP BY unit ASC
        //                         ) a
        //                     ");
        $arrUnit = array();
        if($region!='SEMUA'){
            foreach($sqlUnit as $data){
                if($data->region == $region){
                    array_push($arrUnit, [$kodeunit = $data->unit,
                        $koderegion = $data->region,
                        $bw = $data->bw,
                        $fcr = $data->fcr,
                        $diff_fcr = $data->diff_fcr,
                        $hrg_pakan = $data->hrg_pakan,
                    ]);
                }
            }
        }else{
            foreach($sqlUnit as $data){
                array_push($arrUnit, [$kodeunit = $data->unit,
                    $koderegion = $data->region,
                    $bw = $data->bw,
                    $fcr = $data->fcr,
                    $diff_fcr = $data->diff_fcr,
                    $hrg_pakan = $data->hrg_pakan,
                ]);
            }
        }

        $arrDiff19 = array();
        foreach($sqlUnit as $data){
            if($data->bw <= 1.90){
                array_push($arrDiff19, [
                    $diff_fcr = $data->diff_fcr,
                    $hrg_pakan = $data->hrg_pakan,
                    $feed_cost = rms_feedcost($data->bw, $diff_fcr, $hrg_pakan),
                ]);
            }
        }
        $count19 = count($arrDiff19);
        $sum_diff19 = array_sum(array_column($arrDiff19, 0));
        $sum_hrg19 = array_sum(array_column($arrDiff19, 1));
        $sum_feed19 = array_sum(array_column($arrDiff19, 2));

        $avg_diff_19 = ($sum_diff19 || $count19) ==null ? 0 : round($sum_diff19/$count19,1);
        $avg_hrg_19 = ($sum_diff19 || $count19) ==null ? 0 : round($sum_hrg19/$count19,1);
        $avg_feed_19 = ($sum_diff19 || $count19) ==null ? 0 : round($sum_feed19/$count19,1);

        $arrDiff20 = array();
        foreach($sqlUnit as $data){
            if($data->bw > 1.90){
                array_push($arrDiff20, [
                    $diff_fcr = $data->diff_fcr,
                    $hrg_pakan = $data->hrg_pakan,
                    $feed_cost = rms_feedcost($data->bw, $diff_fcr, $hrg_pakan),
                ]);
            }
        }
        $count20 = count($arrDiff20);
        $sum_diff20 = array_sum(array_column($arrDiff20, 0));
        $sum_hrg20 = array_sum(array_column($arrDiff20, 1));
        $sum_feed20 = array_sum(array_column($arrDiff20, 2));

        $avg_diff_20 = ($sum_diff20 || $count20) ==null ? 0 : round($sum_diff20/$count20,1);
        $avg_hrg_20 = ($sum_hrg20 || $count20) ==null ? 0 : round($sum_hrg20/$count20,1);
        $avg_feed_20 = ($sum_feed20 || $count20) ==null ? 0 : round($sum_feed20/$count20,1);

        $max_diff_unit = array_filter(array_column($arrUnit, 4)) == null ? 0 : max(array_column($arrUnit, 4));
        $max_hrg_unit = array_filter(array_column($arrUnit, 5)) == null ? 0 : max(array_column($arrUnit, 5));

        $min_diff_unit = array_filter(array_column($arrUnit, 4)) == null ? 0 : min(array_filter(array_column($arrUnit, 4)));
        $min_hrg_unit = array_filter(array_column($arrUnit, 5)) == null ? 0 : min(array_filter(array_column($arrUnit, 5)));

        $noAp = 1;
        $sqlAp = DB::select("SELECT a.*, (a.fcr-(SELECT CAST(fcr AS DOUBLE) FROM table_std_pfmc WHERE CAST(bw AS DOUBLE)=ROUND(a.bw,2)))*100 AS diff_fcr
                                FROM (
                                    SELECT region,
                                    ROUND((SUM(cokg)/SUM(coekor)),2) AS bw,
                                    ROUND((SUM(feedkgqty)/SUM(cokg)),3) AS fcr,
                                    (SUM(valbbbelifeed)/SUM(feedkgqty)) AS hrg_pakan
                                    FROM vrhpp
                                    WHERE unit IS NOT NULL
                                    AND rhpprugiproduksi <=0 AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                                    GROUP BY region ASC
                                ) a
                            ");

        // $sqlAp = DB::select("SELECT a.*, (a.fcr-(SELECT CAST(fcr AS DOUBLE) FROM table_std_pfmc WHERE CAST(bw AS DOUBLE)=ROUND(a.bw,2)))*100 AS diff_fcr
        //                         FROM (
        //                             SELECT region,
        //                             ROUND((SUM(cokg)/SUM(coekor)),2) AS bw,
        //                             ROUND((SUM(feedkgqty)/SUM(cokg)),3) AS fcr,
        //                             (SUM(valbbbelifeed)/SUM(feedkgqty)) AS hrg_pakan
        //                             FROM vrhpp
        //                             WHERE unit IS NOT NULL
        //                             AND rhpprugiproduksi <=0 AND MONTH(tgldocfinal) = '$bulan' AND YEAR(tgldocfinal) = '$tahun'
        //                             GROUP BY region ASC
        //                         ) a
        //                     ");
        $arrAp = array();
        foreach($sqlAp as $data){
            array_push($arrAp, [$kodeunit = $data->region,
                $koderegion = $data->region,
                $bw = $data->bw,
                $fcr = $data->fcr,
                $diff_fcr = $data->diff_fcr,
                $hrg_pakan = $data->hrg_pakan,
            ]);
        }

        $arrDiff19Ap = array();
        foreach($sqlAp as $data){
            if($data->bw <= 1.90){
                array_push($arrDiff19Ap, [
                    $diff_fcr = $data->diff_fcr,
                    $hrg_pakan = $data->hrg_pakan,
                    $feed_cost = rms_feedcost($data->bw, $diff_fcr, $hrg_pakan),
                ]);
            }
        }
        $count19Ap = count($arrDiff19Ap);
        $sum_diff19Ap = array_sum(array_column($arrDiff19Ap, 0));
        $sum_hrg19Ap = array_sum(array_column($arrDiff19Ap, 1));
        $sum_feed19Ap = array_sum(array_column($arrDiff19Ap, 2));

        $avg_diff_19Ap = ($sum_diff19Ap || $count19Ap) ==null ? 0 : round($sum_diff19Ap/$count19Ap,1);
        $avg_hrg_19Ap = ($sum_diff19Ap || $count19Ap) ==null ? 0 : round($sum_hrg19Ap/$count19Ap,1);
        $avg_feed_19Ap = ($sum_diff19Ap || $count19Ap) ==null ? 0 : round($sum_feed19Ap/$count19Ap,1);

        $arrDiff20Ap = array();
        foreach($sqlAp as $data){
            if($data->bw > 1.90){
                array_push($arrDiff20Ap, [
                    $diff_fcr = $data->diff_fcr,
                    $hrg_pakan = $data->hrg_pakan,
                    $feed_cost = rms_feedcost($data->bw, $diff_fcr, $hrg_pakan),
                ]);
            }
        }
        $count20Ap = count($arrDiff20Ap);
        $sum_diff20Ap = array_sum(array_column($arrDiff20Ap, 0));
        $sum_hrg20Ap = array_sum(array_column($arrDiff20Ap, 1));
        $sum_feed20Ap = array_sum(array_column($arrDiff20Ap, 2));

        $avg_diff_20Ap = ($sum_diff20Ap || $count20Ap) ==null ? 0 : round($sum_diff20Ap/$count20Ap,1);
        $avg_hrg_20Ap = ($sum_hrg20Ap || $count20Ap) ==null ? 0 : round($sum_hrg20Ap/$count20Ap,1);
        $avg_feed_20Ap = ($sum_feed20Ap || $count20Ap) ==null ? 0 : round($sum_feed20Ap/$count20Ap,1);

        $max_diff_ap = array_filter(array_column($arrAp, 4)) == null ? 0 : max(array_column($arrAp, 4));
        $max_hrg_ap = array_filter(array_column($arrAp, 5)) == null ? 0 : max(array_column($arrAp, 5));

        $min_diff_ap = array_filter(array_column($arrAp, 4)) == null ? 0 : min(array_filter(array_column($arrAp, 4)));
        $min_hrg_ap = array_filter(array_column($arrAp, 5)) == null ? 0 : min(array_filter(array_column($arrAp, 5)));

        if($bulan==''){
            $bulan='PILIH';
        }
        if($tahun==''){
            $tahun='PILIH';
        }
        return view('dashboard.produksi.feedcost',compact('noUnit','arrUnit','noAp','arrAp','bulan','tahun','bulan2','tahun2','ap','region',
            'max_diff_unit','max_hrg_unit','min_diff_unit','min_hrg_unit',
            'max_diff_ap','max_hrg_ap','min_diff_ap','min_hrg_ap',
            'avg_diff_19','avg_hrg_19','avg_feed_19',
            'avg_diff_20','avg_hrg_20','avg_feed_20',
            'avg_diff_19Ap','avg_hrg_19Ap','avg_feed_19Ap',
            'avg_diff_20Ap','avg_hrg_20Ap','avg_feed_20Ap'
        ));
    }

    public function pantauan_flokpanen(Request $request){
        $nav = $request->input('nav');
        $region = $request->input('region');

        $filter = $request->input('filter');
        $cbosetting = $request->input('cbosetting');
        $cbounit = $request->input('cbounit');
        $o_filter = $request->input('o_filter');
        $i_filter = koma2titik($request->input('i_filter'));

        if(empty($filter)){
            $strWhere = "";
        }else{
            if($filter=='setting'){
                if($cbosetting=='TERSETTING'){
                    $strWhere = " WHERE setting <> ''";
                }elseif($cbosetting==''){
                    $strWhere = "";
                }else{
                    $strWhere = " WHERE setting='$cbosetting'";
                }
            }elseif($filter=='unit'){
                $strWhere = " WHERE unit='$cbounit'";
            }else{
                $strWhere = " WHERE $filter $o_filter '$i_filter'";
            }
        }

        $roles = Auth::user()->roles;
        // if(($roles=='sr') || ($roles=='admin')){
        //     $tab_1 = 'active';
        //     $tab_2 = '';
        //     $tab_3 = '';
        // }else{
        //     $tab_1 = '';
        //     $tab_2 = 'active';
        //     $tab_3 = '';
        // }

        $tab_1 = 'active';
        $tab_2 = '';
        $tab_3 = '';

        if($region==''){
            $region='SEMUA';
        }

        $no_scproduksi = 1;
        $no_scestimasi = 1;

        $jabatan = Auth::user()->jabatan;
        $koderegion = Auth::user()->region;
        $kosong = '';
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF QA");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if (in_array($jabatan, $akses)) {
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
        } else {
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $koderegion . '" ORDER BY koderegion ASC');
            $region=$koderegion;
        }
        $regions = Regions::all();
        $noregions=0;

        if($region!='SEMUA'){
            $sqlFlokBerjalan = DB::select("SELECT * FROM (SELECT c.keterangan, b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            b.populasi_chick_in,
                                            b.ts,
                                            (b.performance_umur+b.data_telat) AS umur,
                                            b.data_telat AS data_telat,
                                            b.performance_bw AS bw,
                                            b.performance_diff_fcr AS diff_fcr,
                                            b.performance_dpls AS dpls,
                                            b.ekonomis_harga_doc AS harga_doc,
                                            b.ekonomis_harga_pakan AS harga_pakan,
                                            b.ekonomis_rhpp AS harga_rhpp,
                                            b.ekonomis_hpp AS total,
                                            (b.penj_tonase/b.penj_ekor) AS mrg_bw,
                                            b.penj_ekor AS mrg_ekor,
                                            ((b.penj_tonase/b.penj_ekor)*((b.penj_value/b.penj_tonase)-b.ekonomis_hpp)) AS margin,
                                            b.panen_persen, b.sisa_panen, c.setting, c.target_hari, c.estimasi_rhpp,
                                            (penj_value/penj_tonase) AS penj_hargalb, ((penj_value/penj_tonase)-b.ekonomis_hpp ) AS mrg_kg,
                                            ((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp )) AS  mrg_ek, (penj_ekor*((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp ))) AS nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00' AND ekonomis_rhpp <> 0
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in WHERE b.ap = '$region'
                                        ORDER BY c.id DESC)x $strWhere");

            $sqlUnit = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in WHERE b.ap = '$region'
                                        GROUP BY b.unit ORDER BY b.ap, b.unit ASC");

            $sqlAp = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in WHERE b.ap = '$region'
                                GROUP BY b.ap ASC");
        }else{
            $sqlFlokBerjalan = DB::select("SELECT * FROM (SELECT c.keterangan, b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            b.populasi_chick_in,
                                            b.ts,
                                            (b.performance_umur+b.data_telat) AS umur,
                                            b.data_telat AS data_telat,
                                            b.performance_bw AS bw,
                                            b.performance_diff_fcr AS diff_fcr,
                                            b.performance_dpls AS dpls,
                                            b.ekonomis_harga_doc AS harga_doc,
                                            b.ekonomis_harga_pakan AS harga_pakan,
                                            b.ekonomis_rhpp AS harga_rhpp,
                                            b.ekonomis_hpp AS total,
                                            (b.penj_tonase/b.penj_ekor) AS mrg_bw,
                                            b.penj_ekor AS mrg_ekor,
                                            ((b.penj_tonase/b.penj_ekor)*((b.penj_value/b.penj_tonase)-b.ekonomis_hpp)) AS margin,
                                            b.panen_persen, b.sisa_panen, c.setting, c.target_hari, c.estimasi_rhpp,
                                            (penj_value/penj_tonase) AS penj_hargalb, ((penj_value/penj_tonase)-b.ekonomis_hpp ) AS mrg_kg,
                                            ((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp )) AS  mrg_ek, (penj_ekor*((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp ))) AS nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00' AND ekonomis_rhpp <> 0
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in
                                        ORDER BY c.id DESC)x $strWhere");

            $sqlUnit = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in
                                        GROUP BY b.unit ORDER BY b.ap, b.unit ASC");

            $sqlAp = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in
                                GROUP BY b.ap ASC");
        }
        if($o_filter==''){
            $o_filter='=';
        }
        return view('dashboard.produksi.pantauan_flokpanen', compact('tab_1','tab_2','tab_3','no_scproduksi','no_scestimasi','region','ap','regions','noregions','sqlFlokBerjalan','sqlUnit','sqlAp','filter','o_filter','i_filter','cbosetting','cbounit'));
    }

    public function flokpanen_setting(Request $request){
        $cbosetting = $request->input('cbosetting');
        $filter = $request->input('filter');
        $o_filter = $request->input('o_filter');
        $i_filter = koma2titik($request->input('i_filter'));

        $unit = Auth::user()->unit;
        if(empty($filter)){
            $strWhere = " WHERE unit ='$unit'";
        }else{
            if($filter=='setting'){
                $strWhere = " WHERE unit ='$unit' AND setting='$cbosetting'";
            }else{
                $strWhere = " WHERE unit ='$unit' AND $filter $o_filter '$i_filter'";
            }
        }

        $sqlFlokBerjalan = DB::select("SELECT * FROM (SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            b.populasi_chick_in,
                                            (b.performance_umur+b.data_telat) AS umur,
                                            b.data_telat AS data_telat,
                                            b.performance_bw AS bw,
                                            b.performance_diff_fcr AS diff_fcr,
                                            b.performance_dpls AS dpls,
                                            b.ekonomis_harga_doc AS harga_doc,
                                            b.ekonomis_harga_pakan AS harga_pakan,
                                            b.ekonomis_rhpp AS harga_rhpp,
                                            b.ekonomis_hpp AS total,
                                            (b.penj_tonase/b.penj_ekor) AS mrg_bw,
                                            b.penj_ekor AS mrg_ekor,
                                            ((b.penj_tonase/b.penj_ekor)*((b.penj_value/b.penj_tonase)-b.ekonomis_hpp)) AS margin,
                                            b.panen_persen, b.sisa_panen, c.setting, c.target_hari, c.estimasi_rhpp,
                                            (penj_value/penj_tonase) AS penj_hargalb, ((penj_value/penj_tonase)-b.ekonomis_hpp ) AS mrg_kg,
                                            ((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp )) AS  mrg_ek, (penj_ekor*((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp ))) AS nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00' AND ekonomis_rhpp <> 0
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in
                                        ORDER BY c.id DESC)x $strWhere");

        if($o_filter==''){
            $o_filter='=';
        }
        return view('dashboard.produksi.flokpanen_setting', compact('sqlFlokBerjalan','filter','o_filter','i_filter','cbosetting'));
    }

    public function pantauan_flokpanen_filter(Request $request){
        $nav = $request->input('nav');
        $region = $request->input('region');
        $umur = $request->input('umur');
        $bw = $request->input('bw');
        $diff_fcr = $request->input('diff_fcr');
        $rhpp = $request->input('rhpp');
        $mrg_kg = $request->input('mrg_kg');
        $o_umur = $request->input('o_umur');
        $o_bw = $request->input('o_bw');
        $o_diff_fcr = $request->input('o_diff_fcr');
        $o_rhpp = $request->input('o_rhpp');
        $o_mrg_kg = $request->input('o_mrg_kg');

        if($nav==1){
            $tab_1 = 'active';
            $tab_2 = '';
            $tab_3 = '';
            $tab_4 = '';
            $tab_5 = '';
        }elseif($nav==2){
            $tab_1 = '';
            $tab_2 = 'active';
            $tab_3 = '';
            $tab_4 = '';
            $tab_5 = '';
        }elseif($nav==3){
            $tab_1 = '';
            $tab_2 = '';
            $tab_3 = 'active';
            $tab_4 = '';
            $tab_5 = '';
        }elseif($nav==4){
            $tab_1 = '';
            $tab_2 = '';
            $tab_3 = '';
            $tab_4 = 'active';
            $tab_5 = '';
        }elseif($nav==5){
            $tab_1 = '';
            $tab_2 = '';
            $tab_3 = '';
            $tab_4 = '';
            $tab_5 = 'active';
        }else{
            $tab_1 = 'active';
            $tab_2 = '';
            $tab_3 = '';
            $tab_4 = '';
            $tab_5 = '';
        }

        if($region==''){
            $region='SEMUA';
        }

        $no_scproduksi = 1;
        $no_scestimasi = 1;

        $jabatan = Auth::user()->jabatan;
        $koderegion = Auth::user()->region;
        $kosong = '';
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF QA");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if (in_array($jabatan, $akses)) {
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
        } else {
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $koderegion . '" ORDER BY koderegion ASC');
            $region=$koderegion;
        }
        $regions = Regions::all();
        $noregions=0;

        if($region!='SEMUA'){
            $sqlSource = DB::select("SELECT *, (penj_tonase/penj_ekor) AS penj_bwpanen, (penj_value/penj_tonase) AS penj_hargalb, (ekonomis_harga_doc*populasi_chick_in) AS doc, (ekonomis_harga_pakan*populasi_chick_in) AS pakan, (ekonomis_hpp*populasi_chick_in) AS hpp FROM (
                                    SELECT a.*,
                                        (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                        (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                        (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                    FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                )b WHERE ap = '$region'");

            $sqlFlokBerjalan = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            b.populasi_chick_in,
                                            (b.performance_umur+b.data_telat) AS umur,
                                            b.performance_bw AS bw,
                                            b.performance_diff_fcr AS diff_fcr,
                                            b.performance_dpls AS dpls,
                                            b.ekonomis_harga_doc AS harga_doc,
                                            b.ekonomis_harga_pakan AS harga_pakan,
                                            b.ekonomis_rhpp AS harga_rhpp,
                                            b.ekonomis_hpp AS total,
                                            (b.penj_tonase/b.penj_ekor) AS mrg_bw,
                                            b.penj_ekor AS mrg_ekor,
                                            ((b.penj_tonase/b.penj_ekor)*((b.penj_value/b.penj_tonase)-b.ekonomis_hpp)) AS margin,
                                            b.panen_persen, b.sisa_panen, c.setting, c.target_hari, c.estimasi_rhpp,
                                            (penj_value/penj_tonase) AS penj_hargalb, ((penj_value/penj_tonase)-b.ekonomis_hpp ) AS mrg_kg,
                                            ((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp )) AS  mrg_ek,
                                            (penj_ekor*((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp ))) AS nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in WHERE b.ap = '$region'
                                        AND ((b.performance_umur+b.data_telat) $o_umur '$umur' AND b.performance_bw $o_bw '$bw' AND b.performance_diff_fcr $o_diff_fcr '$diff_fcr' AND b.ekonomis_rhpp $o_rhpp '$rhpp' AND ((penj_value/penj_tonase)-b.ekonomis_hpp ) $o_mrg_kg '$mrg_kg')
                                        ORDER BY c.id DESC");

            $sqlUnit = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in WHERE b.ap = '$region'
                                        GROUP BY b.unit ORDER BY b.ap, b.unit ASC");

            $sqlAp = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in WHERE b.ap = '$region'
                                GROUP BY b.ap ASC");
        }else{
            $sqlSource = DB::select("SELECT *, (penj_tonase/penj_ekor) AS penj_bwpanen, (penj_value/penj_tonase) AS penj_hargalb, (ekonomis_harga_doc*populasi_chick_in) AS doc, (ekonomis_harga_pakan*populasi_chick_in) AS pakan, (ekonomis_hpp*populasi_chick_in) AS hpp FROM (
                                    SELECT a.*,
                                        (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                        (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                        (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                    FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                )b");

            $sqlFlokBerjalan = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            b.populasi_chick_in,
                                            (b.performance_umur+b.data_telat) AS umur,
                                            b.performance_bw AS bw,
                                            b.performance_diff_fcr AS diff_fcr,
                                            b.performance_dpls AS dpls,
                                            b.ekonomis_harga_doc AS harga_doc,
                                            b.ekonomis_harga_pakan AS harga_pakan,
                                            b.ekonomis_rhpp AS harga_rhpp,
                                            b.ekonomis_hpp AS total,
                                            (b.penj_tonase/b.penj_ekor) AS mrg_bw,
                                            b.penj_ekor AS mrg_ekor,
                                            ((b.penj_tonase/b.penj_ekor)*((b.penj_value/b.penj_tonase)-b.ekonomis_hpp)) AS margin,
                                            b.panen_persen, b.sisa_panen, c.setting, c.target_hari, c.estimasi_rhpp,
                                            (penj_value/penj_tonase) AS penj_hargalb, ((penj_value/penj_tonase)-b.ekonomis_hpp ) AS mrg_kg,
                                            ((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp )) AS  mrg_ek, (penj_ekor*((penj_tonase/penj_ekor)*((penj_value/penj_tonase)-b.ekonomis_hpp ))) AS nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in
                                        WHERE (b.performance_umur+b.data_telat) $o_umur '$umur' AND b.performance_bw $o_bw '$bw' AND b.performance_diff_fcr $o_diff_fcr '$diff_fcr' AND b.ekonomis_rhpp $o_rhpp '$rhpp' AND ((penj_value/penj_tonase)-b.ekonomis_hpp ) $o_mrg_kg '$mrg_kg'
                                        ORDER BY c.id DESC");

            $sqlUnit = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                        FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                        LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in
                                        GROUP BY b.unit ORDER BY b.ap, b.unit ASC");

            $sqlAp = DB::select("SELECT b.nama_flok, b.unit, b.ap,
                                            b.tanggal_chick_in,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS penj_ekor,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END) AS bw_panen,
                                            SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_doc*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_doc,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_harga_pakan*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS harga_pakan,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END) AS hpp,
											SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END) AS hrg_lb_act,
											(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END)) AS mrg_kg,
											((SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))*SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_ekor ELSE 0 END)) AS mrg_ek,
                                            (SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)*(SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_value ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.penj_tonase ELSE 0 END)-SUM(CASE WHEN b.penj_ekor > 0 THEN b.ekonomis_hpp*b.populasi_chick_in ELSE 0 END)/SUM(CASE WHEN b.penj_ekor > 0 THEN b.populasi_chick_in ELSE 0 END))) AS mrg_nominal
                                FROM (
                                            SELECT a.*,
                                                    (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                                    (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                                    (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                            FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                        )b
                                LEFT JOIN table_prd_data_harian_setting c ON c.nama_flok = b.nama_flok AND c.unit=b.unit AND c.tanggal_chick_in = b.tanggal_chick_in
                                GROUP BY b.ap ASC");
        }

        return view('dashboard.produksi.pantauan_flokpanen', compact('tab_1','tab_2','tab_3','tab_4','tab_5','no_scproduksi','no_scestimasi','region','ap','regions','noregions','sqlSource','sqlFlokBerjalan','sqlUnit','sqlAp'));
    }

    public function data_harian_setting($nama_flok,$unit,$tanggal_chick_in){
        $nama_flok = mysql_escape($nama_flok);
        $sqlFlok = DB::select("SELECT *, (penj_tonase/penj_ekor) AS penj_bwpanen, (penj_value/penj_tonase) AS penj_hargalb, (ekonomis_harga_doc*populasi_chick_in) AS doc, (ekonomis_harga_pakan*populasi_chick_in) AS pakan, (ekonomis_hpp*populasi_chick_in) AS hpp FROM (
                                    SELECT a.*,
                                        (SELECT SUM(rms_ekor) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_ekor,
                                        (SELECT SUM(rms_kg) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_tonase,
                                        (SELECT SUM(rms_value) FROM vrealisasi_produksi WHERE nama_flok=a.nama_flok AND unit=a.unit AND tanggal_chick_in=a.tanggal_chick_in) AS penj_value
                                    FROM table_prd_data_harian a WHERE a.tanggal_chick_in <> '0000-00-00'
                                )b WHERE nama_flok='$nama_flok' AND unit='$unit' AND tanggal_chick_in='$tanggal_chick_in'");
        //return $sqlFlok;
        $sqlSetting = DB::select("SELECT * FROM table_prd_data_harian_setting WHERE nama_flok='$nama_flok' AND unit='$unit' AND tanggal_chick_in='$tanggal_chick_in'");
        return view('dashboard.produksi.pantauan_flok_setting',compact('sqlFlok','sqlSetting'));
    }

    public function pantauan_flokpanen_produksi_import(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx',
        ]);
        $region = $request->input('region');
        $file = $request->file('file');
        $nama_file = 'SOURCE_PRD'.$file->hashName();
        $path = $file->storeAs('public/excel/',$nama_file);

        $hapus = DB::table('table_prd_hpp_flok_panen_produksi')
                            ->where('ap', $region)
                            ->delete();

        if($hapus){
            $import = new PrdPantuanFlokPanenProduksi($region);
            $import = Excel::import($import, storage_path('app/public/excel/'.$nama_file));
            Storage::delete($path);
        }else{
            $import = new PrdPantuanFlokPanenProduksi($region);
            $import = Excel::import($import, storage_path('app/public/excel/'.$nama_file));
            Storage::delete($path);
        }

        if($import) {
            Alert::toast('Data Berhasil Diimport!', 'success');
            return redirect()->route('produksi.pantauan_flokpanen')->with(['success' => 'Data Berhasil Diimport!']);
        } else {
            Alert::toast('Data Gagal Diimport!', 'danger');
            return redirect()->route('produksi.pantauan_flokpanen')->with(['error' => 'Data Gagal Diimport!']);
        }
    }

    public function margin_segmen(Request $request){
        // $tglawal = $request->input('tglawal');
        // $tglakhir = $request->input('tglakhir');
        $bulan = $request->input('bulan');
        $bulan2 = $request->input('bulan2');
        $tahun = $request->input('tahun');
        $tahun2 = $request->input('tahun2');
        $tglawal = $tahun.'-'.$bulan.'-01';
        $tglakhir = $tahun2.'-'.$bulan2.'-31';
        $region = $request->input('region');
        $nav = $request->input('nav');

        if($region==''){
            $region='PILIH';
        }

        if($nav=='nav_unit'){
            $nav_unit = 'active';
            $nav_ap='';
            $nav_resume_unit='';
            $nav_resume_ap='';
        }elseif($nav=='nav_ap'){
            $nav_unit = '';
            $nav_ap='active';
            $nav_resume_unit='';
            $nav_resume_ap='';
        }elseif($nav=='nav_resume_unit'){
            $nav_unit = '';
            $nav_ap='';
            $nav_resume_unit='active';
            $nav_resume_ap='';
        }elseif($nav=='nav_resume_ap'){
            $nav_unit = '';
            $nav_ap='';
            $nav_resume_unit='';
            $nav_resume_ap='active';
        }else{
            $nav_unit = 'active';
            $nav_ap='';
            $nav_resume_unit='';
            $nav_resume_ap='';
        }

        $no_unit=1;
        $no_ap=1;
        $no_resume_unit=1;
        $no_resume_ap=1;

        if($region != 'PILIH'){
            $sql_unit = DB::select("SELECT kodeunit,'$tglawal' AS tglawal, '$tglakhir' AS tglakhir FROM units WHERE region='$region'");

            $sql_resume_unit = DB::select("SELECT unit,
                                        IFNULL((ak_pop/sum_pop*100),0) AS ak_qty,
                                        IFNULL(ak_bw,0) AS ak_bw, IFNULL(ak_margin_kg,0) AS ak_margin_kg, IFNULL((ak_bw * ak_margin_kg),0) AS ak_margin_ek,

                                        IFNULL((at_pop/sum_pop*100),0) AS at_qty,
                                        IFNULL(at_bw,0) AS at_bw, IFNULL(at_margin_kg,0) AS at_margin_kg, IFNULL((at_bw * at_margin_kg),0) AS at_margin_ek,

                                        IFNULL((ab_pop/sum_pop*100),0) AS ab_qty,
                                        IFNULL(ab_bw,0) AS ab_bw, IFNULL(ab_margin_kg,0) AS ab_margin_kg, IFNULL((ab_bw * ab_margin_kg),0) AS ab_margin_ek,

                                        IFNULL((aj_pop/sum_pop*100),0) AS aj_qty,
                                        IFNULL(aj_bw,0) AS aj_bw, IFNULL(aj_margin_kg,0) AS aj_margin_kg, IFNULL((aj_bw * aj_margin_kg),0) AS aj_margin_ek,

                                        IFNULL((as_pop/sum_pop*100),0) AS as_qty,
                                        IFNULL(as_bw,0) AS as_bw, IFNULL(as_margin_kg,0) AS as_margin_kg, IFNULL((as_bw * as_margin_kg),0) AS as_margin_ek

                                    FROM (
                                        SELECT unit, region,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 4.00 THEN ciawal ELSE 0 END) AS sum_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN ciawal ELSE 0 END) AS ak_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN coekor ELSE 0 END) AS ak_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN ciawal ELSE 0 END) AS at_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN coekor ELSE 0 END) AS at_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN ciawal ELSE 0 END) AS ab_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN coekor ELSE 0 END) AS ab_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN ciawal ELSE 0 END) AS aj_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN coekor ELSE 0 END) AS aj_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN ciawal ELSE 0 END) AS as_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN coekor ELSE 0 END) AS as_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_margin_kg
                                        FROM vrhpp
                                        WHERE unit IS NOT NULL AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir' AND region='$region'
                                        GROUP BY unit ASC
                                    )a");
        }else{
            $sql_unit = array();
            $sql_resume_unit = DB::select("SELECT unit,
                                        IFNULL((ak_pop/sum_pop*100),0) AS ak_qty,
                                        IFNULL(ak_bw,0) AS ak_bw, IFNULL(ak_margin_kg,0) AS ak_margin_kg, IFNULL((ak_bw * ak_margin_kg),0) AS ak_margin_ek,

                                        IFNULL((at_pop/sum_pop*100),0) AS at_qty,
                                        IFNULL(at_bw,0) AS at_bw, IFNULL(at_margin_kg,0) AS at_margin_kg, IFNULL((at_bw * at_margin_kg),0) AS at_margin_ek,

                                        IFNULL((ab_pop/sum_pop*100),0) AS ab_qty,
                                        IFNULL(ab_bw,0) AS ab_bw, IFNULL(ab_margin_kg,0) AS ab_margin_kg, IFNULL((ab_bw * ab_margin_kg),0) AS ab_margin_ek,

                                        IFNULL((aj_pop/sum_pop*100),0) AS aj_qty,
                                        IFNULL(aj_bw,0) AS aj_bw, IFNULL(aj_margin_kg,0) AS aj_margin_kg, IFNULL((aj_bw * aj_margin_kg),0) AS aj_margin_ek,

                                        IFNULL((as_pop/sum_pop*100),0) AS as_qty,
                                        IFNULL(as_bw,0) AS as_bw, IFNULL(as_margin_kg,0) AS as_margin_kg, IFNULL((as_bw * as_margin_kg),0) AS as_margin_ek

                                    FROM (
                                        SELECT unit,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 4.00 THEN ciawal ELSE 0 END) AS sum_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN ciawal ELSE 0 END) AS ak_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN coekor ELSE 0 END) AS ak_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN ciawal ELSE 0 END) AS at_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN coekor ELSE 0 END) AS at_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN ciawal ELSE 0 END) AS ab_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN coekor ELSE 0 END) AS ab_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN ciawal ELSE 0 END) AS aj_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN coekor ELSE 0 END) AS aj_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN ciawal ELSE 0 END) AS as_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN coekor ELSE 0 END) AS as_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_margin_kg
                                        FROM vrhpp
                                        WHERE unit IS NOT NULL AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                                        GROUP BY unit ASC
                                    )a");
        }

        $sql_ap = DB::select("SELECT koderegion, '$tglawal' AS tglawal, '$tglakhir' AS tglakhir FROM regions");

        $sql_resume_ap = DB::select("SELECT region,
                                        IFNULL((ak_pop/sum_pop*100),0) AS ak_qty,
                                        IFNULL(ak_bw,0) AS ak_bw, IFNULL(ak_margin_kg,0) AS ak_margin_kg, IFNULL((ak_bw * ak_margin_kg),0) AS ak_margin_ek,

                                        IFNULL((at_pop/sum_pop*100),0) AS at_qty,
                                        IFNULL(at_bw,0) AS at_bw, IFNULL(at_margin_kg,0) AS at_margin_kg, IFNULL((at_bw * at_margin_kg),0) AS at_margin_ek,

                                        IFNULL((ab_pop/sum_pop*100),0) AS ab_qty,
                                        IFNULL(ab_bw,0) AS ab_bw, IFNULL(ab_margin_kg,0) AS ab_margin_kg, IFNULL((ab_bw * ab_margin_kg),0) AS ab_margin_ek,

                                        IFNULL((aj_pop/sum_pop*100),0) AS aj_qty,
                                        IFNULL(aj_bw,0) AS aj_bw, IFNULL(aj_margin_kg,0) AS aj_margin_kg, IFNULL((aj_bw * aj_margin_kg),0) AS aj_margin_ek,

                                        IFNULL((as_pop/sum_pop*100),0) AS as_qty,
                                        IFNULL(as_bw,0) AS as_bw, IFNULL(as_margin_kg,0) AS as_margin_kg, IFNULL((as_bw * as_margin_kg),0) AS as_margin_ek

                                    FROM (
                                        SELECT region,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 4.00 THEN ciawal ELSE 0 END) AS sum_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN ciawal ELSE 0 END) AS ak_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN coekor ELSE 0 END) AS ak_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN ciawal ELSE 0 END) AS at_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN coekor ELSE 0 END) AS at_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN ciawal ELSE 0 END) AS ab_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN coekor ELSE 0 END) AS ab_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN ciawal ELSE 0 END) AS aj_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN coekor ELSE 0 END) AS aj_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN ciawal ELSE 0 END) AS as_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN coekor ELSE 0 END) AS as_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_margin_kg
                                        FROM vrhpp
                                        WHERE unit IS NOT NULL AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                                        GROUP BY region ASC
                                    )a");

        $sql_foot = DB::select("SELECT
                                        IFNULL((ak_pop/sum_pop*100),0) AS ak_qty,
                                        IFNULL(ak_bw,0) AS ak_bw, IFNULL(ak_margin_kg,0) AS ak_margin_kg, IFNULL((ak_bw * ak_margin_kg),0) AS ak_margin_ek,

                                        IFNULL((at_pop/sum_pop*100),0) AS at_qty,
                                        IFNULL(at_bw,0) AS at_bw, IFNULL(at_margin_kg,0) AS at_margin_kg, IFNULL((at_bw * at_margin_kg),0) AS at_margin_ek,

                                        IFNULL((ab_pop/sum_pop*100),0) AS ab_qty,
                                        IFNULL(ab_bw,0) AS ab_bw, IFNULL(ab_margin_kg,0) AS ab_margin_kg, IFNULL((ab_bw * ab_margin_kg),0) AS ab_margin_ek,

                                        IFNULL((aj_pop/sum_pop*100),0) AS aj_qty,
                                        IFNULL(aj_bw,0) AS aj_bw, IFNULL(aj_margin_kg,0) AS aj_margin_kg, IFNULL((aj_bw * aj_margin_kg),0) AS aj_margin_ek,

                                        IFNULL((as_pop/sum_pop*100),0) AS as_qty,
                                        IFNULL(as_bw,0) AS as_bw, IFNULL(as_margin_kg,0) AS as_margin_kg, IFNULL((as_bw * as_margin_kg),0) AS as_margin_ek

                                    FROM (
                                        SELECT
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 4.00 THEN ciawal ELSE 0 END) AS sum_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN ciawal ELSE 0 END) AS ak_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN coekor ELSE 0 END) AS ak_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.00 AND 1.50 THEN cokg ELSE 0 END) AS ak_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN ciawal ELSE 0 END) AS at_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN coekor ELSE 0 END) AS at_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.51 AND 1.90 THEN cokg ELSE 0 END) AS at_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN ciawal ELSE 0 END) AS ab_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN coekor ELSE 0 END) AS ab_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 1.91 AND 2.30 THEN cokg ELSE 0 END) AS ab_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN ciawal ELSE 0 END) AS aj_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN coekor ELSE 0 END) AS aj_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.31 AND 2.80 THEN cokg ELSE 0 END) AS aj_margin_kg,

                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN ciawal ELSE 0 END) AS as_pop,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN coekor ELSE 0 END) AS as_bw,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN jualayamactual ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_harga_lb,
                                        SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN labaruginominal ELSE 0 END)/SUM(CASE WHEN pfmcbw BETWEEN 2.81 AND 4.00 THEN cokg ELSE 0 END) AS as_margin_kg
                                        FROM vrhpp
                                        WHERE unit IS NOT NULL AND tgldocfinal BETWEEN '$tglawal' AND '$tglakhir'
                                    )a");
        if(($bulan=='') || ($bulan2=='') || ($tahun=='') || ($tahun2=='')){
            $bulan='PILIH';
            $bulan2='PILIH';
            $tahun='PILIH';
            $tahun2='PILIH';
        }
        return view('dashboard.produksi.margin_segmen', compact('bulan','bulan2','tahun','tahun2','no_unit','sql_unit','no_ap','sql_ap','no_resume_unit','sql_resume_unit','no_resume_ap','sql_resume_ap','region','tglawal', 'tglakhir',
                                                                'nav_unit','nav_ap','nav_resume_unit','nav_resume_ap','sql_foot'));
    }

    public function data_harian_clear_temp(){
        $clear = DB::statement("TRUNCATE TABLE table_prd_data_harian_temp");
    }

    public function data_harian_insert(){
        return insertProduksiDataHarian();
    }

    public function get_data_harian(Request $request){
        $ap  = $request->input('ap');
        return getUrlProduksiDataHarian($ap);
    }

    public function data_harian_setting_simpan(Request $request){
        $roles = Auth::user()->roles;
        $setting = $request->input('setting');
        $target_hari = $request->input('target_hari');
        $estimasi_rhpp = $request->input('estimasi_rhpp');
        $keterangan = $request->input('keterangan');

        if($setting == 'PILIH'){
            $setting = '';
        }

        if($target_hari == 'PILIH'){
            $target_hari = '';
        }

        if($estimasi_rhpp == 'PILIH'){
            $estimasi_rhpp = '';
        }

        $tanggal_chick_in = $request->input('tanggal_chick_in');
        $unit = $request->input('unit');
        $nama_flok = addslashes($request->input('nama_flok'));

        $sql = DB::select("SELECT id FROM table_prd_data_harian_setting WHERE tanggal_chick_in='$tanggal_chick_in' AND unit='$unit' AND nama_flok='$nama_flok'");
        if (empty($sql)) {
            $move = DB::statement("INSERT INTO table_prd_data_harian_setting
                                    SELECT *,'','','','' FROM table_prd_data_harian WHERE tanggal_chick_in='$tanggal_chick_in' AND unit='$unit' AND nama_flok='$nama_flok'");
            if($move){
                 $simpan = DB::statement("UPDATE table_prd_data_harian_setting SET setting='$setting', target_hari='$target_hari', estimasi_rhpp='$estimasi_rhpp', keterangan='$keterangan'
                                            WHERE tanggal_chick_in='$tanggal_chick_in' AND unit='$unit' AND nama_flok='$nama_flok'");
            }
        }else{
            $simpan = DB::statement("UPDATE table_prd_data_harian_setting SET setting='$setting', target_hari='$target_hari', estimasi_rhpp='$estimasi_rhpp', keterangan='$keterangan'
                                            WHERE tanggal_chick_in='$tanggal_chick_in' AND unit='$unit' AND nama_flok='$nama_flok'");
        }
        Alert::toast('Data berhasil disimpan', 'success');
        if($roles!='sr'){
            return redirect()->route('produksi.flokpanen_setting')->with(['success' => 'Data berhasil disimpan']);
        }else{
            return redirect()->route('produksi.pantauan_flokpanen')->with(['success' => 'Data berhasil disimpan']);
        }
    }

    public function data_harian_setting_reset($nama_flok, $unit, $tanggal_chick_in){
        $roles = Auth::user()->roles;
        $nama_flok = addslashes($nama_flok);

        $sql = DB::select("SELECT id FROM table_prd_data_harian_setting WHERE tanggal_chick_in='$tanggal_chick_in' AND unit='$unit' AND nama_flok='$nama_flok'");
        if (!empty($sql)) {
            $move = DB::statement("DELETE FROM table_prd_data_harian_setting WHERE tanggal_chick_in='$tanggal_chick_in' AND unit='$unit' AND nama_flok='$nama_flok'");
        }
        Alert::toast('Data berhasil direset', 'success');
        if($roles!='sr'){
            return redirect()->route('produksi.flokpanen_setting')->with(['success' => 'Data berhasil disimpan']);
        }else{
            return redirect()->route('produksi.pantauan_flokpanen')->with(['success' => 'Data berhasil disimpan']);
        }
    }
}
