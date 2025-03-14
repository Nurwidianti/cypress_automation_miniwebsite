<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\Ap;
use App\Models\Unit;
use App\Models\Jabatan;
use App\Models\JabatanBaru;
use Auth;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\DataTables\UsersDataTable;
use File;
use Alert;
use App\Models\Regions;
use Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        $batas = 10;
        $no = 1;
        $karyawan = Users::paginate($batas);
        $jml_karyawan = Users::count();
        $no = $batas * ($karyawan->currentPage() - 1);
        return view('dashboard.user.list', compact('karyawan', 'jml_karyawan', 'no'));
    }

    public function create(){
        $jabatan = Jabatan::select('nama')->distinct()->union(
            DB::table(DB::raw("
                (SELECT 'MAGANG' AS nama
                UNION SELECT 'ASISTEN DIREKTUR'
                UNION SELECT 'ADMINISTRATOR'
                UNION SELECT 'RPHU'
                UNION SELECT 'STAFF OPERASIONAL'
                UNION SELECT 'STAFF IA') AS additional_jabatan
            "))
        )->orderBy('nama')->toBase()->get();

        $region = Regions::select('koderegion', 'namaregion')->unionAll(
            DB::table(DB::raw("(SELECT 'MJL' AS koderegion, 'PT MUSTIKA JAYA LESTARI' AS namaregion) AS additional_jabatan"))
        )->orderBy('koderegion')->toBase()->get();

        return view('dashboard.user.add', compact('jabatan', 'region'));
    }

    public function simpan(Request $request){
        // Cari user berdasarkan NIK atau Nama
        $userFind = Users::where('nik', $request->nik)->orWhere('name', $request->nama)->first();

        // Jika user tidak ditemukan, buat user baru
        if (empty($userFind)) {
            $user = new Users;
            $user->name = $request->nama;
            $user->nik = $request->nik;
            $user->password = empty($request->password) ? Hash::make('Mustika@'.date('Y')) : Hash::make($request->password);  // Hash password
            $user->roles = $request->roles;
            $user->region = $request->region;
            $user->unit = $request->unit;
            $user->jabatan = $request->jabatan;

            // Cek apakah ada file gambar yang di-upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = $request->nik . '_' . $request->nama . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/users'), $filename); // Simpan file di folder 'public/users'
                $user->foto = $filename;  // Simpan nama file di database
            }

            // Simpan user baru ke database
            $user->save();

            // Notifikasi sukses
            Alert::success('BERHASIL', 'User berhasil ditambahkan');
            return redirect('/user');
        } else {
            // Notifikasi error jika user sudah ada
            Alert::error('GAGAL', 'User dengan username ' . $request->nik . ' a/n ' . $request->nama . ' sudah ada')->autoClose(60000);
            return redirect('/user');
        }
    }

    public function edit($id){
        $userKodeRegion = '';
        $user = Users::find($id);

        $jabatan = Jabatan::select('nama')->distinct()->union(
            DB::table(DB::raw("
                (SELECT 'MAGANG' AS nama
                UNION SELECT 'ASISTEN DIREKTUR'
                UNION SELECT 'ADMINISTRATOR'
                UNION SELECT 'RPHU'
                UNION SELECT 'STAFF OPERASIONAL'
                UNION SELECT 'STAFF IA') AS additional_jabatan
            "))
        )->orderBy('nama')->toBase()->get();

        $region = Regions::select('koderegion', 'namaregion')->unionAll(
            DB::table(DB::raw("(SELECT 'MJL' AS koderegion, 'PT MUSTIKA JAYA LESTARI' AS namaregion) AS additional_jabatan"))
        )->orderBy('koderegion')->toBase()->get();

        // Prefill data user
        $userRegion = DB::table(DB::raw("(SELECT koderegion, namaregion FROM regions
            UNION ALL
            SELECT 'MJL' AS koderegion, 'PT MUSTIKA JAYA LESTARI' AS namaregion) AS all_regions")
        )->where('koderegion', $user->region)->groupBy('koderegion')->get();

        $unit = DB::table(DB::raw("(SELECT namaunit, kodeunit, region FROM units
            UNION ALL
            SELECT 'HEAD OFFICE' AS namaunit, 'HO' AS kodeunit, 'MJL' AS region) AS all_units")
        )->where('region', $user->region)->get();

        $userUnit = DB::table(DB::raw("(SELECT namaunit, kodeunit FROM units
            UNION ALL
            SELECT 'HEAD OFFICE' AS namaunit, 'HO' AS kodeunit) AS all_units")
        )->where('kodeunit', $user->unit)->get();

        foreach ($userRegion as $d) {
            $userKodeRegion = $d->koderegion;
            $userRegion = $d->namaregion;
        }

        return view('dashboard.user.edit', compact('user', 'jabatan', 'region', 'userRegion', 'unit', 'userUnit', 'id', 'userKodeRegion'));
    }

    public function update(Request $request, $id){
        $user = Users::find($id);

        $user->name = $request->nama;
        $user->nik = $request->nik;
        $user->roles = $request->roles;
        $user->region = $request->region;
        $user->unit = $request->unit;
        $user->jabatan = $request->jabatan;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $request->nik . '_' . $request->nama . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/img/users'), $filename);
            $user->foto = $filename;
        }

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update();

        Alert::success('BERHASIL', 'User berhasil diedit');
        return redirect('/user');
    }

    public function getUnit($region){
        $unit = Unit::where("region",$region)->pluck('kodeunit','namaunit');
        return response()->json($unit);
    }

    public function getKodeUnit($region){
        $unit = Unit::where("region",$region)->pluck('namaunit','kodeunit');
        return response()->json($unit);
    }

    public function reset($id){
        $user = Users::find($id);
        $user->password = '$2y$10$zHSkghpnQLYKIYY.eGe/yuDH0AriDiwm.2TLgVU/bfckFLtCKgQtq';
        $user->update();
        Alert::toast('Password berhasil direset', 'success');
        return redirect('/user')->with('pesan','Password berhasil direset');
    }

    public function hapus($id){
        // Cari user berdasarkan id
        $user = Users::find($id);

        if ($user) {
            // Ambil nama file foto dari database
            $nama_file = $user->foto;
            // Cek dan hapus file foto jika ada
            if (file_exists(public_path('users/' . $nama_file))) {
                unlink(public_path('users/' . $nama_file)); // Hapus file di folder 'public/users'
            } else if (file_exists(public_path('assets/img/users/' . $nama_file))) {
                unlink(public_path('assets/img/users/' . $nama_file)); // Hapus file di folder 'assets/img/users'
            }
            // Hapus user dari database
            $user->forceDelete();
            // Tampilkan notifikasi sukses
            Alert::toast('User berhasil dihapus', 'success');
        } else {
            // Jika user tidak ditemukan
            Alert::toast('User tidak ditemukan', 'error');
        }

        // Redirect kembali ke halaman user dengan pesan
        return redirect('/user')->with('pesan', 'User berhasil dihapus');
    }

    public function cari(Request $request){
        $cari = $request->input('cari');
        $no = 0;

        $karyawan = DB::table('users')
            ->where('name', 'like', '%' . $cari . '%')
            ->orWhere('nik', 'like', '%' . $cari . '%')
            ->orWhere('jabatan', 'like', '%' . $cari . '%')
            ->orWhere('unit', 'like', '%' . $cari . '%')
            ->orWhere('region', 'like', '%' . $cari . '%')
            ->get();
        $jml_karyawan = DB::table('users')
            ->where('name', 'like', "%" . $cari . "%")
            ->orWhere('nik', 'like', '%' . $cari . '%')
            ->orWhere('jabatan', 'like', '%' . $cari . '%')
            ->orWhere('unit', 'like', '%' . $cari . '%')
            ->orWhere('region', 'like', '%' . $cari . '%')
            ->count();
        return view('dashboard.user.cari', compact('karyawan','jml_karyawan','no','cari'));
    }

    public function toExcel(){
        $karyawan = DB::table('users')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:F1');
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'DATA KARYAWAN');
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray(setTittle());

        $spreadsheet->getActiveSheet()->getStyle('A3:F3')->applyFromArray(setHeader());
        $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(20);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'NIK');
        $sheet->setCellValue('C3', 'NAMA');
        $sheet->setCellValue('D3', 'JABATAN');
        $sheet->setCellValue('E3', 'UNIT');
        $sheet->setCellValue('F3', 'REGION');
        $rows = 4;
        $no = 1;

        foreach ($karyawan as $karyawans) {
            $sheet->setCellValue('A' . $rows, $no++);
            $sheet->setCellValue('B' . $rows, $karyawans->nik);
            $sheet->setCellValue('C' . $rows, $karyawans->name);
            $sheet->setCellValue('D' . $rows, $karyawans->jabatan);
            $sheet->setCellValue('E' . $rows, $karyawans->unit);
            $sheet->setCellValue('F' . $rows, $karyawans->region);

            $sheet->getStyle('A' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('B' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('C' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('D' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('E' . $rows)->applyFromArray(setBody());
            $sheet->getStyle('F' . $rows)->applyFromArray(setBody());

            foreach (range('A', 'F') as $columnID) {
                $sheet->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            $rows++;
        }
        $fileName = "Karyawan.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("export/" . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        return redirect(url('/export/' . $fileName));
    }

    public function sinkron(){
        return getUrlKaryawan();
    }

}
