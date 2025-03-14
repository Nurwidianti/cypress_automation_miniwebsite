<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
Use App\Role;
use App\Models\Home;
use App\Models\Agenda;
use App\Models\Pengunjung;
use App\Models\ScPerbandingan;
use App\Models\ScPenilaianUnit;
use App\Models\ScPenilaianFlukUnit;
use App\Models\ScPenilaianFlukAp;
use App\Models\Picture;
use App\Models\Regions;
use App\Imports\ScPenilaianUploadImport;
use App\Imports\ScPerbandinganImport;
use App\Imports\AppEstMrgCinImport;
use App\Imports\AppEstMrgPanenImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\DataTables;
use \RealRashid\SweetAlert\Facades\Alert;
use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;
use Image;
use App\Models\MoAnswers;
use App\Models\MoComments;
use App\Models\MoPost;
use App\Helper\ResponseFormatter;
use App\Models\MoNotif;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use View;
use Pdf;


class HomeController extends Controller
{
    public function privacy(){
        return view('privacy', compact('no'));
    }

    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        return view('dashboard.home');
    }


    public function pantauan_flok(){
        $roles = Auth::user()->roles;
        $region = Auth::user()->region;
        $kodeunit = Auth::user()->unit;
        $jabatan = Auth::user()->jabatan;
        $arrJabatan = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        $kosong='';
        if (($roles=='pusat') || ($roles=='admin')){
            $strWhere="";
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'SEMUA' FROM regions ORDER BY koderegion ASC");
            $unit = DB::select("SELECT kodeunit, namaunit FROM units ORDER BY namaunit ASC");
            $countDataTelat = DB::table('vpantauanflok')
                                        ->where('data_telat', '>=', 5)
                                        ->where('keterangan_panen', '=', 'BELUM')
                                        ->count();
            $countRhpp = DB::table('vpantauan_flok_rhpp_rugi')
                                    ->count();
            $countAyamClosing = DB::table('vpantauanflok')
                                        ->where('data_telat', '>=', 7)
                                        ->where('panen_persen', '>=', 98)
                                        ->count();
            $countAyamSakit = DB::table('vpantauanflok')
                                        ->distinct('nama_flok')
                                        ->where('diff_fcr', '>', 0.2)
                                        ->where('harga_rhpp', '<', 0)
                                        ->where('kesehatan', '=', 'SAKIT')
                                        ->where('panen_persen', '<', 40)
                                        ->count();
            $kodeunit='SEMUA';
        }else{
            $strWhere="AND ap='$region'";
            $ap = DB::select("SELECT koderegion, namaregion FROM regions WHERE koderegion='$region' ORDER BY koderegion ASC");
            if (($roles=='region')||($roles=='sr')) {
                $unit = DB::select("SELECT kodeunit, namaunit FROM units WHERE region = '$region ' ORDER BY namaunit ASC");
                $countDataTelat = DB::table('vpantauanflok')
                                        ->where('data_telat', '>=', 5)
                                        ->where('keterangan_panen', '=', 'BELUM')
                                        ->where('ap', '=', $region)
                                        ->count();
                $countRhpp = DB::table('vpantauan_flok_rhpp_rugi')
                                        ->where('ap', '=', $region)
                                        ->count();
                $countAyamClosing = DB::table('vpantauanflok')
                                            ->where('data_telat', '>=', 7)
                                            ->where('panen_persen', '>=', 98)
                                            ->where('ap', '=', $region)
                                            ->count();
                $countAyamSakit = DB::table('vpantauanflok')
                                            ->distinct('nama_flok')
                                            ->where('diff_fcr', '>', 0.2)
                                            ->where('harga_rhpp', '<', 0)
                                            ->where('kesehatan', '=', 'SAKIT')
                                            ->where('panen_persen', '<', 40)
                                            ->where('ap', '=', $region)
                                            ->count();
                $kodeunit='SEMUA';
            }else{
                $unit = DB::select("SELECT kodeunit, namaunit FROM units WHERE kodeunit = '$kodeunit ' ORDER BY namaunit ASC");
                $countDataTelat = DB::table('vpantauanflok')
                                        ->where('data_telat', '>=', 5)
                                        ->where('keterangan_panen', '=', 'BELUM')
                                        ->where('unit', '=', $kodeunit)
                                        ->count();
                $countRhpp = DB::table('vpantauan_flok_rhpp_rugi')
                                        ->where('unit', '=', $kodeunit)
                                        ->count();
                $countAyamClosing = DB::table('vpantauanflok')
                                            ->where('data_telat', '>=', 7)
                                            ->where('panen_persen', '>=', 98)
                                            ->where('unit', '=', $kodeunit)
                                            ->count();
                $countAyamSakit = DB::table('vpantauanflok')
                                            ->distinct('nama_flok')
                                            ->where('diff_fcr', '>', 0.2)
                                            ->where('harga_rhpp', '<', 0)
                                            ->where('kesehatan', '=', 'SAKIT')
                                            ->where('panen_persen', '<', 40)
                                            ->where('unit', '=', $kodeunit)
                                            ->count();
            }

        }


        $sqlDataTelat= DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen FROM vpantauanflok
                                    WHERE data_telat >=5 AND keterangan_panen='BELUM' $strWhere");

        $sqlRhpp= DB::select("SELECT * FROM vpantauan_flok_rhpp_rugi WHERE unit IS NOT NULL $strWhere");

        $sqlAyamClosing= DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen FROM vpantauanflok
                                    WHERE data_telat >= 7 AND panen_persen >= 98 $strWhere");

        $sqlAyamSakit= DB::select("SELECT distinct(nama_flok), unit, ap, populasi_chick_in, ts, bw, (diff_fcr * 100) AS diff_fcr, dpls, harga_rhpp, total, kesehatan, sisa_panen FROM vpantauanflok
                                    WHERE diff_fcr > '0.2' AND harga_rhpp > 0 AND kesehatan='SAKIT' AND panen_persen < 40 $strWhere");

        // yaqin start
        $user = [
            'roles' => $roles,
            'region' => $region,
            'unit' => $kodeunit,
            'jabatan' => $jabatan,
        ];
        $flok_under_bw = $this->flok_under_bw(new Request)->getData();
        $data_tidak_wajar = $this->data_tidak_wajar(new Request)->getData();
        $jumlah_data = ['flok_under_bw' => count($flok_under_bw->data), 'data_tidak_wajar' => count($data_tidak_wajar->data)];
        // yaqin end
        return view('dashboard.pantauan_flok', compact('sqlDataTelat','countDataTelat','sqlRhpp','countRhpp','sqlAyamClosing','countAyamClosing','sqlAyamSakit','countAyamSakit','ap','unit','kodeunit', 'user', 'jumlah_data')); // yaqin edit
    }

    public function send_wa(Request $request){
        $number  = $request->input('number');
        $message  = $request->input('message');
        $nama  = $request->input('nama');
        $kirim = sendWa($number, $message);
        if($kirim){
            return response() -> json([
                                    'success' => true,
                                    'data' => ['number' =>$number,
                                                'nama' =>$nama,
                                                'pesan' => 'Pesan berhasil dikirim'
                                            ]
                                    ]);
        }else{
            return response() -> json([
                                    'success' => false,
                                    'data' => [
                                                'pesan' => 'Gagal dikirim'
                                            ]
                                    ]);
        }

    }

    public function get_ap(Request $request){
        $region  = $request->input('region');
        $kodeunit  = $request->input('kodeunit');
        if($kodeunit !=''){
            $sqlAp = DB::select("SELECT kodeunit FROM units WHERE kodeunit ='$kodeunit'");
        }else{
            $sqlAp = DB::select("SELECT kodeunit FROM units WHERE region ='$region'");
        }
        $arrAp = array();
        foreach($sqlAp as $data){
            array_push($arrAp,[
                'kodeunit' => $data->kodeunit,
            ]);
        }
        return response() -> json($arrAp);
    }

    public function get_ap_wa_rhpp(Request $request){
        $ap  = $request->input('region');
        $kodeunit  = $request->input('kodeunit');

        $sql_1000 = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, bw, harga_rhpp, total FROM vpantauanflok
                                    WHERE bw > 1 AND keterangan_panen='BELUM' AND ap='$ap' AND harga_rhpp <= -1000
                                    ORDER BY harga_rhpp DESC");
        if (!empty($sql_1000)) {
            $list_flok_1000 ="";
            $no=1;
            foreach($sql_1000 as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $unit = $data->unit;
                $list_flok_1000 = $list_flok_1000."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
            }
            $pesan1 = "Hallo,\r\n";
            $pesan1 = $pesan1."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan1 = $pesan1."\r\n*HARUS SEGERA DI PANEN*\r\n";
            $pesan1 = $pesan1.$list_flok_1000."\r\n";
            $pesan1 = $pesan1."\r\nKarena *Estimasi RHPP sudah minus dibawah 1.000 dan BW sudah 1 Kg UP*\r\n";
            $pesan1 = $pesan1."Terimakasih";
            $sql_1000Kontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE ap ='$ap' AND jabatan IN ('ASISTEN DIREKTUR', 'DIREKTUR PT')");

            foreach($sql_1000Kontak as $data){
                sendWa($data->nowa, $pesan1);
            }
        }

        $sql = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, bw, harga_rhpp, total FROM vpantauanflok
                                    WHERE bw BETWEEN <= 1.0 AND keterangan_panen='BELUM' AND ap='$ap' AND harga_rhpp <= -2000
                                    ORDER BY harga_rhpp DESC");
        if (!empty($sql)) {
            $list_flok ="";
            $no=1;
            foreach($sql as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $unit = $data->unit;
                $list_flok = $list_flok."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
            }
            $pesan = "Hallo,\r\n";
            $pesan = $pesan."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan = $pesan."\r\n*HARUS DI PANTAU KETAT*\r\n";
            $pesan = $pesan.$list_flok."\r\n";
            $pesan = $pesan."\r\nKarena *Estimasi RHPP sudah minus dan BW sudah 1 Kg UP*\r\n";
            $pesan = $pesan."Terimakasih";

            $sqlKontak = DB::select("SELECT nowa FROM tblkontak WHERE ap ='$ap' AND jabatan IN ('ASISTEN DIREKTUR', 'DIREKTUR PT')");
            $arrKirim2 = array();
            foreach($sqlKontak as $data){
                sendWa($data->nowa, $pesan);
            }
        }

        $sqlAp = DB::select("SELECT kodeunit FROM units WHERE region ='$ap'");
        $arrAp = array();
        foreach($sqlAp as $data){
            array_push($arrAp,[
                'kodeunit' => $data->kodeunit,
            ]);
        }
        return response() -> json($arrAp);
    }

    public function get_ap_wa_ayam(Request $request){
        $ap  = $request->input('region');
        $sql_1000 = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen FROM vpantauanflok
                                    WHERE data_telat >= 7 AND panen_persen >= 98 AND ap='$ap'");
        if (!empty($sql_1000)) {
            $list_flok_1000 ="";
            $no=1;
            foreach($sql_1000 as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $unit = $data->unit;
                $list_flok_1000 = $list_flok_1000."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
            }
            $pesan1 = "Hallo,\r\n";
            $pesan1 = $pesan1."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan1 = $pesan1."\r\n*SEGERA DI CLOSING*\r\n";
            $pesan1 = $pesan1.$list_flok_1000."\r\n";
            $pesan1 = $pesan1."\r\nKarena *Telat lebih dari 7 hari dan Stok Panen sudah habis*\r\n";
            $pesan1 = $pesan1."Terimakasih";

            $sql_1000Kontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE ap ='$ap' AND jabatan IN ('DIREKTUR PT')");
            foreach($sql_1000Kontak as $data){
                sendWa($data->nowa, $pesan1);
            }
        }

        // $sql = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen FROM vpantauanflok
        //                             WHERE data_telat >= 10 AND sisa_panen < 500 AND ap='$ap'");
        // if (!empty($sql)) {
        //     $list_flok ="";
        //     $no=1;
        //     foreach($sql as $data){
        //         $nama_flok = $data->nama_flok;
        //         $ts = $data->ts;
        //         $unit = $data->unit;
        //         $list_flok = $list_flok."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
        //     }
        //     $pesan = "Hallo,\r\n";
        //     $pesan = $pesan."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
        //     $pesan = $pesan."\r\n*SEGERA DI CLOSING HARI INI*\r\n";
        //     $pesan = $pesan.$list_flok."\r\n";
        //     $pesan = $pesan."\r\nKarena *Telat lebih dari 10 hari dan Stok Panen sudah habis*\r\n";
        //     $pesan = $pesan."Terimakasih";

        //     $sqlKontak = DB::select("SELECT nowa FROM tblkontak WHERE ap ='$ap' AND jabatan IN ('DIREKTUR PT')");
        //     $arrKirim2 = array();
        //     foreach($sqlKontak as $data){
        //         sendWa($data->nowa, $pesan);
        //     }
        // }

        $sqlAp = DB::select("SELECT kodeunit FROM units WHERE region ='$ap'");
        $arrAp = array();
        foreach($sqlAp as $data){
            array_push($arrAp,[
                'kodeunit' => $data->kodeunit,
            ]);
        }
        return response() -> json($arrAp);
    }

    public function send_data_telat_unit(Request $request){
        $kodeunit  = $request->input('kodeunit');
        $sql_1000 = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen, diff_fcr FROM vpantauanflok
                                        WHERE data_telat >= 5 AND keterangan_panen='BELUM' AND unit='$kodeunit'");
        $arrKirim1 = array();
        $arrKirim2 = array();
        if (!empty($sql_1000)) {
            $list_flok_1000 ="";
            $no=1;
            foreach($sql_1000 as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $list_flok_1000 = $list_flok_1000."\r\n".$no++.". ".$nama_flok." *[".$kodeunit."]* ".$ts;
            }
            $pesan1 = "Hallo,\r\n";
            $pesan1 = $pesan1."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan1 = $pesan1.$list_flok_1000."\r\n";
            $pesan1 = $pesan1."\r\n*KUNJUNGI SEKARANG*\r\n";
            $pesan1 = $pesan1."\r\nKarena *SUDAH TELAT > 5 HARI DAN BELUM ADA PANEN SAMA SEKALI*\r\n";
            $pesan1 = $pesan1."Terimakasih";

            $sql_1000Kontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE unit ='$kodeunit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI')
                                        UNION ALL
                                        SELECT nowa, nama FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
                                        WHERE data_telat >= 5 AND keterangan_panen='BELUM' AND unit='$kodeunit')");

            foreach($sql_1000Kontak as $data){
                array_push($arrKirim1,[
                    'number' => $data->nowa,
                    'nama' => $data->nama,
                    'message' =>$pesan1,
                    'delay' =>rand(5,15),
                ]);
            }
        }

        $sql = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen, diff_fcr FROM vpantauanflok
                                        WHERE data_telat >= 5 AND keterangan_panen='BELUM' AND unit='$kodeunit'");
            if (!empty($sql)) {
                $list_flok ="";
                $no=1;
                foreach($sql as $data){
                    $nama_flok = $data->nama_flok;
                    $ts = $data->ts;
                    $list_flok = $list_flok."\r\n".$no++.". ".$nama_flok." *[".$kodeunit."]* ".$ts;
                }
                $pesan = "Hallo,\r\n";
                $pesan = $pesan."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
                $pesan = $pesan.$list_flok."\r\n";
                $pesan = $pesan."\r\n*KUNJUNGI SEKARANG*\r\n";
                $pesan = $pesan."Karena *SUDAH TELAT > 5 HARI DAN BELUM ADA PANEN SAMA SEKALI*\r\n\r\n";
                $pesan = $pesan."Terimakasih";
                $sqlKontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE unit ='$kodeunit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI')
                                            UNION ALL
                                            SELECT nowa, nama FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
                                        WHERE data_telat >= 5 AND keterangan_panen='BELUM' AND unit='$kodeunit')");
                $arrKirim = array();
                foreach($sqlKontak as $data){
                    array_push($arrKirim,[
                        'number' => $data->nowa,
                        'nama' => $data->nama,
                        'message' =>$pesan,
                        'delay' =>rand(5,15),
                    ]);
                }
                return response() -> json($arrKirim);
            }
    }

    public function send_data_rhpp_rugi(Request $request){
        $unit  = $request->input('kodeunit');
        $sql_1000 = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, bw, harga_rhpp, total FROM vpantauanflok
                                    WHERE bw > 1 AND keterangan_panen='BELUM' AND unit='$unit' AND harga_rhpp <= -1000
                                    ORDER BY harga_rhpp DESC");
        $arrKirim1 = array();
        $arrKirim2 = array();
        if (!empty($sql_1000)) {
            $list_flok_1000 ="";
            $no=1;
            foreach($sql_1000 as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $list_flok_1000 = $list_flok_1000."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
            }
            $pesan1 = "Hallo,\r\n";
            $pesan1 = $pesan1."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan1 = $pesan1."\r\n*HARUS SEGERA DI PANEN*\r\n";
            $pesan1 = $pesan1.$list_flok_1000."\r\n";
            $pesan1 = $pesan1."\r\nKarena *Estimasi RHPP sudah minus dibawah 1.000 dan BW sudah 1 Kg UP*\r\n";
            $pesan1 = $pesan1."Terimakasih";

            $sql_1000Kontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE unit ='$unit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI')
                                        UNION ALL
                                    SELECT nowa, nama FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
                                    WHERE bw > 1 AND keterangan_panen='BELUM' AND unit='$unit' AND harga_rhpp <= -1000
                                    ORDER BY harga_rhpp DESC)");

            foreach($sql_1000Kontak as $data){
                array_push($arrKirim1,[
                    'number' => $data->nowa,
                    'nama' => $data->nama,
                    'message' =>$pesan1,
                    'delay' =>rand(5,15),
                ]);
            }
        }

        $sql = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, bw, harga_rhpp, total FROM vpantauanflok
                                    WHERE bw <= 1.0 AND keterangan_panen='BELUM' AND unit='$unit' AND harga_rhpp <= -2000
                                    ORDER BY harga_rhpp DESC");
        if (!empty($sql)) {
            $list_flok ="";
            $no=1;
            foreach($sql as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $list_flok = $list_flok."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
            }
            $pesan = "Hallo,\r\n";
            $pesan = $pesan."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan = $pesan."\r\n*HARUS DI PANTAU KETAT*\r\n";
            $pesan = $pesan.$list_flok."\r\n";
            $pesan = $pesan."\r\nKarena *Estimasi RHPP sudah minus dibawah 2.000 dan BW dibawah 1 Kg*\r\n";
            $pesan = $pesan."Terimakasih";

            $sqlKontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE unit ='$unit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI')
                                            UNION ALL
                                            SELECT nowa, nama FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
                                        WHERE bw <= 1.0 AND keterangan_panen='BELUM' AND unit='$unit' AND harga_rhpp <= -2000
                                        ORDER BY harga_rhpp DESC )");
            $arrKirim2 = array();
            foreach($sqlKontak as $data){
                array_push($arrKirim2,[
                    'number' => $data->nowa,
                    'nama' => $data->nama,
                    'message' =>$pesan,
                    'delay' =>rand(5,15),
                ]);
            }
        }
        return response() -> json(array_merge($arrKirim1,$arrKirim2));
    }

    public function send_data_ayam_closing(Request $request){
        $unit  = $request->input('kodeunit');
        $sql_1000 = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen FROM vpantauanflok
                                    WHERE data_telat >= 7 AND panen_persen >= 98 AND unit='$unit'");
        $arrKirim1 = array();
        $arrKirim2 = array();
        if (!empty($sql_1000)) {
            $list_flok_1000 ="";
            $no=1;
            foreach($sql_1000 as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $list_flok_1000 = $list_flok_1000."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
            }
            $pesan1 = "Hallo,\r\n";
            $pesan1 = $pesan1."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan1 = $pesan1."\r\n*SEGERA DI CLOSING*\r\n";
            $pesan1 = $pesan1.$list_flok_1000."\r\n";
            $pesan1 = $pesan1."\r\nKarena *Telat lebih dari 7 hari dan Stok Panen sudah habis*\r\n";
            $pesan1 = $pesan1."Terimakasih";
            $sql_1000Kontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE unit ='$unit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI')
                                        UNION ALL
                                    SELECT nowa, nama FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
                                    WHERE data_telat >= 7 AND panen_persen >= 98 AND unit='$unit')");

            foreach($sql_1000Kontak as $data){
                array_push($arrKirim1,[
                    'number' => $data->nowa,
                    'nama' => $data->nama,
                    'message' =>$pesan1,
                    'delay' =>rand(5,15),
                ]);
            }
        }

        // $sql = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen FROM vpantauanflok
        //                             WHERE data_telat >= 10 AND panen_persen >= 98 AND unit='$unit'");
        // if (!empty($sql)) {
        //     $list_flok ="";
        //     $no=1;
        //     foreach($sql as $data){
        //         $nama_flok = $data->nama_flok;
        //         $ts = $data->ts;
        //         $list_flok = $list_flok."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
        //     }
        //     $pesan = "Hallo,\r\n";
        //     $pesan = $pesan."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
        //     $pesan = $pesan."\r\n*SEGERA DI CLOSING HARI INI*\r\n";
        //     $pesan = $pesan.$list_flok."\r\n";
        //     $pesan = $pesan."\r\nKarena *Telat lebih dari 10 hari dan Stok Panen sudah habis*\r\n";
        //     $pesan = $pesan."Terimakasih";
        //     $sqlKontak = DB::select("SELECT nowa FROM tblkontak WHERE unit ='$unit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI')
        //                                     UNION ALL
        //                                     SELECT nowa FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
        //                             WHERE data_telat >= 10 AND panen_persen >= 98 AND unit='$unit')");
        //     $arrKirim2 = array();
        //     foreach($sqlKontak as $data){
        //         array_push($arrKirim2,[
        //             'number' => $data->nowa,
        //             'message' =>$pesan,
        //             'delay' =>rand(5,15),
        //         ]);
        //     }
        // }
        return response() -> json(array_merge($arrKirim1,$arrKirim2));
    }

    public function send_data_ayam_sakit(Request $request){
        $unit  = $request->input('kodeunit');
        $sql_1000 = DB::select("SELECT distinct(nama_flok), unit, ap, ts FROM vpantauanflok
                                    WHERE diff_fcr > '0.2' AND harga_rhpp < 0 AND kesehatan='SAKIT' AND panen_persen < 40 AND unit='$unit'");
        $arrKirim1 = array();
        $arrKirim2 = array();
        if (!empty($sql_1000)) {
            $list_flok_1000 ="";
            $no=1;
            foreach($sql_1000 as $data){
                $nama_flok = $data->nama_flok;
                $ts = $data->ts;
                $list_flok_1000 = $list_flok_1000."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
            }
            $pesan1 = "Hallo,\r\n";
            $pesan1 = $pesan1."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
            $pesan1 = $pesan1."\r\n*HARUS SEGERA DI JUAL*\r\n";
            $pesan1 = $pesan1.$list_flok_1000."\r\n";
            $pesan1 = $pesan1."\r\nKarena *Status Ayam SAKIT, RHPP MINUS, dan DIFF FCR > 20 POINT*\r\n";
            $pesan1 = $pesan1."Terimakasih";
            $sql_1000Kontak = DB::select("SELECT nowa, nama FROM tblkontak WHERE unit ='$unit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI','SALES')
                                        UNION ALL
                                    SELECT nowa, nama FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
                                    WHERE diff_fcr > '0.2' AND harga_rhpp < 0 AND kesehatan='SAKIT' AND panen_persen < 40 AND unit='$unit')");

            foreach($sql_1000Kontak as $data){
                array_push($arrKirim1,[
                    'number' => $data->nowa,
                    'nama' => $data->nama,
                    'message' =>$pesan1,
                    'delay' =>rand(5,15),
                ]);
            }
        }

        // $sql = DB::select("SELECT distinct(nama_flok), unit, ap, ts FROM vpantauanflok
        //                             WHERE diff_fcr > '0.2' AND harga_rhpp < 0 AND kesehatan='SAKIT' AND panen_persen < 50 AND bw < 1 AND unit='$unit'");
        // if (!empty($sql)) {
        //     $list_flok ="";
        //     $no=1;
        //     foreach($sql as $data){
        //         $nama_flok = $data->nama_flok;
        //         $ts = $data->ts;
        //         $list_flok = $list_flok."\r\n".$no++.". ".$nama_flok." *[".$unit."]* ".$ts;
        //     }
        //     $pesan = "Hallo,\r\n";
        //     $pesan = $pesan."Kami dari *Tim Monitoring Data* menginformasikan bahwa flok dibawah ini :\r\n";
        //     $pesan = $pesan."\r\n*HARUS SEGERA DI OBATI*\r\n";
        //     $pesan = $pesan.$list_flok."\r\n";
        //     $pesan = $pesan."\r\nKarena *Status Ayam SAKIT, RHPP MINUS, DIFF FCR > 20 POINT DAN BW < 1KG*\r\n";
        //     $pesan = $pesan."Terimakasih";
        //     $sqlKontak = DB::select("SELECT nowa FROM tblkontak WHERE unit ='$unit' AND jabatan IN ('KEPALA UNIT','KEPALA PRODUKSI')
        //                                     UNION ALL
        //                                     SELECT nowa FROM tblkontak WHERE nama IN (SELECT ts FROM vpantauanflok
        //                             WHERE diff_fcr > '0.2' AND harga_rhpp < 0 AND kesehatan='SAKIT' AND panen_persen < 50 AND bw < 1 AND unit='$unit')");
        //     $arrKirim2 = array();
        //     foreach($sqlKontak as $data){
        //         array_push($arrKirim2,[
        //             'number' => $data->nowa,
        //             'message' =>$pesan,
        //             'delay' =>rand(5,15),
        //         ]);
        //     }
        // }
        return response() -> json(array_merge($arrKirim1,$arrKirim2));
    }

    public function data_telat($ap, $unit, Request $request){
        if ($request -> ajax()) {
            if($unit!='SEMUA'){
                $strWhere = " AND ap='$ap' AND unit='$unit'";
            }else{
                if($ap!='SEMUA'){
                    $strWhere = " AND ap='$ap'";
                }else{
                    $strWhere = "";
                }
            }
            $data = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen, ROUND(diff_fcr,1) AS diff_fcr FROM vpantauanflok
                                    WHERE data_telat >=5 AND keterangan_panen='BELUM' $strWhere ORDER BY data_telat DESC");
            return response() -> json(['success' => true, 'data' => $data]);
        }
    }

    public function rhpp_minus($ap, $unit, Request $request){
        if ($request -> ajax()) {
            if($unit!='SEMUA'){
                $strWhere = " AND a.ap='$ap' AND a.unit='$unit'";
            }else{
                if($ap!='SEMUA'){
                    $strWhere = " AND a.ap='$ap'";
                }else{
                    $strWhere = "";
                }
            }
            $data = DB::select("SELECT
                a.*,
                CASE WHEN DATEDIFF(NOW(), b.updated_at) <= 3 THEN b.respon_rhpp_rugi ELSE '' END AS respon_rhpp_rugi
            FROM vpantauan_flok_rhpp_rugi AS a LEFT JOIN tb_respon_data_harian AS b
            ON a.unit = b.unit AND a.nama_flok = b.flok AND a.tanggal_chick_in = b.chick_in
            WHERE a.unit IS NOT NULL $strWhere ORDER BY a.harga_rhpp DESC
            ");
            return response() -> json(['success' => true, 'data' => $data]);
        }
    }

    public function ayam_closing($ap, $unit, Request $request){
        if ($request -> ajax()) {
            if($unit!='SEMUA'){
                $strWhere = " AND ap='$ap' AND unit='$unit'";
            }else{
                if($ap!='SEMUA'){
                    $strWhere = " AND ap='$ap'";
                }else{
                    $strWhere = "";
                }
            }
            $data = DB::select("SELECT nama_flok, unit, ap, populasi_chick_in, ts, data_telat, sisa_panen, ROUND(diff_fcr,1) AS diff_fcr FROM vpantauanflok
                                    WHERE data_telat >= 7 AND panen_persen >= 98 $strWhere ORDER BY data_telat DESC");
            return response() -> json(['success' => true, 'data' => $data]);
        }
    }

    public function ayam_sakit($ap, $unit, Request $request){
        if ($request -> ajax()) {
            if($unit!='SEMUA'){
                $strWhere = " AND a.ap='$ap' AND a.unit='$unit'";
            }else{
                if($ap!='SEMUA'){
                    $strWhere = " AND a.ap='$ap'";
                }else{
                    $strWhere = "";
                }
            }
            $data = DB::select("SELECT
                distinct(a.nama_flok),
                a.unit,
                a.ap,
                a.populasi_chick_in,
                a.ts,
                a.bw,
                ROUND(a.diff_fcr,1) AS diff_fcr,
                a.dpls,
                a.harga_rhpp,
                a.total,
                a.kesehatan,
                a.sisa_panen,
                a.tanggal_chick_in,
                CASE WHEN DATEDIFF(NOW(), b.updated_at) <= 3 THEN b.respon_ayam_sakit ELSE '' END AS respon_ayam_sakit
            FROM vpantauanflok AS a LEFT JOIN tb_respon_data_harian AS b
            ON a.unit = b.unit AND a.nama_flok = b.flok AND a.tanggal_chick_in = b.chick_in
            WHERE a.diff_fcr > '0.2' AND a.harga_rhpp < 0 AND a.kesehatan='SAKIT' AND a.panen_persen < 40 $strWhere
            ORDER BY a.diff_fcr DESC");
            return response() -> json(['success' => true, 'data' => $data]);
        }
    }

    public function agenda(Request $request){
        $tahun = date('Y');
        $data = DB::select("SELECT a.id, a.nama_agenda, a.bagian,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=1 ) AS jan,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=2 ) AS feb,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=3 ) AS mar,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=4 ) AS apr,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=5 ) AS mei,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=6 ) AS jun,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=7 ) AS jul,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=8 ) AS agu,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=9 ) AS sep,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=10 ) AS okt,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=11 ) AS nov,
                            (SELECT DATE(tanggal) AS tanggal FROM table_agenda WHERE id=a.id AND MONTH(tanggal)=12 ) AS des
                            FROM table_agenda a WHERE YEAR(tanggal)='$tahun' ORDER BY id DESC");
        return Datatables::of($data)->make(true);
    }

    public function modal_agenda(Request $request){
        $id = $request->input('id');
        $data = DB::select("SELECT * FROM table_agenda WHERE id='$id'");
        return response()->json($data);
    }

    public function agenda_simpan(Request $request){
        $nik = Auth::user()->nik;
        DB::table('table_agenda')->insert([
            'bagian' => $request->input('bagian'),
            'nama_agenda' => $request->input('nama_agenda'),
            'tanggal' => $request->input('tanggal'),
            'deskripsi' => $request->input('deskripsi'),
            'nik' => $nik,
        ]);

        return response()->json(
            [
            'success' => true,
            'message' => 'Data berhasil disimpan'
            ]
        );
    }

    public function agenda_hapus(Request $request){
        $id = $request->input('id');
        DB::statement("DELETE FROM table_agenda WHERE id='$id'");
        return back()->with('success', 'Agenda berhasil dihapus');
    }

    public function agenda_update(Request $request){
        DB::table('table_agenda')->where('id', $request->input('id'))->update([
            'bagian' => $request->input('bagian'),
            'nama_agenda' => $request->input('nama_agenda'),
            'tanggal' => $request->input('tanggal'),
            'deskripsi' => $request->input('deskripsi'),
        ]);

        return response()->json(
            [
            'success' => true,
            'message' => 'Data berhasil diubah'
            ]
        );
    }

    public function slideshow_list(Request $request){
        $cari = $request->input('cari');
        $batas = 12;
        if($cari != ''){
            $picture = Picture::where([
                ['nama', 'LIKE', '%'.$cari.'%']
                ])->orderBy('id','DESC')->paginate($batas);
            $no = $batas*($picture->currentPage()-1);
            $jml = Picture::where([
                ['nama', 'LIKE', '%'.$cari.'%']
                ])->orderBy('id','DESC')->count();
        }else{
            $picture = Picture::orderBy('id','DESC')->paginate($batas);
            $no = $batas*($picture->currentPage()-1);
            $jml = Picture::orderBy('id','DESC')->count();
        }
        return view('dashboard.listArchive',compact('no', 'picture','jml','cari'));
    }

    public function estmrg(){
        $no = 1;
        $sql = DB::select("SELECT * FROM app_estmrg ORDER BY ap ASC");
        return view('dashboard.estmrg', compact('no','sql'));
    }

    public function uploadSlide(Request $request){
        $request->validate([
          'images' => 'required',
        ]);

        if ($request->hasfile('images')) {
            $images = $request->file('images');

            //$file = new Filesystem;
            //$clearFile = File::cleanDirectory(public_path('slideshow'));
            //$clearSlideshow = Home::truncate();
            //if($clearFile){
                foreach($images as $image) {
                    $slidshow = New Home;
                    $nama = $image->getClientOriginalName();
                    $nama_file = time().'.'.$nama;
                    $image->move('slideshow/',$nama_file);
                    $getJudul = explode(".",$nama);
                    $slidshow->file = $nama_file;
                    $slidshow->nama = $getJudul[0];
                    $slidshow->save();
                }
            //}
        }
        return back()->with('success', 'Images uploaded successfully');
    }

    public function uploadFoto(Request $request){
        $request->validate([
          'images' => 'required',
        ]);

        if ($request->hasfile('images')) {
            $images = $request->file('images');
            foreach($images as $image) {
                    $nama = $image->getClientOriginalName();
                    $image->move('assets/img/users/',$nama);
            }
        }
        return back()->with('success', 'Images uploaded successfully');
    }

    public function uploadPoster(Request $request){
        $request->validate([
          'images' => 'required',
        ]);

        if ($request->hasfile('images')) {
            $images = $request->file('images');

            $nama = $images->getClientOriginalName();
            $nama_file = time().'.'.$nama;
            $move = $images->move('poster/',$nama_file);
            if($move){
                DB::table('table_var_statik')->where('id', 1)->update([
                        'modal_startup' => $nama_file,
                ]);
            }
        }
        Alert::success('Images uploaded successfully');
        return back();
    }

    public function slideshow_hapus($id){
        $hapus = DB::statement("DELETE FROM home_slidshow WHERE file='$id'");
        if($hapus){
            File::delete('slideshow/'.$id);
        }
        return back()->with('success', 'Images berhasil dihapus');
    }

    public function slideshow_edit($id){
        $sql = DB::select("SELECT * FROM home_slidshow WHERE file='$id'");
        foreach($sql as $data){
            $id = $data->id;
            $nama = $data->nama;
            $file = $data->file;
        }
        return view('dashboard.slideshow_edit',compact('id','nama','file'));
    }

    public function slideshow_update(Request $request, $id){
        $slideshow = Home::find($id);
        if($request->has('file')){
            $slideshow->nama = $request->nama;
            $slideshow->id = $request->id;
            $file = $request->file;
            $nama_file = time().'.'.$file->getClientOriginalExtension();
            $file->move('slideshow/',$nama_file);
            $slideshow->file = $nama_file;
        }else{
            $slideshow->nama = $request->nama;
            $slideshow->id = $request->id;
        }
        $slideshow->update();
        return redirect('/home/archive/list')->with('archive','Image berhasil diupdate');
    }

    public function load_app_estmrg_panen($ap){
        $ap = strtolower(kode2ap($ap));
        $sql = DB::select("SELECT *,
        REPLACE(IFNULL((round(nominal/dt_kg,0)), 0), '.', '') AS harga FROM app_estmrg_panen_$ap ORDER BY tgl_do DESC");
        return Datatables::of($sql)->addIndexColumn()->make(true);
    }

    public function load_app_estmrg_cin($ap){
        $ap = strtolower(kode2ap($ap));
        $sql = DB::select("SELECT tgl_cin, unit, ap, flok, ekor_cin, REPLACE(IFNULL(harga, 0), '.', '') AS harga,
                        beli_frc, umur, lv, REPLACE(IFNULL(bw, 0), '.', '.') AS bw, tglpanen,
                        REPLACE(IFNULL(ekpanen, 0), '.', '') AS ekpanen, REPLACE(IFNULL(kgpanen, 0), '.', '') AS kgpanen
                        FROM app_estmrg_vsetcin WHERE ap='$ap' ORDER BY tgl_cin DESC");
        return Datatables::of($sql)->addIndexColumn()->make(true);
    }

    public function estimasimargin_unit_satu($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0

                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0
                                            FROM(".$sqlrms_adj.")a
                                    )");

        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginSatu', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_dua($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1

                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1
                                            FROM(".$sqlrms_adj.")a
                                    )");

        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginDua', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_tiga($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2

                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2
                                            FROM(".$sqlrms_adj.")a
                                    )");

        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginTiga', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_empat($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2,

                                            a.$u3, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u3,
                                            (a.$u3*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u3

                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2,
                                                kg$u3, hrg$u3, (kg$u3*hrg$u3/1000) AS nom$u3
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) AS kg$u3,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u3 END AS hrg$u3

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2,
                                    SUM(kg$u3) AS kg$u3, SUM(harga$u3) AS nom$u3
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u3,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u3

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2,
                        ((nom$u3*1000)/kg$u3) AS hrg$u3
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2,
                                            ((nom$u3*1000)/kg$u3) AS hrg$u3
                                            FROM(".$sqlrms_adj.")a
                                    )");

        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginEmpat', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_lima($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2,

                                            a.$u3, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u3,
                                            (a.$u3*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u3,

                                            a.$u4, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u4,
                                            (a.$u4*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u4
                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2,
                                                kg$u3, hrg$u3, (kg$u3*hrg$u3/1000) AS nom$u3,
                                                kg$u4, hrg$u4, (kg$u4*hrg$u4/1000) AS nom$u4
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) AS kg$u3,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u3 END AS hrg$u3,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) AS kg$u4,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u4 END AS hrg$u4

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2,
                                    SUM(kg$u3) AS kg$u3, SUM(harga$u3) AS nom$u3,
                                    SUM(kg$u4) AS kg$u4, SUM(harga$u4) AS nom$u4
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u3,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u3,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u4,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u4

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2,
                        ((nom$u3*1000)/kg$u3) AS hrg$u3,
                        ((nom$u4*1000)/kg$u4) AS hrg$u4
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2,
                                            ((nom$u3*1000)/kg$u3) AS hrg$u3,
                                            ((nom$u4*1000)/kg$u4) AS hrg$u4
                                            FROM(".$sqlrms_adj.")a
                                    )");

        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginLima', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_enam($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2,

                                            a.$u3, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u3,
                                            (a.$u3*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u3,

                                            a.$u4, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u4,
                                            (a.$u4*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u4,

                                            a.$u5, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u5,
                                            (a.$u5*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u5
                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2,
                                                kg$u3, hrg$u3, (kg$u3*hrg$u3/1000) AS nom$u3,
                                                kg$u4, hrg$u4, (kg$u4*hrg$u4/1000) AS nom$u4,
                                                kg$u5, hrg$u5, (kg$u5*hrg$u5/1000) AS nom$u5
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) AS kg$u3,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u3 END AS hrg$u3,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) AS kg$u4,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u4 END AS hrg$u4,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) AS kg$u5,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u5 END AS hrg$u5

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2,
                                    SUM(kg$u3) AS kg$u3, SUM(harga$u3) AS nom$u3,
                                    SUM(kg$u4) AS kg$u4, SUM(harga$u4) AS nom$u4,
                                    SUM(kg$u5) AS kg$u5, SUM(harga$u5) AS nom$u5
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u3,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u3,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u4,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u4,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u5,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u5
                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2,
                        ((nom$u3*1000)/kg$u3) AS hrg$u3,
                        ((nom$u4*1000)/kg$u4) AS hrg$u4,
                        ((nom$u5*1000)/kg$u5) AS hrg$u5
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2,
                                            ((nom$u3*1000)/kg$u3) AS hrg$u3,
                                            ((nom$u4*1000)/kg$u4) AS hrg$u4,
                                            ((nom$u5*1000)/kg$u5) AS hrg$u5
                                            FROM(".$sqlrms_adj.")a
                                    )");

        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginEnam', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_tujuh($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]); //BTG
        $u1 =  strtolower($arrUnit[1]); //KJN
        $u2 =  strtolower($arrUnit[2]); //PKL
        $u3 =  strtolower($arrUnit[3]); //PML
        $u4 =  strtolower($arrUnit[4]); //PML
        $u5 =  strtolower($arrUnit[5]); //PML
        $u6 =  strtolower($arrUnit[6]); //PML

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2,

                                            a.$u3, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u3,
                                            (a.$u3*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u3,

                                            a.$u4, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u4,
                                            (a.$u4*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u4,

                                            a.$u5, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u5,
                                            (a.$u5*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u5,

                                            a.$u6, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u6,
                                            (a.$u6*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u6
                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2,
                                                kg$u3, hrg$u3, (kg$u3*hrg$u3/1000) AS nom$u3,
                                                kg$u4, hrg$u4, (kg$u4*hrg$u4/1000) AS nom$u4,
                                                kg$u5, hrg$u5, (kg$u5*hrg$u5/1000) AS nom$u5,
                                                kg$u6, hrg$u6, (kg$u6*hrg$u6/1000) AS nom$u6
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) AS kg$u3,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u3 END AS hrg$u3,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) AS kg$u4,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u4 END AS hrg$u4,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) AS kg$u5,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u5 END AS hrg$u5,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) AS kg$u6,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u6 END AS hrg$u6

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2,
                                    SUM(kg$u3) AS kg$u3, SUM(harga$u3) AS nom$u3,
                                    SUM(kg$u4) AS kg$u4, SUM(harga$u4) AS nom$u4,
                                    SUM(kg$u5) AS kg$u5, SUM(harga$u5) AS nom$u5,
                                    SUM(kg$u6) AS kg$u6, SUM(harga$u6) AS nom$u6
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u3,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u3,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u4,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u4,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u5,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u5,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u6,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u6

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2,
                        ((nom$u3*1000)/kg$u3) AS hrg$u3,
                        ((nom$u4*1000)/kg$u4) AS hrg$u4,
                        ((nom$u5*1000)/kg$u5) AS hrg$u5,
                        ((nom$u6*1000)/kg$u6) AS hrg$u6
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2,
                                            ((nom$u3*1000)/kg$u3) AS hrg$u3,
                                            ((nom$u4*1000)/kg$u4) AS hrg$u4,
                                            ((nom$u5*1000)/kg$u5) AS hrg$u5,
                                            ((nom$u6*1000)/kg$u6) AS hrg$u6
                                            FROM(".$sqlrms_adj.")a
                                    )");

        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginTujuh', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_delapan($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);
        $u7 =  strtolower($arrUnit[7]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2,

                                            a.$u3, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u3,
                                            (a.$u3*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u3,

                                            a.$u4, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u4,
                                            (a.$u4*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u4,

                                            a.$u5, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u5,
                                            (a.$u5*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u5,

                                            a.$u6, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u6,
                                            (a.$u6*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u6,

                                            a.$u7, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u7,
                                            (a.$u7*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u7
                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2,
                                                kg$u3, hrg$u3, (kg$u3*hrg$u3/1000) AS nom$u3,
                                                kg$u4, hrg$u4, (kg$u4*hrg$u4/1000) AS nom$u4,
                                                kg$u5, hrg$u5, (kg$u5*hrg$u5/1000) AS nom$u5,
                                                kg$u6, hrg$u6, (kg$u6*hrg$u6/1000) AS nom$u6,
                                                kg$u7, hrg$u7, (kg$u7*hrg$u7/1000) AS nom$u7
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) AS kg$u3,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u3 END AS hrg$u3,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) AS kg$u4,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u4 END AS hrg$u4,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) AS kg$u5,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u5 END AS hrg$u5,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) AS kg$u6,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u6 END AS hrg$u6,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen =a.tanggal) AS kg$u7,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u7 END AS hrg$u7

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2,
                                    SUM(kg$u3) AS kg$u3, SUM(harga$u3) AS nom$u3,
                                    SUM(kg$u4) AS kg$u4, SUM(harga$u4) AS nom$u4,
                                    SUM(kg$u5) AS kg$u5, SUM(harga$u5) AS nom$u5,
                                    SUM(kg$u6) AS kg$u6, SUM(harga$u6) AS nom$u6,
                                    SUM(kg$u7) AS kg$u7, SUM(harga$u7) AS nom$u7
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        //$sqlIdHarga = DB::select("SELECT id FROM master_harga_est WHERE unit IN ('".implode("','",$arrUnit)."')");
        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u3,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u3,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u4,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u4,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u5,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u5,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u6,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u6,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u7' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u7 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u7,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u7' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u7 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u7

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2,
                        ((nom$u3*1000)/kg$u3) AS hrg$u3,
                        ((nom$u4*1000)/kg$u4) AS hrg$u4,
                        ((nom$u5*1000)/kg$u5) AS hrg$u5,
                        ((nom$u6*1000)/kg$u6) AS hrg$u6,
                        ((nom$u7*1000)/kg$u7) AS hrg$u7
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2,
                                            ((nom$u3*1000)/kg$u3) AS hrg$u3,
                                            ((nom$u4*1000)/kg$u4) AS hrg$u4,
                                            ((nom$u5*1000)/kg$u5) AS hrg$u5,
                                            ((nom$u6*1000)/kg$u6) AS hrg$u6,
                                            ((nom$u7*1000)/kg$u7) AS hrg$u7
                                            FROM(".$sqlrms_adj.")a
                                    )");
        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6,
                        (nom$u7/kg$u7)*1000 AS hrg$u7
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6,
                                            SUM(a.kg$u7) AS kg$u7, SUM(nom$u7) AS nom$u7
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6,
                        (nom$u7/kg$u7)*1000 AS hrg$u7
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6,
                                            SUM(a.kg$u7) AS kg$u7, SUM(nom$u7) AS nom$u7
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginDelapan', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_sembilan($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
            $navresume = "";
        }else{
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);
        $u7 =  strtolower($arrUnit[7]);
        $u8 =  strtolower($arrUnit[8]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2,

                                            a.$u3, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u3,
                                            (a.$u3*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u3,

                                            a.$u4, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u4,
                                            (a.$u4*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u4,

                                            a.$u5, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u5,
                                            (a.$u5*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u5,

                                            a.$u6, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u6,
                                            (a.$u6*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u6,

                                            a.$u7, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u7,
                                            (a.$u7*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u7,

                                            a.$u8, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u8,
                                            (a.$u8*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u8

                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2,
                                                kg$u3, hrg$u3, (kg$u3*hrg$u3/1000) AS nom$u3,
                                                kg$u4, hrg$u4, (kg$u4*hrg$u4/1000) AS nom$u4,
                                                kg$u5, hrg$u5, (kg$u5*hrg$u5/1000) AS nom$u5,
                                                kg$u6, hrg$u6, (kg$u6*hrg$u6/1000) AS nom$u6,
                                                kg$u7, hrg$u7, (kg$u7*hrg$u7/1000) AS nom$u7,
                                                kg$u8, hrg$u8, (kg$u8*hrg$u8/1000) AS nom$u8
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) AS kg$u3,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u3 END AS hrg$u3,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) AS kg$u4,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u4 END AS hrg$u4,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) AS kg$u5,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u5 END AS hrg$u5,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) AS kg$u6,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u6 END AS hrg$u6,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen =a.tanggal) AS kg$u7,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u7 END AS hrg$u7,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen =a.tanggal) AS kg$u8,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u8 END AS hrg$u8


                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2,
                                    SUM(kg$u3) AS kg$u3, SUM(harga$u3) AS nom$u3,
                                    SUM(kg$u4) AS kg$u4, SUM(harga$u4) AS nom$u4,
                                    SUM(kg$u5) AS kg$u5, SUM(harga$u5) AS nom$u5,
                                    SUM(kg$u6) AS kg$u6, SUM(harga$u6) AS nom$u6,
                                    SUM(kg$u7) AS kg$u7, SUM(harga$u7) AS nom$u7,
                                    SUM(kg$u8) AS kg$u8, SUM(harga$u8) AS nom$u8
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        //$sqlIdHarga = DB::select("SELECT id FROM master_harga_est WHERE unit IN ('".implode("','",$arrUnit)."')");
        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u3,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u3,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u4,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u4,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u5,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u5,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u6,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u6,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u7' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u7 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u7,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u7' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u7 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u7,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u8' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u8 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u8,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u8' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u8 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u8

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2,
                        ((nom$u3*1000)/kg$u3) AS hrg$u3,
                        ((nom$u4*1000)/kg$u4) AS hrg$u4,
                        ((nom$u5*1000)/kg$u5) AS hrg$u5,
                        ((nom$u6*1000)/kg$u6) AS hrg$u6,
                        ((nom$u7*1000)/kg$u7) AS hrg$u7,
                        ((nom$u8*1000)/kg$u8) AS hrg$u8
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2,
                                            ((nom$u3*1000)/kg$u3) AS hrg$u3,
                                            ((nom$u4*1000)/kg$u4) AS hrg$u4,
                                            ((nom$u5*1000)/kg$u5) AS hrg$u5,
                                            ((nom$u6*1000)/kg$u6) AS hrg$u6,
                                            ((nom$u7*1000)/kg$u7) AS hrg$u7,
                                            ((nom$u8*1000)/kg$u8) AS hrg$u8
                                            FROM(".$sqlrms_adj.")a
                                    )");
        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6,
                        (nom$u7/kg$u7)*1000 AS hrg$u7,
                        (nom$u8/kg$u8)*1000 AS hrg$u8
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6,
                                            SUM(a.kg$u7) AS kg$u7, SUM(nom$u7) AS nom$u7,
                                            SUM(a.kg$u8) AS kg$u8, SUM(nom$u8) AS nom$u8
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6,
                        (nom$u7/kg$u7)*1000 AS hrg$u7,
                        (nom$u8/kg$u8)*1000 AS hrg$u8
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6,
                                            SUM(a.kg$u7) AS kg$u7, SUM(nom$u7) AS nom$u7,
                                            SUM(a.kg$u8) AS kg$u8, SUM(nom$u8) AS nom$u8
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginSembilan', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimargin_unit_sepuluh($tab, $ap){
        $year = date('Y');
        $kodeap = $ap;
        $ap = strtolower(kode2ap($ap));
        $tanggal = getTglRealPanen($ap);

        if($tab==1){
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }elseif($tab==2){
            $navcin = "active";
            $navpfmc="";
            $navest = "";
            $navpanen = "";
        }elseif($tab==3){
            $navcin = "";
            $navpfmc="";
            $navest = "active";
            $navpanen = "";
        }elseif($tab==4){
            $navcin = "";
            $navpfmc="";
            $navest = "";
            $navpanen = "active";
        }else{
            $navcin = "";
            $navpfmc="active";
            $navest = "";
            $navpanen = "";
        }

        $sqlAdj = DB::select("SELECT harga_pakan FROM app_estmrg_adjust_value");
        foreach ($sqlAdj AS $data){
            $harga_pakan = $data->harga_pakan;
        }

        $pfmc = DB::select("SELECT * FROM app_estmrg_vpfmc");
        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
        $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);

        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);
        $u7 =  strtolower($arrUnit[7]);
        $u8 =  strtolower($arrUnit[8]);
        $u9 =  strtolower($arrUnit[9]);

        $app_estmrg_vhrg = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_$ap AS (
                                        SELECT a.id, a.tglawal, a.tglakhir,
                                            a.$u0, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u0,
                                            (a.$u0*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u0,

                                            a.$u1, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u1,
                                            (a.$u1*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u1,

                                            a.$u2, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u2,
                                            (a.$u2*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u2,

                                            a.$u3, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u3,
                                            (a.$u3*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u3,

                                            a.$u4, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u4,
                                            (a.$u4*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u4,

                                            a.$u5, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u5,
                                            (a.$u5*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u5,

                                            a.$u6, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u6,
                                            (a.$u6*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u6,

                                            a.$u7, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u7,
                                            (a.$u7*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u7,

                                            a.$u8, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u8,
                                            (a.$u8*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u8,

                                            a.$u9, (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u9' AND tglpanen BETWEEN a.tglawal AND a.tglakhir) AS kg$u9,
                                            (a.$u9*(SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u9' AND tglpanen BETWEEN a.tglawal AND a.tglakhir)/1000) AS harga$u9
                                            FROM app_estmrg_hrg_$ap a
                                    )");

        $app_estmrg_vhrg_bantu = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_vhrg_bantu_$ap AS (
                                            SELECT tanggal,
                                                kg$u0, hrg$u0, (kg$u0*hrg$u0/1000) AS nom$u0,
                                                kg$u1, hrg$u1, (kg$u1*hrg$u1/1000) AS nom$u1,
                                                kg$u2, hrg$u2, (kg$u2*hrg$u2/1000) AS nom$u2,
                                                kg$u3, hrg$u3, (kg$u3*hrg$u3/1000) AS nom$u3,
                                                kg$u4, hrg$u4, (kg$u4*hrg$u4/1000) AS nom$u4,
                                                kg$u5, hrg$u5, (kg$u5*hrg$u5/1000) AS nom$u5,
                                                kg$u6, hrg$u6, (kg$u6*hrg$u6/1000) AS nom$u6,
                                                kg$u7, hrg$u7, (kg$u7*hrg$u7/1000) AS nom$u7,
                                                kg$u8, hrg$u8, (kg$u8*hrg$u8/1000) AS nom$u8,
                                                kg$u9, hrg$u9, (kg$u9*hrg$u9/1000) AS nom$u9
                                                FROM(
                                                SELECT a.tanggal, a.tglawal,
                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) AS kg$u0,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u0' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u0 END AS hrg$u0,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) AS kg$u1,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u1' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u1 END AS hrg$u1,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) AS kg$u2,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u2' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u2 END AS hrg$u2,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) AS kg$u3,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u3' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u3 END AS hrg$u3,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) AS kg$u4,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u4' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u4 END AS hrg$u4,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) AS kg$u5,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u5' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u5 END AS hrg$u5,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) AS kg$u6,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u6' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u6 END AS hrg$u6,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen =a.tanggal) AS kg$u7,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u7' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u7 END AS hrg$u7,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen =a.tanggal) AS kg$u8,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u8' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u8 END AS hrg$u8,

                                                    (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u9' AND tglpanen =a.tanggal) AS kg$u9,
                                                    CASE WHEN (SELECT SUM(kgpanen)/1000 FROM app_estmrg_vsetcin WHERE unit='$u9' AND tglpanen =a.tanggal) IS NULL THEN 0
                                                    ELSE b.$u9 END AS hrg$u9

                                                FROM app_estmrg_master_date a
                                                LEFT JOIN app_estmrg_hrg_$ap b ON b.tglawal=a.tglawal)b
                                    )");

        $estharga = DB::select("SELECT * FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year'");
        $estharga_bulan = DB::select("SELECT MONTH(tglawal) AS tglawal,
                                    SUM(kg$u0) AS kg$u0, SUM(harga$u0) AS nom$u0,
                                    SUM(kg$u1) AS kg$u1, SUM(harga$u1) AS nom$u1,
                                    SUM(kg$u2) AS kg$u2, SUM(harga$u2) AS nom$u2,
                                    SUM(kg$u3) AS kg$u3, SUM(harga$u3) AS nom$u3,
                                    SUM(kg$u4) AS kg$u4, SUM(harga$u4) AS nom$u4,
                                    SUM(kg$u5) AS kg$u5, SUM(harga$u5) AS nom$u5,
                                    SUM(kg$u6) AS kg$u6, SUM(harga$u6) AS nom$u6,
                                    SUM(kg$u7) AS kg$u7, SUM(harga$u7) AS nom$u7,
                                    SUM(kg$u8) AS kg$u8, SUM(harga$u8) AS nom$u8,
                                    SUM(kg$u9) AS kg$u9, SUM(harga$u9) AS nom$u9
                                    FROM app_estmrg_vhrg_$ap WHERE YEAR(tglawal)='$year' GROUP BY MONTH(tglawal) ASC");

        //$sqlIdHarga = DB::select("SELECT id FROM master_harga_est WHERE unit IN ('".implode("','",$arrUnit)."')");
        $sqlIdHarga = DB::select("SELECT id FROM app_estmrg_hrg_$ap");
        $arrIdHarga = array_map(function ($object) { return $object->id; }, $sqlIdHarga);


        $sqlrms_adj = "SELECT a.tanggal,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u0,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u0' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u0 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u0,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u1,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u1' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u1 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u1,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u2,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u2' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u2 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u2,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u3,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u3' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u3 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u3,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u4,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u4' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u4 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u4,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u5,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u5' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u5 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u5,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u6,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u6' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u6 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u6,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u7' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u7 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u7,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u7' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u7 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u7,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u8' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u8 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u8,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u8' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u8 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u8,

                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(dt_kg) FROM app_estmrg_panen_$ap WHERE unit='$u9' AND tgl_do = a.tanggal)/1000
                    ELSE (SELECT kg$u9 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS kg$u9,
                    (CASE WHEN a.tanggal <= '$tanggal' THEN
                        (SELECT SUM(nominal) FROM app_estmrg_panen_$ap WHERE unit='$u9' AND tgl_do = a.tanggal)/1000000
                    ELSE (SELECT nom$u9 FROM app_estmrg_vhrg_bantu_$ap WHERE tanggal=a.tanggal) END) AS nom$u9

                    FROM app_estmrg_master_date a WHERE YEAR(a.tanggal) = $year";

        $rmsbantu_adj = DB::select("SELECT *,
                        ((nom$u0*1000)/kg$u0) AS hrg$u0,
                        ((nom$u1*1000)/kg$u1) AS hrg$u1,
                        ((nom$u2*1000)/kg$u2) AS hrg$u2,
                        ((nom$u3*1000)/kg$u3) AS hrg$u3,
                        ((nom$u4*1000)/kg$u4) AS hrg$u4,
                        ((nom$u5*1000)/kg$u5) AS hrg$u5,
                        ((nom$u6*1000)/kg$u6) AS hrg$u6,
                        ((nom$u7*1000)/kg$u7) AS hrg$u7,
                        ((nom$u8*1000)/kg$u8) AS hrg$u8,
                        ((nom$u9*1000)/kg$u9) AS hrg$u9
                        FROM(".$sqlrms_adj.")a");

        $temp_adj = DB::statement("CREATE TEMPORARY TABLE IF NOT EXISTS app_estmrg_temp_adj_bantu_$ap AS (
                                        SELECT *,
                                            ((nom$u0*1000)/kg$u0) AS hrg$u0,
                                            ((nom$u1*1000)/kg$u1) AS hrg$u1,
                                            ((nom$u2*1000)/kg$u2) AS hrg$u2,
                                            ((nom$u3*1000)/kg$u3) AS hrg$u3,
                                            ((nom$u4*1000)/kg$u4) AS hrg$u4,
                                            ((nom$u5*1000)/kg$u5) AS hrg$u5,
                                            ((nom$u6*1000)/kg$u6) AS hrg$u6,
                                            ((nom$u7*1000)/kg$u7) AS hrg$u7,
                                            ((nom$u8*1000)/kg$u8) AS hrg$u8,
                                            ((nom$u9*1000)/kg$u9) AS hrg$u9
                                            FROM(".$sqlrms_adj.")a
                                    )");
        if($temp_adj){
            $adj_penjualan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6,
                        (nom$u7/kg$u7)*1000 AS hrg$u7,
                        (nom$u8/kg$u8)*1000 AS hrg$u8,
                        (nom$u9/kg$u9)*1000 AS hrg$u9
                        FROM(
                            SELECT b.tglawal, MAX(a.tanggal) AS tglakhir,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6,
                                            SUM(a.kg$u7) AS kg$u7, SUM(nom$u7) AS nom$u7,
                                            SUM(a.kg$u8) AS kg$u8, SUM(nom$u8) AS nom$u8,
                                            SUM(a.kg$u9) AS kg$u9, SUM(nom$u9) AS nom$u9
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY b.tglawal
                        )a");
            //dd($adj_penjualan);
        }

                $adj_penjualan_perbulan = DB::select("SELECT *,
                        (nom$u0/kg$u0)*1000 AS hrg$u0,
                        (nom$u1/kg$u1)*1000 AS hrg$u1,
                        (nom$u2/kg$u2)*1000 AS hrg$u2,
                        (nom$u3/kg$u3)*1000 AS hrg$u3,
                        (nom$u4/kg$u4)*1000 AS hrg$u4,
                        (nom$u5/kg$u5)*1000 AS hrg$u5,
                        (nom$u6/kg$u6)*1000 AS hrg$u6,
                        (nom$u7/kg$u7)*1000 AS hrg$u7,
                        (nom$u8/kg$u8)*1000 AS hrg$u8,
                        (nom$u9/kg$u9)*1000 AS hrg$u9
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                                            SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                            SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                            SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                            SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                            SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4,
                                            SUM(a.kg$u5) AS kg$u5, SUM(nom$u5) AS nom$u5,
                                            SUM(a.kg$u6) AS kg$u6, SUM(nom$u6) AS nom$u6,
                                            SUM(a.kg$u7) AS kg$u7, SUM(nom$u7) AS nom$u7,
                                            SUM(a.kg$u8) AS kg$u8, SUM(nom$u8) AS nom$u8,
                                            SUM(a.kg$u9) AS kg$u9, SUM(nom$u9) AS nom$u9
                            FROM app_estmrg_temp_adj_bantu_$ap a
                            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c");

        $bulan = DB::select("SELECT kode, bulan FROM bulan");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        return view('dashboard.estimasiMarginSepuluh', compact('arrUnit','tanggal','navcin','navest','navpfmc','navpanen','pfmc','estharga','estharga_bulan',
                                                        'arrIdHarga','rmsbantu_adj','adj_penjualan','adj_penjualan_perbulan','harga_pakan',
                                                        'bulan', 'resumeap','kodeap'));
    }

    public function estimasimarginUpdateHarga(Request $request){
        $idharga = $request->input('idharga');
        $harga = $request->input('harga');
        $kolom = $request->input('kolom');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_$ap SET $kolom='$harga' WHERE id='$idharga'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaLan(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_lan SET skh='$harga', sgn='$harga', wng='$harga', mtg='$harga', klt='$harga', gml='$harga', kra='$harga', jmr='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaMmb(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_mmb SET smg='$harga', bja='$harga', ung='$harga', gdo='$harga', dmk='$harga', kla='$harga', bdl='$harga', bsw='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaMpu(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_mpu SET lsr='$harga', ptr='$harga', idm='$harga', cia='$harga', ppt='$harga', crb='$harga', pdg='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaMjr(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_mjr SET blr='$harga', rbg='$harga', bjo='$harga', pti='$harga', kds='$harga', jpr='$harga', pwd='$harga', tbn='$harga', gsk='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaSga(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_sga SET bwn='$harga', slg='$harga', grb='$harga', krd='$harga', byl='$harga', bsn='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaBru(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_bru SET smd='$harga', sbg='$harga', mjk='$harga', cjr='$harga', bdg='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaMum(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_mum SET btl='$harga', klp='$harga', gkd='$harga', smn='$harga', kta='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaAil(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_ail SET btg='$harga', kjn='$harga', pml='$harga', pkl='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaKsm(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_ksm SET mgt='$harga', mdn='$harga', png='$harga', ngw='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaKlb(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_klb SET kbm='$harga', mgl='$harga', tmg='$harga', wnb='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaGps(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_gps SET pbg='$harga', bjn='$harga', pwt='$harga', clp='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaBtb(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_btb SET bma='$harga', brb='$harga', tgl='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimarginUpdateHargaLsw(Request $request){
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');
        $harga = $request->input('harga');
        $kodeap = $request->input('kodeap');
        $ap = strtolower(kode2ap($kodeap));
        $update = DB::statement("UPDATE app_estmrg_hrg_lsw SET kdr='$harga', jbg='$harga'
                                WHERE tglawal = '$tglawal' AND tglakhir = '$tglakhir'");
        $view = 'estimasimargin_unit_'.est_mrg_ap2home(strtoupper($ap));
        return $this->$view(3,$kodeap);
    }

    public function estimasimargin_cleartemp(Request $request){
        $ap = kode2ap($request->input('ap'));
        $table = strtolower('app_estmrg_panen_'.$ap);
        $sql = DB::statement("DELETE FROM $table WHERE ap = '$ap'");
        return $ap;
    }

    public function get_realisasi_panen(Request $request){
        $ap  = kode2ap($request->input('ap'));
        $tglawal  = $request->input('tglawal');
        $tglakhir  = $request->input('tglakhir');
        return getUrlRealisasi($ap,$tglawal,$tglakhir);
    }

    public function realisasi_panen_insert(Request $request){
        $tglawal  = $request->input('tglawal');
        $tglakhir  = $request->input('tglakhir');
        $ap = kode2ap($request->input('ap'));
        return insertRealisasiEstMrg($ap, $tglawal, $tglakhir);
    }

    public function estimasimarginSetcinImport(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);
        $kodeap = $request->input('kodeap');
        $ap = kode2ap($request->input('kodeap'));
        $file = $request->file('file');
        $nama_file = 'ESTMRG_CIN'.$file->hashName();
        $path = $file->storeAs('public/excel/',$nama_file);
        $hapus = DB::statement("DELETE FROM app_estmrg_setcin WHERE ap='$ap'");
        if($hapus){
            $import = Excel::import(new AppEstMrgCinImport(), storage_path('app/public/excel/'.$nama_file));
        }else{
            $import = Excel::import(new AppEstMrgCinImport(), storage_path('app/public/excel/'.$nama_file));
        }
        Storage::delete($path);

        if($import) {
            Alert::toast('Data Berhasil Diimport!', 'success');
        } else {
            Alert::toast('Data Gagal Diimport!', 'danger');
        }
        $view = 'estimasimargin_unit_'.est_mrg_ap2home($ap);
        return $this->$view(2,$kodeap);
    }

    public function estimasimarginSetcinAilImport(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);
        $kodeap = $request->input('kodeap');
        $ap = kode2ap($request->input('kodeap'));
        $file = $request->file('file');
        $nama_file = 'ESTMRG_CIN'.$file->hashName();
        $path = $file->storeAs('public/excel/',$nama_file);
        $hapus = DB::statement("DELETE FROM app_estmrg_setcin WHERE ap='$ap'");
        if($hapus){
            $import = Excel::import(new AppEstMrgCinAilImport(), storage_path('app/public/excel/'.$nama_file));
        }else{
            $import = Excel::import(new AppEstMrgCinAilImport(), storage_path('app/public/excel/'.$nama_file));
        }
        Storage::delete($path);

        if($import) {
            Alert::toast('Data Berhasil Diimport!', 'success');
        } else {
            Alert::toast('Data Gagal Diimport!', 'danger');
        }
        $view = 'estimasimargin'.strtolower($ap);
        return $this->$view(2,$kodeap);
    }

    public function estimasimarginSetcinBruImport(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);
        $kodeap = $request->input('kodeap');
        $file = $request->file('file');
        $nama_file = 'ESTMRG_CIN'.$file->hashName();
        $path = $file->storeAs('public/excel/',$nama_file);
        $hapus = DB::statement("DELETE FROM app_estmrg_setcin WHERE ap='$ap'");
        if($hapus){
            $import = Excel::import(new AppEstMrgCinAilImport(), storage_path('app/public/excel/'.$nama_file));
        }else{
            $import = Excel::import(new AppEstMrgCinAilImport(), storage_path('app/public/excel/'.$nama_file));
        }
        Storage::delete($path);

        if($import) {
            Alert::toast('Data Berhasil Diimport!', 'success');
        } else {
            Alert::toast('Data Gagal Diimport!', 'danger');
        }
        $view = 'estimasimargin'.strtolower($ap);
        return $this->$view(2,$kodeap);
    }

    public function estimasimarginPanenImport(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);
        $kodeap = $request->input('kodeap');
        $ap = kode2ap($request->input('kodeap'));
        $file = $request->file('file');
        $nama_file = 'ESTMRG_PANEN'.$file->hashName();
        $path = $file->storeAs('public/excel/',$nama_file);
        $hapus = DB::statement("DELETE FROM app_estmrg_panen WHERE ap='$ap'");
        if($hapus){
            $import = Excel::import(new AppEstMrgPanenImport(), storage_path('app/public/excel/'.$nama_file));
        }else{
            $import = Excel::import(new AppEstMrgPanenImport(), storage_path('app/public/excel/'.$nama_file));
        }
        Storage::delete($path);

        if($import) {
            Alert::toast('Data Berhasil Diimport!', 'success');
        } else {
            Alert::toast('Data Gagal Diimport!', 'danger');
        }
        return $this->estimasimargin(4,$kodeap);
    }

    public function estimasimarginCreateSource(Request $request){
        $jabatan = Auth::user()->jabatan;
        $region = Auth::user()->region;
        $kosong = '';
        $roles = Auth::user()->roles;
        $navpfmc="";
        $navest = "active";
        $koderegion = $request->input('region');

        if(($roles == 'pusat') || ($roles == 'admin')){
            $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('$kosong'), 'PILIH' FROM regions ORDER BY koderegion ASC");
        }else{
            $ap = DB::select('SELECT koderegion, namaregion FROM regions WHERE koderegion = "'.$region.'" ORDER BY koderegion ASC');
        }

        $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$koderegion'");
        foreach ($sqlUnit as $data) {
            $insert = DB::statement("INSERT INTO master_harga_est (tglawal, tglakhir, unit)
                                SELECT tglawal, tglakhir, '$data->kodeunit' as unit
                                FROM master_date_est");
        }

        $pfmc = DB::select("SELECT * FROM vpfmc");
        $setdoc = DB::select("SELECT a.tgl_setting, a.unit, a.ap, a.flok, a.qty, (a.value_total_beli/a.qty) AS harga, a.value_total_beli,
                            FLOOR(b.umur) AS umur, ROUND((100-b.dpls),0) AS lv, b.bw,
                            DATE_ADD(tgl_setting,INTERVAL FLOOR(b.umur)+1 DAY) AS tglpanen
                            FROM table_set_doc a
                            LEFT JOIN vpfmc b ON b.unit = a.unit
                            WHERE a.tgl_setting BETWEEN '2022-05-03' AND '2022-07-14' ORDER BY a.tgl_setting ASC");

        $realpanen = DB::select("SELECT * FROM table_real_panen WHERE tgl_do BETWEEN '2022-06-27' AND '2022-07-15'");
        return view('dashboard.estimasiMargin', compact('navest','navpfmc','setdoc','pfmc','ap','realpanen'));
    }

    public function estimasimarginResumeExcel($ap){
        $ap = kode2ap($ap);
        $semester2 = DB::select("SELECT * FROM bulan WHERE kode BETWEEN 7 AND 12");
        $resumeap = DB::select("SELECT a.kodeunit, a.region FROM units a WHERE a.region='$ap' ORDER BY kodeunit ASC");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:P1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'ESTIMASI MARGIN TAHUN 2022');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A3', 'NO');
            $spreadsheet->getActiveSheet()->getStyle('A3:P4')->applyFromArray(setHeader());
            $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(30);

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A3', 'NO');
            $spreadsheet->getActiveSheet()->mergeCells('A3:A4');
            $sheet->setCellValue('B3', 'UNIT');
            $spreadsheet->getActiveSheet()->mergeCells('B3:B4');
            $sheet->setCellValue('C3', 'AP');
            $spreadsheet->getActiveSheet()->mergeCells('C3:C4');
            $sheet->setCellValue('D3', 'BLN');
            $spreadsheet->getActiveSheet()->mergeCells('D3:D4');
            $sheet->setCellValue('E3', 'PANEN');
            $sheet->setCellValue('E4', 'KG');
            $sheet->setCellValue('F3', 'PFMC');
            $spreadsheet->getActiveSheet()->mergeCells('F3:H3');
            $sheet->setCellValue('F4', 'BW');
            $sheet->setCellValue('G4', 'FCR');
            $sheet->setCellValue('H4', 'DPL');
            $sheet->setCellValue('I3', 'HARGA BELI');
            $spreadsheet->getActiveSheet()->mergeCells('I3:L3');
            $sheet->setCellValue('I4', 'DOC');
            $sheet->setCellValue('J4', 'PAKAN');
            $sheet->setCellValue('K4', 'OVK');
            $sheet->setCellValue('L4', 'RHPP');
            $sheet->setCellValue('M3', 'HPP');
            $spreadsheet->getActiveSheet()->mergeCells('M3:M4');
            $sheet->setCellValue('N3', 'HARGA LB');
            $spreadsheet->getActiveSheet()->mergeCells('N3:N4');
            $sheet->setCellValue('O3', 'EST MARGIN');
            $spreadsheet->getActiveSheet()->mergeCells('O3:P3');
            $sheet->setCellValue('O4', '/KG');
            $sheet->setCellValue('P4', 'NOM');

            $spreadsheet->getActiveSheet()->mergeCells('R1:W1');
            $spreadsheet->getActiveSheet()->setCellValue('R1', 'RUMUS BANTU');
            $spreadsheet->getActiveSheet()->getStyle('R1')->applyFromArray(setTittle());

            $spreadsheet->getActiveSheet()->getStyle('R3:W4')->applyFromArray(setHeader());
            $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(30);
            $sheet->setCellValue('R3', 'PFMC');
            $spreadsheet->getActiveSheet()->mergeCells('R3:T3');
            $sheet->setCellValue('R4', 'BW');
            $sheet->setCellValue('S4', 'FCR');
            $sheet->setCellValue('T4', 'DPL');
            $sheet->setCellValue('U3', 'HARGA');
            $spreadsheet->getActiveSheet()->mergeCells('U3:W3');
            $sheet->setCellValue('U4', 'PAKAN');
            $sheet->setCellValue('V4', 'OVK');
            $sheet->setCellValue('W4', 'RHPP');


            $no=1;
            $totbw=0;
            $totfcr=0;
            $totdpls=0;
            $totpakan=0;
            $totovk=0;
            $totrhpp=0;
            $totestnom=0;
            $row = 2;
            foreach ($resumeap as $res){
                $hargalb = getHargaLb($res->kodeunit, 7);
                $hpp = getHpp($res->kodeunit, 7);
                $kgpanen = getKgPanen($res->kodeunit, 7);
                $estkgpanen = $hargalb-$hpp;
                $totbw += round(getBwPfmc($res->kodeunit)*$kgpanen,0);
                $totfcr += round(getFcrPfmc($res->kodeunit)*$kgpanen,0);
                $totdpls += round(getDplsPfmc($res->kodeunit)*$kgpanen,0);
                $totpakan += round(getPakanPfmc($res->kodeunit)*$kgpanen,0);
                $totovk += round(getOvkPfmc($res->kodeunit)*$kgpanen,0);
                $totrhpp += round(getRhppPfmc($res->kodeunit)*$kgpanen,0);
                $totestnom +=($kgpanen*$estkgpanen*1000)/1000000;

                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $res->kodeunit);
                $sheet->setCellValue('C' . $row, $res->region);
                $sheet->setCellValue('D' . $row, 7);
                $sheet->setCellValue('E' . $row, $kgpanen);
                $sheet->setCellValue('F' . $row, number_indo_excel_koma1(getBwPfmc($res->kodeunit)));
                $sheet->setCellValue('G' . $row, number_indo_excel_koma1(getFcrPfmc($res->kodeunit)));
                $sheet->setCellValue('H' . $row, number_indo_excel_koma1(getDplsPfmc($res->kodeunit)));
                $sheet->setCellValue('I' . $row, number_indo_excel_koma1(getDocPfmc($res->kodeunit, 7)));
                $sheet->setCellValue('J' . $row, number_indo_excel_koma1(getPakanPfmc($res->kodeunit)));
                $sheet->setCellValue('K' . $row, number_indo_excel_koma1(getOvkPfmc($res->kodeunit)));
                $sheet->setCellValue('L' . $row, number_indo_excel_koma1(getRhppPfmc($res->kodeunit)));
                $sheet->setCellValue('M' . $row, number_indo_excel_koma1($hpp));
                $sheet->setCellValue('N' . $row, number_indo_excel($hargalb));
                $sheet->setCellValue('O' . $row, number_indo_excel($estkgpanen));
                $sheet->setCellValue('P' . $row, number_indo_excel(($kgpanen*$estkgpanen*1000)/1000000));
                $sheet->setCellValue('Q' . $row, number_indo_excel(getBwPfmc($res->kodeunit)*$kgpanen));
                $sheet->setCellValue('R' . $row, number_indo_excel(getFcrPfmc($res->kodeunit)*$kgpanen));
                $sheet->setCellValue('S' . $row, number_indo_excel(getDplsPfmc($res->kodeunit)*$kgpanen));
                $sheet->setCellValue('T' . $row, number_indo_excel(getPakanPfmc($res->kodeunit)*$kgpanen));
                $sheet->setCellValue('U' . $row, number_indo_excel(getOvkPfmc($res->kodeunit)*$kgpanen));
                $sheet->setCellValue('V' . $row, number_indo_excel(getRhppPfmc($res->kodeunit)*$kgpanen));


                foreach (range('A'. $row, 'V'. $row) as $columnID) {
                    $sheet->getStyle($columnID)->applyFromArray(setBody());
                }

                $sheet->getColumnDimension('A')->setWidth('5');
                $sheet->getColumnDimension('B')->setWidth('10');
                $sheet->getColumnDimension('C')->setWidth('10');
                foreach (range('D', 'T') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setWidth('10');
                    $sheet->getColumnDimension($columnID)->setAutoSize(false);
                }
                $row++;
            }

        $fileName = "RESUME_ESTIMASI_HARGA.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function kpiterbaik(){
        $kpi_kp = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_kp a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT BAIK' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_kp)
                    ORDER BY a.totskor DESC");

        $kpi_ts = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_ts a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT BAIK' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_ts)
                    ORDER BY a.totskor DESC");

        $kpi_sales = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_sales_cum a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT BAIK' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_sales_cum)
                    ORDER BY a.totskor DESC");

        $kpi_logistik = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_logistik_cum a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT BAIK' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_logistik)
                    ORDER BY a.totskor DESC");

        $kpi_finance = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_finance_cum a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT BAIK' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_finance)
                    AND a.nama NOT IN ('EVI OKTAFIANA','SRI MARYANI')
                    ORDER BY a.totskor DESC");

        return view('dashboard.kpiTerbaik', compact('kpi_kp','kpi_ts','kpi_sales','kpi_logistik','kpi_finance'));
    }

    public function underperform(){
        $kpi_kp = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_kp a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT KURANG' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_kp) AND b.vmasajabatan > 1
                    ORDER BY a.totskor DESC");

        $kpi_ts = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_ts a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT KURANG' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_ts) AND b.vmasajabatan > 1
                    ORDER BY a.totskor DESC");

        $kpi_sales = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_sales_cum a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT KURANG' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_sales_cum) AND b.vmasajabatan > 1
                    ORDER BY a.totskor DESC");

        $kpi_logistik = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_logistik_cum a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT KURANG' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_logistik) AND b.vmasajabatan > 1
                    ORDER BY a.totskor DESC");

        $kpi_finance = DB::select("SELECT a.*, b.nik, b.vmasakerja, b.vmasajabatan FROM kpi_finance_cum a
                    LEFT JOIN vkaryawan_lastupdate b ON b.nama=a.nama
                    WHERE a.keterangan='SANGAT KURANG' AND a.tanggal=(SELECT MAX(tanggal) FROM kpi_finance) AND b.vmasajabatan > 1
                    ORDER BY a.totskor DESC");

        return view('dashboard.kpiUnderPerform', compact('kpi_kp','kpi_ts','kpi_sales','kpi_logistik','kpi_finance'));
    }

    public function insight_rakor(){
        $sql_peserta_am = DB::select("SELECT * FROM table_am2023_peserta ORDER BY nama_lengkap ASC");
        $sql_peserta_party = DB::select("SELECT * FROM tb_register");
        $sql_resume = DB::select("SELECT pemateri, ROUND(AVG(score),0) AS score FROM table_am2023_insight GROUP BY pemateri ORDER BY SUM(score) DESC");
        return view('dashboard.home.peserta_am', compact('sql_peserta_am', 'sql_peserta_party','sql_resume'));
    }

    public function peserta_am(){
        $sql_peserta_am = DB::select("SELECT * FROM table_am2023_peserta ORDER BY nama_lengkap ASC");
        $sql_peserta_party = DB::select("SELECT * FROM tb_register WHERE konfirm='HADIR'");
        $sql_resume = DB::select("SELECT * FROM (SELECT pemateri, ROUND(AVG(score),0) AS score FROM table_am2023_insight GROUP BY pemateri) a ORDER BY score DESC");
        $sql_bagian = DB::select("SELECT * FROM table_am2023_qr ORDER BY bagian ASC");
        $sql_nomor = DB::select("SELECT * FROM table_am2023_qr_nomor WHERE blok='R' ORDER BY nomor ASC");
        return view('dashboard.home.peserta_am', compact('sql_peserta_am', 'sql_peserta_party','sql_resume','sql_bagian','sql_nomor'));
    }

    public function peserta_am_simpan(Request $request){
        $id = $request->input('id');

        $num_rows = DB::table('table_am2023_peserta')->where([['id', '=', $id]])->count();
        if($num_rows > 0){
            $insert = DB::table('table_am2023_peserta')->where('id', $id)->update([
                        'nik' => $request->input('nik'),
                        'nama_lengkap' => $request->input('nama_lengkap'),
                        'jabatan' => $request->input('jabatan'),
                        'unit' => $request->input('unit'),
                        'ap' => $request->input('ap'),
                    ]);
        }else{
            $insert = DB::table('table_am2023_peserta')->insert([
                        'nik' => $request->input('nik'),
                        'nama_lengkap' => $request->input('nama_lengkap'),
                        'jabatan' => $request->input('jabatan'),
                        'unit' => $request->input('unit'),
                        'ap' => $request->input('ap'),
                    ]);
        }


        if($insert){
            return response()->json(
                [
                'status' => 'success',
                'pesan' => 'Data berhasil disimpan'
                ]
            );
        }else{
            return response()->json(
                    [
                    'status' => 'success'
                    ]
                );
        }
    }

    public function peserta_am_update(Request $request){
        $id = $request->input('id');
        $num_rows = DB::table('table_am2023_peserta')->where([['id', '=', $id]])->count();
        if($num_rows > 0){
            $insert = DB::table('table_am2023_peserta')->where('id', $id)->update([
                        'nik' => $request->input('nik'),
                        'nama_lengkap' => $request->input('nama_lengkap'),
                        'jabatan' => $request->input('jabatan'),
                        'unit' => $request->input('unit'),
                        'ap' => $request->input('ap'),
                    ]);
            Alert::success('Data berhasil diupdate');
        }else{
            Alert::success('Pesan error');
        }
        return redirect(route('home.insight_rakor'));
    }

    public function peserta_am_hapus($id){
        DB::statement("DELETE FROM table_am2023_peserta WHERE id='$id'");
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function peserta_am_edit($id){
        $sql = DB::select("SELECT * FROM table_am2023_peserta WHERE id='$id'");
        foreach($sql as $data){
            $id = $data->id;
            $nik = $data->nik;
            $nama_lengkap = $data->nama_lengkap;
            $jabatan = $data->jabatan;
            $unit = $data->unit;
            $ap = $data->ap;
        }
        return view('dashboard.home.peserta_am_edit',compact('sql','id','nik','nama_lengkap','jabatan','unit','ap'));
    }

    public function peserta_party_send($id){
        $num_rows = DB::table('tb_register')->where([['id', '=', $id]])->count();
        if($num_rows > 0){
            $sqlData = DB::select("SELECT * FROM tb_register WHERE id='$id'");
            $name = $sqlData[0]->name;
            $nowa = $sqlData[0]->nowa;
            $pesan = "*Pesan Otomatis*\r\n\r\n";
            $pesan = $pesan."Selamat Bpk/Ibu/Sdr \r\n";
            $pesan = $pesan.$name." \r\n";
            $pesan = $pesan."Peserta party diwajibkan gabung group dibawah ini :\r\n";
            $pesan = $pesan."https://chat.whatsapp.com/BNGGRFcTClO1O2omaBoRb2 \r\n";
            $pesan = $pesan."Ttd : Panitia AM 2024";
            // $pesan = "Selamat ".$name.", anda terpilih mengikuti acara ulang tahun mustika. Terimakasih";
            $kirim = DB::statement("UPDATE tb_register SET kirim=kirim+1 WHERE id='$id'");
            if($kirim){
                $kirim = sendWa($nowa, $pesan);
            }
            return back()->with('success', 'Pesan berhasil dikirim');
        }else{
            return back()->with('success', 'Data tidak ditemukan');
        }
    }

    public function peserta_party_send_old($id){
        $num_rows = DB::table('tb_register')->where([['id', '=', $id]])->count();
        if($num_rows > 0){
            $sqlData = DB::select("SELECT * FROM tb_register WHERE id='$id'");
            $name = $sqlData[0]->name;
            $nowa = $sqlData[0]->nowa;
            $pesan = "*Pesan Otomatis, wajib dibalas*\r\n\r\n";
            $pesan = $pesan."Selamat Bpk/Ibu/Sdr \r\n";
            $pesan = $pesan.$name." \r\n";
            $pesan = $pesan."telah terdaftar sebagai tamu undangan untuk acara Ulang Tahun Mustika yang diadakan pada : \r\n";
            $pesan = $pesan."Hari/Tgl : Kamis, 1 Februari 2024 \r\n";
            $pesan = $pesan."Tempat  : Hotel Griya Persada Bandungan \r\n";
            $pesan = $pesan."Waktu  : 18.30 WIB \r\n";
            $pesan = $pesan."Dresscode  : Full Hitam (No Kaos) + Sepatu Bebas (No Sandal) \r\n";
            $pesan = $pesan."*Transportasi dan penginapan  menuju tempat acara ditanggung oleh masing-masing unit* \r\n\r\n";
            $pesan = $pesan."Balas *HADIR* jika bisa datang, dan jika tidak bisa datang balas *BATAL* \r\n\r\n";
            $pesan = $pesan."Ttd : Panitia AM 2024";
            // $pesan = "Selamat ".$name.", anda terpilih mengikuti acara ulang tahun mustika. Terimakasih";
            $kirim = DB::statement("UPDATE tb_register SET kirim=kirim+1 WHERE id='$id'");
            if($kirim){
                $kirim = sendWa($nowa, $pesan);
            }
            return back()->with('success', 'Pesan berhasil dikirim');
        }else{
            return back()->with('success', 'Data tidak ditemukan');
        }
    }

    public function peserta_party_hapus($id){
        DB::statement("DELETE FROM tb_register WHERE id='$id'");
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function peserta_am_score_detail($pemateri){
        $sql = DB::select("SELECT * FROM table_am2023_insight WHERE pemateri='$pemateri'");
        return view('dashboard.home.peserta_am_score_detail',compact('sql','pemateri'));
    }

    public function peserta_am_bagian_simpan(Request $request){
        $insert = DB::table('table_am2023_qr')->insert([
                        'bagian' => $request->input('bagian'),
                    ]);
        if($insert){
            return response()->json(
                [
                'status' => 'success',
                'pesan' => 'Data berhasil disimpan'
                ]
            );
        }else{
            return response()->json(
                    [
                    'status' => 'success'
                    ]
                );
        }
    }

    public function peserta_am_bagian_hapus($id){
        DB::statement("DELETE FROM table_am2023_qr WHERE id='$id'");
        return back()->with('success', 'Data berhasil dihapus');
    }

    public function insight_rakor_komen($nik){
        $nik = decrypt($nik);
        $sql = DB::select("SELECT * FROM vkaryawan_lastupdate WHERE nik='$nik'");
        $sqlKomen = DB::select("SELECT * FROM table_am2023_insight WHERE pemateri = '$nik' ORDER BY id DESC");
        $rows = DB::table('table_am2023_insight')->where('pemateri','=',$nik)->count();
        return view('dashboard.insight_rakor_komen', compact('sql','sqlKomen','rows','nik'));
    }

    public function insight_rakor_simpan(Request $request){
        $nama = Auth::user()->name;
        $komentar = $request->input('komentar');
        $result = DB::table('table_am2023_insight')->insert([
                        'pemateri' => $request->input('pemateri'),
                        'nama' => $nama,
                        'pesan' => $komentar,
                    ]);

        if($result){
            return response()->json(
                [
                'status' => 'sukses',
                'nama' => $nama,
                'komentar' => $komentar
                ]
            );
        }else{
            return response()->json(
                    [
                    'status' => 'sukses'
                    ]
                );
        }
    }

    public function penilaianunit(Request $request){
        $region = $request->input('region');
        $tahun = $request->input('tahun');
        $nav = $request->input('nav');
        if($nav=='unit'){
            $navUnit = 'active';
            $navFluk = '';
            $navAp = '';
            $navApFluk = '';
        }elseif($nav=='fluk'){
            $navUnit = '';
            $navFluk = 'active';
            $navAp = '';
            $navApFluk = '';
        }elseif($nav=='ap'){
            $navUnit = '';
            $navFluk = '';
            $navAp = 'active';
            $navApFluk = '';
        }elseif($nav=='apfluk'){
            $navUnit = '';
            $navFluk = '';
            $navAp = '';
            $navApFluk = 'active';
        }else{
            $navUnit = 'active';
            $navFluk = '';
            $navAp = '';
            $navApFluk = '';
        }
        if($region==''){
            $region='SEMUA';
        }
        $ap = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT(''), 'SEMUA' AS semua FROM regions
                            ORDER BY koderegion ASC");
        if($region!='SEMUA'){
            $sql = DB::select("SELECT * FROM table_penilaian_unit WHERE ap='$region' AND YEAR(tanggal)='$tahun' ORDER BY rangking_akhir+1 ASC");
            $sqlFlukUnit = DB::select("SELECT * FROM table_penilaian_fluk_unit WHERE ap='$region' AND YEAR(tanggal)='$tahun'");
        }else{
            $sql = DB::select("SELECT * FROM table_penilaian_unit WHERE YEAR(tanggal)='$tahun' ORDER BY rangking_akhir+1 ASC");
            $sqlFlukUnit = DB::select("SELECT * FROM table_penilaian_fluk_unit WHERE YEAR(tanggal)='$tahun'");
        }

        if($tahun=='2022'){
            $sqlAp = DB::select("SELECT * FROM table_penilaian_ap WHERE YEAR(tanggal)='$tahun' ORDER BY rangking+1 ASC");
        }else{
            $sqlAp = DB::select("SELECT * FROM table_penilaian_ap WHERE YEAR(tanggal)='$tahun' ORDER BY rangking_akhir+1 ASC");
        }

        $sqlCreate = DB::select("SELECT created_at FROM table_penilaian_unit ORDER BY id DESC LIMIT 1");

        $sqlFlukAp = DB::select("SELECT * FROM table_penilaian_fluk_ap WHERE YEAR(tanggal)='$tahun'");

        if(!empty($sqlCreate)){
            foreach($sqlCreate as $data){
                $created_at = $data->created_at;
            }
        }else{
            $created_at = null;
        }

        return view('dashboard.penilaianUnit', compact('navAp','navApFluk','ap','region','sql','sqlAp','sqlFlukUnit','sqlFlukAp','navUnit','navFluk','tahun','created_at'));
        // $roles = Auth::user()->nik;
        // $akses = array("0001.MTK.0209", "0008.MTK.0309", "0004.MTK.0209","0595.MTK.1115","0017.MTK.0609","admin");
        // if (in_array($roles, $akses)) {
        //     return view('dashboard.penilaianUnit', compact('navAp','navApFluk','ap','region','sql','sqlAp','sqlFlukUnit','sqlFlukAp','navUnit','navFluk','tahun','created_at'));
        // } else {
        //     return back()->with('success', 'Mohon maaf untuk skor Unit dan AP Terbaik tahun 2023 sementara di Hold (Tidak bisa diakses),
        //                                     dikarenakan sudah mendekati akhir tahun dan data akan digunakan untuk penentuan AP Terbaik tahun 2023.
        //                                     Terimakasih');
        // }
    }

    public function penilaianunitExcel($region){
        if($region!='SEMUA'){
            $sql = DB::select("SELECT * FROM table_penilaian_unit WHERE ap='$region'");
        }else{
            $sql = DB::select("SELECT * FROM table_penilaian_unit");
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:T1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'PENILAIAN UNIT');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:T4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', 'NOMINAL PROFIT');
        $spreadsheet->getActiveSheet()->mergeCells('D3:F3');
        $sheet->setCellValue('D4', 'ACHV');
        $sheet->setCellValue('E4', 'SCORE');
        $sheet->setCellValue('F4', '%');

        $sheet->setCellValue('G3', 'MARGIN PER KG');
        $spreadsheet->getActiveSheet()->mergeCells('G3:I3');
        $sheet->setCellValue('G4', 'ACHV');
        $sheet->setCellValue('H4', 'SCORE');
        $sheet->setCellValue('I4', '%');

        $sheet->setCellValue('J3', 'RUGI PRODUKSI');
        $spreadsheet->getActiveSheet()->mergeCells('J3:L3');
        $sheet->setCellValue('J4', 'ACHV');
        $sheet->setCellValue('K4', 'SCORE');
        $sheet->setCellValue('L4', '%');

        $sheet->setCellValue('M3', 'PIUTANG PENJUALAN');
        $spreadsheet->getActiveSheet()->mergeCells('M3:O3');
        $sheet->setCellValue('M4', 'ACHV');
        $sheet->setCellValue('N4', 'SCORE');
        $sheet->setCellValue('O4', '%');

        $sheet->setCellValue('P3', 'KPI TEAM');
        $spreadsheet->getActiveSheet()->mergeCells('P3:R3');
        $sheet->setCellValue('P4', 'ACHV');
        $sheet->setCellValue('Q4', 'SCORE');
        $sheet->setCellValue('R4', '%');

        $sheet->setCellValue('S3', 'TOTAL');
        $spreadsheet->getActiveSheet()->mergeCells('S3:T3');
        $sheet->setCellValue('S4', 'SCORE');
        $sheet->setCellValue('T4', 'RANK');

        $rows = 5;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->unit);
            $sheet->setCellValue('C' . $rows, $data->ap);
            $sheet->setCellValue('D' . $rows, number_indo_excel($data->profit_ach));
            $sheet->setCellValue('E' . $rows, number_indo_excel_koma1($data->profit_skor));
            $sheet->setCellValue('F' . $rows, number_indo_persen_excel($data->profit_persen));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data->margin_ach));
            $sheet->setCellValue('H' . $rows, number_indo_excel_koma1($data->margin_skor));
            $sheet->setCellValue('I' . $rows, number_indo_persen_excel($data->margin_persen));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data->rugi_ach));
            $sheet->setCellValue('K' . $rows, number_indo_excel_koma1($data->rugi_skor));
            $sheet->setCellValue('L' . $rows, number_indo_persen_excel($data->rugi_persen));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data->piutang_ach));
            $sheet->setCellValue('N' . $rows, number_indo_excel_koma1($data->piutang_skor));
            $sheet->setCellValue('O' . $rows, number_indo_persen_excel($data->piutang_persen));
            $sheet->setCellValue('P' . $rows, number_indo_excel($data->kpi_ach));
            $sheet->setCellValue('Q' . $rows, number_indo_excel_koma1($data->kpi_skor));
            $sheet->setCellValue('R' . $rows, number_indo_persen_excel($data->kpi_persen));
            $sheet->setCellValue('S' . $rows, number_indo_excel_koma1($data->tot_skor));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data->rangking));

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
            $sheet->getColumnDimension('B')->setWidth('10');
            $sheet->getColumnDimension('C')->setWidth('10');
            foreach (range('D', 'T') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $fileName = "PENILAIAN_UNIT.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function flukUnittExcel($region){
        if($region!='SEMUA'){
            $sql = DB::select("SELECT * FROM table_penilaian_fluk_unit WHERE ap='$region'");
        }else{
            $sql = DB::select("SELECT * FROM table_penilaian_fluk_unit");
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AA1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'SCORE FLUKTUASI UNIT');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AA4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'UNIT');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('C3:C4');

        $sheet->setCellValue('D3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('D3:E3');
        $sheet->setCellValue('D4', 'SCORE');
        $sheet->setCellValue('E4', 'RANK');

        $sheet->setCellValue('F3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('F3:G3');
        $sheet->setCellValue('F4', 'SCORE');
        $sheet->setCellValue('G4', 'RANK');

        $sheet->setCellValue('H3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('H3:I3');
        $sheet->setCellValue('H4', 'SCORE');
        $sheet->setCellValue('I4', 'RANK');

        $sheet->setCellValue('J3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('J3:K3');
        $sheet->setCellValue('J4', 'SCORE');
        $sheet->setCellValue('K4', 'RANK');

        $sheet->setCellValue('L3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('L3:M3');
        $sheet->setCellValue('L4', 'SCORE');
        $sheet->setCellValue('M4', 'RANK');

        $sheet->setCellValue('N3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('N3:O3');
        $sheet->setCellValue('N4', 'SCORE');
        $sheet->setCellValue('O4', 'RANK');

        $sheet->setCellValue('P3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('P3:Q3');
        $sheet->setCellValue('P4', 'SCORE');
        $sheet->setCellValue('Q4', 'RANK');

        $sheet->setCellValue('R3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('R3:S3');
        $sheet->setCellValue('R4', 'SCORE');
        $sheet->setCellValue('S4', 'RANK');

        $sheet->setCellValue('T3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('T3:U3');
        $sheet->setCellValue('T4', 'SCORE');
        $sheet->setCellValue('U4', 'RANK');

        $sheet->setCellValue('V3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('V3:W3');
        $sheet->setCellValue('V4', 'SCORE');
        $sheet->setCellValue('W4', 'RANK');

        $sheet->setCellValue('X3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('X3:Y3');
        $sheet->setCellValue('X4', 'SCORE');
        $sheet->setCellValue('Y4', 'RANK');

        $sheet->setCellValue('Z3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('Z3:AA3');
        $sheet->setCellValue('Z4', 'SCORE');
        $sheet->setCellValue('AA4', 'RANK');

        $rows = 5;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->unit);
            $sheet->setCellValue('C' . $rows, $data->ap);
            $sheet->setCellValue('D' . $rows, number_indo_excel($data->jan_score));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data->jan_rank));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data->feb_score));
            $sheet->setCellValue('G' . $rows, number_indo_excel($data->feb_rank));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data->mar_score));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data->mar_rank));
            $sheet->setCellValue('J' . $rows, number_indo_excel($data->apr_score));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data->apr_rank));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data->mei_score));
            $sheet->setCellValue('M' . $rows, number_indo_excel($data->mei_rank));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data->jun_score));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data->jun_rank));
            $sheet->setCellValue('P' . $rows, number_indo_excel($data->jul_score));
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data->jul_rank));
            $sheet->setCellValue('R' . $rows, number_indo_excel($data->agu_score));
            $sheet->setCellValue('S' . $rows, number_indo_excel($data->agu_rank));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data->sep_score));
            $sheet->setCellValue('U' . $rows, number_indo_excel($data->sep_rank));
            $sheet->setCellValue('V' . $rows, number_indo_excel($data->okt_score));
            $sheet->setCellValue('W' . $rows, number_indo_excel($data->okt_rank));
            $sheet->setCellValue('X' . $rows, number_indo_excel($data->nov_score));
            $sheet->setCellValue('Y' . $rows, number_indo_excel($data->nov_rank));
            $sheet->setCellValue('Z' . $rows, number_indo_excel($data->des_score));
            $sheet->setCellValue('AA' . $rows, number_indo_excel($data->des_rank));

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

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('10');
            $sheet->getColumnDimension('C')->setWidth('10');
            foreach (range('D', 'AA') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $fileName = "PENILAIAN_FLUKTUASI_UNIT.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function flukApExcel(){
        $sql = DB::select("SELECT * FROM table_penilaian_fluk_ap");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:AL1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'SCORE FLUKTUASI AP');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:AL4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'JAN');
        $spreadsheet->getActiveSheet()->mergeCells('C3:E3');
        $sheet->setCellValue('C4', 'SCORE');
        $sheet->setCellValue('D4', 'DIFF');
        $sheet->setCellValue('E4', 'RANK');

        $sheet->setCellValue('F3', 'FEB');
        $spreadsheet->getActiveSheet()->mergeCells('F3:H3');
        $sheet->setCellValue('F4', 'SCORE');
        $sheet->setCellValue('G4', 'DIFF');
        $sheet->setCellValue('H4', 'RANK');

        $sheet->setCellValue('I3', 'MAR');
        $spreadsheet->getActiveSheet()->mergeCells('I3:K3');
        $sheet->setCellValue('I4', 'SCORE');
        $sheet->setCellValue('J4', 'DIFF');
        $sheet->setCellValue('K4', 'RANK');

        $sheet->setCellValue('L3', 'APR');
        $spreadsheet->getActiveSheet()->mergeCells('L3:N3');
        $sheet->setCellValue('L4', 'SCORE');
        $sheet->setCellValue('M4', 'DIFF');
        $sheet->setCellValue('N4', 'RANK');

        $sheet->setCellValue('O3', 'MEI');
        $spreadsheet->getActiveSheet()->mergeCells('O3:Q3');
        $sheet->setCellValue('O4', 'SCORE');
        $sheet->setCellValue('P4', 'DIFF');
        $sheet->setCellValue('Q4', 'RANK');

        $sheet->setCellValue('R3', 'JUN');
        $spreadsheet->getActiveSheet()->mergeCells('R3:T3');
        $sheet->setCellValue('R4', 'SCORE');
        $sheet->setCellValue('S4', 'DIFF');
        $sheet->setCellValue('T4', 'RANK');

        $sheet->setCellValue('U3', 'JUL');
        $spreadsheet->getActiveSheet()->mergeCells('U3:W3');
        $sheet->setCellValue('U4', 'SCORE');
        $sheet->setCellValue('V4', 'DIFF');
        $sheet->setCellValue('W4', 'RANK');

        $sheet->setCellValue('X3', 'AGU');
        $spreadsheet->getActiveSheet()->mergeCells('X3:Z3');
        $sheet->setCellValue('X4', 'SCORE');
        $sheet->setCellValue('Z4', 'DIFF');
        $sheet->setCellValue('Y4', 'RANK');

        $sheet->setCellValue('AA3', 'SEP');
        $spreadsheet->getActiveSheet()->mergeCells('AA3:AC3');
        $sheet->setCellValue('AA4', 'SCORE');
        $sheet->setCellValue('AB4', 'DIFF');
        $sheet->setCellValue('AC4', 'RANK');

        $sheet->setCellValue('AD3', 'OKT');
        $spreadsheet->getActiveSheet()->mergeCells('AD3:AF3');
        $sheet->setCellValue('AD4', 'SCORE');
        $sheet->setCellValue('AE4', 'DIFF');
        $sheet->setCellValue('AF4', 'RANK');

        $sheet->setCellValue('AG3', 'NOV');
        $spreadsheet->getActiveSheet()->mergeCells('AG3:AI3');
        $sheet->setCellValue('AG4', 'SCORE');
        $sheet->setCellValue('AH4', 'DIFF');
        $sheet->setCellValue('AI4', 'RANK');

        $sheet->setCellValue('AJ3', 'DES');
        $spreadsheet->getActiveSheet()->mergeCells('AJ3:AL3');
        $sheet->setCellValue('AJ4', 'SCORE');
        $sheet->setCellValue('AK4', 'DIFF');
        $sheet->setCellValue('AL4', 'RANK');


        $rows = 5;
        $no = 1;
        $rank = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->ap);

            $sheet->setCellValue('C' . $rows, number_indo_excel_koma1($data->jan_score));
            $sheet->setCellValue('D' . $rows, number_indo_excel_koma1($data->jan_diff));
            $sheet->setCellValue('E' . $rows, number_indo_excel($data->jan_rank));

            $sheet->setCellValue('F' . $rows, number_indo_excel_koma1($data->feb_score));
            $sheet->setCellValue('G' . $rows, number_indo_excel_koma1($data->feb_diff));
            $sheet->setCellValue('H' . $rows, number_indo_excel($data->feb_rank));

            $sheet->setCellValue('I' . $rows, number_indo_excel_koma1($data->mar_score));
            $sheet->setCellValue('J' . $rows, number_indo_excel_koma1($data->mar_diff));
            $sheet->setCellValue('K' . $rows, number_indo_excel($data->mar_rank));

            $sheet->setCellValue('L' . $rows, number_indo_excel_koma1($data->apr_score));
            $sheet->setCellValue('M' . $rows, number_indo_excel_koma1($data->apr_diff));
            $sheet->setCellValue('N' . $rows, number_indo_excel($data->apr_rank));

            $sheet->setCellValue('O' . $rows, number_indo_excel_koma1($data->mei_score));
            $sheet->setCellValue('P' . $rows, number_indo_excel_koma1($data->mei_diff));
            $sheet->setCellValue('Q' . $rows, number_indo_excel($data->mei_rank));

            $sheet->setCellValue('R' . $rows, number_indo_excel_koma1($data->jun_score));
            $sheet->setCellValue('S' . $rows, number_indo_excel_koma1($data->jun_diff));
            $sheet->setCellValue('T' . $rows, number_indo_excel($data->jun_rank));

            $sheet->setCellValue('U' . $rows, number_indo_excel_koma1($data->jul_score));
            $sheet->setCellValue('V' . $rows, number_indo_excel_koma1($data->jul_diff));
            $sheet->setCellValue('W' . $rows, number_indo_excel($data->jul_rank));

            $sheet->setCellValue('X' . $rows, number_indo_excel_koma1($data->agu_score));
            $sheet->setCellValue('Z' . $rows, number_indo_excel_koma1($data->agu_diff));
            $sheet->setCellValue('Y' . $rows, number_indo_excel($data->agu_rank));

            $sheet->setCellValue('AA' . $rows, number_indo_excel_koma1($data->sep_score));
            $sheet->setCellValue('AB' . $rows, number_indo_excel_koma1($data->sep_diff));
            $sheet->setCellValue('AC' . $rows, number_indo_excel($data->sep_rank));

            $sheet->setCellValue('AD' . $rows, number_indo_excel_koma1($data->okt_score));
            $sheet->setCellValue('AE' . $rows, number_indo_excel_koma1($data->okt_diff));
            $sheet->setCellValue('AF' . $rows, number_indo_excel($data->okt_rank));

            $sheet->setCellValue('AG' . $rows, number_indo_excel_koma1($data->nov_score));
            $sheet->setCellValue('AH' . $rows, number_indo_excel_koma1($data->nov_diff));
            $sheet->setCellValue('AI' . $rows, number_indo_excel($data->nov_rank));

            $sheet->setCellValue('AJ' . $rows, number_indo_excel_koma1($data->des_score));
            $sheet->setCellValue('AK' . $rows, number_indo_excel_koma1($data->des_diff));
            $sheet->setCellValue('AL' . $rows, number_indo_excel($data->des_rank));

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

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('10');
            $sheet->getColumnDimension('C')->setWidth('10');
            foreach (range('D', 'AL') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $fileName = "PENILAIAN_FLUKTUASI_AP.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function penilaianapExcel(){
        $sql = DB::select("SELECT ap,
                    AVG(profit_ach) AS profit_ach, AVG(profit_skor) AS profit_skor,
                    AVG(margin_ach) AS margin_ach, AVG(margin_skor) AS margin_skor,
                    AVG(rugi_ach) AS rugi_ach, AVG(rugi_skor) AS rugi_skor,
                    AVG(piutang_ach) AS piutang_ach, AVG(piutang_skor) AS piutang_skor,
                    AVG(kpi_ach) AS kpi_ach, AVG(kpi_skor) AS kpi_skor,
                    AVG(tot_skor) AS tot_skor,
                    AVG(pinalty) AS pinalty,
                    AVG(skor_akhir) AS skor_akhir
                    FROM table_penilaian_unit WHERE ap<>'LSW'
                    GROUP BY ap ORDER BY skor_akhir DESC");

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:S1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'PENILAIAN AP');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:S4')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $spreadsheet->getActiveSheet()->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'AP');
        $spreadsheet->getActiveSheet()->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'NOMINAL PROFIT');
        $spreadsheet->getActiveSheet()->mergeCells('C3:E3');
        $sheet->setCellValue('C4', 'ACHV');
        $sheet->setCellValue('D4', 'SCORE');
        $sheet->setCellValue('E4', '%');

        $sheet->setCellValue('F3', 'MARGIN PER KG');
        $spreadsheet->getActiveSheet()->mergeCells('F3:H3');
        $sheet->setCellValue('F4', 'ACHV');
        $sheet->setCellValue('G4', 'SCORE');
        $sheet->setCellValue('H4', '%');

        $sheet->setCellValue('I3', 'RUGI PRODUKSI');
        $spreadsheet->getActiveSheet()->mergeCells('I3:K3');
        $sheet->setCellValue('I4', 'ACHV');
        $sheet->setCellValue('J4', 'SCORE');
        $sheet->setCellValue('K4', '%');

        $sheet->setCellValue('L3', 'PIUTANG PENJUALAN');
        $spreadsheet->getActiveSheet()->mergeCells('L3:N3');
        $sheet->setCellValue('L4', 'ACHV');
        $sheet->setCellValue('M4', 'SCORE');
        $sheet->setCellValue('N4', '%');

        $sheet->setCellValue('O3', 'KPI TEAM');
        $spreadsheet->getActiveSheet()->mergeCells('O3:Q3');
        $sheet->setCellValue('O4', 'ACHV');
        $sheet->setCellValue('P4', 'SCORE');
        $sheet->setCellValue('Q4', '%');

        $sheet->setCellValue('R3', 'TOTAL');
        $spreadsheet->getActiveSheet()->mergeCells('R3:S3');
        $sheet->setCellValue('R4', 'SCORE');
        $sheet->setCellValue('S4', 'RANK');

        $rows = 5;
        $no = 1;
        $rank = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->ap);
            $sheet->setCellValue('C' . $rows, number_indo_excel($data->profit_ach));
            $sheet->setCellValue('D' . $rows, number_indo_excel_koma1($data->profit_skor));
            $sheet->setCellValue('E' . $rows, rmsPersen(35,$data->profit_skor));
            $sheet->setCellValue('F' . $rows, number_indo_excel($data->margin_ach));
            $sheet->setCellValue('G' . $rows, number_indo_excel_koma1($data->margin_skor));
            $sheet->setCellValue('H' . $rows, rmsPersen(15,$data->margin_skor));
            $sheet->setCellValue('I' . $rows, number_indo_excel($data->rugi_ach));
            $sheet->setCellValue('J' . $rows, number_indo_excel_koma1($data->rugi_skor));
            $sheet->setCellValue('K' . $rows, rmsPersen(15,$data->rugi_skor));
            $sheet->setCellValue('L' . $rows, number_indo_excel($data->piutang_ach));
            $sheet->setCellValue('M' . $rows, number_indo_excel_koma1($data->piutang_skor));
            $sheet->setCellValue('N' . $rows, rmsPersen(10,$data->piutang_skor));
            $sheet->setCellValue('O' . $rows, number_indo_excel($data->kpi_ach));
            $sheet->setCellValue('P' . $rows, number_indo_excel_koma1($data->kpi_skor));
            $sheet->setCellValue('Q' . $rows, rmsPersen(25,$data->kpi_skor));
            $sheet->setCellValue('R' . $rows, number_indo_excel_koma1($data->tot_skor));
            $sheet->setCellValue('S' . $rows, $rank++);

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
            $sheet->getColumnDimension('B')->setWidth('10');
            $sheet->getColumnDimension('C')->setWidth('10');
            foreach (range('D', 'S') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $fileName = "PENILAIAN_AP.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function penilaianUnitImport(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $tahun = $request->input('tahun');
        $tanggal = $tahun.'-'.date('m').'-01';
        $file = $request->file('file');
        $nama_file = 'PENILAIAN'.$file->hashName();
        $path = $file->storeAs('public/excel/',$nama_file);

        $import = new ScPenilaianUploadImport($tanggal);
        $import->onlySheets('UNIT', 'AP', 'FLUK-AP', 'FLUK-UNIT');

        $clearUnit = DB::table('table_penilaian_unit')->where(DB::raw('YEAR(tanggal)'), $tahun)->delete();
        $clearAp = DB::table('table_penilaian_ap')->where(DB::raw('YEAR(tanggal)'), $tahun)->delete();
        $clearFlukUnit = DB::table('table_penilaian_fluk_unit')->where(DB::raw('YEAR(tanggal)'), $tahun)->delete();
        $clearFlukAp = DB::table('table_penilaian_fluk_ap')->where(DB::raw('YEAR(tanggal)'), $tahun)->delete();
        if(($clearUnit) && ($clearFlukUnit) && ($clearFlukAp) && ($clearAp)){
            $import = Excel::import($import, storage_path('app/public/excel/'.$nama_file));
        }else{
            $import = Excel::import($import, storage_path('app/public/excel/'.$nama_file));
        }
        Storage::delete($path);

        if($import) {
            Alert::toast('Data Berhasil Diimport!', 'success');
            return redirect()->route('home.penilaianunit')->with(['success' => 'Data Berhasil Diimport!']);
        } else {
            Alert::toast('Data Gagal Diimport!', 'danger');
            return redirect()->route('home.penilaianunit')->with(['error' => 'Data Gagal Diimport!']);
        }
    }

    public function kpiunit(Request $request){
        $tahun      = $request->input('tahun');
        $n = $request->input('n') ?? 'nav-1';

        $bulan      = $request->input('bulan');
        $reqUnit    = $request->input('unit');
        $unit = DB::select("SELECT kodeunit, namaunit FROM units ORDER BY namaunit ASC");
        $bln    = DB::select('select kode, bulan from bulan order by kode+1 asc');
        $data_tahun = DB::table('tahun')->pluck('tahun')->toArray();

        $tglawal    = $tahun.'-01-01';
        $tglakhir   = $tahun.'-'.$bulan.'-31';

        if($reqUnit !='' ){
            $strWhere = "WHERE unit='$reqUnit' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'";
        }else{
            $strWhere = "WHERE MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'";
        }
        $sqlKpiUnit = DB::select("SELECT nama, jabatan, unit, ap, totskor, keterangan FROM kpi_kp $strWhere
                                    UNION ALL
                                    SELECT nama, jabatan, unit, ap, totskor, keterangan FROM kpi_ts $strWhere
                                    UNION ALL
                                    SELECT nama, 'SALES' AS jabatan, unit, ap, totskor, keterangan FROM kpi_sales_cum $strWhere
                                    UNION ALL
                                    SELECT nama, 'ADMIN LOGISTIK' AS jabatan, unit, ap, totskor, keterangan FROM kpi_logistik_cum $strWhere
                                    UNION ALL
                                    SELECT nama, 'ADMIN KEUANGAN' AS jabatan, unit, ap, totskor, keterangan FROM kpi_finance_cum $strWhere");
        $data_kpi_unit = collect(array_map(function($item){
                            return $item;
                        },$sqlKpiUnit));

        $sqlPerbulan = DB::select("SELECT unit, ap,
                                COUNT(CASE WHEN MONTH(tanggal) = 1 THEN totskor ELSE NULL END) AS cjan,
                                COUNT(CASE WHEN MONTH(tanggal) = 2 THEN totskor ELSE NULL END) AS cfeb,
                                COUNT(CASE WHEN MONTH(tanggal) = 3 THEN totskor ELSE NULL END) AS cmar,
                                COUNT(CASE WHEN MONTH(tanggal) = 4 THEN totskor ELSE NULL END) AS capr,
                                COUNT(CASE WHEN MONTH(tanggal) = 5 THEN totskor ELSE NULL END) AS cmei,
                                COUNT(CASE WHEN MONTH(tanggal) = 6 THEN totskor ELSE NULL END) AS cjun,
                                COUNT(CASE WHEN MONTH(tanggal) = 7 THEN totskor ELSE NULL END) AS cjul,
                                COUNT(CASE WHEN MONTH(tanggal) = 8 THEN totskor ELSE NULL END) AS cagu,
                                COUNT(CASE WHEN MONTH(tanggal) = 9 THEN totskor ELSE NULL END) AS csep,
                                COUNT(CASE WHEN MONTH(tanggal) = 10 THEN totskor ELSE NULL END) AS cokt,
                                COUNT(CASE WHEN MONTH(tanggal) = 11 THEN totskor ELSE NULL END) AS cnov,
                                COUNT(CASE WHEN MONTH(tanggal) = 12 THEN totskor ELSE NULL END) AS cdes,

                                AVG(CASE WHEN MONTH(tanggal) = 1 THEN totskor ELSE NULL END) AS ajan,
                                AVG(CASE WHEN MONTH(tanggal) = 2 THEN totskor ELSE NULL END) AS afeb,
                                AVG(CASE WHEN MONTH(tanggal) = 3 THEN totskor ELSE NULL END) AS amar,
                                AVG(CASE WHEN MONTH(tanggal) = 4 THEN totskor ELSE NULL END) AS aapr,
                                AVG(CASE WHEN MONTH(tanggal) = 5 THEN totskor ELSE NULL END) AS amei,
                                AVG(CASE WHEN MONTH(tanggal) = 6 THEN totskor ELSE NULL END) AS ajun,
                                AVG(CASE WHEN MONTH(tanggal) = 7 THEN totskor ELSE NULL END) AS ajul,
                                AVG(CASE WHEN MONTH(tanggal) = 8 THEN totskor ELSE NULL END) AS aagu,
                                AVG(CASE WHEN MONTH(tanggal) = 9 THEN totskor ELSE NULL END) AS asep,
                                AVG(CASE WHEN MONTH(tanggal) = 10 THEN totskor ELSE NULL END) AS aokt,
                                AVG(CASE WHEN MONTH(tanggal) = 11 THEN totskor ELSE NULL END) AS anov,
                                AVG(CASE WHEN MONTH(tanggal) = 12 THEN totskor ELSE NULL END) AS ades
                            FROM vkpi_skor WHERE YEAR(tanggal)='$tahun' GROUP BY unit ORDER BY ap ASC");

         $data_kpi_unit_perbulan = collect(array_map(function($item){
                            return $item;
                        },$sqlPerbulan));

        return view('dashboard.kpiUnit', compact('unit','reqUnit','bulan','bln','data_tahun','tahun','n',
                    'sqlPerbulan','data_kpi_unit','data_kpi_unit_perbulan'));
    }

    public function perbandingan(Request $request){
        $jabatan = Auth::user()->jabatan;
        $nav = $request->input('nav');
        if($nav=='ap'){
            $navUnit = '';
            $navAp = 'active';
        }else{
            $navUnit = 'active';
            $navAp = '';
        }
        $tahun_unit = $request->input('tahun_unit');
        $tahun_ap = $request->input('tahun_ap');

        $strUnit1 = $request->input('unit1');
        $strUnit2 = $request->input('unit2');

        $strAp1 = $request->input('ap1');
        $strAp2 = $request->input('ap2');

        $unit1 = DB::select("SELECT kodeunit, namaunit FROM units UNION ALL SELECT DISTINCT(''), '' FROM units ORDER BY kodeunit ASC");
        $unit2 = DB::select("SELECT kodeunit, namaunit FROM units UNION ALL SELECT DISTINCT(''), '' FROM units ORDER BY kodeunit ASC");

        $ap1 = DB::select("SELECT koderegion, namaregion FROM regions UNION ALL SELECT DISTINCT(''), '' FROM regions ORDER BY koderegion ASC");
        $ap2 = DB::select("SELECT koderegion, namaregion FROM regions UNION ALL SELECT DISTINCT(''), '' FROM regions ORDER BY koderegion ASC");

        $no = 1;
        $arrJabatan = array("KEPALA UNIT", "KEPALA PRODUKSI", "STAFF REGION", "DIREKTUR PT");
        if(!empty($strUnit1) && !empty($strUnit2)){
            if(!in_array($jabatan, $arrJabatan)){
                 $sql = DB::select("SELECT evaluasi, $strUnit1, $strUnit2 FROM table_perbandingan WHERE YEAR(tanggal)='$tahun_unit'");
            }else{
                 $sql = DB::select("SELECT evaluasi, $strUnit1, $strUnit2 FROM table_perbandingan WHERE YEAR(tanggal)='$tahun_unit' AND evaluasi <> 'PROFIT (JUTA)'");
            }
        }else{
            $sql = array();
        }

        $no = 1;
        if(!empty($strAp1) && !empty($strAp2)){
            if(!in_array($jabatan, $arrJabatan)){
                $sqlAp = DB::select("SELECT evaluasi, $strAp1, $strAp2 FROM table_perbandingan WHERE YEAR(tanggal)='$tahun_ap'");
            }else{
                $sqlAp = DB::select("SELECT evaluasi, $strAp1, $strAp2 FROM table_perbandingan WHERE YEAR(tanggal)='$tahun_ap' AND evaluasi <> 'PROFIT (JUTA)'");
            }
        }else{
            $sqlAp = array();
        }

        $noSql=1;
        $arrSql = array();
        foreach ($sql as $data) {
            array_push($arrSql, [
                vs_evaluasi($data->evaluasi,$data->$strUnit1,$data->$strUnit2,'a'),
                vs_valunit($data->evaluasi,$data->$strUnit1),
                $data->evaluasi,
                vs_valunit($data->evaluasi,$data->$strUnit2),
                vs_evaluasi($data->evaluasi,$data->$strUnit2,$data->$strUnit1,'b'),
            ]);
        }

        $noSqlAp=1;
        $arrSqlAp = array();
        foreach ($sqlAp as $data) {
            array_push($arrSqlAp, [
                vs_evaluasi($data->evaluasi,$data->$strAp1,$data->$strAp2,'a'),
                vs_valunit($data->evaluasi,$data->$strAp1),
                $data->evaluasi,
                vs_valunit($data->evaluasi,$data->$strAp2),
                vs_evaluasi($data->evaluasi,$data->$strAp2,$data->$strAp1,'b'),
            ]);
        }

        $arrUnit1 = [];
        foreach ($arrSql as $val) {
            array_push($arrUnit1, $val[0]);
        }
        $countsUnit1 = array_count_values($arrUnit1);
        $unggulUnit1 = !empty($countsUnit1['UNGGUL']) ? $countsUnit1['UNGGUL'] : '0';
        $perUnggulUnit1 = ($unggulUnit1!=0)?($unggulUnit1/count($arrUnit1)) * 100:0;

        $setaraUnit1 = !empty($countsUnit1['SETARA']) ? $countsUnit1['SETARA'] : '0';
        $perSetaraUnit1 = ($setaraUnit1!=0)?($setaraUnit1/count($arrUnit1)) * 100:0;

        $kalahUnit1 = !empty($countsUnit1['KALAH']) ? $countsUnit1['KALAH'] : '0';
        $perKalahUnit1 = ($kalahUnit1!=0)?($kalahUnit1/count($arrUnit1)) * 100:0;

        $arrUnit2 = [];
        foreach ($arrSql as $val) {
            array_push($arrUnit2, $val[4]);
        }
        $countsUnit2 = array_count_values($arrUnit2);
        $unggulUnit2 = !empty($countsUnit2['UNGGUL']) ? $countsUnit2['UNGGUL'] : '0';
        $perUnggulUnit2 = ($unggulUnit2!=0)?($unggulUnit2/count($arrUnit2)) * 100:0;

        $setaraUnit2 = !empty($countsUnit2['SETARA']) ? $countsUnit2['SETARA'] : '0';
        $perSetaraUnit2 = ($setaraUnit2!=0)?($setaraUnit2/count($arrUnit2)) * 100:0;

        $kalahUnit2 = !empty($countsUnit2['KALAH']) ? $countsUnit2['KALAH'] : '0';
        $perKalahUnit2 = ($kalahUnit2!=0)?($kalahUnit2/count($arrUnit2)) * 100:0;

        $arrAp1 = [];
        foreach ($arrSqlAp as $val) {
            array_push($arrAp1, $val[0]);
        }
        $countsAp1 = array_count_values($arrAp1);
        $unggulAp1 = !empty($countsAp1['UNGGUL']) ? $countsAp1['UNGGUL'] : '0';
        $perUnggulAp1 = ($unggulAp1!=0)?($unggulAp1/count($arrAp1)) * 100:0;

        $setaraAp1 = !empty($countsAp1['SETARA']) ? $countsAp1['SETARA'] : '0';
        $perSetaraAp1 = ($setaraAp1!=0)?($setaraAp1/count($arrAp1)) * 100:0;

        $kalahAp1 = !empty($countsAp1['KALAH']) ? $countsAp1['KALAH'] : '0';
        $perKalahAp1 = ($kalahAp1!=0)?($kalahAp1/count($arrAp1)) * 100:0;

        $arrAp2 = [];
        foreach ($arrSqlAp as $val) {
            array_push($arrAp2, $val[4]);
        }
        $countsAp2 = array_count_values($arrAp2);
        $unggulAp2 = !empty($countsAp2['UNGGUL']) ? $countsAp2['UNGGUL'] : '0';
        $perUnggulAp2 = ($unggulAp2!=0)?($unggulAp2/count($arrAp2)) * 100:0;

        $setaraAp2 = !empty($countsAp2['SETARA']) ? $countsAp2['SETARA'] : '0';
        $perSetaraAp2 = ($setaraAp2!=0)?($setaraAp2/count($arrAp2)) * 100:0;

        $kalahAp2 = !empty($countsAp2['KALAH']) ? $countsAp2['KALAH'] : '0';
        $perKalahAp2 = ($kalahAp2!=0)?($kalahAp2/count($arrAp2)) * 100:0;

        $sqlTgl = DB::select("SELECT updated_at FROM table_perbandingan ORDER BY id DESC LIMIT 1");
        foreach ($sqlTgl as $data) {
            $tanggal = $data->updated_at;
        }

        if($tahun_unit==''){
            $tahun_unit = 'PILIH';
        }

        if($tahun_ap==''){
            $tahun_ap = 'PILIH';
        }
        return view('dashboard.perbandingan', compact('no','strUnit1','strUnit2','unit1','unit2','arrSql','tahun_unit','tahun_ap',
                                                        'unggulUnit1','perUnggulUnit1','setaraUnit1','perSetaraUnit1','kalahUnit1','perKalahUnit1',
                                                        'unggulUnit2','perUnggulUnit2','setaraUnit2','perSetaraUnit2','kalahUnit2','perKalahUnit2',
                                                        'no','strAp1','strAp2','ap1','ap2','arrSqlAp',
                                                        'unggulAp1','perUnggulAp1','setaraAp1','perSetaraAp1','kalahAp1','perKalahAp1',
                                                        'unggulAp2','perUnggulAp2','setaraAp2','perSetaraAp2','kalahAp2','perKalahAp2',
                                                        'navUnit','navAp', 'tanggal','noSql','noSqlAp'));
    }

    public function simulasipanen(Request $request){
        $jabatan = Auth::user()->jabatan;
        $region = Auth::user()->region;
        $koderegion = Auth::user()->region;
        $kodeunit = Auth::user()->unit;
        $akses = array("ADMINISTRATOR", "SUPERVISOR", "DIREKTUR UTAMA", "STAFF MANAGEMENT INFORMATION SYSTEM");
        $aksesRegion = array("DIREKTUR PT","STAFF REGION","KEPALA REGION");
        if(in_array($jabatan, $akses)) {
            $unit = DB::select("SELECT kodeunit, namaunit FROM units ORDER BY namaunit ASC");
        }elseif (in_array($jabatan, $aksesRegion)) {
            $unit = DB::select("SELECT kodeunit, namaunit FROM units WHERE region = '$koderegion ' ORDER BY namaunit ASC");
        }else{
            $unit = DB::select("SELECT kodeunit, namaunit FROM units WHERE kodeunit = '$kodeunit ' ORDER BY namaunit ASC");
        }
        $trend = '';
        return view('dashboard.simulasipanen', compact('unit','trend'));
    }

    public function perbandinganImport(Request $request){
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');
        $tahun = $request->input('tahun');
        $tanggal = $tahun.'-'.date('m').'-01';
        $nama_file = 'PERBANDINGAN'.$file->hashName();
        $path = $file->storeAs('public/excel/',$nama_file);
        $clear = DB::table('table_perbandingan')->where(DB::raw('YEAR(tanggal)'), $tahun)->delete();;
        if($clear){
            $import = Excel::import(new ScPerbandinganImport($tanggal), storage_path('app/public/excel/'.$nama_file));
        }else{
            $import = Excel::import(new ScPerbandinganImport($tanggal), storage_path('app/public/excel/'.$nama_file));
        }
        Storage::delete($path);

        if($import) {
            return redirect()->route('home.perbandingan')->with(['success' => 'Data Berhasil Diimport!']);
        } else {
            return redirect()->route('home.perbandingan')->with(['error' => 'Data Gagal Diimport!']);
        }
    }

    public function pengunjung(Request $request, Pengunjung $pengunjung){
        $data = $pengunjung->getData();
        return Datatables::of($data)->make(true);
    }

    public function pengunjungExcel($tahun){
        $sql = DB::select("SELECT * FROM (
				SELECT nik,
					 SUM(CASE WHEN MONTH(tanggal) = 1 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) jan,
                                SUM(CASE WHEN MONTH(tanggal) = 2 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) feb,
                                SUM(CASE WHEN MONTH(tanggal) = 3 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) mar,
                                SUM(CASE WHEN MONTH(tanggal) = 4 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) apr,
                                SUM(CASE WHEN MONTH(tanggal) = 5 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) mei,
                                SUM(CASE WHEN MONTH(tanggal) = 6 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) jun,
                                SUM(CASE WHEN MONTH(tanggal) = 7 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) jul,
                                SUM(CASE WHEN MONTH(tanggal) = 8 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) agu,
                                SUM(CASE WHEN MONTH(tanggal) = 9 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) sep,
                                SUM(CASE WHEN MONTH(tanggal) = 10 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) okt,
                                SUM(CASE WHEN MONTH(tanggal) = 11 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) nov,
                                SUM(CASE WHEN MONTH(tanggal) = 12 AND YEAR(tanggal)='$tahun' THEN 1 ELSE 0 END) desm
                            FROM  count_login GROUP BY nik)a
                JOIN (SELECT nik, nama, unit, ap, jabatanlengkap FROM vkaryawan_lastupdate GROUP BY nik) b ON b.nik = a.nik"
            );

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:O1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'PENGUNJUNG TAHUN '.$tahun);
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:O3')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('B3', 'NAMA');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('C3', 'UNIT');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('D3', 'JAN');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('E3', 'FEB');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('F3', 'MAR');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('G3', 'APR');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('H3', 'MEI');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('I3', 'JUN');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('J3', 'JUL');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('K3', 'AGU');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('L3', 'SEP');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('M3', 'OKT');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('N3', 'NOV');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('O3', 'DES');

        $rows = 4;
        $no = 1;

        foreach ($sql as $data) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $data->nama);
            $sheet->setCellValue('C' . $rows, $data->unit);
            $sheet->setCellValue('D' . $rows, $data->jan);
            $sheet->setCellValue('E' . $rows, $data->feb);
            $sheet->setCellValue('F' . $rows, $data->mar);
            $sheet->setCellValue('G' . $rows, $data->apr);
            $sheet->setCellValue('H' . $rows, $data->mei);
            $sheet->setCellValue('I' . $rows, $data->jun);
            $sheet->setCellValue('J' . $rows, $data->jul);
            $sheet->setCellValue('K' . $rows, $data->agu);
            $sheet->setCellValue('L' . $rows, $data->sep);
            $sheet->setCellValue('M' . $rows, $data->okt);
            $sheet->setCellValue('N' . $rows, $data->nov);
            $sheet->setCellValue('O' . $rows, $data->desm);

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

            $sheet->getColumnDimension('A')->setWidth('5');
            $sheet->getColumnDimension('B')->setWidth('30');
            $sheet->getColumnDimension('C')->setWidth('10');
            foreach (range('D', 'O') as $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth('10');
                $sheet->getColumnDimension($columnID)->setAutoSize(false);
            }
            $rows++;
        }

        $fileName = "REKAP_PENGUNJUNG_".$tahun.".xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function adj_harga_pakan(Request $request){
        $harga_pakan = $request->input('harga_pakan');

        DB::table('app_estmrg_adjust_value')->where('id', '1')->update([
            'harga_pakan' => $request->input('harga_pakan'),
        ]);

        Alert::toast('Data Berhasil disimpan', 'success');
        return back()->with('success', 'Data Berhasil disimpan');
    }

    public function camera_scan(){
        $nik = Auth::user()->nik;
        $sql = DB::select("SELECT url_file FROM home_camera WHERE nik='$nik' ORDER BY id DESC LIMIT 1");
        foreach($sql as $data){
            $file_cam = $data->url_file;
        }
        if (!empty($file_cam)) {
            $destinationPath = url('/camera/'.$file_cam);
            $annotateImageRequest1 = new AnnotateImageRequest();
            $annotateImageRequest1->setImageUri($destinationPath);
            $annotateImageRequest1->setFeature('TEXT_DETECTION');

            $gcvRequest = new GoogleCloudVision([$annotateImageRequest1], "AIzaSyCXq9_qc36nZfYcPv9GwgsL5UZK1KdDxcU");
            $response = $gcvRequest->annotate();
            $allKeys = array_keys((array)$response->responses[0]);
            foreach($allKeys as &$key)
            if ( $key=='error' ) {
                Alert::error('Gagal ambil data', 'Koneksi internet tidak stabil');
                return redirect()->back();
            }

            $aa =  $response->responses[0]->textAnnotations[0]->description;
            $string = str_replace(':', '', $aa);
            $arr = explode("\n", $string);
            $nik = $arr[2];
            $nama = $arr[8];
            $ttl= $arr[9];
            $kelamin= $arr[10];
            $alamat=  $arr[11];
            $rtrw= $arr[12];
            $kelurahan= $arr[13];
            $kecamatan= $arr[14];
            $agama= $arr[16];
            $pekerjaan=  $arr[23];

            $resTeks=array();
            array_push($resTeks, [
                'nik' => $arr[2],
                'nama' => $arr[8],
                'ttl' => $arr[9],
                'kelamin' => $arr[10],
                'alamat' => $arr[11],
                'rtrw' => $arr[12],
                'kelurahan' => $arr[13],
                'kecamatan' => $arr[14],
                'agama' => $arr[16],
                'pekerjaan' => $arr[23],
            ]);
            //dd($resTeks);
            //$resTeks =  $arr[0];
            //$resTeks =  $response->responses[0]->textAnnotations[0]->description;
        }else{
            $file_cam ="none.jpg";
            $resTeks = 0;
            $nik = '';
            $nama = '';
            $ttl= '';
            $kelamin='';
            $alamat=  '';
            $rtrw= '';
            $kelurahan= '';
            $kecamatan= '';
            $agama= '';
            $pekerjaan=  '';
        }
        $sqlData = DB::select("SELECT * FROM vkaryawan_lastupdate WHERE nik='$nik'");
        return view('dashboard.camera_scan', compact('file_cam','resTeks','nik','nama','ttl','kelamin','alamat','rtrw','kelurahan','kecamatan','agama','pekerjaan','sqlData'));
    }

    public function camera_scan_upload(Request $request){
        $request->validate([
          'images' => 'required',
        ]);

        if ($request->hasfile('images')) {
            $destinationPath = '/camera';
            $images = $request->file('images');
            $nama = $images->getClientOriginalName();
            $img = Image::make($images->path());
            $nama_file = rand(0,9999999999).'_'.$nama;
            $img->resize(700, 700, function ($constraint) {
                $constraint->aspectRatio();
            })->save('camera/'.$nama_file);

            $nik = Auth::user()->nik;
            DB::table('home_camera')->insert([
                            'url_file' => $nama_file,
                            'nik' => $nik,
                        ]);
        }
        return back()->with('success', 'Images uploaded successfully');
    }

    public function camera_scan_nota(){
        $nik = Auth::user()->nik;
        $sql = DB::select("SELECT url_file FROM home_camera_nota WHERE nik='$nik' ORDER BY id DESC LIMIT 1");
        foreach($sql as $data){
            $file_cam = $data->url_file;
        }

        if (!empty($file_cam)) {
            $destinationPath = url('/camera/'.$file_cam);
            $annotateImageRequest1 = new AnnotateImageRequest();
            $annotateImageRequest1->setImageUri($destinationPath);
            $annotateImageRequest1->setFeature('TEXT_DETECTION');

            $gcvRequest = new GoogleCloudVision([$annotateImageRequest1], "AIzaSyCXq9_qc36nZfYcPv9GwgsL5UZK1KdDxcU");
            $response = $gcvRequest->annotate();
            $allKeys = array_keys((array)$response->responses[0]);
            foreach($allKeys as &$key)
            if ( $key=='error' ) {
                Alert::error('Gagal ambil data', 'Koneksi internet tidak stabil');
                return redirect()->back();
            }
            $aa =  $response->responses[0]->textAnnotations[0]->description;
            $string = str_replace(' ', '', $aa);
            $arr = explode("\n", $string);

            $filtered_array = array_filter($arr, function ($var) {
                return (is_numeric($var) && strlen($var) == 3);
            });

            $hasil_dt = array_sum($filtered_array)/10;
        }else{
             $file_cam ="none.jpg";
             $hasil_dt = 0;
             $filtered_array = array();
        }
        return view('dashboard.camera_scan_nota', compact('file_cam','hasil_dt','filtered_array'));
    }

    public function camera_scan_nota_upload(Request $request){
        $request->validate([
          'images' => 'required',
        ]);

        if ($request->hasfile('images')) {
            $destinationPath = '/camera';
            $images = $request->file('images');
            $nama = $images->getClientOriginalName();
            $img = Image::make($images->path());
            $nama_file = rand(0,9999999999).'_'.$nama;
            $img->resize(700, 700, function ($constraint) {
                $constraint->aspectRatio();
            })->save('camera/'.$nama_file);

            $nik = Auth::user()->nik;
            DB::table('home_camera_nota')->insert([
                            'url_file' => $nama_file,
                            'nik' => $nik,
                        ]);
        }
        return back()->with('success', 'Images uploaded successfully');
    }

     // yaqin start
    public function rekap_pembelian_vendor_clear_temp()
    {
        DB::statement("TRUNCATE TABLE app_estmrg_setcin_temp");
    }

    public function rekap_pembelian_vendor_insert(Request $request)
    {
        $tglawal  = $request->input('tglawal');
        $tglakhir  = $request->input('tglakhir');
        return insertPembelianVendor($tglawal, $tglakhir);
    }

    public function get_rekap_pembelian_vendor(Request $request)
    {
        $ap  = $request->input('ap');
        $tglawal  = $request->input('tglawal');
        $tglakhir  = $request->input('tglakhir');
        return getUrlPembelianVendor($ap, $tglawal, $tglakhir);
    }

    public function real_panen_clear_temp()
    {
        $data_ap = ['ail','bru','btb','gps','klb','ksm','lan','lsw','mjr','mmb','mpu','mum','sga'];
        foreach($data_ap as $ap){
            $table = 'app_estmrg_panen_'.$ap.'_temp';
            DB::statement("TRUNCATE TABLE $table");
        }
    }

    public function real_panen_insert(Request $request)
    {
        $tglawal  = $request->input('tglawal');
        $tglakhir  = $request->input('tglakhir');
        return insertRealPanen($tglawal, $tglakhir);
    }

    public function get_real_panen(Request $request)
    {
        $ap  = $request->input('ap');
        $tglawal  = $request->input('tglawal');
        $tglakhir  = $request->input('tglakhir');
        return getUrlRealPanen($ap, $tglawal, $tglakhir);
    }
    // yakin end
    // motion -----------
    public function motion_tujuan()
    {
        // http_response_code(200);
        $nama = DB::table('users')->select('name', 'nik')->get();
        $ap = DB::table('users')->select('region')->groupBy('region')->get();
        $unit = DB::table('users')->select('unit')->groupBy('unit')->get();
        $jabatan = DB::table('users')->select('jabatan')->groupBy('jabatan')->get();
        $data = [
            'nama' => $nama,
            'ap' => $ap,
            'unit' => $unit,
            'jabatan' => $jabatan
        ];
        return ResponseFormatter::success($data);
    }

    public function motion()
    {
        $user = Auth::user();
        $post = MoPost::select(
                'tb_mo_post.*',
                DB::raw('IF(tb_mo_post.anonym = 1, "ANONYM", users.name) AS pengirim')
                )
            ->join('users', 'users.nik', '=', 'tb_mo_post.nik')
            ->where('tb_mo_post.nik', $user->nik)
            ->orWhereJsonContains('tujuan_nik', $user->nik)
            ->orWhereJsonContains('tujuan_ap', $user->region)
            ->orWhereJsonContains('tujuan_unit', $user->unit)
            ->orWhereJsonContains('tujuan_jabatan', $user->jabatan)
            ->get();
        // dd($post);
        return view('dashboard.motion.index', compact('post'));
    }

    public function motion_create()
    {
        return view('dashboard.motion.create');
    }

    public function motion_edit($id)
    {
        $user = Auth::user();
        $post = MoPost::find($id);
        if($user->nik == $post->nik) {
            $tujuan = [];
            foreach(json_decode($post->tujuan_nik) as $nik){
                $tujuan[] = [
                    'type' => 'nama',
                    'val' => DB::table('users')->where('nik', $nik)->value('name'),
                    'nik' => $nik
                ];
            }
            foreach(json_decode($post->tujuan_ap) as $ap){
                $tujuan[] = [
                    'type' => 'ap',
                    'val' => $ap,
                    'nik' => ''
                ];
            }
            foreach(json_decode($post->tujuan_unit) as $unit){
                $tujuan[] = [
                    'type' => 'unit',
                    'val' => $unit,
                    'nik' => ''
                ];
            }
            foreach(json_decode($post->tujuan_jabatan) as $jabatan){
                $tujuan[] = [
                    'type' => 'jabatan',
                    'val' => $jabatan,
                    'nik' => ''
                ];
            }
            return view('dashboard.motion.edit', compact('post', 'tujuan'));
        } else {
            abort(404);
        }
    }

    public function motion_post(Request $request)
    {
        $user = Auth::user();
        // kalo ada id maka update kalo tidak maka create
        if(isset($request->id)){
            // update
            $post = MoPost::find($request->id);
            MoNotif::where('post_id', $request->id)->where('nik_pengirim', $user->nik)->delete();
            // cek apakah yang mau mengedit adalah user yang membuat
            if($post->nik != $user->nik){
                abort(404);
                die;
            }
            $msg = 'mengupdate';
            $redirect = '/motion/questions/' . $request->id;
            $type = 'update';
        } else {
            // create
            $post = new MoPost();
            // $notif = new MoNotif();
            $msg = 'mengirim';
            $redirect = '/motion';
            $type = 'create';
        }

        $items = $request->tujuan;
        $tujuan = [
            'tujuan_nik' => [],
            'tujuan_ap' => [],
            'tujuan_unit' => [],
            'tujuan_jabatan' => []
        ];
        $notif = [];
        foreach($items as $item){
            $decode = json_decode($item, true);
            if($decode['type'] === 'nama'){
                $tujuan['tujuan_nik'][] = $decode['nik'];
                $notif[] = [
                    'pengirim' => $request->anonym == 'on' ? 'ANONYM' : $user->name,
                    'nik_pengirim' => $user->nik,
                    'nik_tujuan' => $decode['nik'],
                    'type' => $type
                ];
            }
            if($decode['type'] === 'ap'){
                $tujuan['tujuan_ap'][] = $decode['val'];
                foreach(DB::table('users')->select('nik')->where('region', $decode['val'])->get() as $nik){
                    // $notif[] = $nik->nik;
                    $notif[] = [
                        'pengirim' => $request->anonym == 'on' ? 'ANONYM' : $user->name,
                        'nik_pengirim' => $user->nik,
                        'nik_tujuan' => $nik->nik,
                        'type' => $type
                    ];
                }
            }
            if($decode['type'] === 'unit'){
                $tujuan['tujuan_unit'][] = $decode['val'];
                foreach(DB::table('users')->select('nik')->where('unit', $decode['val'])->get() as $nik){
                    $notif[] = [
                        'pengirim' => $request->anonym == 'on' ? 'ANONYM' : $user->name,
                        'nik_pengirim' => $user->nik,
                        'nik_tujuan' => $nik->nik,
                        'type' => $type
                    ];
                }
            }
            if($decode['type'] === 'jabatan'){
                $tujuan['tujuan_jabatan'][] = $decode['val'];
                foreach(DB::table('users')->select('nik')->where('jabatan', $decode['val'])->get() as $nik){
                    $notif[] = [
                        'pengirim' => $request->anonym == 'on' ? 'ANONYM' : $user->name,
                        'nik_pengirim' => $user->nik,
                        'nik_tujuan' => $nik->nik,
                        'type' => $type
                    ];
                }
            }
        }
        // dd($notif);

        // file
        $file_name = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $file_name = $original_name . '_' . time() . '.' . $extension;
            $file->move('motion-file/', $file_name);
        }

        $post->title = $request->title;
        $post->body = $request->body;
        $file_name != null ? $post->file = $file_name : null;
        $post->link = $request->link;
        $post->nik = $user->nik;
        $post->tujuan_nik = json_encode($tujuan['tujuan_nik']);
        $post->tujuan_ap = json_encode($tujuan['tujuan_ap']);
        $post->tujuan_unit = json_encode($tujuan['tujuan_unit']);
        $post->tujuan_jabatan = json_encode($tujuan['tujuan_jabatan']);
        // $post->notifikasi = json_encode(remove_duplicates($nik_notif));
        $post->anonym = $request->anonym == 'on' ? 1 : 0;

        $save = $post->save();

        // notifikasi
        $p_id = $post->id;
        $notif = array_map(function ($item) use ($p_id) {
            $item['post_id'] = $p_id;
            return $item;
        }, $notif);
        MoNotif::insert($notif);

        if($save){
            Alert::success('Berhasil '. $msg .' laporan');
            return redirect($redirect);
        } else {
            Alert::error('Gagal '. $msg .' laporan');
            return redirect($redirect);
        }
    }

    public function mo_question($id)
    {
        $user = Auth::user();
        $post = MoPost::select(
                    'tb_mo_post.*',
                    DB::raw('IF(tb_mo_post.anonym = 1, "ANONYM", users.name) AS pengirim')
                )
            ->where('tb_mo_post.id', $id)
            ->join('users', 'tb_mo_post.nik', '=', 'users.nik')
            ->first();
        if ($post) {
            $nik = json_decode($post->tujuan_nik);
            $ap = json_decode($post->tujuan_ap);
            $unit = json_decode($post->tujuan_unit);
            $jabatan = json_decode($post->tujuan_jabatan);
            if ($user->nik == $post->nik || in_array($user->nik, $nik) || in_array($user->region, $ap) || in_array($user->unit, $unit) || in_array($user->jabatan, $jabatan)) {
                $deleted = MoNotif::where('post_id', $id)->where('nik_tujuan', $user->nik)->delete();
                if($deleted > 0) {
                    return redirect()->refresh();
                }
                return view('dashboard.motion.questions', compact('post', 'user'));
            } else {
                // jika user yang login tidak memiliki akses melihat
                abort(404);
            }
        } else {
            // jika tidak ada post dengan id
            abort(404);
        }
    }

    public function mo_answer($id)
    {
        $user = Auth::user();
        $answer = MoAnswers::select(
            'tb_mo_answer.*',
            DB::raw('IF(tb_mo_answer.anonym = 1, "ANONYM", users.name) AS pengirim')
        );
        $answer = $answer->join('users', 'tb_mo_answer.nik', '=', 'users.nik');
        $answer = $answer->where('post_id', $id);
        if ($post = MoPost::find($id)) {
            if ($user->nik != $post->nik) {
                $answer = $answer->where('tb_mo_answer.nik', $user->nik);
            }
        }
        $answer = $answer->get();
        return ResponseFormatter::success([$answer], 'Berhasil');
    }

    public function mo_answer_post(Request $request)
    {
        $user = Auth::user();
        try {
            $file_name = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $file_name = $original_name . '_' . time() . '.' . $extension;
                $file->move('motion-file/', $file_name);
            }

            if(!empty($request->id)){
                // update
                $answer = MoAnswers::find($request->id);
                $nik = $answer->nik;
                $type = 'answer';
            } else {
                // create
                $answer = new MoAnswers();
                $nik = $user->nik;
                $type = 'answer';
            }
            $answer->post_id = $request->post_id;
            $answer->body = $request->body;
            $answer->file = $file_name;
            $answer->nik = $nik;
            $answer->anonym = 0;
            $answer->is_read = 0;

            $answer->save();

            MoNotif::create([
                'post_id' => $request->p_id,
                'pengirim' => $user->name,
                'nik_pengirim' => $user->nik,
                'nik_tujuan' => $request->t_nik,
                'type' => $type
            ]);

            $answer->anonym == 1 ? $answer->pengirim = 'ANONYM' : $answer->pengirim = $user->name;
            return ResponseFormatter::success([$answer], 'Berhasil mengirim balasan');
        } catch (\Exception $e) {
            return ResponseFormatter::success([$e], 'Gagal mengirim balasan: ' . $e->getMessage(), 400);
        }
    }

    public function mo_comment($id)
    {
        $comment = MoComments::select(
            'tb_mo_comment.*',
            DB::raw('IF(tb_mo_comment.anonym = 1, "ANONYM", users.name) AS pengirim')
        )
        ->where('answer_id', $id)
        ->join('users', 'tb_mo_comment.nik', '=', 'users.nik')
        ->get();
        return ResponseFormatter::success([$comment], 'Berhasil');
    }

    public function mo_comment_post(Request $request)
    {
        $user = Auth::user();
        try {
            $file_name = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $file_name = $original_name . '_' . time() . '.' . $extension;
                $file->move('motion-file/', $file_name);
            }

            if(!empty($request->id)){
                // update
                $answer = MoComments::find($request->id);
                $type = 'answer';
            } else {
                // create
                $answer = new MoComments();
                $type = 'answer';
            }
            $request->answer_id && $answer->answer_id = $request->answer_id;
            $answer->body = $request->body;
            $answer->file = $file_name;
            $answer->nik = $user->nik;
            $answer->anonym = 0;
            $answer->is_read = 0;

            $answer->save();

            MoNotif::create([
                'post_id' => $request->p_id,
                'pengirim' => $user->name,
                'nik_pengirim' => $user->nik,
                'nik_tujuan' => $request->t_nik,
                'type' => $type
            ]);

            $answer->anonym == 1 ? $answer->pengirim = 'ANONYM' : $answer->pengirim = $user->name;
            return ResponseFormatter::success([$answer], 'Berhasil mengirim balasan');
        } catch (\Exception $e) {
            return ResponseFormatter::success([$e], 'Gagal mengirim balasan: ' . $e->getMessage(), 400);
        }
    }

    // sudah
    public function mo_delete($id)
    {
        $user = Auth::user();
        $post = MoPost::find($id);
        if($post-> nik == $user->nik) {
            try {
                $answer = MoAnswers::where('post_id', $post->id)->get();
                $answerIds = $answer->pluck('id');
                MoComments::whereIn('answer_id', $answerIds)->delete();
                foreach ($answer as $ans) {
                    $ans->delete();
                }
                $post->delete();
                Alert::success('Berhasil menghapus');
                return redirect('/motion');
            } catch (\Exception $e) {
                Alert::error('Gagal menghapus: ' . $e->getMessage());
                return redirect('/motion');
            }
        } else {
            abort(404);
        }
    }

    public function mo_delete_child(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required'
        ]);
        if ($validator->fails()){
            return ResponseFormatter::error([$validator->errors()], $validator->errors()->first());
        } else {
            try {
                switch ($request->type) {
                    case 'answer':
                        $deleted = MoAnswers::find($request->id)->delete();
                        MoComments::where('answer_id', $request->id)->delete();
                        break;
                    case 'comment':
                        $deleted = MoComments::find($request->id)->delete();
                        break;
                }
                return ResponseFormatter::success([$deleted], 'Berhasil menghapus');
            } catch (\Exception $e) {
                return ResponseFormatter::success([$e], 'Gagal menghapus: ' . $e->getMessage(), 400);
            }
        }
    }

    public function survey_sales(Request $request)
    {
        if(Gate::check('isAdmin') || Gate::check('isSofi')) {
            $ftanggal = $request->input('ftanggal');
            $data = DB::table('tb_survey_sales')
                ->whereDate('tb_survey_sales.created_at', $ftanggal)
                ->join('users', 'tb_survey_sales.nik', '=', 'users.nik')
                ->select('tb_survey_sales.*', 'users.name AS nama')
                ->get();
            return view('dashboard.survey_sales', compact('data', 'ftanggal'));
        } else {
            abort(404);
        }
    }


    public function survey_sales_form()
    {
        $name = Auth::user()->name;
        $pergerakan_bakul = [
            [
                'nama' => 'Bakul Playon Tanya Stok',
                'slug' => 'bakul-playon-tanya-stok'
            ],
            [
                'nama' => 'Bakul Luko Tanya Stok',
                'slug' => 'bakul-luko-tanya-stok'
            ],
            [
                'nama' => 'RPA Tanya Stok',
                'slug' => 'rpa-tanya-stok'
            ],
            [
                'nama' => 'Hanya Bakul Rutinan',
                'slug' => 'hanya-bakul-rutinan'
            ],
        ];
        $param = [
            [
                'name' => 'AK (1,0-1,5)',
                'slug' => 'ak'
            ],
            [
                'name' => 'AT (1,5-1,8)',
                'slug' => 'at'
            ],
            [
                'name' => 'AB (1,8-2,3)',
                'slug' => 'ab'
            ],
            [
                'name' => 'AJ (2,3-2,8)',
                'slug' => 'aj'
            ],
            [
                'name' => 'AS (2,8 up)',
                'slug' => 'as'
            ],
        ];
        return view('dashboard.survey_sales_form', compact('name', 'pergerakan_bakul', 'param'));
    }

    public function survey_sales_send(Request $request)
    {
        try {
            $user = Auth::user();
            $ref = DB::table('tb_ref_survey')->where('unit', $user->unit)->first();
            DB::table('tb_survey_sales')->where('nik', $user->nik)->whereDate('created_at', Carbon::now()->format('Y-m-d'))->delete();
            DB::table('tb_survey_sales')->insert([
                'nik' => $user->nik,
                'area_jual' => $ref ? $ref->wilayah : $user->unit,
                'pergerakan_bakul' => json_encode($request->pergerakan_bakul),
                's_bw_ak' => isset($request->s_bw_ak) ? json_encode($request->s_bw_ak) : '[]',
                's_bw_at' => isset($request->s_bw_at) ? json_encode($request->s_bw_at) : '[]',
                's_bw_ab' => isset($request->s_bw_ab) ? json_encode($request->s_bw_ab) : '[]',
                's_bw_aj' => isset($request->s_bw_aj) ? json_encode($request->s_bw_aj) : '[]',
                's_bw_as' => isset($request->s_bw_as) ? json_encode($request->s_bw_as) : '[]',
                'kat_ak' => isset($request->kat_ak) ? json_encode($request->kat_ak) : '[]',
                'kat_at' => isset($request->kat_at) ? json_encode($request->kat_at) : '[]',
                'kat_ab' => isset($request->kat_ab) ? json_encode($request->kat_ab) : '[]',
                'kat_aj' => isset($request->kat_aj) ? json_encode($request->kat_aj) : '[]',
                'kat_as' => isset($request->kat_as) ? json_encode($request->kat_as) : '[]',
                'realisasi_ytd' => $request->ytd,
                'realisasi_lokal' => $request->lokal,
                'realisasi_lk' => $request->lk,
                'realisasi_rpa' => $request->rpa,
                'perlawanan_harga' => $request->perlawanan_harga
            ]);
            Alert::success('Berhasil mengirim survey');
            return redirect('/home');
        } catch (\Exception $e) {
            Alert::error('Gagal mengirim survey' . $e->getMessage());
            return redirect('/survey-sales-form');
        }
    }

    public function dilihat(Request $request){
        $id = $request->input('id');
        $tabel = $request->input('tabel');
        DB::statement("UPDATE $tabel SET dilihat=dilihat+1 WHERE id=$id");

        return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
                ]);
    }

    public function dilihat_dashboard(Request $request){
        $field = $request->input('field');
        DB::statement("UPDATE table_var_statik SET $field=$field+1 WHERE id=1");

        return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
                ]);
    }

    public function pantauan_final()
    {
        $std = DB::table('table_std_pfmc')->get();
        $stdRow = function ($bw) use ($std) {
            return $std->first(function ($item) use ($bw) {
                return $item->bw == $bw;
            });
        };
        // dd($stdRow('1.68'));
        $rhpp_exist = DB::table('table_rhpp')->orderBy('tgldocfinal', 'DESC')->pluck('tgldocfinal')->first();
        if (isset($rhpp_exist)) {
            $bulan = date('m', strtotime($rhpp_exist));
            $tahun = date('Y', strtotime($rhpp_exist));
        } else {
            $bulan = date('m');
            $tahun = date('Y');
        }
        // $bulan = '01';
        // $tahun = '2023';
        $data = DB::table('table_rhpp AS r')
            ->rightJoin('units AS u', 'u.kodeunit', '=', 'r.unit')
            ->select(
                'r.unit',
                'u.region AS ap',
                'u.zona',
                DB::raw("SUM(r.ciawal) AS pop"),
                DB::raw("SUM(CASE WHEN r.nomrhpprugi <= 0 THEN r.ciawal ELSE 0 END) AS pop_rhpp"),
                DB::raw("SUM(r.coekor) AS ekor"),
                DB::raw("SUM(r.cokg) AS ton_lb"),
                DB::raw("SUM(r.feedkgqty) AS ton_pakan"),
                DB::raw("SUM(r.nomrhpptotal) AS total"),
                DB::raw("SUM(CASE WHEN r.nomrhpprugi <= 0 THEN r.nomrhpptotal ELSE 0 END) AS total_rhpp"),
                DB::raw("SUM(r.rmsbantulabarugi) AS rmsrugi"),
                DB::raw("SUM(r.rmsbantuumur) AS rmsumur"),
                DB::raw("SUM(r.rmsbantudpls) AS rmsdpls"),
                DB::raw("SUM(r.nomrhpprugi) AS rugi"),
                DB::raw("SUM(r.nomrhpppfmc) AS pfmc"),
                DB::raw("SUM(r.valbbcndoc) AS vcndoc"),
                DB::raw("SUM(r.jualayamdnbakul) AS vdnbakul"),
                DB::raw("SUM(r.nomrhppbonus) AS rhppbonus"),
                DB::raw("SUM(CASE WHEN r.nomrhpprugi <= 0 THEN r.nomrhppkompensasi ELSE 0 END) AS kompen_rhpp"),
                // DB::raw("SUM(r.nomrhppkompensasi) AS rhppkompensasi"),
                DB::raw("SUM(r.valbbdoc) AS vdoc"),
                DB::raw("SUM(r.valbbbelifeed) AS vfeed"),
                DB::raw("SUM(r.valbbloco) AS vfeedloco"),
                DB::raw("SUM(r.valbbovk) AS vovk"),
                DB::raw("SUM(r.hitunguangselisih) AS potselisih"),
                DB::raw("SUM(r.valtotbeli) AS sapronak"),
                DB::raw("SUM(r.jualayamactual) AS jual"),
                DB::raw("COUNT(*) AS jml_flok"),
                DB::raw("SUM(CASE WHEN r.nomrhpprugi > 0 THEN 1 ELSE 0 END) AS flok_rugi"),
                'u.area_jual',
                'u.grup_area',
            )
            ->whereMonth('r.tgldocfinal', $bulan)
            ->whereYear('r.tgldocfinal', $tahun)
            ->groupBy('r.unit')
            ->orderBy('u.region', 'ASC')
            ->orderBy('r.unit', 'ASC')
            ->get();
        // dd($data);

        // rumus-rumus master (unit ap, pop, ekor, ton_lb, ton_pakan, total, rmsumur, rmsdpls, rugi, pfmc, vcndoc, vdnbakul, rhppbonus, rhppkompensasi, vdoc, vfeed, vovk, potselisih, sapronak, jual, jml_flok, flok_rugi)
        // map 1
        $data->map(function ($item) use ($stdRow) {
            // rupro (kurang rank)
            $item->rupro_rugi = $item->pop != 0 ? round($item->rugi / $item->pop) : 0;
            $item->rupro_jmlflok = $item->jml_flok;
            $item->rupro_flokrugi = $item->flok_rugi;

            // ip (kurang rank)
            $item->ip_bw = $item->ekor == 0 ? 0 : round($item->ton_lb / $item->ekor, 2);
            $item->ip_fcr = $item->ton_lb == 0 ? 0 : round($item->ton_pakan / $item->ton_lb, 3);
            $item->ip_dpls = $item->pop == 0 ? 0 : round($item->rmsdpls / $item->pop, 2);
            $item->ip_umur = $item->pop == 0 ? 0 : round($item->rmsumur / $item->pop, 1);
            $item->ip_ip = $item->ip_bw == 0 ? 0 : round((((100 - $item->ip_dpls) * (100 * $item->ip_bw)) / $item->ip_fcr) / $item->ip_umur);

            // fcr (kurang rank)
            $item->fcr_bw = $item->ekor == 0 ? 0 : round($item->ton_lb / $item->ekor, 2);
            $item->fcr_fcract = $item->ton_lb == 0 ? 0 : round($item->ton_pakan / $item->ton_lb, 3);
            $item->fcr_fcrstd = $stdRow(strval($item->fcr_bw)) ? round($stdRow(strval($item->fcr_bw))->fcr, 3) : 0;
            $item->fcr_fcrdiff = round(($item->fcr_fcract - $item->fcr_fcrstd) * 100, 1);

            // dpls (kurang rank)
            $item->dpls_bw = $item->ekor == 0 ? 0 : round($item->ton_lb / $item->ekor, 2);
            $item->dpls_dplsact = $item->pop == 0 ? 0 : round($item->rmsdpls / $item->pop, 1);
            $item->dpls_dplsstd = $stdRow(strval($item->dpls_bw)) ? round($stdRow(strval($item->dpls_bw))->dpls, 1) : 0;
            $item->dpls_dplsdiff = round($item->dpls_dplsact - $item->dpls_dplsstd, 1);

            // rhpp
            $item->rhpp_difffcr = $item->fcr_fcrdiff;
            $item->rhpp_murni = $item->pop_rhpp == 0 ? 0 : round($item->pfmc / $item->pop_rhpp);
            $item->rhpp_cndoc = $item->pop_rhpp == 0 ? 0 : round($item->vcndoc / $item->pop_rhpp);
            $item->rhpp_bonus = $item->pop_rhpp == 0 ? 0 : round(($item->vdnbakul + $item->rhppbonus) / $item->pop_rhpp);
            $item->rhpp_kompen = $item->pop_rhpp == 0 ? 0 : round($item->kompen_rhpp / $item->pop_rhpp);
            $item->rhpp_rhppekor = $item->pop_rhpp == 0 ? 0 : round($item->total_rhpp / $item->pop_rhpp);

            // hpp
            $item->hpp_doccost = $item->ton_lb == 0 ? 0 : round($item->vdoc / $item->ton_lb);
            $item->hpp_feedcost = $item->ton_lb == 0 ? 0 : round($item->vfeed / $item->ton_lb);
            $item->hpp_ovkcost = $item->ton_lb == 0 ? 0 : round($item->vovk / $item->ton_lb);
            $item->hpp_rhppkg = $item->ton_lb == 0 ? 0 : round($item->total / $item->ton_lb);
            $item->hpp_hpp = $item->hpp_doccost + $item->hpp_feedcost + $item->hpp_ovkcost + $item->hpp_rhppkg;

            // mrg
            $item->mrg_bw = $item->fcr_bw;
            $item->mrg_hpp = $item->ton_lb == 0 ? 0 : round(($item->potselisih + $item->total + $item->sapronak) / $item->ton_lb);
            $item->mrg_hargalb = $item->ton_lb == 0 ? 0 : round($item->jual / $item->ton_lb);
            $item->mrg_mrgkg = $item->mrg_hargalb - $item->mrg_hpp;
            $item->mrg_mrgekor = $item->pop == 0 ? 0 : round($item->rmsrugi / $item->pop);

            // hlb
            $item->hlb_bw = $item->fcr_bw;
            $item->hlb_hargalb = $item->ton_lb == 0 ? 0 : round($item->jual / $item->ton_lb);

            // fcost
            $item->fcost_bw = $item->fcr_bw;
            $item->fcost_difffcr = $item->fcr_fcrdiff;
            $item->fcost_harga = $item->ton_pakan == 0 ? 0 : round($item->vfeedloco / $item->ton_pakan);
            $item->fcost_feedcost = $item->ton_lb == 0 ? 0 : round($item->vfeed / $item->ton_lb);
            return collect($item)->toArray();
        });

        // rata rata per area
        $avghpp = [];
        $maxhlb = [];
        $perfeed = [];
        foreach ($data->groupBy('grup_area') as $key => $value) {
            $avghpp[$key] = round($value->avg('hpp_hpp'));
            $maxhlb[$key] = round($value->max('hlb_hargalb'));
            $perfeed[$key] = round($value->sum('vfeed') / $value->sum('ton_lb'));
        }

        // map 2
        $data->map(function ($item) use ($data, $avghpp, $maxhlb, $perfeed) {
            // rupro
            // ip
            // fcr
            // dpls
            // rhpp (diff perlu di cek)
            $item->rhpp_bw = $item->fcr_bw;
            $item->rhpp_diff = round($item->rhpp_rhppekor - ($data->sum('pop_rhpp') == 0 ? 0 : ($data->sum('total_rhpp') / $data->sum('pop_rhpp'))));
            $item->rhpp_kriteria = ($item->rhpp_diff > 1000) ? "SANGAT TINGGI" : (($item->rhpp_diff >= 501 && $item->rhpp_diff <= 1000) ? "TINGGI" : (($item->rhpp_diff >= -500 && $item->rhpp_diff <= 500) ? "NORMAL" : (($item->rhpp_diff >= -1000 && $item->rhpp_diff <= -501) ? "RENDAH" : "SANGAT RENDAH")));

            // hpp
            $item->hpp_diff = round($item->hpp_hpp - $avghpp[$item->grup_area]);
            $item->hpp_kriteria = ($item->hpp_diff > 500) ? 'SANGAT TINGGI' : ($item->hpp_diff >= 300 ? 'TINGGI' : 'NORMAL');

            // mrg
            // hlb
            $item->hlb_diff = round($item->hlb_hargalb - $maxhlb[$item->grup_area]);
            $item->hlb_vssmt = round($item->hlb_hargalb - ($data->sum('ton_lb') == 0 ? 0 : ($data->sum('jual') / $data->sum('ton_lb'))));

            // fcost
            $item->fcost_diff = $item->fcost_feedcost - $perfeed[$item->grup_area];
            return collect($item)->toArray();
        });
        // dd($data);
        $lap = DB::table('regions')->pluck('koderegion')->toArray();
        $lunit = DB::table('units')->select('kodeunit', 'region')->get();
        return view('dashboard.pantauan_final', compact('data', 'lap', 'lunit'));
    }

    public function flok_under_bw(Request $request)
    {
        $user = Auth::user();
        $unit = $request->input('unit');
        $ap = $request->input('ap');
        try {
            $data = DB::table('vpantauanflok')
                ->selectRaw('
                    vpantauanflok.*,
                    table_std_umur.bw AS std_bw,
                    ROUND(vpantauanflok.umur - data_telat) AS real_umur,
                    ROUND((vpantauanflok.bw * 100000) / table_std_umur.bw) AS capaian_bw
                ')
                ->leftJoin('table_std_umur', function ($join) {
                    $join->on(DB::raw('ROUND(vpantauanflok.umur - data_telat)'), '=', 'table_std_umur.umur');
                })
                ->whereRaw('vpantauanflok.umur - data_telat <= 25')
                ->whereRaw('(vpantauanflok.bw * 100000) / table_std_umur.bw <= 85');
            if (isset($ap)) {
                $data->where('ap', $ap);
            }
            if (isset($unit)) {
                $data->where('unit', $unit);
            }
            if (!in_array($user->roles, ['pusat', 'admin'])) {
                $data->where('ap', $user->region);
                if (!in_array($user->roles, ['region', 'sr'])) {
                    $data->where('unit', $user->unit);
                }
            }
            $data = $data
                ->orderBy('vpantauanflok.ap')
                ->orderBy('vpantauanflok.unit')
                ->get();
            return response()->json(['success' => true, 'msg' => 'berhasil', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage(), 'data' => []]);
        }
    }

    public function data_tidak_wajar(Request $request)
    {
        $user = Auth::user();
        $unit = $request->input('unit');
        $ap = $request->input('ap');
        try {
            $data = DB::table('vpantauanflok');
            if (isset($ap)) {
                $data->where('ap', $ap);
            }
            if (isset($unit)) {
                $data->where('unit', $unit);
            }
            if (!in_array($user->roles, ['pusat', 'admin'])) {
                $data->where('ap', $user->region);
                if (!in_array($user->roles, ['region', 'sr'])) {
                    $data->where('unit', $user->unit);
                }
            }
            $data = $data
                ->whereNotBetween('harga_rhpp', [-7000, 7000])
                ->orderBy('harga_rhpp')
                ->get();
            return response()->json(['success' => true, 'msg' => 'berhasil', 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage(), 'data' => []]);
        }
    }

    public function respon_ayam_sakit(Request $request)
    {
        try {
            $data = [
                'flok' => $request->nama_flok,
                'unit' => $request->unit,
                'chick_in' => $request->tanggal_chick_in,
            ];
            $sql = DB::table('tb_respon_data_harian');
            if ($sql->where($data)->count() > 0) {
                $sql->where($data)->update(['respon_ayam_sakit' => $request->respon]);
            } else {
                $sql->insert(array_merge($data, ['respon_ayam_sakit' => $request->respon]));
            }
            return response()->json(['success' => true, 'msg' => 'Berhasil', 'data' => ['row_affected' => $sql]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage(), 'data' => []]);
        }
    }

    public function respon_rhpp_rugi(Request $request)
    {
        try {
            $data = [
                'flok' => $request->nama_flok,
                'unit' => $request->unit,
                'chick_in' => $request->tanggal_chick_in,
            ];
            $sql = DB::table('tb_respon_data_harian');
            if ($sql->where($data)->count() > 0) {
                $sql->where($data)->update(['respon_rhpp_rugi' => $request->respon]);
            } else {
                $sql->insert(array_merge($data, ['respon_rhpp_rugi' => $request->respon]));
            }
            return response()->json(['success' => true, 'msg' => 'Berhasil', 'data' => ['row_affected' => $sql]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage(), 'data' => []]);
        }
    }

    public function respon_bw_under(Request $request)
    {
        try {
            $result = DB::table('tb_respon_data_harian')->upsert($request->all(), ['flok', 'unit', 'chick_in'], ['respon_bw_under']);
            return response()->json(['success' => true, 'msg' => 'Berhasil', 'data' => ['row_affected' => $result]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage(), 'data' => []]);
        }
    }

    public function send_data_bw_under(Request $request)
    {
        $raw_data = (array) $this->flok_under_bw($request)->getData();
        $unit_include = array_values(array_unique(array_column($raw_data['data'], 'unit')));
        $kontak = DB::table('tblkontak')
            ->whereIn('jabatan', ['KEPALA PRODUKSI', 'TECHNICAL SUPPORT'])
            ->whereIn('unit', $unit_include)
            ->get();
        $pesan = array_values($kontak->map(function ($item) use ($raw_data) {
            $isi_pesan = array_values(array_filter($raw_data['data'], function($x) use ($item) {
                switch ($item->jabatan) {
                    case 'KEPALA PRODUKSI':
                        return $item->unit === $x->unit;
                    case 'TECHNICAL SUPPORT':
                        return $item->unit === $x->unit && $item->nama === $x->ts;
                    default:
                        return false;
                }
            }));
            return [
                'number' => $item->nowa,
                'nama' => $item->nama,
                'flok' => $isi_pesan,
                'delay' => rand(5,15)
            ];
        })->filter(function($item) {
            return !empty($item['flok']);
        })->toArray());
        return response()->json($pesan);
    }

    public function send_data_tidak_wajar(Request $request)
    {
        $raw_data = (array) $this->data_tidak_wajar($request)->getData();
        $unit_include = array_values(array_unique(array_column($raw_data['data'], 'unit')));
        $kontak = DB::table('tblkontak')
            ->whereIn('jabatan', ['KEPALA UNIT'])
            ->whereIn('unit', $unit_include)
            ->get();
        $pesan = array_values($kontak->map(function ($item) use ($raw_data) {
            $isi_pesan = array_values(array_filter($raw_data['data'], function($x) use ($item) {
                switch ($item->jabatan) {
                    case 'KEPALA UNIT':
                        return $item->unit === $x->unit;
                    default:
                        return false;
                }
            }));
            return [
                'number' => $item->nowa,
                'nama' => $item->nama,
                'flok' => $isi_pesan,
                'delay' => rand(5,15)
            ];
        })->filter(function($item) {
            return !empty($item['flok']);
        })->toArray());
        return response()->json($pesan);
    }

    private function survey_mis_soal()
  {
    return (object) [
      'soal_1' => (object) [
        'cols' => 'seberapa_sering_akses_mis',
        's' => 'Seberapa sering anda mengakses Web MIS ?',
        'j' => [
          'Sangat Jarang (1 bulan 1 kali)',
          'Jarang (1 Minggu 1 kali)',
          'Sering (1 Minggu 2-3 kali)',
          'Sangat Sering (Hampir setiap hari)',
        ],
      ],
      'soal_2' => (object) [
        'cols' => 'apakah_tampilan_mis_mudah',
        's' => 'Apakah tampilan Web MIS mudah untuk mencari menu-menu yang anda inginkan ?',
        'j' => [
          'Sangat Sulit',
          'Sulit',
          'Mudah',
          'Sangat Mudah',
        ],
      ],
      'soal_3' => (object) [
        'cols' => 'apakah_tampilan_mis_menarik',
        's' => 'Apakah tampilan menu di web MIS menarik dan mudah dibaca ?',
        'j' => [
          'Tampilan Menarik dan Mudah Dibaca',
          'Tampilan Kurang Menarik dan Mudah Dibaca',
          'Tampilan Menarik dan Tidak Mudah Dibaca',
          'Tampilan Kurang Menarik dan Tidak Mudah Dibaca',
        ],
      ],
      'soal_4' => (object) [
        'cols' => 'kecepatan_akses_mis',
        's' => 'Bagaimana kecepatan akses web MIS (membuka tiap menu & menampilkan isian menu) saat kondisi internet stabil ?',
        'j' => [
          'Sangat Lamban',
          'Lamban',
          'Cepat',
          'Sangat Cepat',
        ],
      ],
      'soal_5' => (object) [
        'cols' => 'apakah_data_mis_bermanfaat',
        's' => 'Apakah data yang ada di MIS bermanfaat untuk anda ?',
        'j' => [
          'Tidak bermanfaat, jika data yang ada tidak menunjang kinerja',
          'Kurang bermanfaat, jika data sebagian kecil menunjang kinerja',
          'Bermanfaat, jika data lebih dari 50% menunjang kinerja',
          'Sangat bermanfaat, jika data sebagaian besar menunjang kinerja',
        ],
      ],
      'soal_6' => (object) [
        's' => 'Sebutkan tiga menu yang paling bermanfaat untuk anda ?<br> Mohon Tulis jawaban di bawah ini :',
        'j' => [],
      ],
      'soal_7' => (object) [
        's' => 'Berikan penilaian untuk kepuasaan terhadap web MIS saat ini<br> Semakin Puas maka skor Semakin Tinggi',
        'j' => [],
      ],
      'soal_8' => (object) [
        's' => 'Berikan Masukan & Saran',
        'j' => [],
      ],
    ];
  }

  public function rekap_survey_mis(Request $request)
  {
    $show = (boolean) $request->input('show') ?? false;
    $n = $request->input('n') ?? 'data';
    $data = DB::table('tb_survey_web_mis')
    ->join('users', 'users.nik', '=', 'tb_survey_web_mis.nik')
    ->select(
      'users.name',
      'users.jabatan',
      'users.region',
      'tb_survey_web_mis.*',
    )
    ->limit($show ? null : 0)
    ->get();
    $soal = (object) array_map(function ($item) use ($show, $data) {
      $item->j = array_map(function ($j) use ($item, $show, $data) {
        $c = DB::table('tb_survey_web_mis')
        ->limit($show ? null : 0)
        ->where($item->cols, $j)
        ->count();
        return (object) [
          'j' => $j,
          'c' => $c,
          'p' => count($data) === 0 ? 0 : ($c / count($data)) * 100,
        ];
      }, $item->j);
      // dd($item);
      return $item;
    }, (array) $this->survey_mis_soal());
    // dd($data);
    return view('dashboard.home.rekap_survey_mis', compact('data', 'soal', 'n'));
  }

  public function survey_mis(Request $request)
  {
    $user = Auth::user()->only(['name', 'nik']);
    $soal = $this->survey_mis_soal();
    return view('dashboard.home.survey_mis', compact('user', 'soal'));
  }

  public function survey_mis_submit(Request $request)
  {
    try {
      $user = Auth::user();
      $validator = Validator::make(
        ['nik' => $user->nik],
        [
          'nik' => [
            Rule::unique('tb_survey_web_mis', 'nik'),
          ],
        ], [
          'nik.unique' => 'Anda sudah pernah mengisi survei',
        ]
      );
      if ($validator->fails()) {
        Alert::error($validator->errors()->first());
        return redirect('home');
      }
      $data = [
        'nik' => $user->nik,
        'seberapa_sering_akses_mis' => $request->j_1,
        'apakah_tampilan_mis_mudah' => $request->j_2,
        'apakah_tampilan_mis_menarik' => $request->j_3,
        'kecepatan_akses_mis' => $request->j_4,
        'apakah_data_mis_bermanfaat' => $request->j_5,
        'menu_bermanfaat_1' => $request->j_6_1,
        'menu_bermanfaat_2' => $request->j_6_2,
        'menu_bermanfaat_3' => $request->j_6_3,
        'kepuasan' => (int) $request->j_7,
        'saran_masukan' => $request->j_8,
      ];
      DB::table('tb_survey_web_mis')->insert($data);
      Alert::success('Terimakasih telah mengisi survey');
      return redirect('home');
    } catch (\Exception $e) {
      Alert::error('Gagal mengirim survei: ' . $e->getMessage());
      return redirect('home');
    }
  }

  public function survey_ho(Request $request)
  {
    $user = Auth::user();
    $self_rating = DB::table('tb_score_karyawan_ho')
    ->where('nik_user', $user->nik)
    ->get()
    ->unique('nik_karyawan')
    ->mapWithKeys(function ($item) {
      return [$item->nik_karyawan => $item];
    });

    $raw_data = DB::table('tb_karyawan_ho')
    ->orderBy('divisi')
    ->orderBy('jabatan')
    ->get()
    ->map(function ($item) use ($self_rating) {
      if (isset($self_rating[$item->nik])) {
        $item->rating = $self_rating[$item->nik]->score;
        $item->pesan = $self_rating[$item->nik]->text;
      } else {
        $item->rating = null;
        $item->pesan = null;
      }
      return $item;
    });

    $data_divisi = $raw_data
    ->groupBy('divisi')
    ->map(function ($item) {
      return $item->groupBy('bagian');
    });
    return view('dashboard.home.survey_ho', compact('data_divisi', 'raw_data'));
  }

  public function survey_ho_save(Request $request)
  {
    $user = Auth::user();
    try {
      $check = DB::table('tb_score_karyawan_ho')
      ->where([
        'nik_user' => $user->nik,
        'nik_karyawan' => $request->nik,
      ])
      ->get();
      if (count($check) === 0) {
        DB::table('tb_score_karyawan_ho')
        ->insert([
          'nik_user' => $user->nik,
          'nik_karyawan' => $request->nik,
          'score' => $request->rating,
          'text' => $request->pesan,
        ]);
      } else {
        DB::table('tb_score_karyawan_ho')
        ->where([
          'nik_user' => $user->nik,
          'nik_karyawan' => $request->nik,
        ])
        ->update([
          'score' => $request->rating,
          'text' => $request->pesan,
        ]);
      }
      return response()->json(['data' => $request->all(), 'check' => check_survey_kepuasan_karyawan_ho($user)]);
    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 400);
    }
  }

  public function survey_ho_rekap(Request $request)
  {
    $n = $request->input('n') ?? 'nav-per-orang';

    $raw = DB::table('tb_score_karyawan_ho')
    ->select(
      'tb_score_karyawan_ho.*',
      DB::raw("AVG(score) AS score"),
      DB::raw("COUNT(score) AS jml"),
    )
    ->groupBy('nik_karyawan')
    ->get()
    ->mapWithKeys(function ($item) {
      return [$item->nik_karyawan => $item];
    });

    $per_orang = DB::table('tb_karyawan_ho')
    ->get()
    ->map(function ($item) use ($raw) {
      if (isset($raw[$item->nik])) {
        $item->avg_score = (double) $raw[$item->nik]->score;
        $item->jml = $raw[$item->nik]->jml;
      } else {
        $item->avg_score = 0;
        $item->jml = 0;
      }
      return $item;
    })
    ->sortByDesc('avg_score')
    ->values();

    $per_bagian = $per_orang
    ->where('avg_score', '<>', 0)
    ->groupBy('bagian')
    ->filter(function ($_, $index) {
      return $index !== '';
    })
    ->map(function ($item, $index) {
      return (object) [
        'bagian' => $index,
        'avg_score' => $item->avg('avg_score'),
        // 'jml' => $item->sum('jml'),
      ];
    })
    ->sortByDesc('avg_score')
    ->values();

    $per_divisi = $per_orang
    ->where('avg_score', '<>', 0)
    ->groupBy('divisi')
    ->filter(function ($_, $index) {
      return $index !== '';
    })
    ->map(function ($item, $index) {
      return (object) [
        'divisi' => $index,
        'avg_score' => $item->avg('avg_score'),
        // 'jml' => $item->sum('jml'),
      ];
    })
    ->sortByDesc('avg_score')
    ->values();

    return view('dashboard.home.rekap_survey_ho', compact('n', 'per_orang', 'per_bagian', 'per_divisi'));
  }

  private function user_survey_logistik()
  {
    $user = Auth::user();
    // $user->nik = '0255.MTK.0913';
    $akses = DB::table('tb_survey_logistik_users2')->where('nik', $user->nik)->first();
    $user->jabatan_survey = $akses->jabatan ?? 'NO JABATAN';
    $user->vendor_survey = json_decode($akses->vendor ?? '[]');
    return $user;
  }

  private function update_survey_logistik_last_login()
  {
    $user = $this->user_survey_logistik();
    DB::table('tb_survey_logistik_last_login')->upsert(['nik' => $user->nik, 'updated_time' => time()], ['nik']);
  }

  public function survey_logistik_save(Request $request)
  {
    try {
      $user = $this->user_survey_logistik();
      $data = collect($request->all())
      ->filter(function($_, $index) {
        return $index !== '_token';
      })
      ->map(function($item, $index) use ($user) {
        return [
          'nik' => $user->nik,
          'soal_id' => (int) $index,
          'jawaban' => $item,
        ];
      })
      ->values()
      ->toArray();
      DB::table('tb_survey_logistik_jawaban')->insert($data);
      $this->update_survey_logistik_last_login();
      return redirect()->route('home');
    } catch (\Exception $e) {
      Alert::error('Gagal: ' . $e->getMessage());
      return redirect()->back();
    }
  }

    public function simulasi_insentif(Request $request)
    {
        $user = Auth::user();
        $user_roles = $user->roles;

        $n = $request->input('n') ?? '';
        $fbulan = $request->input('fbulan') ?? date('m');
        $ftahun = $request->input('ftahun') ?? date('Y');

        $fap = $request->input('fap');
        $base_jabatan = join(array_map(fn($item) => "SELECT '$item' AS xjabatan", ['DIREKTUR', 'KAREG', 'ASDIR', 'KANIT', 'KAPROD', 'SALES', 'STAF', 'TS', 'ADMIN', 'BU']), ' UNION ALL ');
        $base_all_is_1 = ['ASDIR', 'STAF'];
        $base_max_is_0 = ['TS', 'ADMIN', 'BU'];

        $list_ap = DB::table('regions')->where($user->region !== 'MJL' ? ['koderegion' => $user->region] : [])->get();
        $list_bulan = DB::table('bulan')->get();
        $list_tahun = DB::table('tahun')->get();

        $units = DB::table('units AS a')
        ->leftJoin('table_simulasi_insentif_plotting AS b', function($join) use ($ftahun, $fbulan) {
            $join
            ->on('a.kodeunit', 'b.unit')
            ->where([
                'a.kodeunit' => 'b.unit',
                'b.tahun' => "$ftahun",
                'b.bulan' => "$fbulan",
            ]);
        })
        ->where(isset($fap) ? ['a.region' => $fap] : [])
        ->select('a.kodeunit', 'b.kareg')
        ->get()
        ->map(function($item) {
            return (object) [
                'unit' => $item->kodeunit,
                'kareg' => $item->kareg === '1' ? 'ADA' : 'TIDAK',
            ];
        });

        $data = DB::table('units AS a')
        ->join(DB::raw("($base_jabatan) AS d"), function() {})
        ->leftJoin('table_simulasi_insentif_jabatan AS c', function($join) use ($ftahun, $fbulan) {
            $join
            ->on('c.jabatan', 'd.xjabatan')
            ->where('c.tahun', "$ftahun")
            ->where('c.bulan', "$fbulan");
        })
        ->leftJoin('table_simulasi_insentif_plotting AS b', function($join) use ($ftahun, $fbulan, $units) {
            $join
            ->on('a.kodeunit', 'b.unit')
            ->on('d.xjabatan', 'b.jabatan')
            ->where('b.tahun', "$ftahun")
            ->where('b.bulan', "$fbulan")
            ->whereIn('b.unit', $units->pluck('unit')->toArray());
        })
        ->where(isset($fap) ? ['a.region' => $fap] : [])
        ->select('a.region', 'a.kodeunit', 'b.unit', 'b.kareg', 'b.plot_kareg', 'b.plotting', 'b.jumlah', 'c.jabatan', 'c.all', 'c.min', 'c.max', 'd.xjabatan')
        ->get()
        ->map(function($item) use ($base_all_is_1, $base_max_is_0) {
            $result = (object) [];
            $result->region = $item->region;
            $result->unit = $item->unit ?? $item->kodeunit;
            $result->jabatan = $item->jabatan ?? $item->xjabatan;
            $result->kareg = $item->kareg ?? '0';
            $result->plot_kareg = $item->plot_kareg ?? '';
            $result->plotting = $item->plotting ?? '0';
            $result->jumlah = $item->jumlah ?? '0';
            $result->all = $item->all ?? (in_array($result->jabatan, $base_all_is_1) ? '1' : '0');
            $result->min = $item->min ?? '0';
            $result->max = $item->max ?? (in_array($result->jabatan, $base_max_is_0) ? '0' : '0.1');
            $result->g = in_array($result->jabatan, $base_max_is_0) ? 'staf' : 'jabatan';

            return $result;
        })
        ->groupBy(['g', 'all', 'jabatan'])
        ->map(function($z) {
            return $z->map(function($x) {
                return $x->map(function($c, $v) {
                    return (object) [
                        'props' => [
                            'jabatan' => $v,
                            'all' => $c->avg('all'),
                            'min' => $c->avg('min'),
                            'max' => $c->avg('max'),
                        ],
                        'data' => $c->toArray(),
                    ];
                })->toArray();
            })->toArray();
        });

        $plotting = (object) [
            'units' => $units,
            'data' => $data->toArray(),
        ];

        $hitung = DB::table('units AS a')
        ->leftJoin('table_simulasi_insentif_perhitungan AS b', function($join) use ($ftahun, $fbulan) {
            $join
            ->on('a.kodeunit', 'b.unit')
            ->where('b.tahun', "$ftahun")
            ->where('b.bulan', "$fbulan");
        })
        ->leftJoin('table_simulasi_insentif_plotting AS c', function($join) use ($ftahun, $fbulan) {
            $join
            ->on('a.kodeunit', 'c.unit')
            ->where('c.tahun', "$ftahun")
            ->where('c.bulan', "$fbulan");
        })
        ->where(isset($fap) ? ['a.region' => $fap] : [])
        ->groupBy('a.kodeunit')
        ->select(
            'a.kodeunit',
            'a.region',
            'b.*',
            'c.plot_kareg',
        )
        ->get()
        ->map(function($item) {
            return (object) [
                'ap' => $item->ap ?? $item->region,
                'unit' => $item->unit ?? $item->kodeunit,
                'insentif_produksi' => $item->insentif_produksi ?? '0',
                'insentif_penjualan' => $item->insentif_penjualan ?? '0',
                'insentif_kb' => $item->insentif_kb ?? '0',
                'insentif_collab' => $item->insentif_collab ?? '0',
                'insentif_ho' => $item->insentif_ho ?? '0',
                'plot_kareg' => $item->plot_kareg ?? '',
            ];
        });

        return view('dashboard.simulasi_insentif', compact('n', 'plotting', 'hitung', 'list_ap', 'fap', 'user_roles', 'ftahun', 'fbulan', 'list_bulan', 'list_tahun'));
    }

    public function simulasi_insentifx(Request $request){
        $unit = Auth::user()->unit;
        $tab = $request->input('tab');
        switch ($tab) {
            case 1:
                $tab1 = 'active';
                $tab2 = '';
                $tab3 = '';
                $tab4 = '';
                $tab5 = '';
                $tab6 = '';
                break;
            case 2:
                $tab1 = '';
                $tab2 = 'active';
                $tab3 = '';
                $tab4 = '';
                $tab5 = '';
                $tab6 = '';
                break;
            case 3:
                $tab1 = '';
                $tab2 = '';
                $tab3 = 'active';
                $tab4 = '';
                $tab5 = '';
                $tab6 = '';
                break;
            case 4:
                $tab1 = '';
                $tab2 = '';
                $tab3 = '';
                $tab4 = 'active';
                $tab5 = '';
                $tab6 = '';
                break;
            default:
                $tab1 = 'active';
                $tab2 = '';
                $tab3 = '';
                $tab4 = '';
                $tab5 = '';
                $tab6 = '';
                break;
        }
        $ap = 'MJR';
        $sql_range = DB::select("SELECT * FROM master_simulasi_insentif WHERE kategori !='PLOT'");
        $sql_range_plot = DB::select("SELECT * FROM master_simulasi_insentif WHERE kategori ='PLOT'");
        $arrUnit = DB::select("SELECT kodeunit FROM units WHERE region ='$ap'");
        $jmlUnit = DB::table('units')->where('region','=',$ap)->count();

        // me
        $tab1 = '';
        $tab3 = 'active';
        $all = ['ASDIR', 'STAF'];
        $plotting = (object) [];
        $plotting->not_plot = DB::table('master_simulasi_insentif')
        ->where('kategori', '!=', 'PLOT')
        ->get()
        ->mapWithKeys(fn($item) => [$item->jabatan => $item]);
        $plotting->plot = DB::table('master_simulasi_insentif')
        ->where('kategori', 'PLOT')
        ->get()
        ->mapWithKeys(fn($item) => [$item->jabatan => $item]);
        $plotting->wrap = function($item, $units) {
            $res = (object) [];
            foreach ($units as $i) {
                $res->{$i->unit} = $item->where('unit', $i->unit)->sum('nilai') ?? 0.0;
            }
            return $res;
        };
        $plotting->raw = DB::table('table_simulasi_insentif_plotting')
        ->leftJoin('units', 'table_simulasi_insentif_plotting.unit', '=', 'units.kodeunit')
        ->select('table_simulasi_insentif_plotting.*', 'units.region')
        ->get();
        $plotting->units = $plotting->raw
        ->groupBy(['region', 'unit'])
        ->map(fn($item, $ap) => $item->map(fn($_, $unit) => (object) ['ap' => $ap, 'unit' => $unit]))
        ->flatMap(fn($item) => $item)
        ->values();
        $plotting->per_jabatan = $plotting->raw
        ->whereIn('jabatan', $plotting->not_plot->pluck('jabatan')->toArray())
        ->groupBy('jabatan')
        ->map(function($item, $jabatan) use ($all, $plotting) {
            return (object) [
                'all' => in_array($jabatan, $all),
                'min' => $plotting->not_plot[$jabatan]->min ?? 0,
                'max' => $plotting->not_plot[$jabatan]->max ?? 0,
                'jabatan' => $jabatan,
                'plotting' => ($plotting->wrap)($item->where('kategori', 'PLOTTING'), $plotting->units),
                'jumlah' => ($plotting->wrap)($item->where('kategori', 'JUMLAH'), $plotting->units),
            ];
        })
        ->values();
        $plotting->staff = $plotting->raw
        ->whereIn('jabatan', $plotting->plot->pluck('jabatan')->toArray())
        ->groupBy('jabatan')
        ->map(function($item, $jabatan) use ($all, $plotting) {
            return (object) [
                'all' => in_array($jabatan, $all),
                'min' => $plotting->plot[$jabatan]->min ?? 0,
                'jabatan' => $jabatan,
                'plotting' => ($plotting->wrap)($item->where('kategori', 'PLOTTING'), $plotting->units),
                'jumlah' => ($plotting->wrap)($item->where('kategori', 'JUMLAH'), $plotting->units),
            ];
        })
        ->values();

        return view('dashboard.simulasi_insentif',compact('tab1','tab2','tab3','tab4','sql_range','sql_range_plot','arrUnit','jmlUnit','ap', 'plotting'));
    }

    public function simulasi_insentif_range(Request $request){
        $min_direktur  = $request->input('min_direktur');
        $min_kareg  = $request->input('min_kareg');
        $min_asdir  = $request->input('min_asdir');
        $min_kanit  = $request->input('min_kanit');
        $min_kaprod  = $request->input('min_kaprod');
        $min_sales  = $request->input('min_sales');
        $min_staf  = $request->input('min_staf');
        $min_ts  = $request->input('min_ts');
        $min_admin  = $request->input('min_admin');
        $min_bu  = $request->input('min_bu');

        $max_direktur  = $request->input('max_direktur');
        $max_kareg  = $request->input('max_kareg');
        $max_asdir  = $request->input('max_asdir');
        $max_kanit  = $request->input('max_kanit');
        $max_kaprod  = $request->input('max_kaprod');
        $max_sales  = $request->input('max_sales');
        $max_staf  = $request->input('max_staf');
        $max_ts  = $request->input('max_ts');
        $max_admin  = $request->input('max_admin');
        $max_bu  = $request->input('max_bu');

        $kosong = DB::statement("TRUNCATE TABLE master_simulasi_insentif");
        if($kosong){
            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'DIREKTUR',
                'kategori' => '',
                'min' => $request->input('min_direktur'),
                'max' => $request->input('max_direktur'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'KAREG',
                'kategori' => '',
                'min' => $request->input('min_kareg'),
                'max' => $request->input('max_kareg'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'ASDIR',
                'kategori' => '',
                'min' => $request->input('min_asdir'),
                'max' => $request->input('max_asdir'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'KANIT',
                'kategori' => '',
                'min' => $request->input('min_kanit'),
                'max' => $request->input('max_kanit'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'KAPROD',
                'kategori' => '',
                'min' => $request->input('min_kaprod'),
                'max' => $request->input('max_kaprod'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'SALES',
                'kategori' => '',
                'min' => $request->input('min_sales'),
                'max' => $request->input('max_sales'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'STAF',
                'kategori' => '',
                'min' => $request->input('min_staf'),
                'max' => $request->input('max_staf'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'TS',
                'kategori' => 'PLOT',
                'min' => $request->input('min_ts'),
                'max' => $request->input('max_ts'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'ADMIN',
                'kategori' => 'PLOT',
                'min' => $request->input('min_admin'),
                'max' => $request->input('max_admin'),
            ]);

            DB::table('master_simulasi_insentif')->insert([
                'jabatan' => 'BU',
                'kategori' => 'PLOT',
                'min' => $request->input('min_bu'),
                'max' => $request->input('max_bu'),
            ]);
        }
        return response() -> json([
                                    'success' => true,
                                    'data' => 'Data berhasil disimpan'
                                    ]);
    }

    public function simulasi_insentif_plotting_create(){
        $ap = 'MJR';
        $kosongTblHitung = DB::statement("TRUNCATE TABLE table_simulasi_insentif_perhitungan");
        $kosongTblHitung = DB::statement("TRUNCATE TABLE table_simulasi_insentif_plotting");
        $arrJabatan = DB::select("SELECT * FROM master_simulasi_insentif_jabatan");
        $arrUnit = DB::select("SELECT kodeunit FROM units WHERE region ='$ap'");
        $arrData = array();
        foreach($arrJabatan as $rs){
            $jabatan = $rs->jabatan;
            foreach($arrUnit as $data){
                array_push($arrData, [
                    'jabatan' => $jabatan,
                    'unit' => $data->kodeunit,
                    'kategori' => 'PLOTTING',
                    'nilai' => 0.0,
                ]);
            }
        }
        $insert_data = collect($arrData);
        $chunks = $insert_data->chunk(500);
        foreach ($chunks as $chunk){
            DB::table('table_simulasi_insentif_plotting')->insert($chunk->toArray());
        }

        $arrJmlData = array();
        foreach($arrJabatan as $rs){
            $jabatan = $rs->jabatan;
            foreach($arrUnit as $data){
                array_push($arrJmlData, [
                    'jabatan' => $jabatan,
                    'unit' => $data->kodeunit,
                    'kategori' => 'JUMLAH',
                    'nilai' => 0.0,
                ]);
            }
        }
        $insert_data = collect($arrJmlData);
        $chunks = $insert_data->chunk(500);
        foreach ($chunks as $chunk){
            DB::table('table_simulasi_insentif_plotting')->insert($chunk->toArray());
        }

        $arrInsentif = array();
        foreach($arrUnit as $data){
            array_push($arrInsentif, [
                'unit' => $data->kodeunit,
                'kategori' => 'PRODUKSI',
                'nilai' => 0.0,
            ]);
        }
        $insert_data = collect($arrInsentif);
        $chunks = $insert_data->chunk(500);
        foreach ($chunks as $chunk){
            DB::table('table_simulasi_insentif_perhitungan')->insert($chunk->toArray());
        }

        $arrInsentif = array();
        foreach($arrUnit as $data){
            array_push($arrInsentif, [
                'unit' => $data->kodeunit,
                'kategori' => 'PENJUALAN',
                'nilai' => 0.0,
            ]);
        }
        $insert_data = collect($arrInsentif);
        $chunks = $insert_data->chunk(500);
        foreach ($chunks as $chunk){
            DB::table('table_simulasi_insentif_perhitungan')->insert($chunk->toArray());
        }

        $arrInsentif = array();
        foreach($arrUnit as $data){
            array_push($arrInsentif, [
                'unit' => $data->kodeunit,
                'kategori' => 'KB',
                'nilai' => 0.0,
            ]);
        }
        $insert_data = collect($arrInsentif);
        $chunks = $insert_data->chunk(500);
        foreach ($chunks as $chunk){
            DB::table('table_simulasi_insentif_perhitungan')->insert($chunk->toArray());
        }

        $arrInsentif = array();
        foreach($arrUnit as $data){
            array_push($arrInsentif, [
                'unit' => $data->kodeunit,
                'kategori' => 'COLLAB',
                'nilai' => 0.0,
            ]);
        }
        $insert_data = collect($arrInsentif);
        $chunks = $insert_data->chunk(500);
        foreach ($chunks as $chunk){
            DB::table('table_simulasi_insentif_perhitungan')->insert($chunk->toArray());
        }

        $jmlUnit = DB::table('units')->where('region','=',$ap)->count();
        return response() -> json([
                                    'success' => true,
                                    'data' => 'Data berhasil dibuat'
                                    ]);
    }

    public function komplain_plasma_resume_rating(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tahun = $request->input('tahun');
        $data_ap = DB::table('regions')->pluck('koderegion')->sort()->toArray();
        $data_tahun = DB::table('tahun')->pluck('tahun')->sort()->toArray();

        if($ap != ''){
            $strWhere = "WHERE YEAR(tanggal_closing) = '$tahun' AND ap='$ap'";
        }else{
            $strWhere = "WHERE YEAR(tanggal_closing) = '$tahun'";
        }

        $sql_rating = DB::select("SELECT a.*, b.lokasi FROM (
                                    SELECT unit, ap,
                                        COUNT(id) AS flok_ytd,
                                        AVG(respon_peternak) AS rating_ytd,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=1 THEN 1 ELSE 0 END) AS flok_jan,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=1 THEN respon_peternak ELSE null END) AS rating_jan,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=2 THEN 1 ELSE 0 END) AS flok_feb,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=2 THEN respon_peternak ELSE null END) AS rating_feb,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=3 THEN 1 ELSE 0 END) AS flok_mar,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=3 THEN respon_peternak ELSE null END) AS rating_mar,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=4 THEN 1 ELSE 0 END) AS flok_apr,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=4 THEN respon_peternak ELSE null END) AS rating_apr,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=5 THEN 1 ELSE 0 END) AS flok_mei,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=5 THEN respon_peternak ELSE null END) AS rating_mei,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=6 THEN 1 ELSE 0 END) AS flok_jun,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=6 THEN respon_peternak ELSE null END) AS rating_jun,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=7 THEN 1 ELSE 0 END) AS flok_jul,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=7 THEN respon_peternak ELSE null END) AS rating_jul,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=8 THEN 1 ELSE 0 END) AS flok_agu,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=8 THEN respon_peternak ELSE null END) AS rating_agu,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=9 THEN 1 ELSE 0 END) AS flok_sep,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=9 THEN respon_peternak ELSE null END) AS rating_sep,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=10 THEN 1 ELSE 0 END) AS flok_okt,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=10 THEN respon_peternak ELSE null END) AS rating_okt,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=11 THEN 1 ELSE 0 END) AS flok_nov,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=11 THEN respon_peternak ELSE null END) AS rating_nov,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=12 THEN 1 ELSE 0 END) AS flok_des,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=12 THEN respon_peternak ELSE null END) AS rating_des
                                        FROM tb_proses_rhpp $strWhere
                                        GROUP BY unit ASC
                            )a LEFT JOIN units b ON b.kodeunit=a.unit");
        $data_rating = collect(array_map(function($item){
            return $item;
        },$sql_rating));

        $sql_rating_ap = DB::select("SELECT unit, ap,
                                        COUNT(id) AS flok_ytd,
                                        AVG(respon_peternak) AS rating_ytd,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=1 THEN 1 ELSE 0 END) AS flok_jan,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=1 THEN respon_peternak ELSE null END) AS rating_jan,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=2 THEN 1 ELSE 0 END) AS flok_feb,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=2 THEN respon_peternak ELSE null END) AS rating_feb,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=3 THEN 1 ELSE 0 END) AS flok_mar,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=3 THEN respon_peternak ELSE null END) AS rating_mar,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=4 THEN 1 ELSE 0 END) AS flok_apr,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=4 THEN respon_peternak ELSE null END) AS rating_apr,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=5 THEN 1 ELSE 0 END) AS flok_mei,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=5 THEN respon_peternak ELSE null END) AS rating_mei,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=6 THEN 1 ELSE 0 END) AS flok_jun,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=6 THEN respon_peternak ELSE null END) AS rating_jun,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=7 THEN 1 ELSE 0 END) AS flok_jul,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=7 THEN respon_peternak ELSE null END) AS rating_jul,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=8 THEN 1 ELSE 0 END) AS flok_agu,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=8 THEN respon_peternak ELSE null END) AS rating_agu,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=9 THEN 1 ELSE 0 END) AS flok_sep,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=9 THEN respon_peternak ELSE null END) AS rating_sep,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=10 THEN 1 ELSE 0 END) AS flok_okt,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=10 THEN respon_peternak ELSE null END) AS rating_okt,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=11 THEN 1 ELSE 0 END) AS flok_nov,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=11 THEN respon_peternak ELSE null END) AS rating_nov,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=12 THEN 1 ELSE 0 END) AS flok_des,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=12 THEN respon_peternak ELSE null END) AS rating_des
                                        FROM tb_proses_rhpp $strWhere
                                        GROUP BY ap ASC");
        $data_rating_ap = collect(array_map(function($item){
            return $item;
        },$sql_rating_ap));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_resume_rating', compact('last_update','data_ap','data_tahun','ap','tahun','data_rating','data_rating_ap'));
    }

    public function komplain_plasma_resume_komplain(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        $tgl1 = strtotime($tglawal);
        $tgl2 = strtotime($tglakhir);
        $jarak = $tgl2 - $tgl1;
        $hari = $jarak / 60 / 60 / 24;
        if($hari > 60 ){
            return Redirect::back()->withErrors(['msg' => 'Maksimal pengambilan data 60 hari']);
        }

        $data_ap = DB::table('regions')->pluck('koderegion')->sort()->toArray();

        if($ap != ''){
            $strWhereResKomplain = "WHERE ap='$ap' AND tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }else{
            $strWhereResKomplain = "WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }

        $sqlResumeKomplain = DB::select("SELECT a.*, b.lokasi FROM (
												SELECT unit, ap,
                                        	COUNT(id) AS flok_ytd,
                                        	SUM(CASE WHEN komplain_1='DOC' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='DOC' THEN 1 ELSE 0 END) AS doc,
                                       	SUM(CASE WHEN komplain_1='PAKAN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PAKAN' THEN 1 ELSE 0 END) AS pakan,
                                       	SUM(CASE WHEN komplain_1='PANEN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PANEN' THEN 1 ELSE 0 END) AS panen,
                                       	SUM(CASE WHEN komplain_1='PETUGAS' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PETUGAS' THEN 1 ELSE 0 END) AS petugas,
                                       	SUM(CASE WHEN komplain_1 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END) AS kebijakan
                                 	FROM tb_proses_rhpp $strWhereResKomplain
                                        GROUP BY unit ORDER BY ap ASC
                        )a LEFT JOIN units b ON b.kodeunit=a.unit");
        $data_resume_komplain = collect(array_map(function($item){
            return $item;
        },$sqlResumeKomplain));

        $sqlResumeKomplainAp = DB::select("SELECT unit, ap,
                                        	COUNT(id) AS flok_ytd,
                                        	SUM(CASE WHEN komplain_1='DOC' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='DOC' THEN 1 ELSE 0 END) AS doc,
                                            SUM(CASE WHEN komplain_1='PAKAN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PAKAN' THEN 1 ELSE 0 END) AS pakan,
                                            SUM(CASE WHEN komplain_1='PANEN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PANEN' THEN 1 ELSE 0 END) AS panen,
                                            SUM(CASE WHEN komplain_1='PETUGAS' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PETUGAS' THEN 1 ELSE 0 END) AS petugas,
                                            SUM(CASE WHEN komplain_1 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END) AS kebijakan
                                        FROM tb_proses_rhpp $strWhereResKomplain
                                        GROUP BY ap ASC");
        $data_resume_komplain_ap = collect(array_map(function($item){
            return $item;
        },$sqlResumeKomplainAp));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_resume_komplain', compact('last_update','data_ap','ap','tglawal','tglakhir','data_resume_komplain','data_resume_komplain_ap'));
    }

    public function komplain_plasma_jenis_doc(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        $tgl1 = strtotime($tglawal);
        $tgl2 = strtotime($tglakhir);
        $jarak = $tgl2 - $tgl1;
        $hari = $jarak / 60 / 60 / 24;
        if($hari >= 60 ){
            return Redirect::back()->withErrors(['msg' => 'Maksimal pengambilan data 60 hari']);
        }

        $sqlKomplainDoc = DB::select("SELECT jenis, COUNT(nama_flok) AS flok_pakai, SUM(CASE WHEN komplain_1='DOC' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='DOC' THEN 1 ELSE 0 END) AS doc FROM(
                                            SELECT a.*,b.jenis FROM (
                                                SELECT CONCAT(nama_flok,unit,tanggal_closing) AS kode_flok, nama_flok, komplain_1, komplain_2 FROM tb_proses_rhpp WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'
                                            )a LEFT JOIN (
                                                SELECT CONCAT(nama_flok,unit,tanggal_doc_out) AS kode_flok, nama_flok, unit, SPLIT_STR(jenis, ',', 1) AS jenis FROM tb_rhpp WHERE tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'
                                            )b ON b.kode_flok=a.kode_flok
                                        )c WHERE jenis IS NOT NULL GROUP BY jenis ASC");
        $data_komplain_doc = collect(array_map(function($item){
            return $item;
        },$sqlKomplainDoc));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_jenis_doc', compact('last_update','tglawal','tglakhir','data_komplain_doc'));
    }

    public function komplain_plasma_vendor_pakan(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        $sqlKomplainPakan = DB::select("SELECT vendor_pakan, COUNT(nama_flok) AS flok_pakai, SUM(CASE WHEN komplain_1='PAKAN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PAKAN' THEN 1 ELSE 0 END) AS pakan FROM(
                                            SELECT a.*,b.vendor_pakan FROM (
                                                SELECT CONCAT(nama_flok,unit,tanggal_closing) AS kode_flok, nama_flok, komplain_1, komplain_2 FROM tb_proses_rhpp WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'
                                            )a LEFT JOIN (
                                                SELECT CONCAT(nama_flok,unit,tanggal_doc_out) AS kode_flok, nama_flok, unit, vendor_pakan FROM tb_rhpp WHERE tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'
                                            )b ON b.kode_flok=a.kode_flok
                                        )c WHERE vendor_pakan IS NOT NULL GROUP BY vendor_pakan ASC");
        $data_komplain_pakan = collect(array_map(function($item){
            return $item;
        },$sqlKomplainPakan));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_vendor_pakan', compact('last_update','tglawal','tglakhir','data_komplain_pakan'));
    }

    public function komplain_plasma_lama_panen(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        if($ap != ''){
            $strWhere = "WHERE x.ap='$ap'";
        }else{
            $strWhere = "WHERE 1=1";
        }

        $data_ap = DB::table('regions')->pluck('koderegion')->sort()->toArray();
        $sql_panen_unit = DB::select("SELECT x.unit, x.ap, x.flok_closing, x.panen, y.hari_panen, y.panen_hari_persen FROM (
                                            SELECT unit, ap,
                                                COUNT(id) AS flok_closing,
                                                SUM(CASE WHEN komplain_1='PANEN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PANEN' THEN 1 ELSE 0 END) AS panen
                                            FROM tb_proses_rhpp WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir' GROUP BY unit ASC
                                        )x LEFT JOIN (
                                            SELECT unit, ap, COUNT(id) AS flok_closing, AVG(hari_panen) AS hari_panen, AVG(panen_hari_persen) AS panen_hari_persen FROM (
                                                SELECT id, unit, ap, nama_flok, tanggal_doc_out, hari_panen,
                                                    (chick_out_ekor/hari_panen) AS panen_hari, ((chick_out_ekor/hari_panen)/chick_out_ekor)*100 AS panen_hari_persen
                                                FROM tb_rhpp WHERE tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'
                                            )a GROUP BY unit ASC
                                        )y ON y.unit=x.unit $strWhere");
        $data_panen_unit = collect(array_map(function($item){
            return $item;
        },$sql_panen_unit));

        $sql_panen_ap = DB::select("SELECT x.unit, x.ap, x.flok_closing, x.panen, y.hari_panen, y.panen_hari_persen FROM (
                                            SELECT unit, ap,
                                                COUNT(id) AS flok_closing,
                                                SUM(CASE WHEN komplain_1='PANEN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PANEN' THEN 1 ELSE 0 END) AS panen
                                            FROM tb_proses_rhpp WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir' GROUP BY ap ASC
                                        )x LEFT JOIN (
                                            SELECT unit, ap, COUNT(id) AS flok_closing, AVG(hari_panen) AS hari_panen, AVG(panen_hari_persen) AS panen_hari_persen FROM (
                                                SELECT id, unit, ap, nama_flok, tanggal_doc_out, hari_panen,
                                                    (chick_out_ekor/hari_panen) AS panen_hari, ((chick_out_ekor/hari_panen)/chick_out_ekor)*100 AS panen_hari_persen
                                                FROM tb_rhpp WHERE tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'
                                            )a GROUP BY ap ASC
                                        )y ON y.ap=x.ap");
        $data_panen_ap = collect(array_map(function($item){
            return $item;
        },$sql_panen_ap));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_lama_panen', compact('last_update','tglawal','tglakhir','data_ap','ap','data_panen_unit','data_panen_ap'));
    }

    public function komplain_plasma_resume_unit_ap(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        if($ap != ''){
            $strWhereResKomplain = "WHERE ap='$ap' AND tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }else{
            $strWhereResKomplain = "WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }

        $data_ap = DB::table('regions')->pluck('koderegion')->sort()->toArray();
        $sql_resume_unit = DB::select("SELECT unit, ap,
                                        COUNT(nama_flok) AS flok_final,
                                        SUM(CASE WHEN komplain_1 <> '---' THEN 1 ELSE 0 END) AS flok_komplain,
                                        SUM(CASE WHEN kategori='BERAT' THEN poin ELSE 0 END) AS kategori_berat,
                                        SUM(CASE WHEN kategori='SEDANG' THEN poin ELSE 0 END) AS kategori_sedang,
                                        SUM(CASE WHEN kategori='RINGAN' THEN poin ELSE 0 END) AS kategori_ringan
                                        FROM tb_proses_rhpp $strWhereResKomplain
                                    GROUP BY unit ASC");
        $data_resume_unit = collect(array_map(function($item){
            return $item;
        },$sql_resume_unit));

        $sql_resume_ap = DB::select("SELECT unit, ap,
                                        COUNT(nama_flok) AS flok_final,
                                        SUM(CASE WHEN komplain_1 <> '---' THEN 1 ELSE 0 END) AS flok_komplain,
                                        SUM(CASE WHEN kategori='BERAT' THEN poin ELSE 0 END) AS kategori_berat,
                                        SUM(CASE WHEN kategori='SEDANG' THEN poin ELSE 0 END) AS kategori_sedang,
                                        SUM(CASE WHEN kategori='RINGAN' THEN poin ELSE 0 END) AS kategori_ringan
                                        FROM tb_proses_rhpp $strWhereResKomplain
                                    GROUP BY ap ASC");
        $data_resume_ap = collect(array_map(function($item){
            return $item;
        },$sql_resume_ap));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_resume_unit_ap', compact('last_update','tglawal','tglakhir','data_ap','ap','data_resume_unit','data_resume_ap'));
    }

    public function komplain_plasma_resume_rating_ts_kp(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        if($ap != ''){
            $strWhere = "WHERE a.ap='$ap' AND tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'";
        }else{
            $strWhere = "WHERE tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'";
        }

        $data_ap = DB::table('regions')->pluck('koderegion')->sort()->toArray();
        $sql_resume_ts = DB::select("SELECT nama_ppl, unit, ap, AVG(respon_peternak) AS respon_peternak, COUNT(nama_flok) AS flok_final,
                                        (SUM(CASE WHEN komplain_1 IS NOT NULL AND komplain_1 !='---' AND komplain_1 !='' THEN 1 ELSE 0 END)+ SUM(CASE WHEN komplain_2 IS NOT NULL AND komplain_2 !='---' AND komplain_2 !='' THEN 1 ELSE 0 END)) AS flok_komplain
                                        FROM (
                                            SELECT a.nama_ppl, a.unit, a.ap, a.nama_flok, a.tanggal_doc_out, b.respon_peternak, b.penilaian_plasma, b.komplain_1, b.komplain_2 FROM tb_rhpp a
                                            LEFT JOIN tb_proses_rhpp b ON b.unit=a.unit AND b.nama_flok=a.nama_flok AND b.tanggal_closing=a.tanggal_doc_out
                                             $strWhere
                                        )x GROUP BY nama_ppl ASC");
        $data_resume_ts = collect(array_map(function($item){
            return $item;
        },$sql_resume_ts));

        $sql_resume_kp = DB::select("SELECT kaprod, unit, ap, AVG(respon_peternak) AS respon_peternak, COUNT(nama_flok) AS flok_final,
                                        (SUM(CASE WHEN komplain_1 IS NOT NULL AND komplain_1 !='---' AND komplain_1 !='' THEN 1 ELSE 0 END)+ SUM(CASE WHEN komplain_2 IS NOT NULL AND komplain_2 !='---' AND komplain_2 !='' THEN 1 ELSE 0 END)) AS flok_komplain
                                        FROM (
                                            SELECT a.kaprod, a.unit, a.ap, a.nama_flok, a.tanggal_doc_out, b.respon_peternak, b.penilaian_plasma, b.komplain_1, b.komplain_2 FROM tb_rhpp a
                                            LEFT JOIN tb_proses_rhpp b ON b.unit=a.unit AND b.nama_flok=a.nama_flok AND b.tanggal_closing=a.tanggal_doc_out
                                             $strWhere
                                        )x GROUP BY kaprod ASC");
        $data_resume_kp = collect(array_map(function($item){
            return $item;
        },$sql_resume_kp));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_resume_rating_ts_kp', compact('last_update','tglawal','tglakhir','data_ap','ap','data_resume_ts','data_resume_kp'));
    }

    public function komplain_plasma_resume_kebijakan(Request $request){
        ini_set('memory_limit', '4096M');

        $ap = $request->input('ap');
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        // $tgl1 = strtotime($tglawal);
        // $tgl2 = strtotime($tglakhir);
        // $jarak = $tgl2 - $tgl1;
        // $hari = $jarak / 60 / 60 / 24;
        // if($hari > 60 ){
        //     return Redirect::back()->withErrors(['msg' => 'Maksimal pengambilan data 60 hari']);
        // }

        $data_ap = DB::table('regions')->pluck('koderegion')->sort()->toArray();

        if($ap != ''){
            $strWhere = "WHERE ap='$ap' AND tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }else{
            $strWhere = "WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }

        $sqlResumeKebijakan = DB::select("SELECT a.*, b.lokasi FROM (
                                            SELECT unit, ap, COUNT(id) AS flok_ytd,
                                            SUM(CASE WHEN komplain_1 = 'KONTRAK' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'KONTRAK' THEN 1 ELSE 0 END) AS kontrak,
                                            SUM(CASE WHEN komplain_1 = 'PENCAIRAN RHPP' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'PENCAIRAN RHPP' THEN 1 ELSE 0 END) AS pencairan_rhpp,
                                            SUM(CASE WHEN komplain_1 = 'KOMPENSASI' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'KOMPENSASI' THEN 1 ELSE 0 END) AS kompensasi,
                                            SUM(CASE WHEN komplain_1 = 'MUTASI PAKAN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'MUTASI PAKAN' THEN 1 ELSE 0 END) AS mutasi_pakan,
                                            SUM(CASE WHEN komplain_1 = 'SIKLUS CHICK IN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'SIKLUS CHICK IN' THEN 1 ELSE 0 END) AS siklus_chickin,
                                            SUM(CASE WHEN komplain_1 = 'KIRIMAN PAKAN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'KIRIMAN PAKAN' THEN 1 ELSE 0 END) AS kiriman_pakan,
                                            SUM(CASE WHEN komplain_1 = 'PERPAJAKAN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'PERPAJAKAN' THEN 1 ELSE 0 END) AS perpajakan,
                                            SUM(CASE WHEN komplain_1 = 'BONUS' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'BONUS' THEN 1 ELSE 0 END) AS bonus,
                                            SUM(CASE WHEN komplain_1 = 'LAIN-LAIN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'LAIN-LAIN' THEN 1 ELSE 0 END) AS lain_lain
                                            FROM tb_proses_rhpp $strWhere GROUP BY unit ASC
                                        )a LEFT JOIN units b ON b.kodeunit=a.unit");
        $data_resume_kebijakan = collect(array_map(function($item){
            return $item;
        },$sqlResumeKebijakan));

        $sqlResumeKebijakanAp = DB::select("SELECT unit, ap, COUNT(id) AS flok_ytd,
                                            SUM(CASE WHEN komplain_1 = 'KONTRAK' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'KONTRAK' THEN 1 ELSE 0 END) AS kontrak,
                                            SUM(CASE WHEN komplain_1 = 'PENCAIRAN RHPP' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'PENCAIRAN RHPP' THEN 1 ELSE 0 END) AS pencairan_rhpp,
                                            SUM(CASE WHEN komplain_1 = 'KOMPENSASI' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'KOMPENSASI' THEN 1 ELSE 0 END) AS kompensasi,
                                            SUM(CASE WHEN komplain_1 = 'MUTASI PAKAN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'MUTASI PAKAN' THEN 1 ELSE 0 END) AS mutasi_pakan,
                                            SUM(CASE WHEN komplain_1 = 'SIKLUS CHICK IN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'SIKLUS CHICK IN' THEN 1 ELSE 0 END) AS siklus_chickin,
                                            SUM(CASE WHEN komplain_1 = 'KIRIMAN PAKAN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'KIRIMAN PAKAN' THEN 1 ELSE 0 END) AS kiriman_pakan,
                                            SUM(CASE WHEN komplain_1 = 'PERPAJAKAN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'PERPAJAKAN' THEN 1 ELSE 0 END) AS perpajakan,
                                            SUM(CASE WHEN komplain_1 = 'BONUS' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'BONUS' THEN 1 ELSE 0 END) AS bonus,
                                            SUM(CASE WHEN komplain_1 = 'LAIN-LAIN' THEN 1 ELSE 0 END) + SUM(CASE WHEN komplain_2 = 'LAIN-LAIN' THEN 1 ELSE 0 END) AS lain_lain
                                            FROM tb_proses_rhpp $strWhere GROUP BY ap ASC");
        $data_resume_kebijakan_ap = collect(array_map(function($item){
            return $item;
        },$sqlResumeKebijakanAp));

        $last_update = lastUpdate('tb_proses_rhpp', 'updated_at');
        return view('dashboard.home.komplain_plasma_resume_kebijakan', compact('last_update','data_ap','ap','tglawal','tglakhir','data_resume_kebijakan','data_resume_kebijakan_ap'));
    }

    public function komplain_plasma(Request $request){
        ini_set('memory_limit', '4096M');

        $n = $request->input('n') ?? 'nav-1';
        $tglawal = $request->input('tglawal');
        $tglakhir = $request->input('tglakhir');

        $tgl1 = strtotime($tglawal);
        $tgl2 = strtotime($tglakhir);
        $jarak = $tgl2 - $tgl1;
        $hari = $jarak / 60 / 60 / 24;
        if($hari > 60 ){
            return Redirect::back()->withErrors(['msg' => 'Maksimal pengambilan data 60 hari']);
        }

        $ap = $request->input('ap');
        $tahun = $request->input('tahun');
        $data_ap = DB::table('regions')->pluck('koderegion')->sort()->toArray();
        $data_tahun = DB::table('tahun')->pluck('tahun')->sort()->toArray();

        if($ap != ''){
            $strWhere = "WHERE YEAR(tanggal_closing) = '$tahun' AND ap='$ap'";
            $strWhereResKomplain = "WHERE ap='$ap' AND tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }else{
            $strWhere = "WHERE YEAR(tanggal_closing) = '$tahun'";
            $strWhereResKomplain = "WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'";
        }

        $sql_rating = DB::select("SELECT a.*, b.lokasi FROM (
                                    SELECT unit, ap,
                                        COUNT(id) AS flok_ytd,
                                        AVG(respon_peternak) AS rating_ytd,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=1 THEN 1 ELSE 0 END) AS flok_jan,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=1 THEN respon_peternak ELSE null END) AS rating_jan,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=2 THEN 1 ELSE 0 END) AS flok_feb,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=2 THEN respon_peternak ELSE null END) AS rating_feb,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=3 THEN 1 ELSE 0 END) AS flok_mar,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=3 THEN respon_peternak ELSE null END) AS rating_mar,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=4 THEN 1 ELSE 0 END) AS flok_apr,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=4 THEN respon_peternak ELSE null END) AS rating_apr,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=5 THEN 1 ELSE 0 END) AS flok_mei,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=5 THEN respon_peternak ELSE null END) AS rating_mei,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=6 THEN 1 ELSE 0 END) AS flok_jun,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=6 THEN respon_peternak ELSE null END) AS rating_jun,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=7 THEN 1 ELSE 0 END) AS flok_jul,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=7 THEN respon_peternak ELSE null END) AS rating_jul,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=8 THEN 1 ELSE 0 END) AS flok_agu,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=8 THEN respon_peternak ELSE null END) AS rating_agu,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=9 THEN 1 ELSE 0 END) AS flok_sep,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=9 THEN respon_peternak ELSE null END) AS rating_sep,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=10 THEN 1 ELSE 0 END) AS flok_okt,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=10 THEN respon_peternak ELSE null END) AS rating_okt,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=11 THEN 1 ELSE 0 END) AS flok_nov,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=11 THEN respon_peternak ELSE null END) AS rating_nov,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=12 THEN 1 ELSE 0 END) AS flok_des,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=12 THEN respon_peternak ELSE null END) AS rating_des
                                        FROM tb_proses_rhpp $strWhere
                                        GROUP BY unit ASC
                            )a LEFT JOIN units b ON b.kodeunit=a.unit");
        $data_rating = collect(array_map(function($item){
            return $item;
        },$sql_rating));

        $sql_rating_ap = DB::select("SELECT unit, ap,
                                        COUNT(id) AS flok_ytd,
                                        AVG(respon_peternak) AS rating_ytd,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=1 THEN 1 ELSE 0 END) AS flok_jan,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=1 THEN respon_peternak ELSE null END) AS rating_jan,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=2 THEN 1 ELSE 0 END) AS flok_feb,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=2 THEN respon_peternak ELSE null END) AS rating_feb,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=3 THEN 1 ELSE 0 END) AS flok_mar,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=3 THEN respon_peternak ELSE null END) AS rating_mar,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=4 THEN 1 ELSE 0 END) AS flok_apr,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=4 THEN respon_peternak ELSE null END) AS rating_apr,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=5 THEN 1 ELSE 0 END) AS flok_mei,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=5 THEN respon_peternak ELSE null END) AS rating_mei,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=6 THEN 1 ELSE 0 END) AS flok_jun,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=6 THEN respon_peternak ELSE null END) AS rating_jun,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=7 THEN 1 ELSE 0 END) AS flok_jul,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=7 THEN respon_peternak ELSE null END) AS rating_jul,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=8 THEN 1 ELSE 0 END) AS flok_agu,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=8 THEN respon_peternak ELSE null END) AS rating_agu,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=9 THEN 1 ELSE 0 END) AS flok_sep,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=9 THEN respon_peternak ELSE null END) AS rating_sep,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=10 THEN 1 ELSE 0 END) AS flok_okt,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=10 THEN respon_peternak ELSE null END) AS rating_okt,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=11 THEN 1 ELSE 0 END) AS flok_nov,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=11 THEN respon_peternak ELSE null END) AS rating_nov,
                                        SUM(CASE WHEN MONTH(tanggal_closing)=12 THEN 1 ELSE 0 END) AS flok_des,
                                        AVG(CASE WHEN MONTH(tanggal_closing)=12 THEN respon_peternak ELSE null END) AS rating_des
                                        FROM tb_proses_rhpp $strWhere
                                        GROUP BY ap ASC");
        $data_rating_ap = collect(array_map(function($item){
            return $item;
        },$sql_rating_ap));

        $sqlResumeKomplain = DB::select("SELECT a.*, b.lokasi FROM (
												SELECT unit, ap,
                                        	COUNT(id) AS flok_ytd,
                                        	SUM(CASE WHEN komplain_1='DOC' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='DOC' THEN 1 ELSE 0 END) AS doc,
                                       	SUM(CASE WHEN komplain_1='PAKAN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PAKAN' THEN 1 ELSE 0 END) AS pakan,
                                       	SUM(CASE WHEN komplain_1='PANEN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PANEN' THEN 1 ELSE 0 END) AS panen,
                                       	SUM(CASE WHEN komplain_1='PETUGAS' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PETUGAS' THEN 1 ELSE 0 END) AS petugas,
                                       	SUM(CASE WHEN komplain_1 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END) AS kebijakan
                                 	FROM tb_proses_rhpp $strWhereResKomplain
                                        GROUP BY unit ORDER BY ap ASC
                        )a LEFT JOIN units b ON b.kodeunit=a.unit");
        $data_resume_komplain = collect(array_map(function($item){
            return $item;
        },$sqlResumeKomplain));

        $sqlResumeKomplainAp = DB::select("SELECT unit, ap,
                                        	COUNT(id) AS flok_ytd,
                                        	SUM(CASE WHEN komplain_1='DOC' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='DOC' THEN 1 ELSE 0 END) AS doc,
                                            SUM(CASE WHEN komplain_1='PAKAN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PAKAN' THEN 1 ELSE 0 END) AS pakan,
                                            SUM(CASE WHEN komplain_1='PANEN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PANEN' THEN 1 ELSE 0 END) AS panen,
                                            SUM(CASE WHEN komplain_1='PETUGAS' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PETUGAS' THEN 1 ELSE 0 END) AS petugas,
                                            SUM(CASE WHEN komplain_1 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2 IN ('KONTRAK','PENCAIRAN RHPP','KONTRAK','KOMPENSASI','MUTASI PAKAN','SIKLUS CHICK IN','KIRIMAN PAKAN','PERPAJAKAN','BONUS','LAIN-LAIN') THEN 1 ELSE 0 END) AS kebijakan
                                        FROM tb_proses_rhpp $strWhereResKomplain
                                        GROUP BY ap ASC");
        $data_resume_komplain_ap = collect(array_map(function($item){
            return $item;
        },$sqlResumeKomplainAp));

        $sqlKomplainDoc = DB::select("SELECT jenis, COUNT(nama_flok) AS flok_pakai, SUM(CASE WHEN komplain_1='DOC' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='DOC' THEN 1 ELSE 0 END) AS doc FROM(
                                            SELECT a.*,b.jenis FROM (
                                                SELECT CONCAT(nama_flok,unit,tanggal_closing) AS kode_flok, nama_flok, komplain_1, komplain_2 FROM tb_proses_rhpp WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'
                                            )a LEFT JOIN (
                                                SELECT CONCAT(nama_flok,unit,tanggal_doc_out) AS kode_flok, nama_flok, unit, SPLIT_STR(jenis, ',', 1) AS jenis FROM tb_rhpp WHERE tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'
                                            )b ON b.kode_flok=a.kode_flok
                                        )c WHERE jenis IS NOT NULL GROUP BY jenis ASC");
        $data_komplain_doc = collect(array_map(function($item){
            return $item;
        },$sqlKomplainDoc));

        $sqlKomplainPakan = DB::select("SELECT vendor_pakan, COUNT(nama_flok) AS flok_pakai, SUM(CASE WHEN komplain_1='PAKAN' THEN 1 ELSE 0 END)+SUM(CASE WHEN komplain_2='PAKAN' THEN 1 ELSE 0 END) AS pakan FROM(
                                            SELECT a.*,b.vendor_pakan FROM (
                                                SELECT CONCAT(nama_flok,unit,tanggal_closing) AS kode_flok, nama_flok, komplain_1, komplain_2 FROM tb_proses_rhpp WHERE tanggal_closing BETWEEN '$tglawal' AND '$tglakhir'
                                            )a LEFT JOIN (
                                                SELECT CONCAT(nama_flok,unit,tanggal_doc_out) AS kode_flok, nama_flok, unit, vendor_pakan FROM tb_rhpp WHERE tanggal_doc_out BETWEEN '$tglawal' AND '$tglakhir'
                                            )b ON b.kode_flok=a.kode_flok
                                        )c WHERE vendor_pakan IS NOT NULL GROUP BY vendor_pakan ASC");
        $data_komplain_pakan = collect(array_map(function($item){
            return $item;
        },$sqlKomplainPakan));

        $sql_resume_unit = DB::select("SELECT unit, ap,
                                        COUNT(nama_flok) AS flok_final,
                                        SUM(CASE WHEN komplain_1 <> '---' THEN 1 ELSE 0 END) AS flok_komplain,
                                        SUM(CASE WHEN kategori='BERAT' THEN poin ELSE 0 END) AS kategori_berat,
                                        SUM(CASE WHEN kategori='SEDANG' THEN poin ELSE 0 END) AS kategori_sedang,
                                        SUM(CASE WHEN kategori='RINGAN' THEN poin ELSE 0 END) AS kategori_ringan
                                        FROM tb_proses_rhpp $strWhereResKomplain
                                    GROUP BY unit ASC");
        $data_resume_unit = collect(array_map(function($item){
            return $item;
        },$sql_resume_unit));

        $sql_resume_ap = DB::select("SELECT unit, ap,
                                        COUNT(nama_flok) AS flok_final,
                                        SUM(CASE WHEN komplain_1 <> '---' THEN 1 ELSE 0 END) AS flok_komplain,
                                        SUM(CASE WHEN kategori='BERAT' THEN poin ELSE 0 END) AS kategori_berat,
                                        SUM(CASE WHEN kategori='SEDANG' THEN poin ELSE 0 END) AS kategori_sedang,
                                        SUM(CASE WHEN kategori='RINGAN' THEN poin ELSE 0 END) AS kategori_ringan
                                        FROM tb_proses_rhpp $strWhereResKomplain
                                    GROUP BY ap ASC");
        $data_resume_ap = collect(array_map(function($item){
            return $item;
        },$sql_resume_ap));

        // dd($data_rating);
        $last_update = lastUpdate('table_sclog_rekap_pembelian_pakan', 'updated_at');
        return view('dashboard.home.komplain_plasma', compact('last_update', 'n', 'data_ap','data_tahun', 'tglawal', 'tglakhir','ap','tahun',
                                                                'data_rating','data_resume_komplain','data_komplain_doc','data_komplain_pakan',
                                                                'data_rating_ap','data_resume_komplain_ap','data_resume_unit','data_resume_ap'));
    }

    public function source_komplain_plasma()
    {
        $user = Auth::user();

        $list_unit = DB::table('units')
            ->select(
                DB::raw("kodeunit as unit"),
                DB::raw("region as ap"),
            )
            ->where(($user->roles == 'pusat' || $user->roles == 'admin') ? [/* no filter */] : ['region' => $user->region])
            ->orderBy('region')
            ->orderBy('kodeunit')
            ->get()
            ->groupBy('ap');

        return view('dashboard.home.source_komplain_plasma', compact('list_unit'));
    }

    public function get_source_komplain_plasma($tglawal,$tglakhir,$ap,$unit){
        $roles = Auth::user()->roles;
        if($ap!='SEMUA'){
            if($unit!='SEMUA'){
                $data = DB::select("SELECT a.id, a.unit, a.ap, a.nama_flok, a.tanggal_do_terakhir AS tanggal_closing, a.tanggal_mulai, respon_peternak, penilaian_plasma, komplain_1, komplain_2, c.jenis, c.vendor_pakan, kategori, poin,
                                    (CASE WHEN komplain_1 IS NULL AND komplain_2 IS NULL THEN '---' WHEN komplain_1 ='---' AND komplain_2 ='---' THEN '---' WHEN keterangan ='' AND file_bukti ='' THEN 'BELUM' WHEN keterangan !='' AND file_bukti ='' THEN 'DALAM PROSES...' ELSE 'SELESAI' END) AS progres,
                                    keterangan, file_bukti, b.nomor_hp, b.nama_plasma, validasi, d.kronologi_munculnya_komplain, d.tindakan_yang_sudah_dilakukan, d.tindakan_perbaikan_ke_depan
                                FROM tb_proses_rhpp a
                                LEFT JOIN tb_plasma b ON b.nama_flok=a.nama_flok AND b.nama_unit=a.unit
                                LEFT JOIN tb_rhpp c ON c.nama_flok=a.nama_flok AND c.unit=a.unit AND c.tanggal_doc_out=a.tanggal_do_terakhir
                                LEFT JOIN tb_detail_komplain_plasma d ON d.nama_flok=a.nama_flok AND d.unit=a.unit AND d.tanggal_do_terakhir=a.tanggal_do_terakhir
                                WHERE penilaian_plasma <> '' AND a.ap='$ap' AND a.unit='$unit' AND a.tanggal_do_terakhir BETWEEN '$tglawal' AND '$tglakhir'");
            }else{
                $data = DB::select("SELECT a.id, a.unit, a.ap, a.nama_flok, a.tanggal_do_terakhir AS tanggal_closing, a.tanggal_mulai, respon_peternak, penilaian_plasma, komplain_1, komplain_2, c.jenis, c.vendor_pakan, kategori, poin,
                                    (CASE WHEN komplain_1 IS NULL AND komplain_2 IS NULL THEN '---' WHEN komplain_1 ='---' AND komplain_2 ='---' THEN '---' WHEN keterangan ='' AND file_bukti ='' THEN 'BELUM' WHEN keterangan !='' AND file_bukti ='' THEN 'DALAM PROSES...' ELSE 'SELESAI' END) AS progres,
                                    keterangan, file_bukti, b.nomor_hp, b.nama_plasma, validasi, d.kronologi_munculnya_komplain, d.tindakan_yang_sudah_dilakukan, d.tindakan_perbaikan_ke_depan
                                FROM tb_proses_rhpp a
                                LEFT JOIN tb_plasma b ON b.nama_flok=a.nama_flok AND b.nama_unit=a.unit
                                LEFT JOIN tb_rhpp c ON c.nama_flok=a.nama_flok AND c.unit=a.unit AND c.tanggal_doc_out=a.tanggal_do_terakhir
                                LEFT JOIN tb_detail_komplain_plasma d ON d.nama_flok=a.nama_flok AND d.unit=a.unit AND d.tanggal_do_terakhir=a.tanggal_do_terakhir
                                WHERE penilaian_plasma <> '' AND a.ap='$ap' AND a.tanggal_do_terakhir BETWEEN '$tglawal' AND '$tglakhir'");
            }
        }else{
            $data = DB::select("SELECT a.id, a.unit, a.ap, a.nama_flok, a.tanggal_do_terakhir AS tanggal_closing, a.tanggal_mulai, respon_peternak, penilaian_plasma, komplain_1, komplain_2, c.jenis, c.vendor_pakan, kategori, poin,
                                    (CASE WHEN komplain_1 IS NULL AND komplain_2 IS NULL THEN '---' WHEN komplain_1 ='---' AND komplain_2 ='---' THEN '---' WHEN keterangan ='' AND file_bukti ='' THEN 'BELUM' WHEN keterangan !='' AND file_bukti ='' THEN 'DALAM PROSES...' ELSE 'SELESAI' END) AS progres,
                                    keterangan, file_bukti, b.nomor_hp, b.nama_plasma, validasi, d.kronologi_munculnya_komplain, d.tindakan_yang_sudah_dilakukan, d.tindakan_perbaikan_ke_depan
                                FROM tb_proses_rhpp a
                                LEFT JOIN tb_plasma b ON b.nama_flok=a.nama_flok AND b.nama_unit=a.unit
                                LEFT JOIN tb_rhpp c ON c.nama_flok=a.nama_flok AND c.unit=a.unit AND c.tanggal_doc_out=a.tanggal_do_terakhir
                                LEFT JOIN tb_detail_komplain_plasma d ON d.nama_flok=a.nama_flok AND d.unit=a.unit AND d.tanggal_do_terakhir=a.tanggal_do_terakhir
                                WHERE penilaian_plasma <> '' AND a.tanggal_do_terakhir BETWEEN '$tglawal' AND '$tglakhir'");
        }

        if(($roles==='pusat') || ($roles==='admin')){
            return Datatables::of($data)
            ->addColumn('Actions', function($data) {
                if($data->validasi != ''){
                    return '<button type="button" class="btn btn-primary btn-sm" id="getEditData" data-id="'.$data->id.'"><span class="cil-pencil"></span></button>&nbsp;
                        <a href="https://wa.me/'.no_wa($data->nomor_hp).'?text=Hallo%20Bpk/Ibu%20'.$data->nama_plasma.'..." class="btn btn-success btn-sm" target="_blank"><span class="cil-chat-bubble"></span></a>&nbsp;
                        <button type="button" class="btn btn-warning btn-sm" detail_komplain><span class="cil-pencil text-white"></span></button>&nbsp;
                        <button type="button" class="btn btn-secondary btn-sm" _pdf><span class="cil-arrow-thick-to-bottom text-white"></span></button>';
                }else{
                    return '<button type="button" class="btn btn-outline-danger btn-sm" id="btnValidasi" data-id="'.$data->id.'"><span class="cil-check"></span></button>&nbsp;
                        <button type="button" class="btn btn-primary btn-sm" id="getEditData" data-id="'.$data->id.'"><span class="cil-pencil"></span></button>&nbsp;
                        <a href="https://wa.me/'.no_wa($data->nomor_hp).'?text=Hallo%20Bpk/Ibu%20'.$data->nama_plasma.'..." class="btn btn-success btn-sm" target="_blank"><span class="cil-chat-bubble"></span></a>&nbsp;
                        <button type="button" class="btn btn-warning btn-sm" detail_komplain><span class="cil-pencil text-white"></span></button>&nbsp;
                        <button type="button" class="btn btn-secondary btn-sm" _pdf><span class="cil-arrow-thick-to-bottom text-white"></span></button>';
                }
            })
            ->rawColumns(['status', 'Actions'])
            ->make(true);
        }else{
            return Datatables::of($data)
            ->addColumn('Actions', function($data) {
                return '<button type="button" class="btn btn-primary btn-sm" id="getEditData" data-id="'.$data->id.'"><span class="cil-pencil"></span></button>&nbsp;
                        <a href="https://wa.me/'.no_wa($data->nomor_hp).'?text=Hallo%20Bpk/Ibu%20'.$data->nama_plasma.'..." class="btn btn-success btn-sm" target="_blank"><span class="cil-chat-bubble"></span></a>&nbsp;
                        <button type="button" class="btn btn-warning btn-sm" detail_komplain><span class="cil-pencil text-white"></span></button>&nbsp;
                        <button type="button" class="btn btn-secondary btn-sm" _pdf><span class="cil-arrow-thick-to-bottom text-white"></span></button>';
            })
            ->rawColumns(['status', 'Actions'])
            ->make(true);
        }
    }

    public function get_id_komplain_plasma($id){
        $data = DB::select("SELECT * FROM tb_proses_rhpp WHERE id='$id'");
        $html = '<div class="form-group">
                    <label><strong>PENILAIAN PLASMA</strong></label>
                    <label>'.$data[0]->penilaian_plasma.'</label>
                </div>
                <div class="form-group">
                    <label>KOMPLAIN 1</label>
                    <select class="form-control" name="komplain_1" id="komplain_1" required>
                        <option value="'.$data[0]->komplain_1.'">'.$data[0]->komplain_1.'</option>
                        <option value="---">---</option>
                        <option value="PAKAN">PAKAN</option>
                        <option value="DOC">DOC</option>
                        <option value="PANEN">PANEN</option>
                        <option value="PETUGAS">PETUGAS</option>
                        <option value="KONTRAK">KEBIJAKAN - KONTRAK</option>
                        <option value="PENCAIRAN RHPP">KEBIJAKAN - PENCAIRAN RHPP</option>
                        <option value="KOMPENSASI">KEBIJAKAN - KOMPENSASI</option>
                        <option value="MUTASI PAKAN">KEBIJAKAN - MUTASI PAKAN</option>
                        <option value="SIKLUS CHICK IN">KEBIJAKAN - SIKLUS CHICK IN</option>
                        <option value="KIRIMAN PAKAN">KEBIJAKAN - KIRIMAN PAKAN</option>
                        <option value="PERPAJAKAN">KEBIJAKAN - PERPAJAKAN</option>
                        <option value="BONUS">KEBIJAKAN - BONUS</option>
                        <option value="LAIN-LAIN">KEBIJAKAN - LAIN-LAIN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>KOMPLAIN 2</label>
                    <select class="form-control" name="komplain_2" id="komplain_2" required>
                        <option value="'.$data[0]->komplain_2.'">'.$data[0]->komplain_2.'</option>
                        <option value="---">---</option>
                        <option value="PAKAN">PAKAN</option>
                        <option value="DOC">DOC</option>
                        <option value="PANEN">PANEN</option>
                        <option value="PETUGAS">PETUGAS</option>
                        <option value="KONTRAK">KEBIJAKAN - KONTRAK</option>
                        <option value="PENCAIRAN RHPP">KEBIJAKAN - PENCAIRAN RHPP</option>
                        <option value="KOMPENSASI">KEBIJAKAN - KOMPENSASI</option>
                        <option value="MUTASI PAKAN">KEBIJAKAN - MUTASI PAKAN</option>
                        <option value="SIKLUS CHICK IN">KEBIJAKAN - SIKLUS CHICK IN</option>
                        <option value="KIRIMAN PAKAN">KEBIJAKAN - KIRIMAN PAKAN</option>
                        <option value="PERPAJAKAN">KEBIJAKAN - PERPAJAKAN</option>
                        <option value="BONUS">KEBIJAKAN - BONUS</option>
                        <option value="LAIN-LAIN">KEBIJAKAN - LAIN-LAIN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>KATEGORI</label>
                    <select class="form-control" name="kategori" id="kategori" required>
                        <option value="'.$data[0]->kategori.'">'.$data[0]->kategori.'</option>
                        <option value="---">---</option>
                        <option value="BERAT">BERAT</option>
                        <option value="SEDANG">SEDANG</option>
                        <option value="RINGAN">RINGAN</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>KETERANGAN</label>
                    <textarea class="form-control" name="keterangan" id="keterangan" rows="3">'.$data[0]->keterangan.'</textarea>
                </div>
                <div class="form-group">
                    <label">BUKTI FILE</label>
                    <input class="form-control" type="file" id="file_bukti" name="file_bukti">
                </div>';
        return response()->json(['html'=>$html]);
    }

    public function update_source_komplain_plasma(Request $request, $id){
        if($request->input('file_bukti')==='undefined'){
            $validator = Validator::make($request->all(), [
                'komplain_1' => 'required',
                'komplain_2' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()]);
            }
            DB::table('tb_proses_rhpp')->where('id', $id)->update([
                    'komplain_1' => $request->input('komplain_1'),
                    'komplain_2' => $request->input('komplain_2'),
                    'kategori' => $request->input('kategori'),
                    'poin' => poin_komplain($request->input('kategori')),
                    'keterangan' => $request->input('keterangan'),
                ]);
            return response()->json(['success'=>'Data updated successfully']);
        }else{
            $validator = Validator::make($request->all(), [
                'komplain_1' => 'required',
                'komplain_2' => 'required',
                'file_bukti' => 'mimes:png,jpg,jpeg,csv,txt,xlx,xls,pdf|max:2048',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()]);
            }
            $input['file_bukti'] = time().'.'.$request->file_bukti->getClientOriginalExtension();
            $file_upload =  $request->file_bukti->move('komplain-plasma-file/',$input['file_bukti']);
            if ($file_upload) {
                DB::table('tb_proses_rhpp')->where('id', $id)->update([
                    'komplain_1' => $request->input('komplain_1'),
                    'komplain_2' => $request->input('komplain_2'),
                    'kategori' => $request->input('kategori'),
                    'poin' => poin_komplain($request->input('kategori')),
                    'keterangan' => $request->input('keterangan'),
                    'file_bukti' => $input['file_bukti'],
                ]);
                return response()->json(['success'=>'Data updated successfully']);
            } else {
                return response()->json(['error'=>'Gagal diproses']);
            }
        }
    }

    public function komplain_plasma_validasi($id){
        $nik = Auth::user()->nik;
        DB::table('tb_proses_rhpp')->where('id', $id)->update([
            'validasi' => $nik,
        ]);
        return response()->json(['success'=>'Data updated successfully']);
    }

    public function simulasi_insentif_plotting_update_data(Request $request){
        $id  = $request->input('id');
        $value  = $request->input('value');
        $sql = DB::table('table_simulasi_insentif_plotting')->where('id', $id)->update([
            'nilai' => $value,
        ]);

        if($sql){
            return response()->json(
                [
                'success' => true,
                'message' => 'Data berhasil disimpan'
                ]
            );
        }else{
            return response()->json(
                [
                'success' => false,
                'message' => 'Gagal.!!'
                ]
            );
        }

    }

    public function simulasi_insentif_upsert(Request $request)
    {
        try {
            $data = [
                'unit' => $request->unit,
                'jabatan' => $request->jabatan,
                'kategori' => $request->kategori,
            ];
            DB::table('table_simulasi_insentif_plotting')
            ->upsert(
                $data,
                ['unit', 'jabatan', 'kategori'],
                ['nilai'],
            );
            return response()->json(['msg' => 'berhasil', 'data' => $data], 200);
        } catch (\Throwable $th) {
            return response()->json(['msg' => $th->getMessage()], 500);
        }
    }

    public function simulasi_insentif_save_plotting(Request $request)
    {
        try {
            $data = json_decode($request->data, true);
            DB::table('table_simulasi_insentif_perhitungan')->upsert($data['hitung'], ['unit', 'tahun', 'bulan'], ['insentif_produksi', 'insentif_penjualan', 'insentif_kb', 'insentif_collab', 'insentif_ho']);
            DB::table('table_simulasi_insentif_jabatan')->upsert($data['jabatan'], ['jabatan', 'tahun', 'bulan'], ['all', 'min', 'max']);
            DB::table('table_simulasi_insentif_plotting')->upsert($data['plotting'], ['unit', 'jabatan', 'tahun', 'bulan'], ['kareg', 'plot_kareg', 'plotting', 'jumlah']);
            Alert::success('Berhasil menyimpan');
            return redirect()->back();
        } catch (\Throwable $th) {
            Alert::error('Terjadi kesalahan. ' . $th->getMessage());
            return redirect()->back();
        }
    }

    public function survey_media_sosial_soal()
    {
        $user = Auth::user();

        $jawaban = DB::table('tb_survey_media_sosial_jawaban')
        ->where('nik', $user->nik)
        ->get()
        ->mapWithKeys(fn($i) => [$i->soal_id => $i]);

        $soal = DB::table('tb_survey_media_sosial_soal')
        ->get()
        ->map(function($item) use ($jawaban) {
            $item->pilihan = json_decode($item->pilihan, true);
            $item->jawaban = $jawaban[$item->id] ?? null;
            return $item;
        })
        ->filter(fn($item) => $item->jawaban === null)
        ->values();

        return $soal;
    }

    public function survey_media_sosial()
    {
        $user = Auth::user();

        $soal = $this->survey_media_sosial_soal();

        return view('dashboard.home.survey_media_sosial', compact('user', 'soal'));
    }

    public function survey_media_sosial_save(Request $request)
    {
        try {
            $user = Auth::user();

            $soal = DB::table('tb_survey_media_sosial_soal')
            ->get()
            ->mapWithKeys(fn($i) => [$i->id => $i]);

            $data = collect($request->all())
            ->filter(fn($_, $i) => $i !== '_token')
            ->map(function($item, $id) use ($user, $soal) {
                return [
                    'nik' => $user->nik,
                    'soal_id' => $id,
                    'jawaban' => ($soal[$id] ?? (object) ['type' => null])->type === 'multi-option' ? json_encode($item) : $item,
                ];
            })
            ->values();
            DB::table('tb_survey_media_sosial_jawaban')->upsert($data->toArray(), ['soal_id', 'nik'], ['jawaban']);

            $response_data = $data
            ->map(function($item) use ($soal) {
                return (object) [
                    'soal' => $soal->get($item['soal_id'])->soal,
                    'jawaban' => $item['jawaban'],
                ];
            });

            // Alert::success('Berhasil mengirim jawaban');
            // return redirect()->route('home');
            return response()->json(['msg' => 'Berhasil', 'data' => $response_data], 200);
        } catch (\Throwable $th) {
            // Alert::error('Terjadi kesalahan. ' . $th->getMessage());
            // return redirect()->back();
            return response()->json(['msg' => 'Gagal. ' . $th->getMessage(), 'data' => []], 500);
        }
    }

    public function resume_survey_media_sosial()
    {
        $soal = DB::table('tb_survey_media_sosial_soal')
        ->get()
        ->map(function($item) {
            if (in_array($item->type, ['self-option'])) {
                $item->pilihan = collect([...json_decode($item->pilihan, true), 'PILIHAN LAINNYA']);
            } else {
                $item->pilihan = collect(json_decode($item->pilihan, true));
            }
            return $item;
        })
        ->mapWithKeys(fn($i) => [$i->id => $i]);

        $soal_id_with_multi_option = $soal
        ->filter(fn($i) => in_array($i->type, ['multi-option']))
        ->map(fn($_, $i) => $i)
        ->values()
        ->toArray();

        $soal_id_with_self_option = $soal
        ->filter(fn($i) => in_array($i->type, ['self-option']))
        ->map(fn($_, $i) => $i)
        ->values()
        ->toArray();

        $soal_null = (object) [
            'id' => 0,
            'soal' => null,
            'pilihan' => [],
            'type' => null,
            'reset' => null,
        ];

        $data = DB::table('tb_survey_media_sosial_jawaban')
        ->get()
        ->map(function($item) use ($soal, $soal_id_with_multi_option, $soal_id_with_self_option) {
            if (in_array($item->soal_id, $soal_id_with_multi_option)) {
                $item->jawaban = json_decode($item->jawaban, true);
            }
            // if (in_array($item->soal_id, $soal_id_with_self_option)) {
            //     $item->jawaban = $soal->get($item->soal_id)->pilihan->contains($item->jawaban) ? $item->jawaban : 'PILIHAN LAINNYA';
            // }
            return $item;
        })
        ->groupBy('soal_id')
        ->map(function($item, $soal_id) use ($soal, $soal_null, $soal_id_with_self_option) {
            $joined = $soal->get($soal_id) ?? $soal_null;
            return (object) [
                'id' => $joined->id,
                'soal' => $joined->soal,
                'expand' => $joined->type === 'esay' ? $item->pluck('jawaban')->toArray() : [],
                'details' => $joined->pilihan
                ->map(function($pilihan) use ($item, $soal_id, $soal_id_with_self_option) {
                    $result = (object) [];
                    $result->pilihan = $pilihan;
                    if ($pilihan === 'PILIHAN LAINNYA' && in_array($soal_id, $soal_id_with_self_option)) {
                        $result->jumlah = $item
                        ->filter(function($item) use ($pilihan) {
                            return $item->jawaban !== $pilihan;
                        })
                        ->count();
                        $result->expand = $item
                        ->pluck('jawaban')
                        ->toArray();
                    } else {
                        $result->jumlah = $item
                        ->filter(function($item) use ($pilihan) {
                            return (
                                gettype($item->jawaban) === 'string' && $item->jawaban === $pilihan)
                                || (gettype($item->jawaban) === 'array' && in_array($pilihan, $item->jawaban)
                            );
                        })
                        ->count();
                    }
                    $result->jumlah_all = $item->count();
                    $result->persen = $result->jumlah_all === 0 ? 0 : ($result->jumlah / $result->jumlah_all * 100);
                    return $result;
                })
                ->toArray(),
            ];
        })
        ->values();

        return view('dashboard.home.resume_survey_media_sosial', compact('data'));
    }

    public function cetak_invoice(Request $request){
        $n = $request->input('n') ?? 'nav-1';
        $barang = DB::table('table_invoice_barang')->get();
        $pelanggan = DB::table('table_invoice_pelanggan')->orderBy('nama_lengkap')->get();
        $data_unit = DB::table('units')
        ->orderBy('kodeunit', 'ASC')
        ->get()
        ->mapWithKeys(fn($i) => [$i->kodeunit => $i]);
        $transaksi = DB::table('table_invoice_data AS a')
        ->leftJoin('table_invoice_pelanggan AS b', 'a.id_pelanggan', 'b.id')
        ->select('a.*', 'b.nama_lengkap')
        ->get();
        return view('dashboard.home.cetak_invoice', compact('barang', 'pelanggan','n','data_unit', 'transaksi'));
    }

    public function get_data_invoice_pelanggan(){
            $data = DB::select("SELECT * FROM table_invoice_pelanggan");
            return Datatables::of($data)
                ->addColumn('Actions', function($data) {
                    return '<button type="button" class="btn btn-primary btn-sm" id="get_id_pelanggan" data-id="'.$data->id.'" data-toggle="modal" data-target="#modal-tambah-pelanggan"><i class="icon icon-xxl mt-5 mb-2 cil-pencil"></i></button>&nbsp;
                    <button type="button" data-id="'.$data->id.'" data-toggle="modal" data-target="#DeleteModalPelanggan" class="btn btn-danger btn-sm" id="getDeleteIdPelanggan"><i class="icon icon-xxl mt-5 mb-2 cil-trash"></i></button>';
                })
                ->rawColumns(['status', 'Actions'])
                ->make(true);
    }

    public function invoice_pelanggan_simpan(Request $request){
        $id = $request->input('id');
        $num_row = getRows('table_invoice_pelanggan', 'id', $id);
        if($num_row > 0){
            DB::table('table_invoice_pelanggan')->where('id', $id)->update([
                'nama_lengkap' => $request->input('nama_lengkap'),
                'alamat' => $request->input('alamat'),
            ]);
            return response()->json(
                [
                'success' => true,
                'message' => 'Data berhasil diubah'
                ]
            );
        }else{
            DB::table('table_invoice_pelanggan')->insert([
                'nama_lengkap' => $request->input('nama_lengkap'),
                'alamat' => $request->input('alamat'),
            ]);
            return response()->json(
                [
                'success' => true,
                'message' => 'Data berhasil disimpan'
                ]
            );
        }
    }

    public function get_data_invoice_pelanggan_id($id){
        $sql = DB::select("SELECT * FROM table_invoice_pelanggan WHERE id='$id'");
        return json_encode($sql);
    }

    public function get_data_invoice_pelanggan_hapus($id){
        $sql = DB::table('table_invoice_pelanggan')->where('id', '=', $id)->delete();
        if($sql){
            return response()->json(
                [
                'success' => true,
                'message' => 'Data berhasil dihapus'
                ]
            );
        }
    }

    public function create_nomor_invoice(Request $request){
        $unit = $request->input('unit');

        $sql_ap = DB::select("SELECT region FROM units WHERE kodeunit='$unit'");
        $ap = $sql_ap[0]->region ?? '';

        $no_terakhir = 0;
        $tahun = date('Y');
        $sql = DB::select("SELECT  MAX(RIGHT(no_invoice,4)) AS no_terakhir FROM table_invoice_data WHERE YEAR(tanggal)=$tahun AND ap='$ap'");
        $no_terakhir =  $sql[0]->no_terakhir;
        //INV/GPS_CLP/24/0001
        if(!empty($no_terakhir)) {
            $no_urut = abs($no_terakhir + 1);
            $no_invoice = 'INV/'.$ap.'_'.$unit.'/'.date('y').'/'.sprintf("%04s", $no_urut);
        }else {
            $no_urut = 0001;
            $no_invoice = 'INV/'.$ap.'_'.$unit.'/'.date('y').'/'.sprintf("%04s", $no_urut);
        }
        return response()->json(['no_invoice' => $no_invoice, 'unit' => $unit, 'ap' => $ap]);
    }

    public function get_data_invoice_barang(){
            $data = DB::select("SELECT * FROM table_invoice_barang");
            return Datatables::of($data)
                ->addColumn('Actions', function($data) {
                    return '<button type="button" class="btn btn-primary btn-sm" id="get_id_barang" data-id_barang="'.$data->id.'" data-toggle="modal" data-target="#modal-tambah-barang"><i class="icon icon-xxl mt-5 mb-2 cil-pencil"></i></button>&nbsp;
                    <button type="button" data-id_barang="'.$data->id.'" data-toggle="modal" data-target="#DeleteModalBarang" class="btn btn-danger btn-sm" id="getDeleteIdBarang"><i class="icon icon-xxl mt-5 mb-2 cil-trash"></i></button>';
                })
                ->rawColumns(['status', 'Actions'])
                ->make(true);
    }

    public function invoice_data_barang_simpan (Request $request){
        $id = $request->input('id');
        $num_row = getRows('table_invoice_barang', 'id', $id);
        if($num_row > 0){
            DB::table('table_invoice_barang')->where('id', $id)->update([
                'nama_barang' => $request->input('nama_barang'),
                'satuan' => $request->input('satuan'),
                'harga' => $request->input('harga'),
            ]);
            return response()->json(
                [
                'success' => true,
                'message' => 'Data berhasil diubah'
                ]
            );
        }else{
            DB::table('table_invoice_barang')->insert([
                'nama_barang' => $request->input('nama_barang'),
                'satuan' => $request->input('satuan'),
                'harga' => $request->input('harga'),
            ]);
            return response()->json(
                [
                'success' => true,
                'message' => 'Data berhasil disimpan'
                ]
            );
        }
    }

    public function get_data_invoice_barang_id($id){
        $sql = DB::select("SELECT * FROM table_invoice_barang WHERE id='$id'");
        return json_encode($sql);
    }

    public function get_data_invoice_barang_hapus($id){
        $sql = DB::table('table_invoice_barang')->where('id', '=', $id)->delete();
        if($sql){
            return response()->json(
                [
                'success' => true,
                'message' => 'Data berhasil dihapus'
                ]
            );
        }
    }

    public function print_invoice(Request $request){
        $nomor_invoice = $request->route('id');
        $nomor_invoice = str_replace(".","/",$nomor_invoice);
        $sql = DB::select("SELECT a.*, b.*, c.*, (harga * jumlah) AS nilai FROM table_invoice_data a
                            INNER JOIN table_invoice_detail b ON b.nomor_invoice=a.no_invoice
                            INNER JOIN table_invoice_pelanggan c ON c.id=a.id_pelanggan
                            WHERE a.no_invoice = '$nomor_invoice'");

        $data_sql = collect(array_map(function($item){
                    return $item;
                },$sql));

        $nama_pelanggan = $sql[0]->nama_lengkap;
        $alamat_pelanggan = $sql[0]->alamat;

        $tgl_invoice = $sql[0]->tanggal;
        $termin = $sql[0]->termin;
        $due_date = $sql[0]->tgl_jatuh_tempo;
        $html = View::make('dashboard.home.print_invoice', compact('sql','data_sql'));
        $dompdf = Pdf::loadHTML($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->set_option('defaultMediaType', 'all');
        $dompdf->set_option('isFontSubsettingEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->setOption('isRemoteEnabled', true);
        $dompdf->setOption('dpi', 171);
        $dompdf->render();
        return $dompdf->stream('surat_tugas.pdf');
    }

    public function save_invoice(Request $request)
    {
        try {
            $data = [
                'tanggal' => date('Y-m-d'),
                'ap' => $request->ap,
                'unit' => $request->unit,
                'no_invoice' => $request->no_invoice,
                'id_pelanggan' => $request->id_pelanggan,
                'termin' => $request->termin,
                'tgl_jatuh_tempo' => $request->tgl_jatuh_tempo,
                'diskon' => $request->diskon,
                'pajak' => $request->pajak,
                'tarif_pajak' => $request->tarif_pajak,
                'total' => $request->total,
            ];
            $detail = array_map(fn($item) => [
                'nomor_invoice' => $request->no_invoice,
                'diskripsi' => $item['nama_barang'],
                'jumlah' => $item['qty'],
                'satuan' => $item['satuan'],
                'harga' => $item['harga'],
                'sub_totoal' => $item['nilai'],
            ], $request->invoice);

            DB::table('table_invoice_data')->insert($data);
            DB::table('table_invoice_detail')->insert($detail);
            return response()->json(['message' => 'Berhasil', 'data' => $request->all()], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function delete_invoice(Request $request)
    {
        try {
            DB::table('table_invoice_data')->where('no_invoice', "$request->no_invoice")->delete();
            DB::table('table_invoice_detail')->where('nomor_invoice', "$request->no_invoice")->delete();
            Alert::success('Berhasil');
            return redirect()->back();
        } catch (\Throwable $th) {
            Alert::error('Error: ' . $th->getMessage());
            return redirect()->back();
        }
    }

    public function data_evaluasi_create()
    {
        return view('dashboard.home.data_evaluasi_editor');
    }

    public function data_evaluasi_edit(int $id)
    {
        $user = Auth::user();
        $data = DB::table('tb_post_data_evaluasi')
        ->where([
            'id' => $id,
            'nik' => $user->nik,
        ])
        ->first();
        if (!$data) {
            abort(404);
        }
        return view('dashboard.home.data_evaluasi_editor', compact('data'));
    }

    public function data_evaluasi_save(Request $request)
    {
        try {
            $user = Auth::user();
            $data = [
                'judul' => $request->judul,
                'body' => $request->body,
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $file_name = $original_name . '_' . time() . '.' . $extension;
                $file->move('data-evaluasi-file/', $file_name);
                $data['thumb'] = $file_name;
            }

            if (isset($request->id)) {
                DB::table('tb_post_data_evaluasi')->where('id', $request->id)->update($data);
            } else {
                $data['nik'] = $user->nik;
                $data['tanggal'] = date('Y-m-d');
                DB::table('tb_post_data_evaluasi')->insert($data);
            }

            Alert::success('Berhasil');
            return redirect()->route('home');
        } catch (\Throwable $th) {
            Alert::error("Gagal {$th->getMessage()}");
            return redirect()->back();
        }
    }

    public function data_evaluasi_upload(Request $request)
    {
        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $original_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $file_name = $original_name . '_' . time() . '.' . $extension;
                $file->move('data-evaluasi-file/', $file_name);
                return response()->json(['file' => url('data-evaluasi-file/'. $file_name)], 200);
            } else {
                return response()->json(['message' => 'Error No file uploaded'], 400);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => "Error {$th->getMessage()}"], 500);
        }
    }

    public function data_evaluasi_show(int $id)
    {
        $user = Auth::user();
        $data = DB::table('tb_post_data_evaluasi')->where('id', $id)->first();
        if (!$data) {
            abort(404);
        } else {
            $data->editable = $user->nik === $data->nik;
        }
        return view('dashboard.home.data_evaluasi_show', compact('data', 'id'));
    }

    public function data_evaluasi_delete(int $id)
    {
        try {
            $user = Auth::user();
            $delete_status = DB::table('tb_post_data_evaluasi')->where(['id' => $id, 'nik' => $user->nik])->delete();
            if ($delete_status === 0) {
                Alert::error('Error, data tidak ditemukan');
            } else {
                Alert::success('Berhasil');
            }
            return redirect()->route('home');
        } catch (\Throwable $th) {
            Alert::error($th->getMessage());
            return redirect()->route('home');
        }
    }

    public function data_evaluasi_load_comment(int $post_id, int $page)
    {
        try {
            $per_page = 10;
            $offset = ($page - 1) * $per_page;
            $comments = DB::table('tb_post_data_evaluasi_comment as a')
            ->leftJoin('users as b', 'a.nik', 'b.nik')
            ->where('a.post_id', $post_id)
            // ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($per_page)
            ->select(
                'a.*',
                DB::raw("coalesce(b.name, a.nik, 'user tidak ditemukan') as user"),
            )
            ->get()
            ->map(function($item) {
                $item->body = preg_replace('/\r\n|\r|\n/', '<br>', $item->body);
                return $item;
            });

            return response()->json($comments, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function data_evaluasi_send_comment(Request $request)
    {
        try {
            $user = Auth::user();

            $response_data = [
                'name' => $user->name,
                'comment' => preg_replace('/\r\n|\r|\n/', '<br>', $request->comment),
            ];

            DB::table('tb_post_data_evaluasi_comment')->insert([
                'post_id' => $request->post_id,
                'nik' => $user->nik,
                'body' => $request->comment,
            ]);

            return response()->json($response_data, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function log_wa_api()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://wa.ptmustika.my.id/whatsapp.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode('[' . rtrim($result, ',') . ']');

        return view('dashboard.home.log_wa_home_part', compact('data'));
    }

    public function get_evaluasi_data_posts()
    {
        try {
            $data = DB::table('tb_post_data_evaluasi as a')
            ->leftJoin('users as b', 'a.nik', 'b.nik')
            ->select('a.*', 'b.name')
            ->orderBy('a.id', 'desc')
            ->paginate(3);

            $data->getCollection()->transform(function($item) {
                $item->body = \Str::limit(strip_tags($item->body), 800);
                return $item;
            });

            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function lokasi()
    {
        $user = Auth::user();
        $coords = DB::table('units')->where('kodeunit', $user->unit)->pluck('long', 'lat')->first();
        return view('dashboard.lokasi', compact('coords'));
    }

    public function lokasi_save(Request $request)
    {
        try {
            $user = Auth::user();
            $result = DB::table('units')->where('kodeunit', $user->unit)->update(['long' => $request->long, 'lat' => $request->lat, 'alamat' => $request->alamat]);
            if ($result === 0) {
                Alert::error("Unit [{$user->unit}] tidak ditemukan");
                return redirect()->back();
            }
            Alert::success('Berhasil');
            return redirect()->back();
        } catch (\Throwable $th) {
            Alert::error($th->getMessage());
            return redirect()->back();
        }
    }

    public function wa_blast()
    {
        $daftar = DB::table('tb_wa_blast_number')->get();
        return view('dashboard.wa_blast', compact('daftar'));
    }

    public function wa_blast_import(Request $request)
    {
        try {
            $file = $request->file('file');
            $raw = Excel::toArray((object) [], $file);
            $data = array_map(fn($item) => ['nama' => $item[1], 'area' => $item[2], 'nomor' => preg_replace('/^0/', '62', trim($item[3]))], array_slice($raw[0], 1));
            DB::table('tb_wa_blast_number')->truncate();
            DB::table('tb_wa_blast_number')->insert($data);
            Alert::success('Berhasil');
            return redirect()->back();
        } catch (\Throwable $th) {
            Alert::error('Gagal ' . $th->getMessage());
            return redirect()->back();
        }
    }

    public function wa_blast_sender(Request $request)
    {
        try {
            $data = [
                'api_key' => 'aDOYclFtJKAKPkRVRFWyAokb4LfyRM',
                'sender' => '62816268436',
                'number' => '6285201248240',
                // 'number' => $request->nomor,
                'message' => $request->message,
            ];

            $encode = json_encode($data);
            $encode_length = strlen($encode);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://wa.ptmustika.my.id/send-message');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "Content-Length: $encode_length"]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encode);
            curl_exec($ch);
            $res = curl_exec($ch);
            curl_close($ch);

            return response()->json(['message' => 'Berhasil', 'wa_server_response' => $res], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function detail_komplain_save(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'unit' => 'required',
                    'nama_flok' => 'required',
                    'tanggal_do_terakhir' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $data = $request->only([
                'unit',
                'nama_flok',
                'tanggal_do_terakhir',
                'kronologi_munculnya_komplain',
                'tindakan_yang_sudah_dilakukan',
                'tindakan_perbaikan_ke_depan',
            ]);

            DB::table('tb_detail_komplain_plasma')
                ->upsert(
                    $data,
                    [
                        /* uniq */
                        'unit',
                        'nama_flok',
                        'tanggal_do_terakhir',
                    ],
                    [
                        /* update */
                        'kronologi_munculnya_komplain',
                        'tindakan_yang_sudah_dilakukan',
                        'tindakan_perbaikan_ke_depan',
                    ]
                );
            return response()->json($request->all());
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function komplain_plasma_pdf(Request $request)
    {
        function format_tanggal(string $input) {
            try {
                [$tahun, $bulan, $tanggal] = explode('-', $input);
                return (object) [
                    'tanggal' => $tanggal,
                    'bulan' => [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$bulan],
                    'tahun' => $tahun,
                ];
            } catch (\Throwable $th) {
                return null;
            }
        }

        $bulan_list = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $date = '2024-12-24';
        $data = (object) $request->all();
        if ($data) {
            $data->format_tanggal_mulai = format_tanggal($data->tanggal_mulai);
            $data->format_tanggal_do_terakhir = format_tanggal($data->tanggal_do_terakhir);
            $data->format_tanggal_now = date('Y-m-d');
            $data->format_tanggal_now = format_tanggal($data->format_tanggal_now);
            $data->nama_unit = ucfirst(strtolower(DB::table('units')->where('kodeunit', $data->unit)->pluck('namaunit')->first()));
        }
        // dd($data);
        // return view('dashboard.home.komplain_plasma_pdf', compact('data'));
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.home.komplain_plasma_pdf', compact('data'))->stream("{$data->nama_flok} {$data->tanggal_closing}.pdf");
    }

    public function indeks_kepuasan_tim_ho_soal()
    {
        $user = Auth::user();
        // $user->jabatan = 'KEPALA PRODUKSI';

        $season = (function() {
            $start = '2025-01-01'; // tanggal mulai
            $interval = '+4 months'; // interval pengecekkan

            $seasons = [];
            for (
                $i = strtotime($start);
                $i <= strtotime(date('Y-m-d'));
                $i = strtotime($interval, $i)
            ) {
                $seasons[] = date('Y-m-d', $i);
            }

            return count($seasons);
        })();

        $list_bagian = DB::table('tb_kepuasan_ho_bagian')
            ->whereJsonContains('penilai', $user->jabatan)
            ->limit($season === 0 ? 0 : null)
            ->get();

        $list_soal = DB::table('tb_kepuasan_ho_soal')
            ->limit($season === 0 ? 0 : null)
            ->get()
            ->map(function($item) {
                $item->options = json_decode($item->options, true);
                return $item;
            });

        $list_jawaban = DB::table('tb_kepuasan_ho_jawaban')
            ->where([
                'season' => $season,
                'nik' => $user->nik,
            ])
            ->limit($season === 0 ? 0 : null)
            ->get();

        return (object) [
            'user' => $user,
            'season' => $season,
            'bagians' => $list_bagian
                ->map(
                    fn($bagian) => (object) [
                        'bagian' => $bagian,
                        'soals' => $list_soal
                            ->whereNotIn(
                                'id',
                                $list_jawaban
                                    ->where('bagian_id', $bagian->id)
                                    ->pluck('soal_id')
                            ),
                    ]
                )
                ->filter(fn($item) => $item->soals->count() > 0)
                ->values(),
        ];
    }

    public function indeks_kepuasan_tim_ho()
    {
        $raw = $this->indeks_kepuasan_tim_ho_soal();
        $user = $raw->user;
        $bagians = $raw->bagians;
        $season = $raw->season;
        return view('dashboard.home.indeks_kepuasan_tim_ho', compact('user', 'bagians', 'season'));
    }

    public function indeks_kepuasan_tim_ho_save(Request $request)
    {
        try {
            $user = Auth::user();
            $data = collect($request->all())
                ->filter(function($_, $index) {
                    return $index !== '_token';
                })
                ->map(function($item, $index) use ($user) {
                    [$season, $bagian_id, $soal_id] = explode('|', $index);
                    return [
                        'nik' => $user->nik,
                        'season' => (int) $season,
                        'soal_id' => (int) $soal_id,
                        'bagian_id' => (int) $bagian_id,
                        'option' => $item,
                    ];
                })
                ->values()
                ->toArray();

            DB::table('tb_kepuasan_ho_jawaban')->upsert($data, ['nik', 'season', 'soal_id', 'bagian_id'], ['option']);

            Alert::success('Berhasil');
            return redirect()->route('home');
        } catch (\Throwable $th) {
            Alert::error("Error: {$th->getMessage()}");
            return redirect()->back();
        }
    }

    public function ask_ai(Request $request) {
        try {
            $user = Auth::user();
            $history = DB::table('tbl_history_ai')
                ->where('nik', $user->nik)
                ->orderBy('created_at', 'asc')
                ->select('role', DB::raw("message as text"))
                ->get();

            $data = [
                'history' => $history,
                'text' => $request->text,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileExtension = $image->getClientOriginalExtension();
                $fileName = time() . '_'. \Str::random(5) . '_' . $fileExtension;

                $filePath = public_path('chatAI/' . $fileName);

                $img = Image::make($image);
                $img->resize(700,700, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->save($filePath);

                $buffer = base64_encode(file_get_contents($image->path()));
                $data['buffer'] = $buffer;
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://x-elysia.ptmustika.my.id/gemini/image",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($data),
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                return response()->json([
                    'error' => curl_error($curl)
                ], 500);
            }
            curl_close($curl);

            $responseData = json_decode($response, true);

            if (isset($responseData['candidates']) && !empty($responseData['candidates'])) {
                $penjelasan = $responseData['candidates'][0]['content']['parts'][0]['text'];
            } else {
                $penjelasan = "Data tidak ditemukan.";
            }

            DB::table('tbl_history_ai')->insert([
                'nik' => $user->nik,
                'role' => 'user',
                'message' => $request->text,
                'created_at' => Carbon::now(),
            ]);

            DB::table('tbl_history_ai')->insert([
                'nik' => $user->nik,
                'role' => 'assistant',
                'message' => $penjelasan,
                'created_at' => Carbon::now(),
            ]);

            DB::table('tbl_ai')->insert([
                'nik' => $user->nik,
                'question' => $request->text,
                'text' => $penjelasan,
                'image' => $fileName ?? '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->back();
        } catch (\Throwable $th) {
            Alert::error('Gagal!', $th->getMessage());
            return redirect()->back();
        }
    }
}
