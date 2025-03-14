<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ScLogDocHargaUploadImport;
use App\Imports\ScLogDocRekapDocImport;
use App\Imports\ScLogDocHargaHarianImport;
use App\Imports\PlottingDocImport;
use App\Imports\PlottingDocPusatImport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\LogDoc;
use App\Models\ScLogDocRekap;
use App\Models\ScLogDocKeterangan;
use Auth;
use Alert;
use App\Imports\PoDocUploadImport;
use App\Models\Plasma;
use App\Models\KontakTelp;
use App\Models\Regions;
use App\Models\Unit;
use App\Models\PoDoc;
use App\Models\PoDocFilterTemp;
use App\Models\PlottingDoc;
use App\Models\PlottingDocPusat;
use RealRashid\SweetAlert\Facades\Alert as FacadesAlert;
use Yajra\DataTables\DataTables;;
use App\Models\MasterRing;
use App\Imports\MasterRingImport;
use File;

class LogDocController extends Controller{

    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        $jabatan = Auth::user()->jabatan;
        $nik = Auth::user()->nik;

        $batas = 10;
        $no = 1;
        $logdoc = LogDoc::where('akses','LIKE','%'.$jabatan.'%')
        ->orWhere('akses','LIKE','%'.$nik.'%')
        ->orderBy('id','ASC')->paginate($batas);

        $no = $batas*($logdoc->currentPage()-1);
        $jml = LogDoc::where('akses','LIKE','%'.$jabatan.'%')
        ->orWhere('akses','LIKE','%'.$nik.'%')->count();

        $arrId = array();
        foreach($logdoc as $data){
            array_push($arrId, [$data->id]);
        }
        return view('dashboard.logdoc.menuList',compact('no', 'logdoc', 'jml', 'arrId'));
    }

    public function poDocPerVendor(Request $request){
        $totalSenin = 0;
        $totalSelasa = 0;
        $totalRabu = 0;
        $totalKamis = 0;
        $totalJumat = 0;
        $totalSabtu = 0;
        $totalMinggu = 0;
        $no = 1;
        $region = Auth::user()->region;
        $unit = Auth::user()->unit;
        $roles = Auth::user()->roles;
        $jabatan = Auth::user()->jabatan;
        $nik = Auth::user()->nik;

        $previousUrl = $request->headers->get('referer');

        if ($previousUrl == 'https://mis.ptmjl.co.id/logdoc' || $previousUrl == 'http://127.0.0.1:8000/logdoc') {
            PoDocFilterTemp::where('nikfilter', $nik)->delete();
        }

        $checkFilterEmpty = PoDocFilterTemp::where('nikfilter', $nik)->first();

        if (!empty($request->tanggalAwalFilter) || !empty($request->tanggalAkhirFilter) || !empty($request->vendorFilter) || !empty($request->regionFilter) || !empty($request->unitFilter)) {
            $tanggalAwalFilter = $request->tanggalAwalFilter;
            $tanggalAkhirFilter = $request->tanggalAkhirFilter;
            $vendorFilter = $request->vendorFilter;
            $regionFilter = $request->regionFilter;
            $unitFilter = $request->unitFilter;
        } else {
            if (empty($checkFilterEmpty)) {
                $tanggalAwalFilter = $request->tanggalAwalFilter;
                $tanggalAkhirFilter = $request->tanggalAkhirFilter;
                $vendorFilter = $request->vendorFilter;
                $regionFilter = $request->regionFilter;
                $unitFilter = $request->unitFilter;
            } else {
                $tanggalAwalFilter = $checkFilterEmpty->tanggalawalfilter;
                $tanggalAkhirFilter = $checkFilterEmpty->tanggalakhirfilter;
                $vendorFilter = $checkFilterEmpty->vendorfilter;
                $regionFilter = json_decode($checkFilterEmpty->apfilter);
                $unitFilter = $checkFilterEmpty->unitfilter;
            }
        }

        if (!empty($tanggalAwalFilter) || !empty($tanggalAkhirFilter) || !empty($vendorFilter) || !empty($regionFilter) || !empty($unitFilter)) {
            PoDocFilterTemp::where('nikfilter', $nik)->delete();
            $newPoDocFilter = new PoDocFilterTemp;
            $newPoDocFilter->nikfilter = $nik;
            $newPoDocFilter->tanggalawalfilter = $tanggalAwalFilter;
            $newPoDocFilter->tanggalakhirfilter = $tanggalAkhirFilter;
            $newPoDocFilter->vendorfilter = $vendorFilter;
            $newPoDocFilter->apfilter = json_encode($regionFilter);
            $newPoDocFilter->unitfilter = $unitFilter;
            $newPoDocFilter->save();
        }

        $vendorDoc = DB::select("SELECT kode_vendor, kode_broker
            FROM master_kode_vendor_doc
            WHERE kode_broker = 'BF'
            GROUP BY kode_vendor
            UNION ALL
            SELECT 'BROKER' AS kode_vendor, 'BROKER' AS kode_vendor
            ORDER BY kode_vendor
        ");

        if ($jabatan != "ADMIN LOGISTIK" && $roles != "sr") {
            $regions = Regions::all();
        } else {
            $regions = DB::select("SELECT koderegion, namaregion FROM regions WHERE koderegion = '$region' ORDER BY koderegion ASC");
        }

        foreach ($regions as $r) {
            $reg = $r->namaregion;
        }

        $unitSelect = "";
        if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK') {
            $unitSelect = Unit::where('kodearca', 'LIKE', '%'.$reg.'%')->pluck('kodeunit','namaunit');
        } else {
            if (!empty($regionFilter) && count($regionFilter) <= 1 ) {
                $unitSelect = Unit::where('kodearca', 'LIKE', '%'.$regionFilter[0].'%')->pluck('kodeunit','namaunit');
            }
        }

        $query = DB::table('tbl_po_doc');

        if (!empty($tanggalAwalFilter) && !empty($tanggalAkhirFilter)) {
            $query->whereBetween('tanggal', [$tanggalAwalFilter, $tanggalAkhirFilter]);
        }

        if ($vendorFilter != 'SEMUA') {
            $query->where('namavendor', $vendorFilter);
        }

        if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK') {
            if (!empty($regionFilter)) {
                $query->where('namado', $regionFilter);
            }
        } else {
            if (!is_null($regionFilter) && !in_array('SEMUA', $regionFilter)) {
                $query->whereIn('namado', $regionFilter);
            }
        }

        if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK') {
            if (!empty($unitFilter) && $unitFilter != 'SEMUA') {
                $query->where('unit', $unitFilter);
            }
        } else {
            if ($unitFilter != 'SEMUA') {
                $query->where('unit', $unitFilter);
            }
        }

        $query->orderBy('tanggal')
            ->orderBy('namavendor')
            ->orderBy('namapeternak');

        $poDocFilter = $query->get();

        foreach ($poDocFilter as $poDoc) {
            if ($poDoc->hari == 'SENIN') {
                $totalSenin += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'SELASA') {
                $totalSelasa += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'RABU') {
                $totalRabu += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'KAMIS') {
                $totalKamis += $poDoc->jumlahbox;
            } else if ($poDoc->hari == "JUM'AT") {
                $totalJumat += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'SABTU') {
                $totalSabtu += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'MINGGU') {
                $totalMinggu += $poDoc->jumlahbox;
            }
        }

        $totalSetting = $totalSenin + $totalSelasa + $totalRabu + $totalKamis + $totalJumat + $totalSabtu + $totalMinggu;

        return view('dashboard.logdoc.poDocPerVendor.poDocPerVendor', compact('vendorDoc', 'vendorFilter', 'regions', 'reg', 'region', 'unit', 'unitSelect', 'no', 'previousUrl', 'regionFilter', 'unitFilter', 'tanggalAwalFilter', 'tanggalAkhirFilter', 'roles', 'jabatan', 'nik', 'poDocFilter', 'totalSenin', 'totalSelasa', 'totalRabu', 'totalKamis', 'totalJumat', 'totalSabtu', 'totalMinggu', 'totalSetting'));
    }

    public function arsipPoDocPerVendor(Request $request){
        $totalSenin = 0;
        $totalSelasa = 0;
        $totalRabu = 0;
        $totalKamis = 0;
        $totalJumat = 0;
        $totalSabtu = 0;
        $totalMinggu = 0;
        $no = 1;
        $region = Auth::user()->region;
        $unit = Auth::user()->unit;
        $roles = Auth::user()->roles;
        $jabatan = Auth::user()->jabatan;
        $nik = Auth::user()->nik;

        $previousUrl = $request->headers->get('referer');

        if ($previousUrl == 'https://mis.ptmjl.co.id/logdoc' || $previousUrl == 'http://127.0.0.1:8000/logdoc') {
            PoDocFilterTemp::where('nikfilter', $nik)->delete();
        }

        $checkFilterEmpty = PoDocFilterTemp::where('nikfilter', $nik)->first();

        if (!empty($request->tanggalAwalFilter) || !empty($request->tanggalAkhirFilter) || !empty($request->vendorFilter) || !empty($request->regionFilter) || !empty($request->unitFilter)) {
            $tanggalAwalFilter = $request->tanggalAwalFilter;
            $tanggalAkhirFilter = $request->tanggalAkhirFilter;
            $vendorFilter = $request->vendorFilter;
            $regionFilter = $request->regionFilter;
            $unitFilter = $request->unitFilter;
        } else {
            if (empty($checkFilterEmpty)) {
                $tanggalAwalFilter = $request->tanggalAwalFilter;
                $tanggalAkhirFilter = $request->tanggalAkhirFilter;
                $vendorFilter = $request->vendorFilter;
                $regionFilter = $request->regionFilter;
                $unitFilter = $request->unitFilter;
            } else {
                $tanggalAwalFilter = $checkFilterEmpty->tanggalawalfilter;
                $tanggalAkhirFilter = $checkFilterEmpty->tanggalakhirfilter;
                $vendorFilter = $checkFilterEmpty->vendorfilter;
                $regionFilter = json_decode($checkFilterEmpty->apfilter);
                $unitFilter = $checkFilterEmpty->unitfilter;
            }
        }

        if (!empty($tanggalAwalFilter) || !empty($tanggalAkhirFilter) || !empty($vendorFilter) || !empty($regionFilter) || !empty($unitFilter)) {
            PoDocFilterTemp::where('nikfilter', $nik)->delete();
            $newPoDocFilter = new PoDocFilterTemp;
            $newPoDocFilter->nikfilter = $nik;
            $newPoDocFilter->tanggalawalfilter = $tanggalAwalFilter;
            $newPoDocFilter->tanggalakhirfilter = $tanggalAkhirFilter;
            $newPoDocFilter->vendorfilter = $vendorFilter;
            $newPoDocFilter->apfilter = json_encode($regionFilter);
            $newPoDocFilter->unitfilter = $unitFilter;
            $newPoDocFilter->save();
        }

        $vendorDoc = DB::select("SELECT kode_vendor, kode_broker
            FROM master_kode_vendor_doc
            WHERE kode_broker = 'BF'
            GROUP BY kode_vendor
            UNION ALL
            SELECT 'BROKER' AS kode_vendor, 'BROKER' AS kode_vendor
            ORDER BY kode_vendor
        ");

        if ($jabatan != "ADMIN LOGISTIK" && $roles != "sr") {
            $regions = Regions::all();
        } else {
            $regions = DB::select("SELECT koderegion, namaregion FROM regions WHERE koderegion = '$region' ORDER BY koderegion ASC");
        }

        foreach ($regions as $r) {
            $reg = $r->namaregion;
        }

        $unitSelect = "";
        if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK') {
            $unitSelect = Unit::where('kodearca', 'LIKE', '%'.$reg.'%')->pluck('kodeunit','namaunit');
        } else {
            if (!empty($regionFilter) && count($regionFilter) <= 1 ) {
                $unitSelect = Unit::where('kodearca', 'LIKE', '%'.$regionFilter[0].'%')->pluck('kodeunit','namaunit');
            }
        }

        $query = DB::table('tbl_po_doc_arsip');

        if (!empty($tanggalAwalFilter) && !empty($tanggalAkhirFilter)) {
            $query->whereBetween('tanggal', [$tanggalAwalFilter, $tanggalAkhirFilter]);
        }

        if ($vendorFilter != 'SEMUA') {
            $query->where('namavendor', $vendorFilter);
        }

        if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK') {
            if (!empty($regionFilter)) {
                $query->where('namado', $regionFilter);
            }
        } else {
            if (!is_null($regionFilter) && !in_array('SEMUA', $regionFilter)) {
                $query->whereIn('namado', $regionFilter);
            }
        }

        if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK') {
            if (!empty($unitFilter) && $unitFilter != 'SEMUA') {
                $query->where('unit', $unitFilter);
            }
        } else {
            if ($unitFilter != 'SEMUA') {
                $query->where('unit', $unitFilter);
            }
        }

        $query->orderBy('tanggal')
            ->orderBy('namavendor')
            ->orderBy('namapeternak');

        $poDocFilter = $query->get();

        foreach ($poDocFilter as $poDoc) {
            if ($poDoc->hari == 'SENIN') {
                $totalSenin += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'SELASA') {
                $totalSelasa += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'RABU') {
                $totalRabu += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'KAMIS') {
                $totalKamis += $poDoc->jumlahbox;
            } else if ($poDoc->hari == "JUM'AT") {
                $totalJumat += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'SABTU') {
                $totalSabtu += $poDoc->jumlahbox;
            } else if ($poDoc->hari == 'MINGGU') {
                $totalMinggu += $poDoc->jumlahbox;
            }
        }

        $totalSetting = $totalSenin + $totalSelasa + $totalRabu + $totalKamis + $totalJumat + $totalSabtu + $totalMinggu;

        return view('dashboard.logdoc.poDocPerVendor.poDocPerVendorArsip', compact('vendorDoc', 'vendorFilter', 'regions', 'reg', 'region', 'unit', 'unitSelect', 'no', 'previousUrl', 'regionFilter', 'unitFilter', 'tanggalAwalFilter', 'tanggalAkhirFilter', 'roles', 'jabatan', 'nik', 'poDocFilter', 'totalSenin', 'totalSelasa', 'totalRabu', 'totalKamis', 'totalJumat', 'totalSabtu', 'totalMinggu', 'totalSetting'));
    }

    public function createPoDocPerVendor(){
            $kodeAp = Auth::user()->region;
            $kodeUnit = Auth::user()->unit;
            $roles = Auth::user()->roles;
            $jabatan = Auth::user()->jabatan;

            $vendorDoc = DB::select("SELECT kode_vendor, kode_broker
                FROM master_kode_vendor_doc
                WHERE kode_broker = 'BF'
                GROUP BY kode_vendor
                UNION ALL
                SELECT 'BROKER' AS kode_vendor, 'BROKER' AS kode_vendor
                ORDER BY kode_vendor
            ");

            if (($roles == 'pusat') || ($roles == 'admin')) {
                $region = DB::select("SELECT koderegion, namaregion FROM regions ORDER BY koderegion ASC");
            } else {
                $region = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $kodeAp . '" ORDER BY koderegion ASC');
            }

            $poDoc = PoDoc::all();

            if (($roles == 'pusat') || ($roles == 'admin')) {
                $nomor_telepon_ts = KontakTelp::select('unit', 'ap', 'jabatan', 'nama', 'nowa')->orderBy('unit', 'ASC')->orderBy('jabatan', 'ASC')->get();
            } else {
                if ($roles == 'sr') {
                    $nomor_telepon_ts = KontakTelp::select('unit', 'ap', 'jabatan', 'nama', 'nowa')->where('ap', $kodeAp)->orderBy('unit', 'ASC')->orderBy('jabatan', 'ASC')->get();
                } else if ($jabatan == 'ADMIN LOGISTIK') {
                    $nomor_telepon_ts = KontakTelp::select('unit', 'ap', 'jabatan', 'nama', 'nowa')->where('unit', $kodeUnit)->orderBy('unit', 'ASC')->orderBy('jabatan', 'ASC')->get();
                }
            }

            return view('dashboard.logdoc.poDocPerVendor.createPoDocPerVendor', compact('kodeAp', 'kodeUnit', 'poDoc', 'vendorDoc', 'region', 'roles', 'jabatan', 'nomor_telepon_ts'));
    }

    public function uploadPoDocPerVendor(Request $request){
            $roles = Auth::user()->roles;
            $nik = Auth::user()->nik;

            $vendor = $request->vendor;

            $kodeCustomer = '';
            switch ($request->unitCp) {
                case "CRB":
                    $kodeCustomer = '202 920';
                    break;
                case "IDM":
                    $kodeCustomer = '202 918';
                    break;
                case "PTR":
                    $kodeCustomer = '202 917';
                    break;
                case "LSR":
                    $kodeCustomer = '203 856';
                    break;
                case "PDG":
                    $kodeCustomer = '202 853';
                    break;
                case "PPT":
                    $kodeCustomer = '202 779';
                    break;
                case "CIA":
                    $kodeCustomer = '202 689';
                    break;
                case "BDG":
                    $kodeCustomer = '202 906';
                    break;
                case "SBG":
                    $kodeCustomer = '202 905';
                    break;
                case "CJR":
                    $kodeCustomer = '202 904';
                    break;
                case "MJK":
                    $kodeCustomer = '202 849';
                    break;
                case "SMD":
                    $kodeCustomer = '202 850';
                    break;
                case "TRG":
                    $kodeCustomer = '1020';
                    break;
                case "BNJ":
                    $kodeCustomer = '1006';
                    break;
                case "BGR":
                    $kodeCustomer = '1021';
                    break;
                default:
                    $kodeCustomer = NULL;
                    break;
            }

            if (strpos($vendor, 'CP') !== false) {
                if (($roles == 'admin') || ($nik == '0022.MTK.1009') || ($nik == '1888.MTK.0722') || ($nik == '0110.MTK.0412')) {
                    $poDocValidation = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaPeternakCp)->where('unit', $request->unitCp)->count();
                    $poDoc = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaPeternakCp)->where('unit', $request->unitCp)->first();
                } else {
                    $poDocValidation = PoDoc::where('namapeternak', $request->namaPeternakCp)->where('unit', $request->unitCp)->count();
                    $poDoc = PoDoc::where('namapeternak', $request->namaPeternakCp)->where('unit', $request->unitCp)->first();
                }

                if ($poDocValidation > 0) {
                    Alert::error('Input PO DOC Gagal', 'Flok '.$poDoc->namapeternak.' sudah terinput di '.$poDoc->namavendor.' pada tanggal '.$poDoc->tanggal)->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                } else {
                    $poDocPerVendor = new PoDoc;
                    $poDocPerVendor->namavendor = $request->vendor;
                    $poDocPerVendor->tanggal = $request->tanggalCp;
                    $poDocPerVendor->hari = $request->hariCp;
                    $poDocPerVendor->namado = $request->regionTextCp;
                    $poDocPerVendor->unit = $request->unitCp;
                    if ($request->vendor == 'CP JBR') {
                        $poDocPerVendor->kodecustomer = $request->regionTextCp == 'BRU' || $request->regionTextCp == 'MPU' ? NULL : $kodeCustomer;
                    } else {
                        $poDocPerVendor->kodecustomer = NULL;
                    }
                    $poDocPerVendor->gradedoc = $request->jenisGradeCp;
                    $poDocPerVendor->namapeternak = $request->namaPeternakCp;
                    $poDocPerVendor->alamatkandang = $request->alamatKandangCp;
                    $poDocPerVendor->notelepon = $request->nomorTeleponCp;
                    $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSCp);
                    $poDocPerVendor->jumlahbox = !empty($request->plastikVaccCp) ? $request->plastikVaccCp : $request->plastikNonVaccCp ;
                    $poDocPerVendor->feedgel = $request->feedGelCp;
                    $poDocPerVendor->keterangan = $request->keteranganCp;
                    $poDocPerVendor->save();
                    Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaPeternakCp .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                }
            } else if (strpos($vendor, 'MB') !== false) {
                if (($roles == 'admin') || ($nik == '0022.MTK.1009') || ($nik == '1888.MTK.0722') || ($nik == '0110.MTK.0412')) {
                    $poDocValidation = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaPeternakMb)->where('unit', $request->unitMb)->count();
                    $poDoc = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaPeternakMb)->where('unit', $request->unitMb)->first();
                } else {
                    $poDocValidation = PoDoc::where('namapeternak', $request->namaPeternakMb)->where('unit', $request->unitMb)->count();
                    $poDoc = PoDoc::where('namapeternak', $request->namaPeternakMb)->where('unit', $request->unitMb)->first();
                }

                if ($poDocValidation > 0) {
                    Alert::error('Input PO DOC Gagal', 'Flok '.$poDoc->namapeternak.' sudah terinput di '.$poDoc->namavendor.' pada tanggal '.$poDoc->tanggal)->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                } else {
                    $poDocPerVendor = new PoDoc;
                    $poDocPerVendor->namavendor = $request->vendor;
                    $poDocPerVendor->tanggal = $request->tanggalMb;
                    $poDocPerVendor->hari = $request->hariMb;
                    $poDocPerVendor->namado = $request->regionTextMb;
                    $poDocPerVendor->unit = $request->unitMb;
                    $poDocPerVendor->namapeternak = $request->namaPeternakMb;
                    $poDocPerVendor->alamatkandang = $request->alamatLokasiMb;
                    $poDocPerVendor->notelepon = $request->nomorTeleponMb;
                    $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSMb);
                    $poDocPerVendor->jumlahbox = $request->jumlahBoxMb;
                    $poDocPerVendor->gradedoc = $request->jenisGradeMb;
                    $poDocPerVendor->vaksin = $request->jenisVaksinMb;
                    $poDocPerVendor->plastik = $request->boxPlastikMb;
                    $poDocPerVendor->perlakuan = $request->perlakuanMb;
                    $poDocPerVendor->keterangan = $request->keteranganMb;
                    $poDocPerVendor->save();
                    Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaPeternakMb .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                }
            } else if (strpos($vendor, 'DMC') !== false) {
                if (($roles == 'admin') || ($nik == '0022.MTK.1009') || ($nik == '1888.MTK.0722') || ($nik == '0110.MTK.0412')) {
                    $poDocValidation = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaFlokDmc)->where('unit', $request->unitDmc)->count();
                    $poDoc = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaFlokDmc)->where('unit', $request->unitDmc)->first();
                } else {
                    $poDocValidation = PoDoc::where('namapeternak', $request->namaFlokDmc)->where('unit', $request->unitDmc)->count();
                    $poDoc = PoDoc::where('namapeternak', $request->namaFlokDmc)->where('unit', $request->unitDmc)->first();
                }

                if ($poDocValidation > 0) {
                    Alert::error('Input PO DOC Gagal', 'Flok '.$poDoc->namapeternak.' sudah terinput di '.$poDoc->namavendor.' pada tanggal '.$poDoc->tanggal)->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                } else {
                    $poDocPerVendor = new PoDoc;
                    $poDocPerVendor->namavendor = $request->vendor;
                    $poDocPerVendor->tanggal = $request->tanggalDmc;
                    $poDocPerVendor->hari = $request->hariDmc;
                    $poDocPerVendor->namado = $request->regionTextDmc;
                    $poDocPerVendor->unit = $request->unitDmc;
                    $poDocPerVendor->namapeternak = $request->namaFlokDmc;
                    $poDocPerVendor->alamatkandang = $request->alamatDmc;
                    $poDocPerVendor->notelepon = $request->nomorTeleponDmc;
                    $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSDmc);
                    $poDocPerVendor->jumlahbox = $request->popBoxDmc;
                    $poDocPerVendor->vaksin = $request->jenisVaksinDmc;
                    $poDocPerVendor->gradedoc = $request->jenisGradeDmc;
                    $poDocPerVendor->keterangan = $request->keteranganDmc;
                    $poDocPerVendor->save();
                    Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaFlokDmc .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                }
            } else if (strpos($vendor, 'SREEYA') !== false) {
                if (($roles == 'admin') || ($nik == '0022.MTK.1009') || ($nik == '1888.MTK.0722') || ($nik == '0110.MTK.0412')) {
                    $poDocValidation = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaFlokSreeya)->where('unit', $request->unitSreeya)->count();
                    $poDoc = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaFlokSreeya)->where('unit', $request->unitSreeya)->first();
                } else {
                    $poDocValidation = PoDoc::where('namapeternak', $request->namaFlokSreeya)->where('unit', $request->unitSreeya)->count();
                    $poDoc = PoDoc::where('namapeternak', $request->namaFlokSreeya)->where('unit', $request->unitSreeya)->first();
                }

                if ($poDocValidation > 0) {
                    Alert::error('Input PO DOC Gagal', 'Flok '.$poDoc->namapeternak.' sudah terinput di '.$poDoc->namavendor.' pada tanggal '.$poDoc->tanggal)->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                } else {
                    $poDocPerVendor = new PoDoc;
                    $poDocPerVendor->namavendor = $request->vendor;
                    $poDocPerVendor->tanggal = $request->tanggalSreeya;
                    $poDocPerVendor->hari = $request->hariSreeya;
                    $poDocPerVendor->namado = $request->regionTextSreeya;
                    $poDocPerVendor->unit = $request->unitSreeya;
                    $poDocPerVendor->namapeternak = $request->namaFlokSreeya;
                    $poDocPerVendor->alamatkandang = $request->alamatSreeya;
                    $poDocPerVendor->notelepon = $request->nomorTeleponSreeya;
                    $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSSreeya);
                    $poDocPerVendor->jumlahbox = $request->popBoxSreeya;
                    $poDocPerVendor->vaksin = $request->jenisVaksinSreeya;
                    $poDocPerVendor->gradedoc = $request->jenisGradeSreeya;
                    $poDocPerVendor->keterangan = $request->keteranganSreeya;
                    $poDocPerVendor->save();
                    Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaFlokSreeya .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                }
            } else {
                if (($roles == 'admin') || ($nik == '0022.MTK.1009') || ($nik == '1888.MTK.0722') || ($nik == '0110.MTK.0412')) {
                    $poDocValidation = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaPeternakCtu)->where('unit', $request->unitCtu)->count();
                    $poDoc = PoDoc::where('namavendor', $request->vendor)->where('namapeternak', $request->namaPeternakCtu)->where('unit', $request->unitCtu)->first();
                } else {
                    $poDocValidation = PoDoc::where('namapeternak', $request->namaPeternakCtu)->where('unit', $request->unitCtu)->count();
                    $poDoc = PoDoc::where('namapeternak', $request->namaPeternakCtu)->where('unit', $request->unitCtu)->first();
                }

                if ($poDocValidation > 0) {
                    Alert::error('Input PO DOC Gagal', 'Flok ' . $poDoc->namapeternak . ' sudah terinput di ' . $poDoc->namavendor.' pada tanggal '.$poDoc->tanggal)->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                } else {
                    $poDocPerVendor = new PoDoc;
                    $poDocPerVendor->namavendor = $request->vendor;
                    $poDocPerVendor->tanggal = $request->tanggalCtu;
                    $poDocPerVendor->hari = $request->hariCtu;
                    $poDocPerVendor->namado = $request->regionTextCtu;
                    $poDocPerVendor->unit = $request->unitCtu;
                    $poDocPerVendor->namapeternak = $request->namaPeternakCtu;
                    $poDocPerVendor->alamatkandang = $request->alamatCtu;
                    $poDocPerVendor->notelepon = $request->nomorHpCtu;
                    $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSCtu);
                    $poDocPerVendor->jumlahbox = $request->popBoxCtu;
                    $poDocPerVendor->gradedoc = $request->jenisGradeCtu;
                    $poDocPerVendor->vaksin = $request->jenisVaksinCtu;
                    $poDocPerVendor->keterangan = $request->keteranganCtu;
                    $poDocPerVendor->save();
                    Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaPeternakCtu .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
                    return redirect()->route('logdoc.poDocPerVendor');
                }
            }
    }

    public function editPoDocPerVendor($id){
            $kodeAp = Auth::user()->region;
            $kodeUnit = Auth::user()->unit;
            $roles = Auth::user()->roles;
            $jabatan = Auth::user()->jabatan;
            $vendor_doc = '';
            $kode_vendor = '';
            $koderegion =  '';
            $namaregion =  '';

            $vendorDoc = DB::select("SELECT kode_vendor, kode_broker
                FROM master_kode_vendor_doc
                WHERE kode_broker = 'BF'
                GROUP BY kode_vendor
                UNION ALL
                SELECT 'BROKER' AS kode_vendor, 'BROKER' AS kode_vendor
                ORDER BY kode_vendor
            ");

            if (($roles == 'pusat') || ($roles == 'admin')) {
                $region = DB::select("SELECT koderegion, namaregion FROM regions ORDER BY koderegion ASC");
            } else {
                $region = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "' . $kodeAp . '" ORDER BY koderegion ASC');
            }

            $poDoc = PoDoc::find($id);

            if (($roles == 'pusat') || ($roles == 'admin')) {
                $regionFilter = DB::select("SELECT koderegion, namaregion FROM regions WHERE namaregion = '$poDoc->namado' ORDER BY koderegion ASC");
            } else {
                $regionFilter = DB::select("SELECT koderegion, namaregion FROM regions WHERE namaregion = '$poDoc->namado' AND koderegion = '$kodeAp' ORDER BY koderegion ASC");
            }

            foreach ($regionFilter as $p) {
                $koderegion = $p->koderegion;
                $namaregion = $p->namaregion;
            }

            $nomor = json_decode($poDoc->noteleponppl);
            $phoneNumbers = [];
            foreach ($nomor as $item) {
                $phoneNumber = substr($item, strpos($item, ':') + 2);
                $phoneNumbers[] = $phoneNumber;
            }

            $nomorTeleponTsSelected = KontakTelp::select('unit', 'ap', 'jabatan', 'nama', 'nowa')->whereIn('nowa', $phoneNumbers)->orderBy('unit', 'ASC')->orderBy('jabatan', 'ASC')->get();

            if (($roles == 'pusat') || ($roles == 'admin')) {
                $nomor_telepon_ts = KontakTelp::select('unit', 'ap', 'jabatan', 'nama', 'nowa')->orderBy('unit', 'ASC')->orderBy('jabatan', 'ASC')->get();
            } else {
                if ($roles == 'sr') {
                    $nomor_telepon_ts = KontakTelp::select('unit', 'ap', 'jabatan', 'nama', 'nowa')->where('ap', $kodeAp)->orderBy('unit', 'ASC')->orderBy('jabatan', 'ASC')->get();
                } else if ($jabatan == 'ADMIN LOGISTIK') {
                    $nomor_telepon_ts = KontakTelp::select('unit', 'ap', 'jabatan', 'nama', 'nowa')->where('unit', $kodeUnit)->orderBy('unit', 'ASC')->orderBy('jabatan', 'ASC')->get();
                }
            }

            return view('dashboard.logdoc.poDocPerVendor.editPoDocPerVendor', compact('id', 'kodeAp', 'kodeUnit', 'poDoc', 'vendorDoc', 'koderegion', 'namaregion', 'region', 'jabatan', 'roles', 'nomorTeleponTsSelected', 'nomor_telepon_ts'));
    }

    public function updatePoDocPerVendor(Request $request, $id){
            $vendor = $request->vendor;
            $poDocPerVendor = PoDoc::find($id);

            $kodeCustomer = '';
            switch ($request->unitCp) {
                case "CRB":
                    $kodeCustomer = '202 920';
                    break;
                case "IDM":
                    $kodeCustomer = '202 918';
                    break;
                case "PTR":
                    $kodeCustomer = '202 917';
                    break;
                case "LSR":
                    $kodeCustomer = '203 856';
                    break;
                case "PDG":
                    $kodeCustomer = '202 853';
                    break;
                case "PPT":
                    $kodeCustomer = '202 779';
                    break;
                case "CIA":
                    $kodeCustomer = '202 689';
                    break;
                case "BDG":
                    $kodeCustomer = '202 906';
                    break;
                case "SBG":
                    $kodeCustomer = '202 905';
                    break;
                case "CJR":
                    $kodeCustomer = '202 904';
                    break;
                case "MJK":
                    $kodeCustomer = '202 849';
                    break;
                case "SMD":
                    $kodeCustomer = '202 850';
                    break;
                case "TRG":
                    $kodeCustomer = '1020';
                    break;
                case "BNJ":
                    $kodeCustomer = '1006';
                    break;
                case "BGR":
                    $kodeCustomer = '1021';
                    break;
                default:
                    $kodeCustomer = NULL;
                    break;
            }

            if (strpos($vendor, 'CP') !== false) {
                $poDocPerVendor->namavendor = $request->vendor;
                $poDocPerVendor->tanggal = $request->tanggalCp;
                $poDocPerVendor->hari = $request->hariCp;
                $poDocPerVendor->namado = $request->regionTextCp;
                $poDocPerVendor->unit = $request->unitCp;
                if ($request->vendor == 'CP JBR') {
                    $poDocPerVendor->kodecustomer = $request->regionTextCp == 'BRU' || $request->regionTextCp == 'MPU' ? NULL : $kodeCustomer;
                } else {
                    $poDocPerVendor->kodecustomer = NULL;
                }
                $poDocPerVendor->gradedoc = $request->jenisGradeCp;
                $poDocPerVendor->namapeternak = $request->namaPeternakCp;
                $poDocPerVendor->alamatkandang = $request->alamatKandangCp;
                $poDocPerVendor->notelepon = $request->nomorTeleponCp;
                $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSCp);
                $poDocPerVendor->noteleponppl = $request->nomorTeleponTSCp;
                $poDocPerVendor->jumlahbox = !empty($request->plastikVaccCp) ? $request->plastikVaccCp : $request->plastikNonVaccCp;
                $poDocPerVendor->feedgel = $request->feedGelCp;
                $poDocPerVendor->keterangan = $request->keteranganCp;
            } else if (strpos($vendor, 'MB') !== false) {
                $poDocPerVendor->namavendor = $request->vendor;
                $poDocPerVendor->tanggal = $request->tanggalMb;
                $poDocPerVendor->hari = $request->hariMb;
                $poDocPerVendor->namado = $request->regionTextMb;
                $poDocPerVendor->unit = $request->unitMb;
                $poDocPerVendor->namapeternak = $request->namaPeternakMb;
                $poDocPerVendor->alamatkandang = $request->alamatLokasiMb;
                $poDocPerVendor->notelepon = $request->nomorTeleponMb;
                $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSMb);
                $poDocPerVendor->jumlahbox = $request->jumlahBoxMb;
                $poDocPerVendor->gradedoc = $request->jenisGradeMb;
                $poDocPerVendor->vaksin = $request->jenisVaksinMb;
                $poDocPerVendor->plastik = $request->boxPlastikMb;
                $poDocPerVendor->perlakuan = $request->perlakuanMb;
                $poDocPerVendor->keterangan = $request->keteranganMb;
            } else if (strpos($vendor, 'DMC') !== false) {
                $poDocPerVendor->namavendor = $request->vendor;
                $poDocPerVendor->tanggal = $request->tanggalDmc;
                $poDocPerVendor->hari = $request->hariDmc;
                $poDocPerVendor->namado = $request->regionTextDmc;
                $poDocPerVendor->unit = $request->unitDmc;
                $poDocPerVendor->namapeternak = $request->namaFlokDmc;
                $poDocPerVendor->alamatkandang = $request->alamatDmc;
                $poDocPerVendor->notelepon = $request->nomorTeleponDmc;
                $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSDmc);
                $poDocPerVendor->jumlahbox = $request->popBoxDmc;
                $poDocPerVendor->vaksin = $request->jenisVaksinDmc;
                $poDocPerVendor->gradedoc = $request->jenisGradeDmc;
                $poDocPerVendor->keterangan = $request->keteranganDmc;
            } else if (strpos($vendor, 'SREEYA') !== false) {
                $poDocPerVendor->namavendor = $request->vendor;
                $poDocPerVendor->tanggal = $request->tanggalSreeya;
                $poDocPerVendor->hari = $request->hariSreeya;
                $poDocPerVendor->namado = $request->regionTextSreeya;
                $poDocPerVendor->unit = $request->unitSreeya;
                $poDocPerVendor->namapeternak = $request->namaFlokSreeya;
                $poDocPerVendor->alamatkandang = $request->alamatSreeya;
                $poDocPerVendor->notelepon = $request->nomorTeleponSreeya;
                $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSSreeya);
                $poDocPerVendor->jumlahbox = $request->popBoxSreeya;
                $poDocPerVendor->vaksin = $request->jenisVaksinSreeya;
                $poDocPerVendor->gradedoc = $request->jenisGradeSreeya;
                $poDocPerVendor->keterangan = $request->keteranganSreeya;
            } else {
                $poDocPerVendor->namavendor = $request->vendor;
                $poDocPerVendor->tanggal = $request->tanggalCtu;
                $poDocPerVendor->hari = $request->hariCtu;
                $poDocPerVendor->namado = $request->regionTextCtu;
                $poDocPerVendor->unit = $request->unitCtu;
                $poDocPerVendor->namapeternak = $request->namaPeternakCtu;
                $poDocPerVendor->alamatkandang = $request->alamatCtu;
                $poDocPerVendor->notelepon = $request->nomorHpCtu;
                $poDocPerVendor->noteleponppl = json_encode($request->nomorTeleponTSCtu);
                $poDocPerVendor->jumlahbox = $request->popBoxCtu;
                $poDocPerVendor->gradedoc = $request->jenisGradeCtu;
                $poDocPerVendor->vaksin = $request->jenisVaksinCtu;
                $poDocPerVendor->keterangan = $request->keteranganCtu;
            }

            $poDocPerVendor->update();

            if (strpos($vendor, 'CP') !== false) {
                Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaPeternakCp .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
            } else if (strpos($vendor, 'MB') !== false) {
                Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaPeternakMb .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
            } else if (strpos($vendor, 'DMC') !== false) {
                Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaFlokDmc .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
            } else if (strpos($vendor, 'SREEYA') !== false) {
                Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaFlokSreeya .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
            } else {
                Alert::success('BERHASIL', 'Purchase Order DOC '. $request->namaPeternakCtu .' ' . $request->vendor . ' berhasil diupdate')->autoClose(6000);
            }

            return redirect()->route('logdoc.poDocPerVendor');
    }

    public function deletePoDocPerVendor($id){
        $poDoc = PoDoc::find($id);
        Alert::success('Purchase Order DOC '.$poDoc->namapeternak.' '.date("d-m-Y", strtotime($poDoc->tanggal)).' berhasil dihapus');
        $poDoc->delete();
        return redirect()->back();
    }

    public function dataPeternak(Request $request) {
        $region = Auth::user()->region;
        $unit = Auth::user()->unit;
        $roles = Auth::user()->roles;
        $jabatan = Auth::user()->jabatan;
        $perPage = (int) $request->input('length', 10);
        $offset = (int) $request->input('start', 0);
        $search = $request->input('search.value');

        // Query dasar tanpa filter pencarian
        $baseQuery =
        "FROM (
            SELECT *
            FROM tb_plasma
            WHERE performa LIKE '%baik%'
        ) tb_plasma";

        // Filter akses pengguna
        $accessFilter = '';
        $bindings = [];

        if ($roles == 'sr') {
            $accessFilter = "tb_plasma.ap = '$region'";
        } elseif ($jabatan == 'ADMIN LOGISTIK') {
            $accessFilter = "tb_plasma.nama_unit = '$unit'";
        }

        // Kondisi pencarian
        $searchFilter = '';

        if (!empty($search)) {
            $searchFilter = "(tb_plasma.nama_plasma LIKE ? OR tb_plasma.nama_flok LIKE ? OR tb_plasma.alamat_flok LIKE ? OR tb_plasma.provinsi_flok LIKE ? OR tb_plasma.kota_or_kabupaten_flok LIKE ? OR tb_plasma.kecamatan_flok LIKE ? OR tb_plasma.kelurahan_or_desa_flok LIKE ? OR tb_plasma.kode_pos_flok LIKE ? OR tb_plasma.rt_flok LIKE ? OR tb_plasma.rw_flok LIKE ? OR tb_plasma.kota LIKE ? OR tb_plasma.unit LIKE ? OR tb_plasma.nama_unit LIKE ? OR tb_plasma.nomor_hp LIKE ? OR tb_plasma.email LIKE ? OR tb_plasma.ap LIKE ?)";
            $searchParam = "%$search%";
            $bindings = array_merge($bindings, array_fill(0, 16, $searchParam));
        }

        // Gabungkan filter akses dan filter pencarian
        $whereClause = '';
        if ($accessFilter && $searchFilter) {
            $whereClause = "WHERE $accessFilter AND $searchFilter";
        } elseif ($accessFilter) {
            $whereClause = "WHERE $accessFilter";
        } elseif ($searchFilter) {
            $whereClause = "WHERE $searchFilter";
        }

        // Query Total
        $totalQuery = "SELECT COUNT(*) AS total $baseQuery $whereClause";
        // $totalQuery = "SELECT COUNT(*) AS total $baseQuery $whereClause";
        $totalResult = DB::select($totalQuery, $bindings);
        $total = $totalResult[0]->total;

        // Query Data
        $dataQuery =
            "SELECT *
            $baseQuery
            $whereClause
            LIMIT $perPage OFFSET $offset";
        $data = DB::select($dataQuery, $bindings);

        // Menambahkan nomor urut dinamis
        foreach ($data as $index => $item) {
            $item->no_urut = $offset + $index + 1;
        }

        // Siapkan data untuk DataTables
        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function periksaKontrak(Request $request) {
        $ap = ptkode($request->region);
        $unit = $request->unit;
        $flok = urlencode($request->nama_flok);

        $opts = [
            "http" => [
                "timeout" => 1200,
                "method" => "GET",
                "header" => "Accept: application/json\r\n" .
                    "Content-Type: application/json\r\n" .
                    "X-Api-Key: devmustikaapaccess\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $file = file_get_contents('https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/Ap_apis/kontrak/'.$ap.'?unit='.$unit.'&flok='.$flok, false, $context);
        $arrayData = json_decode($file, true);
        $status = 'Belum Kontrak';
        foreach ($arrayData['data'] as $d) {
            $status = $d['status'] == 'Sudah Kontrak' ? 'Sudah Kontrak' : 'Belum Kontrak';
        }

        return response()->json(['status' => $status]);
    }

    private function get_from_api($url) {
        $ch = curl_init();
        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: devmustikaapaccess',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = curl_error($ch);
            curl_close($ch);
            return (object) [
                'status' => false,
                'message' => $message,
                'data' => [],
            ];
        }

        curl_close($ch);
        return json_decode($response);
    }

    public function databasePlasma(Request $request) {
        set_time_limit(0);
        try {
            $ap = $request->ap;
            $unit = $request->unit;
            $nik = Auth::user()->nik;
            $roles = Auth::user()->roles;

            if ($roles == 'user') {
                DB::table('tb_plasma_temp')->where('ap', $ap)->where('nama_unit', $unit)->delete();
            } else {
                DB::table('tb_plasma_temp')->where('ap', $ap)->delete();
            }

            $opts = [
                "http" => [
                    "timeout" => 1200,
                    "method" => "GET",
                    "header" => "Accept: application/json\r\n" .
                        "Content-Type: application/json\r\n" .
                        "X-Api-Key: devmustikaapaccess\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $file = file_get_contents('https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/ap_apis/peternak/' . $ap , false, $context);
            $arrayData = json_decode($file, true);
            $insert_data = array();
            $count = 0;
            foreach ($arrayData['data'] as $data) {
                $values = array(
                    'nama_plasma' => $data['nama_plasma'] ?? null,
                    'alamat_plasma' => $data['alamat_plasma'] ?? null,
                    'provinsi_plasma' => $data['provinsi_plasma'] ?? null,
                    'kota_or_kabupaten_plasma' => $data['kota_or_kabupaten_plasma'] ?? null,
                    'kecamatan_plasma' => $data['kecamatan_plasma'] ?? null,
                    'kelurahan_or_desa_plasma' => $data['kelurahan_or_desa_plasma'] ?? null,
                    'kode_pos_plasma' => $data['kode_pos_plasma'] ?? null,
                    'rt_plasma' => $data['rt_plasma'] ?? null,
                    'rw_plasma' => $data['rw_plasma'] ?? null,
                    'nama_flok' => $data['nama_flok'] ?? null,
                    'alamat_flok' => $data['alamat_flok'] ?? null,
                    'provinsi_flok' => $data['provinsi_flok'] ?? null,
                    'kota_or_kabupaten_flok' => $data['kota_or_kabupaten_flok'] ?? null,
                    'kecamatan_flok' => $data['kecamatan_flok'] ?? null,
                    'kelurahan_or_desa_flok' => $data['kelurahan_or_desa_flok'] ?? null,
                    'kode_pos_flok' => $data['kode_pos_flok'] ?? null,
                    'rt_flok' => $data['rt_flok'] ?? null,
                    'rw_flok' => $data['rw_flok'] ?? null,
                    'kota' => $data['kota'] ?? null,
                    'jumlah_populasi' => $data['jumlah_populasi'] ?? null,
                    'unit' => $data['unit'] ?? null,
                    'nama_unit' => $data['nama_unit'] ?? null,
                    'jenis_kandang' => $data['jenis_kandang'] ?? null,
                    'performa' => $data['performa'] ?? null,
                    'ts' => $data['ts'] ?? null,
                    'jaminan' => $data['jaminan'] ?? null,
                    'npwp' => $data['npwp'] ?? null,
                    'nama_npwp' => $data['nama_npwp'] ?? null,
                    'latitude_gps' => $data['latitude_gps'] ?? null,
                    'nomor_hp' => $data['nomor_hp'] ?? null,
                    'butuh_faktur_pajak' => $data['butuh_faktur_pajak'] ?? null,
                    'email' => $data['email'] ?? null,
                    'id_peternak' => $data['id_peternak'] ?? null,
                    'tipe_farm' => $data['tipe_farm'] ?? null,
                    'ap' => $ap,
                    'nik' => $nik,
                );
                $insert_data[] = $values;
                $count = ++$count;
            }
            $insert_data = collect($insert_data);
            $chunks = $insert_data->chunk(500);
            foreach ($chunks as $chunk) {
                DB::table('tb_plasma_temp')->insert($chunk->toArray());
            }

            if ($roles == 'user') {
                DB::table('tb_plasma')->where('ap', $ap)->where('nama_unit', $unit)->delete();
                $whereInsertPlasma = "WHERE ap = '$ap' AND nama_unit = '$unit'";
            } else {
                DB::table('tb_plasma')->where('ap', $ap)->delete();
                $whereInsertPlasma = "WHERE ap = '$ap'";
            }

            DB::statement("INSERT INTO tb_plasma (nama_plasma, alamat_plasma, provinsi_plasma, kota_or_kabupaten_plasma, kecamatan_plasma, kelurahan_or_desa_plasma, kode_pos_plasma, rt_plasma, rw_plasma, nama_flok, alamat_flok, provinsi_flok, kota_or_kabupaten_flok, kecamatan_flok, kelurahan_or_desa_flok, kode_pos_flok, rt_flok, rw_flok, kota, jumlah_populasi, unit, nama_unit, jenis_kandang, performa, ts, jaminan, npwp, nama_npwp, latitude_gps, nomor_hp, butuh_faktur_pajak, email, ap, nik, id_peternak, tipe_farm) SELECT nama_plasma, alamat_plasma, provinsi_plasma, kota_or_kabupaten_plasma, kecamatan_plasma, kelurahan_or_desa_plasma, kode_pos_plasma, rt_plasma, rw_plasma, nama_flok, alamat_flok, provinsi_flok, kota_or_kabupaten_flok, kecamatan_flok, kelurahan_or_desa_flok, kode_pos_flok, rt_flok, rw_flok, kota, jumlah_populasi, unit, nama_unit, jenis_kandang, performa, ts, jaminan, npwp, nama_npwp, latitude_gps, nomor_hp, butuh_faktur_pajak, email, ap, nik, id_peternak, tipe_farm FROM tb_plasma_temp $whereInsertPlasma");

            if ($roles == 'user') {
                DB::table('tb_plasma_temp')->where('ap', $ap)->where('nama_unit', $unit)->delete();
            } else {
                DB::table('tb_plasma_temp')->where('ap', $ap)->delete();
            }

            return response()->json(['message' => 'success', 'data' => $count], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function gradePlasma(Request $request) {
        set_time_limit(0);
        try {
            $ap = $request->ap;
            $unit = $request->unit;
            $roles = Auth::user()->roles;

            if ($roles == 'user') {
                DB::table('tb_laporan_grade_plasma')->where('ap', $ap)->where('unit', $unit)->delete();
                $response = $this->get_from_api("https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/Ap_apis/laporan_grade_plasma/$ap?unit=$unit");
            } else {
                DB::table('tb_laporan_grade_plasma')->where('ap', $ap)->delete();
                $response = $this->get_from_api("https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/Ap_apis/laporan_grade_plasma/$ap");
            }

            collect($response->data)->map(function ($item) use ($ap) {
                return [
                    'nama_flok' => $item->nama_flok,
                    'unit' => $item->unit,
                    'ap' => $ap,
                    'status' => $item->status,
                    'pop_total' => $item->pop_total,
                    'periode' => $item->periode,
                    'point_periode' => $item->point_periode,
                    'pop_per_periode' => $item->pop_per_periode,
                    'bw' => $item->bw,
                    'fcr' => $item->fcr,
                    'deplesi_persen' => $item->deplesi_persen,
                    'umur' => $item->umur,
                    'adg' => $item->adg,
                    'ip' => $item->ip,
                    'point_ip' => $item->point_ip,
                    'rugi' => $item->rugi,
                    'point_rugi' => $item->point_rugi,
                    'frek_rhpp_rugi' => $item->frek_rhpp_rugi,
                    'persen_rugi' => $item->persen_rugi,
                    'point_persen_rugi' => $item->point_persen_rugi,
                    'rhpp_per_ekor' => $item->rhpp_per_ekor,
                    'point_asli' => $item->point_asli,
                    'point_plus_populasi' => $item->point_plus_populasi,
                    'grade' => $item->grade,
                ];
            })->chunk(500)->each(function ($chunk) {
                DB::table('tb_laporan_grade_plasma')->insert($chunk->toArray());
            });

            return response()->json(['message' => 'success', 'data' => count($response->data)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => 0], 500);
        }
    }

    public function plasmaAktif(Request $request) {
        set_time_limit(0);
        try {
            $ap = $request->ap;
            $unit = $request->unit;
            $roles = Auth::user()->roles;

            if ($roles == 'user') {
                DB::table('tb_database_plasma_aktif')->where('ap', $ap)->where('unit', $unit)->delete();
                $response = $this->get_from_api("https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/ap_apis/database_plasma_aktif/$ap?unit=$unit");
            } else {
                DB::table('tb_database_plasma_aktif')->where('ap', $ap)->delete();
                $response = $this->get_from_api("https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/ap_apis/database_plasma_aktif/$ap");
            }

            collect($response->data)->map(function ($item) use ($ap) {
                return [
                    'ap' => $ap,
                    'unit' => $item->unit,
                    'peternak_id' => $item->peternak_id,
                    'nama_flok' => $item->nama_flok,
                    'populasi' => $item->populasi,
                    'tipe_kandang' => $item->tipe_kandang,
                    'jenis_kandang' => $item->jenis_kandang,
                    'luas_kandang' => $item->luas_kandang,
                    'density' => $item->density,
                    'genset_kapasitas' => $item->genset->kapasitas,
                    'genset_jumlah' => $item->genset->jumlah,
                    'alarm_listrik_mati' => $item->alarm->listrik_mati,
                    'alarm_kipas_mati' => $item->alarm->kipas_mati,
                ];
            })->chunk(500)->each(function ($item) {
                DB::table('tb_database_plasma_aktif')->insert($item->toArray());
            });

            return response()->json(['message' => 'success', 'data' => count($response->data)], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => 0], 500);
        }
    }

    public function prasyaratFlokAktif(Request $request) {
        set_time_limit(0);
        try {
            $ap = $request->input('ap');
            $unit = $request->input('unit');
            $roles = Auth::user()->roles;

            if ($roles == 'user') {
                DB::table('tbl_prasyarat_flok_aktif_temp')->where('ap', $ap)->where('unit', $unit)->delete();
                $url = "https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/ap_apis/flok_aktif_resume_checklist_prasyarat/$ap?unit=$unit";
            } else {
                DB::table('tbl_prasyarat_flok_aktif_temp')->where('ap', $ap)->delete();
                $url = "https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/ap_apis/flok_aktif_resume_checklist_prasyarat/$ap";
            }

            $opts = [
                "http" => [
                    "timeout" => 1200,
                    "method" => "GET",
                    "header" => "Accept: application/json\r\n" .
                                "Content-Type: application/json\r\n" .
                                "X-Api-Key: devmustikaapaccess\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $file = file_get_contents($url, false, $context);
            $arrayData = json_decode($file, true);
            $insert_data = [];
            $count = 0;
            foreach ($arrayData['data'] as $d) {
                $values = [
                    'ap' => $ap,
                    'unit' => $d['unit'],
                    'perusahaan_id' => $d['perusahaan_id'],
                    'perusahaan_name' => $d['perusahaan_name'],
                    'cabang_id' => $d['cabang_id'],
                    'cabang_name' => $d['cabang_name'],
                    'flok_id' => $d['flok_id'],
                    'flok_name' => $d['flok_name'],
                    'kontrak_id' => $d['kontrak_id'],
                    'tanggal_chickin' => $d['tanggal_chickin'],
                    'periode' => $d['periode'],
                    'jenis_kandang' => $d['jenis_kandang'],
                    'nilai_nilai_kandang' => $d['detail_resumes'][0]['nilai'],
                    'tingkat_resiko_nilai_kandang' => $d['detail_resumes'][0]['tingkat_resiko'],
                    'created_at_nilai_kandang' => $d['detail_resumes'][0]['created_at'],
                    'created_by_nilai_kandang' => $d['detail_resumes'][0]['created_by'],
                    'keterangan_nilai_kandang' => $d['detail_resumes'][0]['keterangan'],
                    'nilai_kelistrikan' => $d['detail_resumes'][1]['nilai'],
                    'tingkat_resiko_kelistrikan' => $d['detail_resumes'][1]['tingkat_resiko'],
                    'created_at_kelistrikan' => $d['detail_resumes'][1]['created_at'],
                    'created_by_kelistrikan' => $d['detail_resumes'][1]['created_by'],
                    'keterangan_kelistrikan' => $d['detail_resumes'][1]['keterangan'],
                    'nilai_penggerak_diesel' => $d['detail_resumes'][2]['nilai'],
                    'tingkat_resiko_penggerak_diesel' => $d['detail_resumes'][2]['tingkat_resiko'],
                    'created_at_penggerak_diesel' => $d['detail_resumes'][2]['created_at'],
                    'created_by_penggerak_diesel' => $d['detail_resumes'][2]['created_by'],
                    'keterangan_penggerak_diesel' => $d['detail_resumes'][2]['keterangan'],
                    'nilai_beban_puncak' => $d['detail_resumes'][3]['nilai'],
                    'tingkat_resiko_beban_puncak' => $d['detail_resumes'][3]['tingkat_resiko'],
                    'created_at_beban_puncak' => $d['detail_resumes'][3]['created_at'],
                    'created_by_beban_puncak' => $d['detail_resumes'][3]['created_by'],
                    'keterangan_beban_puncak' => $d['detail_resumes'][3]['keterangan'],
                ];
                $insert_data[] = $values;
                $count = ++$count;
            }
            $insert_data = collect($insert_data);
            $chunks = $insert_data->chunk(1000);
            foreach ($chunks as $chunk) {
                DB::table('tbl_prasyarat_flok_aktif_temp')->insert($chunk->toArray());
            }

            if ($roles == 'user') {
                DB::table('tbl_prasyarat_flok_aktif')->where('ap', $ap)->where('unit', $unit)->delete();
            } else {
                DB::table('tbl_prasyarat_flok_aktif')->where('ap', $ap)->delete();
            }

            $batchSize = 1000;
            $offset = 0;
            do {
                if ($roles == 'user') {
                    $tempData = DB::table('tbl_prasyarat_flok_aktif_temp')->where('ap', $ap)->where('unit', $unit)->offset($offset)->limit($batchSize)->get();
                } else {
                    $tempData = DB::table('tbl_prasyarat_flok_aktif_temp')->where('ap', $ap)->offset($offset)->limit($batchSize)->get();
                }
                $insertData = $tempData->map(function ($item) {
                    return [
                        'ap' => $item->ap,
                        'unit' => $item->unit,
                        'perusahaan_id' => $item->perusahaan_id,
                        'perusahaan_name' => $item->perusahaan_name,
                        'cabang_id' => $item->cabang_id,
                        'cabang_name' => $item->cabang_name,
                        'flok_id' => $item->flok_id,
                        'flok_name' => $item->flok_name,
                        'kontrak_id' => $item->kontrak_id,
                        'tanggal_chickin' => $item->tanggal_chickin,
                        'periode' => $item->periode,
                        'jenis_kandang' => $item->jenis_kandang,
                        'nilai_nilai_kandang' => $item->nilai_nilai_kandang,
                        'tingkat_resiko_nilai_kandang' => $item->tingkat_resiko_nilai_kandang,
                        'created_at_nilai_kandang' => $item->created_at_nilai_kandang,
                        'created_by_nilai_kandang' => $item->created_by_nilai_kandang,
                        'keterangan_nilai_kandang' => $item->keterangan_nilai_kandang,
                        'nilai_kelistrikan' => $item->nilai_kelistrikan,
                        'tingkat_resiko_kelistrikan' => $item->tingkat_resiko_kelistrikan,
                        'created_at_kelistrikan' => $item->created_at_kelistrikan,
                        'created_by_kelistrikan' => $item->created_by_kelistrikan,
                        'keterangan_kelistrikan' => $item->keterangan_kelistrikan,
                        'nilai_penggerak_diesel' => $item->nilai_penggerak_diesel,
                        'tingkat_resiko_penggerak_diesel' => $item->tingkat_resiko_penggerak_diesel,
                        'created_at_penggerak_diesel' => $item->created_at_penggerak_diesel,
                        'created_by_penggerak_diesel' => $item->created_by_penggerak_diesel,
                        'keterangan_penggerak_diesel' => $item->keterangan_penggerak_diesel,
                        'nilai_beban_puncak' => $item->nilai_beban_puncak,
                        'tingkat_resiko_beban_puncak' => $item->tingkat_resiko_beban_puncak,
                        'created_at_beban_puncak' => $item->created_at_beban_puncak,
                        'created_by_beban_puncak' => $item->created_by_beban_puncak,
                        'keterangan_beban_puncak' => $item->keterangan_beban_puncak];
                })->toArray();
                DB::table('tbl_prasyarat_flok_aktif')->insert($insertData);
                $offset += $batchSize;
            } while (!$tempData->isEmpty());

            if ($roles == 'user') {
                DB::table('tbl_prasyarat_flok_aktif_temp')->where('ap', $ap)->where('unit', $unit)->delete();
            } else {
                DB::table('tbl_prasyarat_flok_aktif_temp')->where('ap', $ap)->delete();
            }

            return response()->json(['message' => 'success', 'data' => $count], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'data' => 0], 500);
        }
    }

    public function uploadBuktiRekomendasi(Request $request){
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:1024',
        ], [
            'file.mimes' => 'File harus berupa PDF, DOC, atau DOCX',
            'file.max' => 'Ukuran file tidak boleh lebih dari 1 MB',
        ]);
        $unit = $request->unit;
        $flok = $request->flok;
        $vendor = $request->vendor;
        $file = $request->file;
        $tanggal = $request->tanggal;
        $filename = "REKOMENDASI_{$flok}_UNIT_{$unit}_{$tanggal}." . $file->getClientOriginalExtension();

        $existingFile = DB::table('tbl_po_doc')->where('namapeternak', $flok)->where('unit', $unit)->where('namavendor', $vendor)->value('filebukti');
        if ($existingFile) {
            DB::table('tbl_po_doc')->where('namapeternak', $flok)->where('unit', $unit)->where('namavendor', $vendor)->update([
                'filebukti' => NULL,
            ]);
            $oldFilePath = public_path('bukti-rekomendasi/' . $existingFile);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        $path = $file->move('bukti-rekomendasi/', $filename);
        if (!$path) {
            return response()->json(['error' => 'Gagal mengunggah file'], 500);
        }

        $updated = DB::table('tbl_po_doc')->where('namapeternak', $flok)->where('unit', $unit)->where('namavendor', $vendor)->update([
            'filebukti' => $filename,
        ]);

        if ($updated) {
            return response()->json(['success' => 'File berhasil diupload']);
        } else {
            $newFilePath = public_path('bukti-rekomendasi/' . $filename);
            if (file_exists($newFilePath)) {
                unlink($newFilePath);
            }
            return response()->json(['error' => 'Gagal memperbarui data di database'], 500);
        }
    }

    public function ubahStatusPengiriman(Request $request){
        $unit = $request->unit;
        $flok = $request->flok;
        $vendor = $request->vendor;
        $statuspengiriman = $request->statuspengiriman;

        $updated = DB::table('tbl_po_doc')->where('namavendor', $vendor)->where('namapeternak', $flok)->where('unit', $unit)->update(['statuspengiriman' => $statuspengiriman]);

        if (!$updated) {
            return response()->json(['error' => 'Data tidak ditemukan atau gagal diupdate'], 404);
        }
        return response()->json(['success' => 'Status pengiriman berhasil diperbarui']);
    }

    public function masterRingPoDocPerVendor(Request $request){
        $region = Auth::user()->region;
        $unit = Auth::user()->unit;
        $roles = Auth::user()->roles;
        $jabatan = Auth::user()->jabatan;
        $nik = Auth::user()->nik;

        $regionFilter = $request->regionFilter;
        $unitFilter = $request->unitFilter;

        $no = 0;
        $regions = '';
        $unitSelect = '';
        $masterRingPoDocPerVendor = '';
        $regions = Regions::all();
        if (!empty($regionFilter) || !empty($unitFilter)) {
            $unitSelect = Unit::where('region', $regionFilter)->pluck('kodeunit', 'namaunit');
            $masterRingPoDocPerVendor = MasterRing::query()
                ->when($regionFilter && $regionFilter !== 'SEMUA', fn($query) => $query->where('ap', $regionFilter))
                ->when($unitFilter && $unitFilter !== 'SEMUA', fn($query) => $query->where('unit', $unitFilter))
                ->get();
        }

        return view('dashboard.logdoc.poDocPerVendor.masterRingPoDocPerVendor', compact('region', 'unit', 'roles', 'jabatan', 'nik', 'regionFilter', 'unitFilter', 'no', 'regions', 'unitSelect', 'masterRingPoDocPerVendor'));
    }

    public function masterRingPoDocPerVendorImport(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');
        $nama_file = 'TEMPLATE_MASTER_RING_PENJUALAN_' . $file->hashName();
        $path = $file->storeAs('public/excel/', $nama_file);
        $import = new MasterRingImport();

        MasterRing::truncate();

        try {
            Excel::import($import, storage_path('app/public/excel/' . $nama_file));
            Alert::success('Data Berhasil Diimport!');
        } catch (\Exception $e) {
            Alert::error('Data Gagal Diimport! ' . $e->getMessage());
        }

        Storage::delete($path);
        return redirect()->back();
    }

    public function masterRingPoDocPerVendorEdit($id){
        $data = MasterRing::findOrFail($id);
        return response()->json($data);
    }

    public function masterRingPoDocPerVendorUpdate(Request $request, $id){
        $request->validate([
            'kabupaten' => 'required|string',
            'kecamatan' => 'required|string',
            'unit' => 'required|string',
            'ap' => 'required|string',
            'ring' => 'required|string',
        ]);
        $data = MasterRing::findOrFail($id);
        $data->update($request->only(['kabupaten', 'kecamatan', 'unit', 'ap', 'ring']));
        return response()->json(['message' => 'Data berhasil diperbarui']);
    }

    public function masterRingPoDocPerVendorDelete($id){
        $masterRing = MasterRing::find($id);

        if ($masterRing) {
            $masterRing->delete();
            return response()->json(['message' => 'Master ring unit berhasil dihapus']);
        }

        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    public function deleteRangePoDocPerVendor(Request $request){
        $tanggalAwalDelete = $request->tanggalAwalDelete;
        $tanggalAkhirDelete = $request->tanggalAkhirDelete;

        $tanggalAwalTimestamp = strtotime($tanggalAwalDelete);
        $tanggalAkhirTimestamp = strtotime($tanggalAkhirDelete);

        $poDocs = DB::table('tbl_po_doc')->whereBetween('tanggal', [$tanggalAwalDelete, $tanggalAkhirDelete])->get();

        foreach ($poDocs as $d) {
            $grade = flokToGrade($d->unit, $d->namapeternak);
            $ring = unitToRing($d->unit, $d->alamatkandang);
            $density = flokToDensity($d->unit, $d->namapeternak, strpos($d->namavendor, 'SREEYA') !== false ? $d->jumlahbox / 100 : $d->jumlahbox);
            $prasyarat = flokToPrasyarat($d->unit, $d->namapeternak);
            $peralatanCH = flokToPeralatanCH($d->unit, $d->namapeternak);
            $hasil = flokToHasilPoDoc($grade, $prasyarat['jenis_kandang'], $ring, $prasyarat['total_score'], ($peralatanCH['alarm'] == 'Ada' ? 'Ada' : 'Tidak Ada'), ($peralatanCH['genset'] == 'Ada' ? 'Ada' : 'Tidak Ada'));


            $poDocArray = (array) $d;
            unset($poDocArray['id']);

            $poDocArray['gradeflok'] = $grade;
            $poDocArray['ring'] = $ring;
            $poDocArray['density'] = $density;
            $poDocArray['jeniskandang'] = $prasyarat['jenis_kandang'];
            $poDocArray['prasyaratperkandangan'] = $prasyarat['nilai_kandang'];
            $poDocArray['prasyaratkelistrikan'] = $prasyarat['kelistrikan'];
            $poDocArray['prasyarattotal'] = $prasyarat['total_score'];
            $poDocArray['alatchalarm'] = $peralatanCH['alarm'];
            $poDocArray['alatchgenset'] = $peralatanCH['genset'];
            $poDocArray['hasilpo'] = $hasil;

            DB::table('tbl_po_doc_arsip')->insert($poDocArray);
        }

        DB::table('tbl_po_doc')->whereBetween('tanggal', [$tanggalAwalDelete, $tanggalAkhirDelete])->delete();

        Alert::success('BERHASIL', 'Data PO DOC tanggal '.date("d-m-Y", $tanggalAwalTimestamp).' sampai '.date("d-m-Y", $tanggalAkhirTimestamp).' berhasil diarsipkan')->autoClose(10000);
        return redirect()->back();
    }
}
