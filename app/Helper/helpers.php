<?php
// Siswo Start
use App\Models\UpdateSuratEdaran;
use App\Helper\ResponseFormatter;
use App\Models\UpdateSopBukuSaku;
use App\Models\StatistikPengunjungQA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Function Helpers for User
function cboUserRoles(){
  echo '<option value="" selected hidden>PILIH</option>
  <option value="admin">ADMIN</option>
  <option value="pusat">PUSAT</option>
  <option value="region">REGION</option>
	<option value="sr">SR</option>
	<option value="user">USER</option>';
}

function cboTahunPajak(){
  $tahun_sekarang = date('Y');

  for ($i = $tahun_sekarang - 5; $i <= $tahun_sekarang; $i++) {
    echo '<option value="' . $i . '">' . $i . '</option>';
  }
}


function rupiah($angka){
    $hasil_rupiah = "Rp " . number_format($angka, 2, ',', '.');
    echo $hasil_rupiah;
}

function number_indo($angka){
    if($angka!=''){
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        echo "";
    }
}

function number_indo_value($angka){
    if(is_numeric($angka)){
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        echo $angka;
    }
}

function number_indo_persen_kosong($angka){
    if($angka!=''){
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah%</span>";
    }else{
        echo "";
    }
}

function number_minus($angka){
    if($angka!=''){
        $color = intval($angka) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$angka</span>";
    }else{
        echo "";
    }
}

function number_kosong($angka){
    if ($angka <= 0) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function number_indo_white($angka){
    if($angka!=''){
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        echo "<span style='color:white'>$hasil_rupiah</span>";
    }else{
        echo "";
    }
}

function number_indo_white_persen($angka){
    if($angka!=''){
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        echo "<span style='color:white'>$hasil_rupiah%</span>";
    }else{
        echo "";
    }
}

function kosongToNol($angka){
    if(is_numeric($angka)){
        return $angka;
    }else{
        return 0;
    }
}

function number_indo_excel($angka){
    if ($angka == '') {
        return "";
    } else {
        return $hasil_rupiah = round($angka);
    }
}

function number_indo_excel_nominus($angka){
    if ($angka <= 1) {
        return "";
    } else {
        return $hasil_rupiah = round($angka);
    }
}

function number_indo_excel_thp($angka){
    if(is_numeric($angka)){
        return $hasil_rupiah = round($angka);
    } else {
        return $angka;
    }
}

function number_indo_excel_nol($angka){
    if ($angka == '') {
        return 0;
    } else {
        return $hasil_rupiah = round($angka);
    }
}

function number_indo_excel_rupiah($angka){
    if ($angka == '') {
        return "";
    } else {
        $angka = (float)$angka;
        return number_format($angka, 0, ',', '.');
    }
}

function number_indo_excel_koma($angka){
    if(is_numeric($angka)){
        return $hasil_rupiah = number_format($angka, 2, ',', '.');
    }else{
        return "";
    }
}

function number_indo_excel_koma1($angka){
    if (!is_numeric($angka)) {
        return "";
    } else {
        return $hasil_rupiah = number_format($angka, 1, ',', '.');
    }
}

function number_indo_excel_koma3($angka){
    if ($angka == '') {
        return "";
    } else {
        return $hasil_rupiah = round($angka,3);
    }
}

function number_indo_nol($angka){
    if($angka!=''){
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        echo 0;
    }
}

function number_indo_koma($angka){
    if(is_numeric($angka)){
    $hasil_rupiah = number_format($angka, 2, ',', '.');
    $color = $hasil_rupiah < 0 ? '#fe0000' : 'black';
    echo "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        echo '';
    }
    //echo $hasil_rupiah;
}

function number_indo_koma1($angka){
    if(is_numeric($angka)){
        $hasil_rupiah = number_format($angka, 1, ',', '.');
        $color = $hasil_rupiah < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
        //echo $hasil_rupiah;
    }else{
        return '';
    }
}

function number_indo_koma1_nol($angka){
    if(is_numeric($angka)){
        $hasil_rupiah = number_format($angka, 1, ',', '.');
        $color = $hasil_rupiah < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
        //echo $hasil_rupiah;
    }else{
        return 0;
    }
}

function number_indo_koma1_white($angka){
    if(is_numeric($angka)){
        $hasil_rupiah = number_format($angka, 1, ',', '.');
        $color = $hasil_rupiah < 0 ? '#fe0000' : 'white';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
        //echo $hasil_rupiah;
    }else{
        return '';
    }
}

function return_number_indo_koma1($angka){
    if(is_numeric($angka)){
        $hasil_rupiah = number_format($angka, 1, ',', '.');
        return $hasil_rupiah;
    }else{
        return '';
    }
}

function number_indo_koma3($angka){
    if(is_numeric($angka)){
        $hasil_rupiah = number_format($angka, 3, ',', '.');
        return $hasil_rupiah;
    }else{
        return '';
    }
}

function kodept($kodept){
    switch ($kodept) {
        case 'MPU':
            $kodept = 'PT MITRA PETERNAKAN UNGGAS';
            break;
        case 'MJR':
            $kodept = 'PT MURIA JAYA RAYA';
            break;
        case 'AIL':
            $kodept = 'PT ANEKA INTAN LESTARI';
            break;
        case 'SGA':
            $kodept = 'PT SAWUNG GEMA ABADI';
            break;
        case 'MMB':
            $kodept = 'PT MITRA MAHKOTA BUANA';
            break;
        case 'LAN':
            $kodept = 'PT LAWU ABADI NUSA';
            break;
        case 'BRU':
            $kodept = 'PT BAROKAH RESTU UTAMA';
            break;
        case 'MUM':
            $kodept = 'PT MITRA UNGGAS MAKMUR';
            break;
        case 'KSM':
            $kodept = 'PT KARYA SATWA MULIA';
            break;
        case 'MJL':
            $kodept = 'PT MUSTIKA JAYA LESTARI';
            break;
        case 'BTB':
            $kodept = 'PT BINTANG TERANG BERSINAR';
            break;
        case 'SAW':
            $kodept = 'PT SLAMET AGUNG WIJAYA';
            break;
        case 'GPS':
            $kodept = 'PT GILAR PERWIRA SATRIA';
            break;
        case 'KLB':
            $kodept = 'PT KEDU LINTAS BERBINTANG';
            break;
        case 'LSW':
            $kodept = 'PT LAJU SATWA WISESA';
            break;
        default:
            echo "PT tidak terdaftar";
    }
    return $kodept;
}

function kodearca($kodeunit){
    switch ($kodeunit) {
        case 'A1-PT MITRA UNGGAS MAKMUR':
            $unit = 'SMN';
            break;
        case 'A3-PT MITRA UNGGAS MAKMUR':
            $unit = 'GKD';
            break;
        case 'A4-PT MITRA UNGGAS MAKMUR':
            $unit = 'KLP';
            break;
        case 'A5-PT MITRA UNGGAS MAKMUR':
            $unit = 'BTL';
            break;
        case 'A6-PT MITRA UNGGAS MAKMUR':
            $unit = 'KTA';
            break;

        case 'B1-PT SAWUNG GEMA ABADI':
            $unit = 'SLG';
            break;
        case 'B3-PT SAWUNG GEMA ABADI':
            $unit = 'BYL';
            break;
        case 'B4-PT SAWUNG GEMA ABADI':
            $unit = 'KRD';
            break;
        case 'B6-PT SAWUNG GEMA ABADI':
            $unit = 'BWN';
            break;
        case 'B7-PT SAWUNG GEMA ABADI':
            $unit = 'GRB';
            break;

        case 'C1-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'CRB';
            break;
        case 'C3-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'IDM';
            break;
        case 'C4-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'PTR';
            break;
        case 'C5-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'LSR';
            break;
        case 'C6-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'PDG';
            break;
        case 'C7-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'PPT';
            break;
        case 'C8-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'CIA';
            break;
        case 'C9-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'BNJ';
            break;
        case 'C10-PT MITRA PETERNAKAN UNGGAS':
            $unit = 'BGR';
            break;

        case 'D1-PT MITRA MAHKOTA BUANA':
            $unit = 'SMG';
            break;
        case 'D2-PT MITRA MAHKOTA BUANA':
            $unit = 'UNG';
            break;
        case 'D3-PT MITRA MAHKOTA BUANA':
            $unit = 'DMK';
            break;
        case 'D5-PT MITRA MAHKOTA BUANA':
            $unit = 'GDO';
            break;
        case 'D6-PT MITRA MAHKOTA BUANA':
            $unit = 'BJA';
            break;
        case 'D7-PT MITRA MAHKOTA BUANA':
            $unit = 'KLA';
            break;
        case 'D8-PT MITRA MAHKOTA BUANA':
            $unit = 'BDL';
            break;
        case 'D9-PT MITRA MAHKOTA BUANA':
            $unit = 'BSW';
            break;

        case 'E1-PT ANEKA INTAN LESTARI':
            $unit = 'PKL';
            break;
        case 'E2-PT ANEKA INTAN LESTARI':
            $unit = 'PML';
            break;
        case 'E3-PT ANEKA INTAN LESTARI':
            $unit = 'BTG';
            break;
        case 'E5-PT ANEKA INTAN LESTARI':
            $unit = 'KJN';
            break;

        case 'F1-PT MURIA JAYA RAYA':
            $unit = 'KDS';
            break;
        case 'F2-PT MURIA JAYA RAYA':
            $unit = 'PTI';
            break;
        case 'F3-PT MURIA JAYA RAYA':
            $unit = 'JPR';
            break;
        case 'F4-PT MURIA JAYA RAYA':
            $unit = 'PWD';
            break;
        case 'F5-PT MURIA JAYA RAYA':
            $unit = 'BLR';
            break;
        case 'F6-PT MURIA JAYA RAYA':
            $unit = 'RBG';
            break;
        case 'F7-PT MURIA JAYA RAYA':
            $unit = 'BJO';
            break;
        case 'F8-PT MURIA JAYA RAYA':
            $unit = 'TBN';
            break;
        case 'F9-PT MURIA JAYA RAYA':
            $unit = 'GSK';
            break;

        case 'G1-PT LAWU ABADI NUSA':
            $unit = 'SKH';
            break;
        case 'G2-PT LAWU ABADI NUSA':
            $unit = 'WNG';
            break;
        case 'G3-PT LAWU ABADI NUSA':
            $unit = 'SGN';
            break;
        case 'G4-PT LAWU ABADI NUSA':
            $unit = 'KLT';
            break;
        case 'G5-PT LAWU ABADI NUSA':
            $unit = 'MTG';
            break;
        case 'G6-PT LAWU ABADI NUSA':
            $unit = 'GML';
            break;
        case 'G7-PT LAWU ABADI NUSA':
            $unit = 'KRA';
            break;
        case 'G8-PT LAWU ABADI NUSA':
            $unit = 'JMR';
            break;
        case 'G9-PT LAWU ABADI NUSA':
            $unit = 'MYR';
            break;

        case 'H1-PT BAROKAH RESTU UTAMA':
            $unit = 'BDG';
            break;
        case 'H2-PT BAROKAH RESTU UTAMA':
            $unit = 'SBG';
            break;
        case 'H3-PT BAROKAH RESTU UTAMA':
            $unit = 'CJR';
            break;
        case 'H5-PT BAROKAH RESTU UTAMA':
            $unit = 'MJK';
            break;
        case 'H6-PT BAROKAH RESTU UTAMA':
            $unit = 'SMD';
            break;
        case 'H7-PT BAROKAH RESTU UTAMA':
            $unit = 'TRG';
            break;

        case 'I1-PT KARYA SATWA MULIA':
            $unit = 'MDN';
            break;
        case 'I2-PT KARYA SATWA MULIA':
            $unit = 'PNG';
            break;
        case 'I3-PT KARYA SATWA MULIA':
            $unit = 'MGT';
            break;
        case 'I4-PT KARYA SATWA MULIA':
            $unit = 'NGW';
            break;

        case 'J1-PT KEDU LINTAS BERBINTANG':
            $unit = 'TMG';
            break;
        case 'J2-PT KEDU LINTAS BERBINTANG':
            $unit = 'WNB';
            break;
        case 'J3-PT KEDU LINTAS BERBINTANG':
            $unit = 'MGL';
            break;
        case 'J4-PT KEDU LINTAS BERBINTANG':
            $unit = 'KBM';
            break;

        case 'L1-PT BINTANG TERANG BERSINAR':
            $unit = 'BRB';
            break;
        case 'L2-PT BINTANG TERANG BERSINAR':
            $unit = 'TGL';
            break;
        case 'L3-PT BINTANG TERANG BERSINAR':
            $unit = 'BMA';
            break;
        case 'L4-PT BINTANG TERANG BERSINAR':
            $unit = 'BKA';
            break;

        case 'M1-PT GILAR PERWIRA SATRIA':
            $unit = 'PBG';
            break;
        case 'M2-PT GILAR PERWIRA SATRIA':
            $unit = 'PWT';
            break;
        case 'M3-PT GILAR PERWIRA SATRIA':
            $unit = 'BJN';
            break;
        case 'M4-PT GILAR PERWIRA SATRIA':
            $unit = 'CLP';
            break;

        case 'N1-PT LAJU SATWA WISESA':
            $unit = 'KDR';
            break;
        case 'N2-PT LAJU SATWA WISESA':
            $unit = 'JBG';
            break;

        default:
            echo "Unit tidak terdaftar";
    }
    return $unit;
}

function longMonth($bulan){
    switch ($bulan) {
        case 1:
            $bulan = 'JANUARI';
            break;
        case 2:
            $bulan = 'FEBRUARI';
            break;
        case 3:
            $bulan = 'MARET';
            break;
        case 4:
            $bulan = 'APRIL';
            break;
        case 5:
            $bulan = 'MEI';
            break;
        case 6:
            $bulan = 'JUNI';
            break;
        case 7:
            $bulan = 'JULI';
            break;
        case 8:
            $bulan = 'AGUSTUS';
            break;
        case 9:
            $bulan = 'SEPTEMBER';
            break;
        case 10:
            $bulan = 'OKTOBER';
            break;
        case 11:
            $bulan = 'NOVEMBER';
            break;
        case 12:
            $bulan = 'DESEMBER';
            break;
        default:
            $bulan = $bulan;
    }
    return $bulan;
}

function shortMonth($bulan){
    switch ($bulan) {
        case 1:
            $bulan = 'Jan';
            break;
        case 2:
            $bulan = 'Feb';
            break;
        case 3:
            $bulan = 'Mar';
            break;
        case 4:
            $bulan = 'Apr';
            break;
        case 5:
            $bulan = 'Mei';
            break;
        case 6:
            $bulan = 'Jun';
            break;
        case 7:
            $bulan = 'Jul';
            break;
        case 8:
            $bulan = 'Agt';
            break;
        case 9:
            $bulan = 'Sep';
            break;
        case 10:
            $bulan = 'Okt';
            break;
        case 11:
            $bulan = 'Nov';
            break;
        case 12:
            $bulan = 'Des';
            break;
        default:
            $bulan = '';
    }
    return $bulan;
}

function numberMonth($bulan){
    switch ($bulan) {
        case 1:
            $bulan = '01';
            break;
        case 2:
            $bulan = '02';
            break;
        case 3:
            $bulan = '03';
            break;
        case 4:
            $bulan = '04';
            break;
        case 5:
            $bulan = '05';
            break;
        case 6:
            $bulan = '06';
            break;
        case 7:
            $bulan = '07';
            break;
        case 8:
            $bulan = '08';
            break;
        case 9:
            $bulan = '09';
            break;
        case 10:
            $bulan = '10';
            break;
        case 11:
            $bulan = '11';
            break;
        case 12:
            $bulan = '12';
            break;
        default:
            $bulan = '';
    }
    return $bulan;
}

function arrAp(){
    return $ap = array("AIL", "MJR", "BRU", "BTB", "GPS", "KLB", "KSM", "LAN", "LSW", "MMB", "MPU", "MUM", "SGA");
}

function hrAp($ap){
    return substr($ap,3,3);
}

function ap($unit){
    return [
        'BTG' => 'AIL', 'KJN' => 'AIL', 'PML' => 'AIL', 'PKL' => 'AIL', 'HO AIL' => 'AIL', 'RDK' => 'AIL',
        'SMD' => 'BRU', 'SBG' => 'BRU', 'MJK' => 'BRU', 'CJR' => 'BRU', 'BDG' => 'BRU', 'HO BRU' => 'BRU', 'KRW' => 'BRU', 'TRG' => 'BRU',
        'BMA' => 'BTB', 'BRB' => 'BTB', 'TGL' => 'BTB', 'BKA' => 'BTB', 'HO BTB' => 'BTB', 'WON' => 'BTB',
        'PBG' => 'GPS', 'BJN' => 'GPS', 'PWT' => 'GPS', 'CLP' => 'GPS', 'HO GPS' => 'GPS',
        'KBM' => 'KLB', 'MGL' => 'KLB', 'TMG' => 'KLB', 'WNB' => 'KLB', 'HO KLB' => 'KLB',
        'MGT' => 'KSM', 'MDN' => 'KSM', 'PNG' => 'KSM', 'NGW' => 'KSM', 'HO KSM' => 'KSM', 'NJK' => 'KSM',
        'GML' => 'LAN', 'SKH' => 'LAN', 'MTG' => 'LAN', 'KLT' => 'LAN', 'SGN' => 'LAN', 'WNG' => 'LAN', 'KRA' => 'LAN', 'HO LAN' => 'LAN', 'JMR' => 'LAN', 'MYR' => 'LAN',
        'KDR' => 'LSW', 'HO LSW' => 'LSW', 'JBG' => 'LSW',
        'BLR' => 'MJR', 'RBG' => 'MJR', 'BJO' => 'MJR', 'PTI' => 'MJR', 'KDS' => 'MJR', 'JPR' => 'MJR', 'PWD' => 'MJR', 'HO MJR' => 'MJR', 'GSK' => 'MJR', 'TBN' => 'MJR',
        'SMG' => 'MMB', 'BJA' => 'MMB', 'UNG' => 'MMB', 'GDO' => 'MMB', 'DMK' => 'MMB', 'KLA' => 'MMB', 'HO MMB' => 'MMB', 'BDL' => 'MMB', 'BSW' => 'MMB',
        'LSR' => 'MPU', 'PTR' => 'MPU', 'IDM' => 'MPU', 'CIA' => 'MPU', 'PPT' => 'MPU', 'CRB' => 'MPU', 'PDG' => 'MPU', 'HO MPU' => 'MPU', 'KNG' => 'MPU', 'BNJ' => 'MPU', 'BGR' => 'MPU',
        'BTL' => 'MUM', 'KLP' => 'MUM', 'GKD' => 'MUM', 'SMN' => 'MUM', 'KTA' => 'MUM', 'HO MUM' => 'MUM',
        'BWN' => 'SGA', 'SLG' => 'SGA', 'GRB' => 'SGA', 'KRD' => 'SGA', 'BYL' => 'SGA', 'HO SGA' => 'SGA', 'BSN' => 'SGA',
        'HO' => 'MJL',
    ][$unit] ?? null;
}

function reg2ap($reg){
    switch ($reg) {
        case 'BDG':
            $ap = 'BRU';
            break;
        case 'CRB':
            $ap = 'MPU';
            break;
        case 'KDR':
            $ap = 'LSW';
            break;
        case 'KDS':
            $ap = 'MJR';
            break;
        case 'MDN':
            $ap = 'KSM';
            break;
        case 'PBG':
            $ap = 'GPS';
            break;
        case 'PKL':
            $ap = 'AIL';
            break;
        case 'SLG':
            $ap = 'SGA';
            break;
        case 'SLO':
            $ap = 'LAN';
            break;
        case 'SMG':
            $ap = 'MMB';
            break;
        case 'TGL':
            $ap = 'BTB';
            break;
        case 'TMG':
            $ap = 'KLB';
            break;
        case 'YOG':
            $ap = 'MUM';
            break;
        default:
            echo "AP tidak terdaftar";
    }
    return $ap;
}

function cboBulan(){
    echo '<option value="" selected disabled>PILIH</option>
	<option value="1">JANUARI</option>
	<option value="2">FEBRUARI</option>
	<option value="3">MARET</option>
	<option value="4">APRIL</option>
	<option value="5">MEI</option>
	<option value="6">JUNI</option>
	<option value="7">JULI</option>
	<option value="8">AGUSTUS</option>
	<option value="9">SEPTEMBER</option>
	<option value="10">OKTOBER</option>
	<option value="11">NOVEMBER</option>
	<option value="12">DESEMBER</option>';
}

function cboTahun(){
  echo '<option value="" selected disabled>PILIH</option>
  <option value="2020">2020</option>
	<option value="2021">2021</option>
	<option value="2022">2022</option>
	<option value="2023">2023</option>
  <option value="2024">2024</option>';
}

function setHeader(){
    return $styleArrayHeader = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
            'rotation' => 90,
            'startColor' => [
                'argb' => 'FFA0A0A0',
            ],
            'endColor' => [
                'argb' => 'FFFFFFFF',
            ],
        ],
    ];
}

function setTittle(){
    return $styleArrayHeader = [
        'font' => [
            'bold' => true,
            'size' => 15,
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];
}

function setBody(){
    return $styleArrayBody = [
        'font' => [
            'bold' => false,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
}

function setBorderRight(){
    return $styleArrayBody = [
        'font' => [
            'bold' => false,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
        'borders' => [
            'right' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
            ],
        ],
    ];
}

function buatKode($unit, $nama){
    return str_replace(' ', '0', $unit . $nama);
}

function kodeUnit($kodearca){
    return substr($kodearca, 0, 2);
}

function tglIndo($tgl){

    $tanggal = substr($tgl, 8, 2);

    $nama_bulan = array("Januari", "Februari", "Maret", "April", "Mei",
        "Juni", "Juli", "Agustus", "September",
        "Oktober", "November", "Desember");

    $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
    $tahun = substr($tgl, 0, 4);

    return $tanggal . ' ' . $bulan . ' ' . $tahun;
}

function tglIndoAngka($tgl){
    $tanggal = substr($tgl, 8, 2);
    $nama_bulan = array("01", "02", "03", "04", "05",
        "06", "07", "08", "09",
        "10", "11", "12");
    $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
    $tahun = substr($tgl, 2, 2);
    return $tanggal . '-' . $bulan . '-' . $tahun;
}

function tglIndoAngkaBulan($tgl){
    $tanggal = substr($tgl, 8, 2);
    $nama_bulan = array("01", "02", "03", "04", "05",
        "06", "07", "08", "09",
        "10", "11", "12");
    $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
    $tahun = substr($tgl, 2, 2);
    return $tanggal . '-' . $bulan;
}

function blnTahun($tgl){

    $tanggal = substr($tgl, 8, 2);

    $nama_bulan = array("Januari", "Februari", "Maret", "April", "Mei",
        "Juni", "Juli", "Agustus", "September",
        "Oktober", "November", "Desember");

    $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
    $tahun = substr($tgl, 0, 4);

    return upper($bulan) . ' ' . $tahun;
}

function tglIndoShort($tgl){
    if($tgl == ''){
        return '';
    }else{
        $tanggal = substr($tgl, 8, 2);
        $nama_bulan = array("Jan", "Feb", "Mar", "Apr", "Mei",
            "Jun", "Jul", "Agu", "Sep",
            "Okt", "Nov", "Des");

        $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
        $tahun = substr($tgl, 0, 4);

        return $tanggal . ' ' . $bulan;
    }
}

function tglIndoShortThn($tgl){
    if(($tgl=='---') || ($tgl=='')){
        return $tgl;
    }
    $tanggal = substr($tgl, 8, 2);
    $nama_bulan = array("Jan", "Feb", "Mar", "Apr", "Mei",
        "Jun", "Jul", "Agu", "Sep",
        "Okt", "Nov", "Des");

    $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
    $tahun = substr($tgl, 0, 4);

    return $tanggal.'-'.$bulan.'-'.$tahun;
}

function tglIndoShortThnSpace($tgl){
    $tanggal = substr($tgl, 8, 2);
    $nama_bulan = array("Jan", "Feb", "Mar", "Apr", "Mei",
        "Jun", "Jul", "Agu", "Sep",
        "Okt", "Nov", "Des");

    $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
    $tahun = substr($tgl, 2, 2);

    return $tanggal.' '.$bulan.' '.$tahun;
}

function Upper($value){
    return strtoupper($value);
}

function number_indo_1000($angka){
    if(is_numeric($angka)){
        $hasil_rupiah = number_format($angka / 1000, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        return $angka;
    }

}

function number_indo_1000_nol($angka){
    $hasil_rupiah = number_format($angka / 1000, 0, ',', '.');
    return $hasil_rupiah;
}

function number_indo_1000_koma($angka){
    $hasil_rupiah = number_format($angka / 1000, 1, ',', '.');
    $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
    echo "<span style='color:$color'>$hasil_rupiah</span>";
}

function number_indo_1000_koma_blue($angka){
    if ((intval($angka) < 0) || ($angka == "")) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka / 1000, 1, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'blue';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }

}

function baru($status){
    if ($status != '') {
        echo "<span class='badge badge-danger'>NEW</span>";
    }
}

function sinkron($status,$ap){
    if ($status == NULL) {
        echo "<a href='/source-hpp/sinrkon/$ap'><span class='badge badge-danger'>GAGAL UPDATE</span></a>";
    }else{
        echo $status;
    }
}

function tglsql($tanggal) {
   $pisah   = explode('/',$tanggal);
   $larik   = array($pisah[2],$pisah[1],$pisah[0]);
   $satukan = implode('-',$larik);
   return $satukan;
}

function kodeuser($nama,$unit){
    $kodeuser = $unit.$nama;
    $kodeuser = str_replace(" ","0",$kodeuser);
    return $kodeuser;
}

function number_indo_1000_koma_kosong($angka){
    if ($angka <= 0) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka / 1000, 1, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function number_indo_1000_koma_kosong_excel($angka){
    if ($angka <= 0) {
        return "";
    } else {
        return $hasil_rupiah = number_format($angka / 1000, 1, ',', '.');
    }
}

function number_indo_1000_kosong($angka){
    if ($angka < 500) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka / 1000, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function number_indo_1000_kosong_excel($angka){
    if ($angka < 500) {
        return "";
    } else {
        return $hasil_rupiah = round($angka / 1000, 0);
    }
}

function number_indo_1000_kosong_excel_thp($angka){
    if(is_numeric($angka)){
        return $hasil_rupiah = round($angka / 1000, 0);
    } else {
        return "";
    }
}

function number_indo_1000_kosong_excel_thp_eva($angka){
    if(is_numeric($angka)){
        return $hasil_rupiah = round($angka/1000,0);
    } else {
        return $angka;
    }
}

function number_indo_persen($angka){
    if (is_numeric($angka)){
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        if ($hasil_rupiah <= 0) {
            return "";
        } else {
            $hasil_rupiah = $hasil_rupiah . "%";
            $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
            echo "<span style='color:$color'>$hasil_rupiah</span>";
        }
    }else{
        return "";
    }
}

function number_indo_persen_koma1($angka){
    $hasil_rupiah = number_format($angka, 1, ',', '.');
    if ($hasil_rupiah <= 0) {
        return "";
    } else {
        $hasil_rupiah = $hasil_rupiah . "%";
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function number_indo_persen_nol($angka){
    $hasil_rupiah = number_format($angka, 0, ',', '.');
    if ($hasil_rupiah <= 0) {
        return "0%";
    } else {
        $hasil_rupiah = $hasil_rupiah . "%";
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function number_indo_persen_minus($angka){
    $hasil_rupiah = number_format($angka, 0, ',', '.');
    $hasil_rupiah = $hasil_rupiah . "%";
    $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
    echo "<span style='color:$color'>$hasil_rupiah</span>";
}

function number_indo_persen_excel($angka){
    $hasil_rupiah = number_format($angka, 0, ',', '.');
    if ($hasil_rupiah <= 0) {
        return "";
    } else {
        return $hasil_rupiah = $hasil_rupiah . "%";
    }
}

function number_indo_persen_excel_koma($angka){
    $hasil_rupiah = number_format($angka, 1, ',', '.');
    if ($hasil_rupiah <= 0) {
        return "";
    } else {
        return $hasil_rupiah = $hasil_rupiah . "%";
    }
}

function number_indo_persen100_excel($angka){
    $hasil_rupiah = number_format($angka*100, 0, ',', '.');
    if ($hasil_rupiah <= 0) {
        return "";
    } else {
        return $hasil_rupiah = $hasil_rupiah . "%";
    }
}

function number_indo_persen_excel_nol($angka){
    $hasil_rupiah = number_format($angka, 0, ',', '.');
    if ($hasil_rupiah <= 0) {
        return "0%";
    } else {
        return $hasil_rupiah = $hasil_rupiah . "%";
    }
}

function number_indo_kosong($angka){
    if ($angka <= 0) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka, 1, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function dilihat_menu($angka){
    if ($angka <= 0) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka);
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color;font-weight: bold;font-size:16px'><img src='/assets/img/visible.png' style='opacity: 0.35; width: 21px; height: 21px; margin-bottom:2px'/></span> $hasil_rupiah ";
    }
}

function aksesQaMenu($akses){
	$jml=DB::table('menu_qa')
        ->select('id')
        ->where('akses','LIKE','%'.$akses.'%')
        ->count();
	return $jml;
}

function docCost($harga,$bw,$dpls){
    if (!empty($harga) && !empty($bw)) {
        $hasil = $harga/$bw/(100-$dpls)*100;
        return round($hasil,0);
    } else {
        return 0;
    }
}

function ip($dpls,$bw,$fcr,$umur){
    if (!empty($dpls) && !empty($bw)) {
        $hasil = ((((100-$dpls)*(100*$bw))/$fcr))/$umur;
        return round($hasil,0);
    } else {
        return 0;
    }
}

function avgMarginZona($zona,$bulan,$tahun){
    if($bulan!=0){
        $sql = DB::select("SELECT AVG(margin) AS margin FROM (SELECT
            (SUM(jualayamactual)/SUM(cokg)) AS hj,
            ROUND((SUM(jualayamactual)/SUM(cokg)) - ((SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg))) AS margin
            FROM table_rhpp WHERE area='$zona' AND MONTH(tgldocfinal)=$bulan AND YEAR(tgldocfinal)=$tahun
            GROUP BY unit)a");
    }else{
        $sql = DB::select("SELECT AVG(margin) AS margin FROM (SELECT
            (SUM(jualayamactual)/SUM(cokg)) AS hj,
            ROUND((SUM(jualayamactual)/SUM(cokg)) - ((SUM(valtotbeli)+SUM(nomrhpptotal)+SUM(hitunguangselisih))/SUM(cokg))) AS margin
            FROM table_rhpp WHERE area='$zona' AND YEAR(tgldocfinal)=$tahun
            GROUP BY unit)a");
    }

    foreach ($sql as $val) {
        $valmargin = $val->margin;
    }
    if (!empty($valmargin)) {
        return round($valmargin,0);
    } else {
        return 0;
    }
}

function diffMarginPersen($marginZona,$diffMargin){
     if (!empty($marginZona) && !empty($diffMargin)) {
        $hasil = (ABS($marginZona)+$diffMargin)/ABS($marginZona)*100;
     }else{
        $hasil =0;
     }
     return $hasil;
}

function colorPiutang($number){
    if($number >= 10){
        $number = number_format($number,1);
        echo "<span style='color:blue'>$number%</span>";
    }else{
        if($number <=0 ){
            return '';
        }else{
            return number_format($number,1).'%';
        }
    }
}

function colorPiutangGab($number){
    if($number >= 100){
        $number = number_format($number,0);
        echo "<span style='color:blue'>$number%</span>";
    }else{
        if($number <=0 ){
            return '';
        }else{
            return number_format($number,0).'%';
        }
    }
}

function aksesUser($user){
    switch ($user) {
        case 'SEMUA':
            $akses = 'STAFF MANAGEMENT INFORMATION SYSTEM,ADMINISTRATOR,DIREKTUR UTAMA,DIREKTUR PT,SUPERVISOR,STAFF QA,STAFF OPERASIONAL,STAFF DEVELOPMENT,STAFF HRD,KEPALA REGION,STAFF REGION,KEPALA UNIT,KEPALA PRODUKSI,TECHNICAL SUPPORT,ADMIN KEUANGAN,ADMIN LOGISTIK, ADMIN PT,BAGIAN UMUM,SALES,ADMIN UMUM';
            break;
        case 'DIREKTUR':
            $akses = 'STAFF MANAGEMENT INFORMATION SYSTEM,ADMINISTRATOR,DIREKTUR UTAMA,DIREKTUR PT,SUPERVISOR,STAFF QA,STAFF OPERASIONAL,STAFF DEVELOPMENT,STAFF HRD';
            break;
       case 'REGION':
            $akses = 'STAFF MANAGEMENT INFORMATION SYSTEM,ADMINISTRATOR,DIREKTUR UTAMA,DIREKTUR PT,SUPERVISOR,STAFF QA,STAFF OPERASIONAL,STAFF DEVELOPMENT,STAFF HRD,KEPALA REGION,STAFF REGION';
            break;
        case 'KANIT':
            $akses = 'STAFF MANAGEMENT INFORMATION SYSTEM,ADMINISTRATOR,DIREKTUR UTAMA,DIREKTUR PT,SUPERVISOR,STAFF QA,STAFF OPERASIONAL,STAFF DEVELOPMENT,STAFF HRD,KEPALA REGION,STAFF REGION,KEPALA UNIT';
            break;
        case 'KAPROD':
            $akses = 'STAFF MANAGEMENT INFORMATION SYSTEM,ADMINISTRATOR,DIREKTUR UTAMA,DIREKTUR PT,SUPERVISOR,STAFF QA,STAFF OPERASIONAL,STAFF DEVELOPMENT,STAFF HRD,KEPALA REGION,STAFF REGION,KEPALA UNIT,KEPALA PRODUKSI,ADMIN UMUM';
            break;
        default:
            echo "Unit tidak terdaftar";
    }
    return $akses;
}

function createGrdTs($tanggal = null, $nama = null, $unit = null, $ap = null,
                    $zona = null, $masakerja = null, $start = null, $end = null,
                    $rugiprd_ek = null, $rugiprd_skor = null, $frqrugi_flok = null, $frqrugi_rugi = null,
                    $frqrugi_persen = null, $frqrugi_skor = null, $margin_kg = null,
                    $margin_zona=null, $margin_persen = null, $margin_skor = null, $kapasitas_ek = null,
                    $kapasitas_bln = null, $kapasitas_avg = null, $kapasitas_skor = null, $skor = null,
                    $grade = null){
    DB::table('grade_ts')->insert([
        'tanggal' => $tanggal,
        'nama' => $nama,
        'unit' => $unit,
        'ap' => $ap,
        'zona' => $zona,
        'masakerja' => $masakerja,
        'start' => $start,
        'end' => $end,
        'rugiprd_ek' => $rugiprd_ek,
        'rugiprd_skor' => $rugiprd_skor,
        'frqrugi_flok' => $frqrugi_flok,
        'frqrugi_rugi' => $frqrugi_rugi,
        'frqrugi_persen' => $frqrugi_persen,
        'frqrugi_skor' => $frqrugi_skor,
        'margin_kg' => $margin_kg,
        'margin_zona' => $margin_zona,
        'margin_persen' => $margin_persen,
        'margin_skor' => $margin_skor,
        'kapasitas_ek' => $kapasitas_ek,
        'kapasitas_bln' => $kapasitas_bln,
        'kapasitas_avg' => $kapasitas_avg,
        'kapasitas_skor' => $kapasitas_skor,
        'skor' => $skor,
        'grade' => $grade,
    ]);
}

function createGrdKp($tanggal = null, $nama = null, $unit = null, $ap = null,
                    $zona = null, $masakerja = null, $start = null, $end = null,
                    $rugiprd_ek = null, $rugiprd_skor = null, $frqrugi_flok = null, $frqrugi_rugi = null,
                    $frqrugi_persen = null, $frqrugi_skor = null, $margin_kg = null,
                    $margin_zona=null, $margin_persen = null, $margin_skor = null, $kapasitas_ek = null,
                    $kapasitas_bln = null, $kapasitas_avg = null, $kapasitas_skor = null, $skor = null,
                    $grade = null){
    DB::table('grade_kp')->insert([
        'tanggal' => $tanggal,
        'nama' => $nama,
        'unit' => $unit,
        'ap' => $ap,
        'zona' => $zona,
        'masakerja' => $masakerja,
        'start' => $start,
        'end' => $end,
        'rugiprd_ek' => $rugiprd_ek,
        'rugiprd_skor' => $rugiprd_skor,
        'frqrugi_flok' => $frqrugi_flok,
        'frqrugi_rugi' => $frqrugi_rugi,
        'frqrugi_persen' => $frqrugi_persen,
        'frqrugi_skor' => $frqrugi_skor,
        'margin_kg' => $margin_kg,
        'margin_zona' => $margin_zona,
        'margin_persen' => $margin_persen,
        'margin_skor' => $margin_skor,
        'kapasitas_ek' => $kapasitas_ek,
        'kapasitas_bln' => $kapasitas_bln,
        'kapasitas_avg' => $kapasitas_avg,
        'kapasitas_skor' => $kapasitas_skor,
        'skor' => $skor,
        'grade' => $grade,
    ]);
}

function endTgl($bulan, $tahun){
    switch ($bulan) {
        case 1:
            $bulan = '31';
            break;
        case 2:
            if($tahun%4==0){
                 $bulan = '29';
            }else{
                 $bulan = '28';
            }
            break;
        case 3:
            $bulan = '31';
            break;
        case 4:
            $bulan = '30';
            break;
        case 5:
            $bulan = '31';
            break;
        case 6:
            $bulan = '30';
            break;
        case 7:
            $bulan = '31';
            break;
        case 8:
            $bulan = '31';
            break;
        case 9:
            $bulan = '30';
            break;
        case 10:
            $bulan = '31';
            break;
        case 11:
            $bulan = '30';
            break;
        case 12:
            $bulan = '31';
            break;
        default:
            echo "";
    }
    return $bulan;
}

function tahunKabisat($tahun){
    if ($TAHUN%4==0){
        echo "$TAHUN TAHUN KABISAT";
    }else if($TAHUN%4!=0){
        echo "$TAHUN BUKAN TAHUN KABISAT";
    }
}

function nolToKosong($angka){
    if($angka!=0){
        return $angka;
    }else{
        return '';
    }
}

function insertStokAyam(){
    $tanggal = date ('Y-m-d');
    $hapusAp = DB::table('table_stok_harian')->where('tanggal', $tanggal)->delete();
    $hapusUnit = DB::table('table_stok_harian_unit')->where('tanggal', $tanggal)->delete();
    if(($hapusAp) && ($hapusUnit)){
        $sqlAp = DB::statement("INSERT INTO table_stok_harian(tanggal, ap, gabstok, gabhpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS)
                            SELECT CURDATE(), koderegion, gabStok, gabHpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS
                            FROM vstokharian");
        if($sqlAp){
            $sqlUnit = DB::statement("INSERT INTO table_stok_harian_unit(tanggal, unit, gabstok, gabhpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS)
                            SELECT CURDATE(), kodeunit, gabStok, gabHpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS
                            FROM vstokharian_unit");
        }
    }else{
        $sqlAp = DB::statement("INSERT INTO table_stok_harian(tanggal, ap, gabstok, gabhpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS)
                            SELECT CURDATE(), koderegion, gabStok, gabHpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS
                            FROM vstokharian");
        if($sqlAp){
            $sqlUnit = DB::statement("INSERT INTO table_stok_harian_unit(tanggal, unit, gabstok, gabhpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS)
                            SELECT CURDATE(), kodeunit, gabStok, gabHpp, stokAk, hppAk, stokAt, hppAt, stokAn, hppAn, stokAb, hppAb, stokAj, hppAj, stokAs, hppAS
                            FROM vstokharian_unit");
        }
    }
}

function insertStokAyamUnit(){
    $tanggal = date ('Y-m-d');
    $hapus = DB::table('table_stok_harian_unit')->where('tanggal', $tanggal)->delete();
    if($hapus){
        $sql = DB::select("INSERT INTO table_stok_harian_unit(tanggal, unit, gabstok, gabhpp, stok10, hpp10, stok15, hpp15, stok19, hpp19, stok23, hpp23, stok28, hpp28)
                            SELECT CURDATE(), kodeunit, gabStok, gabHpp, stok10, hpp10, stok15, hpp15, stok19, hpp19, stok23, hpp23, stok28, hpp28
                            FROM vstokharian_unit");
    }else{
        $sql = DB::select("INSERT INTO table_stok_harian_unit(tanggal, unit, gabstok, gabhpp, stok10, hpp10, stok15, hpp15, stok19, hpp19, stok23, hpp23, stok28, hpp28)
                            SELECT CURDATE(), kodeunit, gabStok, gabHpp, stok10, hpp10, stok15, hpp15, stok19, hpp19, stok23, hpp23, stok28, hpp28
                            FROM vstokharian_unit");
    }
}

function number_indo_feedcost_kosong($angka,$bulan){
    $bln=date('m');
    if (($angka <= 0) && ($bulan > $bln)) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function number_indo_feedcost_kosong1($angka,$bulan){
    $bln=date('m');
    if (($angka <= 0) && ($bulan > $bln)) {
        return "";
    } else {
        $hasil_rupiah = number_format($angka, 1, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function removeKeys( array $array ){
  $array = array_values( $array );
  foreach ( $array as &$value )
  {
    if ( is_array( $value ) )
    {
      $value = removeKeys( $value );
    }
  }
  return $array;
}

function object_to_array($data){
    if (is_array($data) || is_object($data))
    {
        $result = [];
        foreach ($data as $key => $value)
        {
            $result[$key] = (is_array($data) || is_object($data)) ? object_to_array($value) : $value;
        }
        return $result;
    }
    return $data;
}

function colorScaleTinggi($value, $min, $max){
    $value = (float)str_replace("%","",$value);
    $min = (float)str_replace("%","",$min);
    $max = (float)str_replace("%","",$max);
    if (is_numeric($value) && is_numeric($min) && is_numeric($max)) {
        $selisih = $max-$min;
        $satuan = $selisih/8;
        if ($value <= $min) {
            $poin = 0;
        } elseif ($value <= $min+($satuan*1)) {
            $poin = 0.1;
        } elseif ($value <= $min+($satuan*2)) {
            $poin = 0.2;
        } elseif ($value <= $min+($satuan*3)) {
            $poin = 0.3;
        } elseif ($value <= $min+($satuan*4)) {
            $poin = 0.4;
        } elseif ($value <= $min+($satuan*5)) {
            $poin = 0.5;
        } elseif ($value <= $min+($satuan*6)) {
            $poin = 0.6;
        } elseif ($value <= $min+($satuan*7)) {
            $poin = 0.7;
        } elseif ($value <= $min+($satuan*8)) {
            $poin = 0.8;
        } else{
            $poin = 0;
        }
        return $poin;
    }else{
        return 0;
    }
}

function colorScaleRendah($value, $min, $max){
    $value = (float)str_replace("%","",$value);
    if($value==''){
        return 0;
    }
    $min = (float)str_replace("%","",$min);
    $max = (float)str_replace("%","",$max);
    if (is_numeric($value) && is_numeric($min) && is_numeric($max)) {
        $selisih = $max-$min;
        $satuan = $selisih/8;
        if ($value <= $min) {
            $poin = 0.8;
        } elseif ($value <= $min+($satuan*1)) {
            $poin = 0.7;
        } elseif ($value <= $min+($satuan*2)) {
            $poin = 0.6;
        } elseif ($value <= $min+($satuan*3)) {
            $poin = 0.5;
        } elseif ($value <= $min+($satuan*4)) {
            $poin = 0.4;
        } elseif ($value <= $min+($satuan*5)) {
            $poin = 0.3;
        } elseif ($value <= $min+($satuan*6)) {
            $poin = 0.2;
        } elseif ($value <= $min+($satuan*7)) {
            $poin = 0.1;
        } elseif ($value <= $min+($satuan*8)) {
            $poin = 0;
        } else{
            $poin = 0;
        }
        return $poin;
    }else{
        return 0;
    }
}

function up100persen($angka){
    $cek = $angka;
    $angka = (float)str_replace("%","",$angka);
    $angka = number_format($angka, 0, ',', '.');
    if($angka > 100){
         echo "<span style='color:blue'><strong>$angka%</strong></span>";
    }else{
        if($cek!=''){
             echo $angka.'%';
        }else{
            echo '';
        }
    }
}

function up0persen($angka){
    $cek = $angka;
    $angka = (float)str_replace("%","",$angka);
    $angka = number_format($angka, 0, ',', '.');
    if($angka > 0){
         echo "<span style='color:blue'><strong>$angka%</strong></span>";
    }else{
        if($cek!=''){
             echo $angka.'%';
        }else{
            echo '';
        }
    }
}

function number_indo_nol_blue($angka){
    if(is_numeric($angka)){
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'blue';
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        echo 0;
    }
}

function pencapaian_ipunit($bw,$fcr,$dpls,$umur){
    if (is_numeric($dpls) && is_numeric($bw) && is_numeric($fcr) && is_numeric($umur)) {
        $hasil = ROUND((((100-$dpls)*(100*$bw)))/$fcr/$umur,0);
        return $hasil;
    }else{
        return 0;
    }
}

function EvaluasiIp($ytd,$target){
    if($ytd >= $target){
        return "MASUK";
    }else{
        return "BELUM";
    }
}

function NilaiIp($value){
    if (is_numeric($value)){
        if($value > 375){
            return "EXCELLENT";
        }elseif($value >= 350){
            return "BAIK";
        }elseif($value >= 325){
            return "SEDANG";
        }else{
            return "KURANG";
        }
    }else{
        return "";
    }
}

function EvaluasiIpColor($value){
    if($value != 'MASUK'){
        echo "<span style='color:#c9051b; font-weight: bold;'>BELUM</span>";
    }else{
        echo "<span style='color:#0238bf; font-weight: bold;'>MASUK</span>";
    }
}

function koma2titik($angka){
    $hasil=str_replace(",",".",$angka);
    return $hasil;
}

function titik2koma($angka){
    $hasil=str_replace(".",",",$angka);
    return $hasil;
}

function rem($angka){
    $hasil=str_replace(".","",$angka);
    $hasil=str_replace(",","",$hasil);
    return $hasil;
}

function toFloat($angka){
    $hasil=str_replace(".","",$angka);
    $hasil=str_replace(",",".",$hasil);
    return $hasil;
}

function DiffStd($bw,$fcr){
    $bw = round($bw,2);
    $sql = DB::select("SELECT fcr FROM table_std_pfmc WHERE CAST(bw AS DECIMAL(10,2))= CAST($bw AS DECIMAL(10,2))");
    foreach ($sql as $val) {
        $valFcr = $val->fcr;
    }
    if (!empty($valFcr)){
        return round(($fcr-$valFcr)*100,1);
    }else{
        return 0;
    }
}

function toJabatan($value){
    if($value=='ADMIN SALES'){
        return 'ADMIN KEUANGAN';
    }elseif($value=='ADMIN FINANCE'){
        return 'ADMIN KEUANGAN';
    }elseif($value=='ADMIN SALES PRODUKSI'){
        return 'ADMIN KEUANGAN';
    }elseif($value=='ADMIN SALES FINANCE'){
        return 'ADMIN KEUANGAN';
    }elseif($value=='TECHNICAL SUPPORT JABAR'){
        return 'TECHNICAL SUPPORT';
    }elseif($value=='TECHNICAL SUPPORT JATENG'){
        return 'TECHNICAL SUPPORT';
    }elseif($value=='TECHNICAL SUPPORT JATIM'){
        return 'TECHNICAL SUPPORT';
    }elseif($value=='SALES JABAR'){
        return 'SALES';
    }elseif($value=='SALES JATENG'){
        return 'SALES';
    }elseif($value=='SALES JATIM'){
        return 'SALES';
    }elseif($value=='SALES KALIMANTAN'){
        return 'SALES';
    }elseif($value=='SALES SUMATERA'){
        return 'SALES';
    }elseif($value=='KEPALA UNIT SENIOR'){
        return 'KEPALA UNIT';
    }elseif($value=='KEPALA PRODUKSI JABAR'){
        return 'KEPALA PRODUKSI';
    }elseif($value=='KEPALA PRODUKSI JATENG'){
        return 'KEPALA PRODUKSI';
    }elseif($value=='KEPALA PRODUKSI JATIM'){
        return 'KEPALA PRODUKSI';
    }else{
        return $value;
    }
}

function setId($id){
    return decrypt($id);
}

function getId($id){
    return encrypt($id);
}

function getTglRealPanen($ap){
    $ap = strtolower($ap);
    $sqlTgl = DB::select("SELECT MAX(tgl_do) AS tanggal FROM app_estmrg_panen_$ap WHERE ap = '$ap' AND nominal > 0");
    foreach ($sqlTgl AS $data){
        $tanggal = $data->tanggal;
    }
    return $tanggal;
}

function cariKey($id, $array, $field) {
    foreach ($array as $key => $val) {
        if ($val[$field] === $id) {
            return $key;
        }
    }
    return null;
 }

 function getKgPanen($unit, $bulan, $ap){
    $kg = 0;
    $ap = strtolower($ap);
    $unit = 'kg'.$unit;
    $sql = DB::select("SELECT SUM($unit) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    foreach ($sql AS $data){
        $kg = $data->kg;
    }
    return $kg;
 }

 function getBwPfmc($unit){
    $bw=0;
    $sql = DB::select("SELECT bw FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sql AS $data){
        $bw = $data->bw;
    }
    if($bw==''){
        $bw=0;
    }
    return $bw;
 }

 function getFcrPfmc($unit){
    $fcr = 0;
    $sql = DB::select("SELECT fcr FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sql AS $data){
        $fcr = $data->fcr;
    }
    return $fcr;
 }

 function getDplsPfmc($unit){
    $dpls=0;
    $sql = DB::select("SELECT dpls FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sql AS $data){
        $dpls = $data->dpls;
    }
    return $dpls;
 }

 function getPakanPfmc($unit){
    $pakan=0;
    $sql = DB::select("SELECT pakan FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sql AS $data){
        $pakan = $data->pakan;
    }
    return $pakan;
 }

 function getOvkPfmc($unit){
    $ovk=0;
    $sql = DB::select("SELECT ovk FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sql AS $data){
        $ovk = $data->ovk;
    }
    return $ovk;
 }

 function getRhppPfmc($unit){
    $rhpp=0;
    $sql = DB::select("SELECT rhpp FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sql AS $data){
        $rhpp = $data->rhpp;
    }
    return $rhpp;
 }

 function getDocPfmc($unit, $bulan){
    $sql = DB::select("SELECT SUM(beli_frc)/SUM(ekor_cin) AS doc FROM app_estmrg_vsetcin WHERE unit='$unit' AND MONTH(tglpanen)=$bulan");
    foreach ($sql AS $data){
        $doc = $data->doc;
    }
    return $doc;
 }

 function getHpp($unit, $bulan, $segment){
    $sqlDoc = DB::select("SELECT SUM(beli_frc)/SUM(ekor_cin) AS doc FROM app_estmrg_vsetcin WHERE unit='$unit' AND MONTH(tglpanen)=$bulan");
    foreach ($sqlDoc AS $data){
        $doc = $data->doc;
    }

    $sqlOvk = DB::select("SELECT ovk FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sqlOvk AS $data){
        $ovk = $data->ovk;
    }

    $sqlRhpp = DB::select("SELECT rhpp FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sqlRhpp AS $data){
        $rhpp = $data->rhpp;
    }

    $sqlBw = DB::select("SELECT bw FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sqlBw AS $data){
        $bw = $data->bw;
    }

    $sqlDpls = DB::select("SELECT dpls FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sqlDpls AS $data){
        $dpls = $data->dpls;
    }

    $sqlFcr = DB::select("SELECT fcr FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sqlFcr AS $data){
        $fcr = $data->fcr;
    }

    $sqlPakan = DB::select("SELECT pakan FROM app_estmrg_vpfmc WHERE unit='$unit'");
    foreach ($sqlPakan AS $data){
        $pakan = $data->pakan;
    }

    if($segment=='BB'){
        $hasil = ($doc == 0 ? 0 : (($doc+$ovk)/$bw/(100/100-($dpls*1/100)))+(($fcr*$pakan)));
    }else{
        $hasil = ($doc == 0 ? 0 : (($doc+$ovk+$rhpp)/$bw/(100/100-($dpls*1/100)))+(($fcr*$pakan)));
    }
    return $hasil;
 }

 function getHargaLb($unit, $bulan, $ap){
    $ap = strtolower($ap);
    $harga=0;
    $kg = 'kg'.$unit;
    $nom = 'nom'.$unit;
    $hrg = 'hrg'.$unit;
    $sql = DB::select("SELECT tglawal,
                        ROUND(($nom/$kg)*1000,0) AS harga
                        FROM(
                            SELECT MONTH(b.tglawal) AS tglawal,
                            SUM(a.$kg) AS $kg, SUM($nom) AS $nom
                            FROM app_estmrg_temp_adj_bantu_$ap a
                        INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c WHERE tglawal=$bulan");
    foreach ($sql AS $data){
        $harga = $data->harga;
    }
    return $harga;
 }

 function getKgPanenAp($bulan, $ap){
    $ap = strtolower($ap);
    $kg=0;
    $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
    $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);
    $jml = count($arrUnit);
    if($jml==9){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);
        $u7 =  strtolower($arrUnit[7]);
        $u8 =  strtolower($arrUnit[8]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0)+IFNULL(SUM(kg$u2),0)+IFNULL(SUM(kg$u3),0)+IFNULL(SUM(kg$u4),0)+IFNULL(SUM(kg$u5),0)+IFNULL(SUM(kg$u6),0)+IFNULL(SUM(kg$u7),0)+IFNULL(SUM(kg$u8),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==8){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);
        $u7 =  strtolower($arrUnit[7]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0)+IFNULL(SUM(kg$u2),0)+IFNULL(SUM(kg$u3),0)+IFNULL(SUM(kg$u4),0)+IFNULL(SUM(kg$u5),0)+IFNULL(SUM(kg$u6),0)+IFNULL(SUM(kg$u7),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==7){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0)+IFNULL(SUM(kg$u2),0)+IFNULL(SUM(kg$u3),0)+IFNULL(SUM(kg$u4),0)+IFNULL(SUM(kg$u5),0)+IFNULL(SUM(kg$u6),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==6){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0)+IFNULL(SUM(kg$u2),0)+IFNULL(SUM(kg$u3),0)+IFNULL(SUM(kg$u4),0)+IFNULL(SUM(kg$u5),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==5){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0)+IFNULL(SUM(kg$u2),0)+IFNULL(SUM(kg$u3),0)+IFNULL(SUM(kg$u4),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==4){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0)+IFNULL(SUM(kg$u2),0)+IFNULL(SUM(kg$u3),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==3){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0)+IFNULL(SUM(kg$u2),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==2){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0)+IFNULL(SUM(kg$u1),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }elseif($jml==1){
        $u0 =  strtolower($arrUnit[0]);

        $sql = DB::select("SELECT IFNULL(SUM(kg$u0),0) AS kg
            FROM app_estmrg_temp_adj_bantu_$ap a
            INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal WHERE MONTH(a.tanggal)='$bulan'");
    }
    foreach ($sql AS $data){
        $kg = $data->kg;
    }
    return $kg;
 }

 function getHppAp($doc,$ovk,$rhpp,$bw,$dpls,$fcr,$pakan,$segment){
    if($segment=='BB'){
        $hasil = (($doc == 0) || ($ovk == 0) ? 0 : (($doc+$ovk)/$bw/(100/100-($dpls*1/100)))+(($fcr*$pakan)));
    }else{
        $hasil = (($doc == 0) || ($ovk == 0) ? 0 : (($doc+$ovk+$rhpp)/$bw/(100/100-($dpls*1/100)))+(($fcr*$pakan)));
    }
    return $hasil;
 }

 function getDocAp($bulan, $ap){
    $sql = DB::select("SELECT SUM(beli_frc)/SUM(ekor_cin) AS doc FROM app_estmrg_vsetcin WHERE ap='$ap' AND MONTH(tglpanen)=$bulan");
    foreach ($sql AS $data){
        $doc = $data->doc;
    }
    return $doc;
 }

 function getHargaLbAp($bulan, $ap){
    $ap = strtolower($ap);
    $sqlUnit = DB::select("SELECT kodeunit FROM units WHERE region='$ap' ORDER BY kodeunit ASC");
    $arrUnit = array_map(function ($object) { return $object->kodeunit; }, $sqlUnit);
    $jml = count($arrUnit);
    if($jml==9){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);
        $u7 =  strtolower($arrUnit[7]);
        $u8 =  strtolower($arrUnit[8]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0)+IFNULL(nom$u2, 0)+IFNULL(nom$u3, 0)+IFNULL(nom$u4, 0)+IFNULL(nom$u5, 0)+IFNULL(nom$u6, 0)+IFNULL(nom$u7, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0)+IFNULL(kg$u2, 0)+IFNULL(kg$u3, 0)+IFNULL(kg$u4, 0)+IFNULL(kg$u5, 0)+IFNULL(kg$u6, 0)+IFNULL(kg$u7, 0))*1000),0) AS hrgap
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
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==8){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);
        $u7 =  strtolower($arrUnit[7]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0)+IFNULL(nom$u2, 0)+IFNULL(nom$u3, 0)+IFNULL(nom$u4, 0)+IFNULL(nom$u5, 0)+IFNULL(nom$u6, 0)+IFNULL(nom$u7, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0)+IFNULL(kg$u2, 0)+IFNULL(kg$u3, 0)+IFNULL(kg$u4, 0)+IFNULL(kg$u5, 0)+IFNULL(kg$u6, 0)+IFNULL(kg$u7, 0))*1000),0) AS hrgap
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
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==7){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);
        $u6 =  strtolower($arrUnit[6]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0)+IFNULL(nom$u2, 0)+IFNULL(nom$u3, 0)+IFNULL(nom$u4, 0)+IFNULL(nom$u5, 0)+IFNULL(nom$u6, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0)+IFNULL(kg$u2, 0)+IFNULL(kg$u3, 0)+IFNULL(kg$u4, 0)+IFNULL(kg$u5, 0)+IFNULL(kg$u6, 0))*1000),0) AS hrgap
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
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==6){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);
        $u5 =  strtolower($arrUnit[5]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0)+IFNULL(nom$u2, 0)+IFNULL(nom$u3, 0)+IFNULL(nom$u4, 0)+IFNULL(nom$u5, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0)+IFNULL(kg$u2, 0)+IFNULL(kg$u3, 0)+IFNULL(kg$u4, 0)+IFNULL(kg$u5, 0))*1000),0) AS hrgap
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
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==5){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);
        $u4 =  strtolower($arrUnit[4]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0)+IFNULL(nom$u2, 0)+IFNULL(nom$u3, 0)+IFNULL(nom$u4, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0)+IFNULL(kg$u2, 0)+IFNULL(kg$u3, 0)+IFNULL(kg$u4, 0))*1000),0) AS hrgap
                        FROM(
                                SELECT MONTH(b.tglawal) AS tglawal,
                                                SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                                SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                                SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                                SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3,
                                                SUM(a.kg$u4) AS kg$u4, SUM(nom$u4) AS nom$u4
                                FROM app_estmrg_temp_adj_bantu_$ap a
                                INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==4){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);
        $u3 =  strtolower($arrUnit[3]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0)+IFNULL(nom$u2, 0)+IFNULL(nom$u3, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0)+IFNULL(kg$u2, 0)+IFNULL(kg$u3, 0))*1000),0) AS hrgap
                        FROM(
                                SELECT MONTH(b.tglawal) AS tglawal,
                                                SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                                SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                                SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2,
                                                SUM(a.kg$u3) AS kg$u3, SUM(nom$u3) AS nom$u3
                                FROM app_estmrg_temp_adj_bantu_$ap a
                                INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==3){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);
        $u2 =  strtolower($arrUnit[2]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0)+IFNULL(nom$u2, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0)+IFNULL(kg$u2, 0))*1000),0) AS hrgap
                        FROM(
                                SELECT MONTH(b.tglawal) AS tglawal,
                                                SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                                SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1,
                                                SUM(a.kg$u2) AS kg$u2, SUM(nom$u2) AS nom$u2
                                FROM app_estmrg_temp_adj_bantu_$ap a
                                INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==2){
        $u0 =  strtolower($arrUnit[0]);
        $u1 =  strtolower($arrUnit[1]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0)+IFNULL(nom$u1, 0))/(IFNULL(kg$u0, 0)+IFNULL(kg$u1, 0))*1000),0) AS hrgap
                        FROM(
                                SELECT MONTH(b.tglawal) AS tglawal,
                                                SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0,
                                                SUM(a.kg$u1) AS kg$u1, SUM(nom$u1) AS nom$u1
                                FROM app_estmrg_temp_adj_bantu_$ap a
                                INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c WHERE tglawal = '$bulan'");
    }elseif($jml==1){
        $u0 =  strtolower($arrUnit[0]);

        $sql = DB::select("SELECT tglawal,
                        ROUND(((IFNULL(nom$u0, 0))/(IFNULL(kg$u0, 0))*1000),0) AS hrgap
                        FROM(
                                SELECT MONTH(b.tglawal) AS tglawal,
                                                SUM(a.kg$u0) AS kg$u0, SUM(nom$u0) AS nom$u0
                                FROM app_estmrg_temp_adj_bantu_$ap a
                                INNER JOIN app_estmrg_master_date b ON b.tanggal=a.tanggal GROUP BY MONTH(a.tanggal)
                        )c WHERE tglawal = '$bulan'");
    }
    foreach ($sql AS $data){
        $hargalbap = $data->hrgap;
    }
    return $hargalbap;
 }

 function getEstMrgAp($estnom,$kgpanen){
    return $kgpanen==0 ? 0 : ($estnom*1000000)/($kgpanen*1000);
 }

 function kode2ap($reg){
    switch ($reg) {
        case 'WJTXYV50':
            $ap = 'MUM';
            break;
        case '169MZ4LN':
            $ap = 'MMB';
            break;
        case 'SL88BT50':
            $ap = 'MPU';
            break;
        case '8IGFORYD':
            $ap = 'SGA';
            break;
        case 'FO84F597':
            $ap = 'AIL';
            break;
        case 'UIMT7JMF':
            $ap = 'MJR';
            break;
        case 'T558LCKJ':
            $ap = 'LAN';
            break;
        case '6VAJX6F0':
            $ap = 'KSM';
            break;
        case '40KHFFVT':
            $ap = 'BRU';
            break;
        case 'CWLXPM2D':
            $ap = 'KLB';
            break;
        case 'BGYY0147':
            $ap = 'BTB';
            break;
        case 'E16RV5HE':
            $ap = 'SAW';
            break;
        case 'Q5E7UYZD':
            $ap = 'GPS';
            break;
        case 'KL46R32P':
            $ap = 'LSW';
            break;
        default:
            $ap = '';
    }
    return $ap;
}

function ap2kode($kode){
    switch ($kode) {
        case 'MUM':
            $ap = 'WJTXYV50';
            break;
        case 'MMB':
            $ap = '169MZ4LN';
            break;
        case 'MPU':
            $ap = 'SL88BT50';
            break;
        case 'SGA':
            $ap = '8IGFORYD';
            break;
        case 'AIL':
            $ap = 'FO84F597';
            break;
        case 'MJR':
            $ap = 'UIMT7JMF';
            break;
        case 'LAN':
            $ap = 'T558LCKJ';
            break;
        case 'KSM':
            $ap = '6VAJX6F0';
            break;
        case 'BRU':
            $ap = '40KHFFVT';
            break;
        case 'KLB':
            $ap = 'CWLXPM2D';
            break;
        case 'BTB':
            $ap = 'BGYY0147';
            break;
        case 'SAW':
            $ap = 'E16RV5HE';
            break;
        case 'GPS':
            $ap = 'Q5E7UYZD';
            break;
        case 'LSW':
            $ap = 'KL46R32P';
            break;
        default:
            $ap = '';
    }
    return $ap;
}

function cboAp(){
    $sql = DB::select("SELECT koderegion, namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT('PILIH'), '' AS semua FROM regions
                            ORDER BY namaregion ASC");
    foreach($sql as $data){
        echo "<option selected value='$data->koderegion'>$data->koderegion</option>";
    }
}

function cboVendorPakan(){
    $sql = DB::select("SELECT kode_pakan, kode_pakan AS nama_pakan FROM master_kode_vendor_pakan GROUP BY kode_pakan
                            UNION ALL
                            SELECT DISTINCT(''), 'PILIH' AS semua FROM master_kode_vendor_pakan
                            ORDER BY kode_pakan ASC");
    foreach($sql as $data){
        echo "<option selected value='$data->nama_pakan'>$data->nama_pakan</option>";
    }
}

function cboApSemua(){
    $sql = DB::select("SELECT koderegion, id, koderegion AS namaregion FROM regions
                            UNION ALL
                            SELECT DISTINCT(''), '0' AS id, 'SEMUA' AS semua FROM regions
                            ORDER BY id+1, namaregion ASC");
    foreach($sql as $data){
        echo "<option selected value='$data->koderegion'>$data->namaregion</option>";
    }
}

function cboUnit(){
    $sql = DB::select("SELECT kodeunit, id, kodeunit AS namaunit FROM units
                            UNION ALL
                            SELECT DISTINCT(''), '0' AS id, 'SEMUA' AS semua FROM units
                            ORDER BY id+1, namaunit ASC");
    foreach($sql as $data){
        echo "<option selected value='$data->kodeunit'>$data->namaunit</option>";
    }
}

function cboUnitPilih(){
    $sql = DB::select("SELECT kodeunit, id, kodeunit AS namaunit FROM units ORDER BY namaunit ASC");
    foreach($sql as $data){
        echo "<option selected value='$data->kodeunit'>$data->namaunit</option>";
    }
}

function number_indo_return($angka){
    if(is_numeric($angka)){
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        return "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        return "";
    }
}

function number_indo_return_koma1($angka){
    if(is_numeric($angka)){
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 1, ',', '.');
        $color = intval($hasil_rupiah) < 0 ? '#fe0000' : 'black';
        return "<span style='color:$color'>$hasil_rupiah</span>";
    }else{
        return "";
    }
}

function toKodeVendorDoc($vendor){
    $kode_vendor = '';
    $sql = DB::select("SELECT kode_vendor FROM master_kode_vendor_doc WHERE vendor_doc='$vendor'");
    foreach($sql as $data){
        $kode_vendor = $data->kode_vendor;
    }
    return $kode_vendor;
}

function toKodeBrokerDoc($vendor){
    $kode_broker = '';
    $sql = DB::select("SELECT kode_broker FROM master_kode_vendor_doc WHERE vendor_doc='$vendor'");
    foreach($sql as $data){
        $kode_broker = $data->kode_broker;
    }
    return $kode_broker;
}

function toKodeGradeDoc($vendor){
    $grade = '';
    $sql = DB::select("SELECT grade FROM master_kode_grade_doc WHERE kode_doc='$vendor'");
    foreach($sql as $data){
        $grade = $data->grade;
    }
    return $grade;
}

function toKodeVendorPakan($vendor){
    $kode_pakan = '';
    $sql = DB::select("SELECT kode_pakan FROM master_kode_vendor_pakan WHERE vendor_pakan='$vendor' LIMIT 1");
    foreach($sql as $data){
        $kode_pakan = $data->kode_pakan;
    }
    return $kode_pakan;
}

function toKodeGradePakan($vendor){
    $grade = '';
    $sql = DB::select("SELECT grade FROM master_kode_grade_pakan WHERE kode_pakan='$vendor'");
    foreach($sql as $data){
        $grade = $data->grade;
    }
    return $grade;
}

function getRows($table, $field, $param){
    $count = DB::table($table)->where($field, '=', $param)->count();
    return $count;
}

function getRowsIsNull($table, $field){
    $count = DB::table($table)->whereNull($field)->count();
    return $count;
}

function divOperation(){
    return array(
        '0001.MTK.0209',
        '0004.MTK.0209',
        '0008.MTK.0309',
        '0315.MTK.1213',
        '1287.MTK.0717',
        '0447.MTK.0515',
        '1551.MTK.0219',
        '1732.MTK.0520',
        '0538.MTK.0915',
        '1761.MTK.0121',
        '1872.MTK.0622',
        '1888.MTK.0722',
        '1908.MTK.0822',
        '1959.MTK.1122',
        '1962.MTK.1122',
        '0110.MTK.0412',
        '0065.MTK.1011',
    );
}

function sendWa($nowa, $pesan){
    $api_key = 'iID9iuieE4QKtpgXVaWHnWJHaDIFzL';
    $sender = '62882007021086';
    $url = 'https://wa.ptmustika.my.id/send-message';
    $param = array("api_key" => $api_key,
                    "sender" => $sender,
                    "number" => no_wa($nowa),
                    "message" => $pesan
                );

    $json = json_encode($param);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.strlen($json)
        )
    );

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    $result = curl_exec($ch);

    curl_close($ch);
}

function kirim_wa($nowa, $pesan){
    $api_key = 'KP6D5ya4QuqZYSL7MR1LD8ahvKfRoo';
    $sender = '62882007021086';
    $url = 'https://wa.ptmustika.my.id/send-message';
    $param = array("api_key" => $api_key,
                    "sender" => $sender,
                    "number" => no_wa($nowa),
                    "message" => $pesan
                );

    $json = json_encode($param);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.strlen($json)
        )
    );

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    $result = curl_exec($ch);

    curl_close($ch);
}

function no_wa($nohp){
    if(!preg_match("/[^+0-9]/",trim($nohp))){
        if(substr(trim($nohp), 0, 2)=="62"){
            $nohp    =trim($nohp);
        }
        else if(substr(trim($nohp), 0, 1)=="0"){
            $nohp    ="62".substr(trim($nohp), 1);
        }
    }
    return $nohp;
}

function wa_api($phone,$message){
        // Pastikan phone menggunakan kode negara atau
    // 62 di depannya untuk Indonesia atau
    // bisa menggunakan 0 jika nomor tujuan Indonesia
    $token = 'h8pi1avxiqUVjeQN52UiIGXlnbVKyfnGW9bOpKLTtCO9zpx6Rd';
    $url = 'http://nusagateway.com/api/send-message.php';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT,30);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array(
        'token'    => $token,
        'phone'     => $phone,
        'message'   => $message,
    ));
    $response = curl_exec($curl);
    curl_close($curl);
}

function getDayMonth($bulan, $tahun){
    if (is_numeric($bulan) && is_numeric($tahun)){
        $bulan_now = date('m');
        $tahun_now = date('Y');
        if($tahun==$tahun_now){
            if($bulan > $bulan_now){
                $jumHari = 0;
            }elseif($bulan == $bulan_now){
                $jumHari = date('d');
            }else{
                $jumHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
            }
        }else{
             $jumHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        }
    }else{
        $jumHari = 0;
    }
    return $jumHari;
}

function toAp($unit){
    $ap = '';
    $sql = DB::select("SELECT region FROM units WHERE kodeunit='$unit'");
    foreach($sql as $data){
        $ap = $data->region;
    }
    return $ap;
}

function insertPlasma(){
    $deleteData = DB::statement("DELETE FROM tb_plasma");
    if ($deleteData) {
        $insertData = DB::statement("INSERT INTO tb_plasma SELECT * FROM tb_plasma_temp");
        if ($insertData) {
            $clear = DB::statement("TRUNCATE TABLE tb_plasma_temp");
            if ($clear) {
                return 1;
            } else {
                return 'error clear temp';
            }
        } else {
            return 'error copy';
        }
    } else {
        return 'error delete data';
    }
}

function getUrlPlasma($ap){
    set_time_limit(0);
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
    $items = array();
    $insert_data = array();
    $count = 0;
    foreach ($arrayData['data'] as $data) {
        $values = array(
            'nama_plasma' => $data['nama_plasma'],
            'alamat_plasma' => $data['alamat_plasma'],
            'nama_flok' => $data['nama_flok'],
            'alamat_flok' => $data['alamat_flok'],
            'kota' => $data['kota'],
            'jumlah_populasi' => $data['jumlah_populasi'],
            'unit' => $data['unit'],
            'nama_unit' => $data['nama_unit'],
            'jenis_kandang' => $data['jenis_kandang'],
            'performa' => $data['performa'],
            'ts' => $data['ts'],
            'jaminan' => $data['jaminan'],
            'npwp' => $data['npwp'],
            'nama_npwp' => $data['nama_npwp'],
            'latitude_gps' => $data['latitude_gps'],
            'nomor_hp' => $data['nomor_hp'],
            'ap' => $ap
        );
        $insert_data[] = $values;
        $count = ++$count;
    }
    $insert_data = collect($insert_data);
    $chunks = $insert_data->chunk(500);
    foreach ($chunks as $chunk) {
        DB::table('tb_plasma_temp')->insert($chunk->toArray());
    }
    echo $count . ' record berhasil diupdate';
}

function gagalSinkron($status){
    $date_now = date('Y-m-d');
    if ($status != NULL) {
        $pieces = explode(" ", $status);
        if($pieces[0] < $date_now){
            echo "<span class='badge badge-warning'>".$pieces[0]."</br>".$pieces[1]."</span>";
        }else{
            echo $pieces[0]."</br>".$pieces[1];
        }
    } else {
        echo "<span class='badge badge-danger'>GAGAL</br>UPDATE</span>";
    }
}

function GuzzleAPI($method, $url){
    set_time_limit(0);
    $client = new GuzzleHttp\Client();
    $data = array();
    try {
        $response = $client->request($method, $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'x-api-key' => 'devmustikaapaccess',
            ]
        ]);
        $data['success'] = true;
        $data['response'] = json_decode($response->getBody()->getContents());
    } catch (GuzzleHttp\Exception\RequestException $e) {
        // Handle exception ketika request tidak berhasil
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $message = $response->getBody()->getContents();
        $data['success'] = false;
        $data['error'] = "Error $statusCode: $message";
    } catch (Exception $e) {
        // Handle exception ketika terjadi kesalahan lainnya
        $data['success'] = false;
        $data['error'] = "Error: " . $e->getMessage();
    }
    return $data;
}

function getInitials($string){
    $words = explode(' ', $string); // Memisahkan kata-kata dalam string menjadi array
    $initials = '';

    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1)); // Mengambil huruf pertama setiap kata dan mengubahnya menjadi huruf besar
    }

    return $initials;
}

function kodeUnitArca($kodeunit){
    $units = [
        // PT MUM
        'SMN' => 'A1',
        'GKD' => 'A3',
        'KLP' => 'A4',
        'BTL' => 'A5',
        'KTA' => 'A6',

        // PT SGA
        'SLG' => 'B1',
        'BYL' => 'B3',
        'KRD' => 'B4',
        'BWN' => 'B6',
        'GRB' => 'B7',
        'BSN' => 'B8',

        // PT MPU
        'CRB' => 'C1',
        'IDM' => 'C3',
        'PTR' => 'C4',
        'LSR' => 'C5',
        'PDG' => 'C6',
        'PPT' => 'C7',
        'TSM' => 'C8',
        'BNJ' => 'C9',
        'BGR' => 'C10',

        // PT MMB
        'SMG' => 'D1',
        'UNG' => 'D2',
        'DMK' => 'D3',
        'GDO' => 'D5',
        'BJA' => 'D6',
        'KLA' => 'D7',
        'BDL' => 'D8',
        'BSW' => 'D9',

        // PT AIL
        'PKL' => 'E1',
        'PML' => 'E2',
        'BTG' => 'E3',
        'KJN' => 'E5',

        // PT MJR
        'KDS' => 'F1',
        'PTI' => 'F2',
        'JPR' => 'F3',
        'PWD' => 'F4',
        'BLR' => 'F5',
        'RBG' => 'F6',
        'BJO' => 'F7',
        'TBN' => 'F8',
        'GSK' => 'F9',

        // PT LAN
        'SKH' => 'G1',
        'WNG' => 'G2',
        'SGN' => 'G3',
        'KLT' => 'G4',
        'MTG' => 'G5',
        'GML' => 'G6',
        'KRA' => 'G7',
        'JMR' => 'G8',
        'MYR' => 'G9',

        // PT BRU
        'BDG' => 'H1',
        'SBG' => 'H2',
        'CJR' => 'H3',
        'MJK' => 'H5',
        'SMD' => 'H6',
        'TRG' => 'H7',

        // PT KSM
        'MDN' => 'I1',
        'PNG' => 'I2',
        'MGT' => 'I3',
        'NGW' => 'I4',

        // PT KLB
        'TMG' => 'J1',
        'WNB' => 'J2',
        'MGL' => 'J3',
        'KBM' => 'J4',

        // PT BTB
        'BRB' => 'L1',
        'TGL' => 'L2',
        'BMA' => 'L3',

        // PT GPS
        'PBG' => 'M1',
        'PWT' => 'M2',
        'BJN' => 'M3',
        'CLP' => 'M4',

        // PT LSW
        'KDR' => 'N1',
        'JBG' => 'N2',
        'TLA' => 'N3',
    ];

    if (isset($units[$kodeunit])) {
        return $units[$kodeunit];
    } else {
        echo "Unit tidak terdaftar";
    }
}

function timeDiffIndo($value){
    setlocale(LC_TIME, 'id_ID');
    return Carbon\Carbon::parse($value)->locale('id')->diffForHumans();
}

function timeIndo($value){
    setlocale(LC_TIME, 'id_ID');
    \Carbon\Carbon::setLocale('id');
    return \Carbon\Carbon::parse($value)->translatedFormat('j F Y');
}

function menu_kunjungan($tabel, $id){
    DB::statement("UPDATE $tabel SET dilihat=dilihat+1 WHERE id=$id");
}

function parseKoma($input) {
    $output = str_replace(['[', ']', '"'], '', $input);
    $output = str_replace(',', ', ', $output);
    return $output;
}

function number_indo_absolute($angka){
    if ($angka == '' || $angka == 0) {
        echo 0;
    } else if ($angka !== '' || $angka !== 0) {
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, ',', '.');
        $color = $angka < 0 ? '#fe0000' : '#3c4b64';
        if ($angka < 0) {
            $hasil_rupiah = substr($hasil_rupiah, 1);
        }
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function getUrlPll($ap, $tglawal, $tglakhir, $kodeakun){
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
    $file = file_get_contents('https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/Ap_apis/buku_besar_acc/'.$ap.'?tanggal_awal='.$tglawal.'&tanggal_akhir='.$tglakhir.'&kode_akun='.$kodeakun, false, $context);
    $arrayData = json_decode($file, true);
    $count = 0;
    $insert_data = array();
    foreach ($arrayData['data']['result'] as $data) {
        $kode_akun = $data['kode_akun'];
        foreach ($data['detail'] as $d) {
            $tanggal_transaksi = $d['tanggal_transaksi'];
            $namacabang = $d['namacabang'];
            $deskripsi = $d['deskripsi'];
            $nojurnal = $d['nojurnal'];
            $tipe = $d['tipe'];
            $kredit_fungsional = $d['kredit_fungsional'];
            $kredit_sumber = $d['kredit_sumber'];
            $posisi = $d['posisi'];

            $values = array(
                'kodeakun' => $kode_akun,
                'tanggal' => $tanggal_transaksi,
                'unit' => $namacabang,
                'keterangan' => $deskripsi,
                'nomorjurnal' => $nojurnal,
                'tipe' =>  $tipe,
                'fungsionalkredit' =>  $kredit_fungsional,
                'sumberkredit' => $kredit_sumber,
                'posisi' => $posisi,
                'ap' => $ap === 'SAW' ? 'GPS' : $ap,
            );
            $insert_data[] = $values;
            $count = ++$count;
        }
    }
    $insert_data = collect($insert_data);
    $chunks = $insert_data->chunk(500);
    foreach ($chunks as $chunk) {
        DB::table('tbl_pll_temp')->insert($chunk->toArray());
    }
    echo $count . ' record berhasil diupdate';
}

function insertPll($tglawal, $tglakhir, $kodeakun){
    $kodeakunString = implode("', '", $kodeakun);
    $deleteData = DB::statement("DELETE FROM tbl_pll WHERE kodeakun IN ('$kodeakunString') AND tanggal BETWEEN '$tglawal' AND '$tglakhir'");
    if ($deleteData) {
        $insertData = DB::statement("INSERT INTO tbl_pll (tanggal, ap, unit, kodeakun, keterangan, nomorjurnal, tipe, fungsionalkredit, sumberkredit, posisi) SELECT tanggal, ap, unit, kodeakun, keterangan, nomorjurnal, tipe, fungsionalkredit, sumberkredit, posisi FROM tbl_pll_temp");
        if ($insertData) {
            $clear = DB::statement("TRUNCATE TABLE tbl_pll_temp");
            if ($clear) {
                return 1;
            } else {
                return 'error clear temp';
            }
        } else {
            return 'error copy';
        }
    }
}

function number_indo_1juta_kosong_excel($angka){
    if(is_numeric($angka)){
        return round($angka/1000000,0);
    } else {
        return $angka;
    }
}

function StripToSpace($string){
    $hasil = str_replace("_"," ",$string);
    return $hasil;
}

function SpaceToStrip($string){
    $hasil = str_replace(" ","_",$string);
    $hasil = str_replace("/","_",$hasil);
    $hasil = str_replace("-","_",$hasil);
    return $hasil;
}

function total_kolom($array= [], $field){
    if (empty($array)) {
        return 0;
    }
    foreach($array as $data){
        $value[] = $data->$field;
    }
    $hasil = array_sum($value);
    return $hasil;
}

function addTgl($tgl,$int){
    if($tgl==''){
        $tgl = '';
    }else{
        $tgl = date('d-m-Y',strtotime('+'.$int.' day',strtotime($tgl)));
    }
	return $tgl;
}

function lastUpdate($table, $field){
    $last_update = '';
    $sql = DB::select("SELECT $field FROM $table ORDER BY id DESC LIMIT 1");
    $last_update = $sql[0]->$field;
    return $last_update;
}

function excelDateTransform($value, $format = 'Y-m-d'){
  try {
    return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
  } catch (\Exception $e) {
    return \Carbon\Carbon::createFromFormat($format, $value);
  }
}

function data_sort($data, $param = null){
  return collect($data)
  ->sortBy($param ?? array_keys($data[0])[0])
  ->values()
  ->toArray();
}

function number_indo_no_minus($angka){
    if($angka!=''){
        $angka = (float)$angka;
        $hasil_rupiah = '('.number_format(abs($angka), 0, '.', ',').')'; // Menggunakan nilai absolut untuk mendapatkan angka positif
        $color = '#fe0000';
        if ($angka == 0) {
            echo "<span>$angka</span>";
        } else {
            echo "<span style='color:$color'>$hasil_rupiah</span>";
        }
    }else{
        if($angka == '0'){
            echo $angka;
        }else{
            echo "";
        }
    }
}

function number_indo_absolute_excel_black($angka){
    if ($angka == '' || $angka == 0) {
        echo 0;
    } else if ($angka !== '' || $angka !== 0) {
        $hasil_rupiah = number_format($angka, 0, '.', ',');
        if ($angka < 0) {
            $hasil_rupiah = substr($hasil_rupiah, 1);
        }
        echo $hasil_rupiah;
    }
}

function number_indo_absolute_excel($angka){
    if ($angka == '' || $angka == 0) {
        echo 0;
    } else if ($angka !== '' || $angka !== 0) {
        $angka = (float)$angka;
        $hasil_rupiah = number_format($angka, 0, '.', ',');
        $color = $angka < 0 ? '#fe0000' : '#3c4b64';
        if ($angka < 0) {
            $hasil_rupiah = substr($hasil_rupiah, 1);
        }
        echo "<span style='color:$color'>$hasil_rupiah</span>";
    }
}

function number_indo_no_minus_excel($angka){
    if($angka!=''){
        $hasil_rupiah = number_format($angka, 2, '.', ',');
        echo "<span>$hasil_rupiah</span>";
    }else{
        if($angka == '0'){
            echo $angka;
        }else{
            echo "";
        }
    }
}

function rgba_scale($val, $array = []){
    try {
        $index = array_search($val, $array);
        if ($index === false) {
            return 0;
        }
        return (1 / count($array)) * ($index + 1);
    } catch (\Throwable $th) {
        return 1;
    }
}

function cboBulanWithoutPlaceholder(){
    echo '<option value="1">JANUARI</option>
	<option value="2">FEBRUARI</option>
	<option value="3">MARET</option>
	<option value="4">APRIL</option>
	<option value="5">MEI</option>
	<option value="6">JUNI</option>
	<option value="7">JULI</option>
	<option value="8">AGUSTUS</option>
	<option value="9">SEPTEMBER</option>
	<option value="10">OKTOBER</option>
	<option value="11">NOVEMBER</option>
	<option value="12">DESEMBER</option>';
}

function unitToNomorRekening($unit, $bank) {
    $masterRekening = DB::table('tbl_buku_bank_master_rekening')->where('unit', $unit)->where('namabank', $bank)->first();
    if ($masterRekening) {
        return $masterRekening->nomor;
    }
    throw new Exception("No account number found for unit: $unit and bank: $bank");
}

function kodeUnitLong($kodeunit){
    $unit = DB::table('units')->where('kodeunit', $kodeunit)->first();

    if (!empty($unit)) {
        return $unit->namaunit;
    } else {
        echo "Unit tidak terdaftar";
    }
}

function jabatanUser($nik){
    $user = DB::table('users')->where('nik', $nik)->first();

    if (!empty($user)) {
        return $user->jabatan;
    } else {
        echo 'NIK tidak terdaftar';
    }
}

function nameUser($nik){
    $user = DB::table('users')->where('nik', $nik)->first();

    if (!empty($user)) {
        return $user->name;
    } else {
        echo 'NIK tidak terdaftar';
    }
}

function rmsPersenNol($val1,$val2){
    if (is_numeric($val1) && is_numeric($val2)){
        $hasil = ($val1!=0) ? round(($val2/$val1)*100,0).'%':'';
        if($hasil >= 1){
            return $hasil;
        }else{
            return '0%';
        }
    }else{
        return '0%';
    }
}

function diffCreatedDateToToday($createdAt) {
    // Mengonversi created_at ke objek Carbon
    $createdDate = Carbon\Carbon::parse($createdAt);

    // Mendapatkan tanggal dan waktu saat ini
    $now = Carbon\Carbon::now();

    // Menghitung selisih hari
    $difference = $now->diffInDays($createdDate);

    return $difference;
}

function terbilang($x) {
    $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];

    if ($x < 12)
        return " " . $angka[$x];
    elseif ($x < 20)
        return terbilang($x - 10) . " belas";
    elseif ($x < 100)
        return terbilang($x / 10) . " puluh" . terbilang($x % 10);
    elseif ($x < 200)
        return "seratus" . terbilang($x - 100);
    elseif ($x < 1000)
        return terbilang($x / 100) . " ratus" . terbilang($x % 100);
    elseif ($x < 2000)
        return "seribu" . terbilang($x - 1000);
    elseif ($x < 1000000)
        return terbilang($x / 1000) . " ribu" . terbilang($x % 1000);
    elseif ($x < 1000000000)
        return terbilang($x / 1000000) . " juta" . terbilang($x % 1000000);
}

function totalRekeningUnit($bank, $unitList){
    $unitArray = "'".implode("','", $unitList)."'";
    $sql = DB::select("SELECT COUNT(nomor) AS jumlahRekening FROM tbl_buku_bank_master_rekening WHERE unit IN ($unitArray) AND namabank = '$bank'");
    foreach ($sql as $d) {
        $jumlahRekening = $d->jumlahRekening;
    }
    return $jumlahRekening;
}

function unitToRing($unit, $alamat){
    $alamatParts = array_filter(array_map('trim', explode(',', $alamat)), function ($value) {
        return $value !== null && $value !== '';
    });
    $kabupaten = $alamatParts[count($alamatParts) - 3] ?? '';
    $kecamatan = $alamatParts[count($alamatParts) - 4] ?? '';
    $query = "SELECT ring FROM tbl_master_ring_po WHERE unit LIKE :unit";
    $bindings = ['unit' => '%' . $unit . '%'];
    if (!empty($kabupaten)) {
        $query .= " AND kabupaten LIKE :kabupaten";
        $bindings['kabupaten'] = '%' . $kabupaten . '%';
    }
    if (!empty($kecamatan)) {
        $query .= " AND kecamatan LIKE :kecamatan";
        $bindings['kecamatan'] = '%' . $kecamatan . '%';
    }
    $result = DB::select($query, $bindings);
    return !empty($unit) && !empty($alamat) && !empty($result) ? $result[0]->ring : '---';
}

function flokToGrade($unit, $flok){
    $query = DB::select(
        "SELECT grade
        FROM tb_laporan_grade_plasma
        WHERE unit LIKE :unit
        AND nama_flok = :flok",
        [
            'unit' => '%' . $unit . '%',
            'flok' => $flok
        ]
    );

    return !empty($unit) && !empty($flok) && !empty($query) ? $query[0]->grade : '---';
}

function flokToPrasyarat($unit, $flok) {
    $query = DB::select(
        "SELECT
            flok_name,
            ap,
            unit,
            jenis_kandang,
            nilai_kandang,
            ROUND(CASE
                WHEN kelistrikan != 0 AND penggerak_diesel != 0 THEN (kelistrikan + penggerak_diesel) / 2
                WHEN kelistrikan = 0 AND penggerak_diesel != 0 THEN penggerak_diesel
                WHEN kelistrikan != 0 AND penggerak_diesel = 0 THEN kelistrikan
                ELSE 0
            END) AS kelistrikan,
            ROUND(CASE
                WHEN jenis_kandang = 'CH' AND kelistrikan != 0 AND penggerak_diesel != 0 THEN (kelistrikan + penggerak_diesel) / 2 * 60 / 100 + (nilai_kandang * 40 / 100)
                WHEN jenis_kandang = 'CH' AND kelistrikan = 0 AND penggerak_diesel != 0 THEN (penggerak_diesel * 60 / 100) + (nilai_kandang * 40 / 100)
                WHEN jenis_kandang = 'CH' AND kelistrikan != 0 AND penggerak_diesel = 0 THEN (kelistrikan * 60 / 100) + (nilai_kandang * 40 / 100)
                WHEN jenis_kandang = 'CH' AND kelistrikan = 0 AND penggerak_diesel = 0 THEN (nilai_kandang * 40 / 100)
                WHEN jenis_kandang = 'OPEN' THEN nilai_kandang
                ELSE 0
            END) AS total_score
        FROM (
            SELECT
                flok_name,
                ap,
                unit,
                CASE WHEN jenis_kandang = 'CLOSED' THEN 'CH' ELSE 'OPEN' END AS jenis_kandang,
                nilai_nilai_kandang AS nilai_kandang,
                nilai_kelistrikan AS kelistrikan,
                nilai_penggerak_diesel AS penggerak_diesel
            FROM tbl_prasyarat_flok_aktif
            WHERE kontrak_id != 0 AND flok_name = :flok AND unit = :unit
        ) AS subquery
        ORDER BY ap ASC, unit ASC, flok_name ASC
        LIMIT 1",
        [
            'unit' => $unit,
            'flok' => $flok
        ]
    );

    if (!empty($unit) && !empty($flok) && !empty($query) && isset($query[0])) {
        return [
            'jenis_kandang' => $query[0]->jenis_kandang,
            'nilai_kandang' => $query[0]->nilai_kandang,
            'kelistrikan' => $query[0]->kelistrikan,
            'total_score' => $query[0]->total_score,
        ];
    } else {
        return [
            'jenis_kandang' => '---',
            'nilai_kandang' => '---',
            'kelistrikan' => '---',
            'total_score' => '---',
        ];
    }
}

function flokToDensity($unit, $flok, $box) {
    $query = DB::select(
        "SELECT unit, nama_flok, populasi
        FROM tb_database_plasma_aktif
        WHERE nama_flok = :flok AND unit = :unit",
        [
            'unit' => $unit,
            'flok' => $flok
        ]
    );

    if (empty($unit) || empty($flok) || empty($box) || empty($query) || !isset($query[0])) {
        return '---';
    }

    $kapasitas = $query[0]->populasi;
    $adjusted_kapasitas = round($kapasitas + ($kapasitas * 10 / 100));
    $populasi = $box * 100;

    if ($adjusted_kapasitas <= $populasi) {
        return 'OVER';
    } else {
        return 'NORMAL';
    }
}

function flokToPeralatanCH($unit, $flok) {
    $query = DB::select(
        "SELECT
            tipe_kandang,
            CASE WHEN tipe_kandang LIKE '%Close House%' AND genset_jumlah >= 1 THEN 'Ada' WHEN tipe_kandang LIKE '%Open%' THEN '---' ELSE 'Tidak Ada' END AS genset,
            alarm_listrik_mati,
            alarm_kipas_mati,
            CASE WHEN tipe_kandang LIKE '%Closed House%' AND alarm_listrik_mati LIKE '%Tidak Ada%' AND alarm_kipas_mati LIKE '%Tidak Ada%' THEN 'Tidak Ada' WHEN tipe_kandang LIKE '%Open%' THEN '---' ELSE 'Ada' END AS alarm
        FROM tb_database_plasma_aktif
        WHERE nama_flok = :flok AND unit = :unit",
        [
            'unit' => $unit,
            'flok' => $flok
        ]
    );

    if (empty($unit) || empty($flok) || empty($query) || !isset($query[0])) {
        $genset = '---';
        $alarm = '---';
    } else {
        $genset = $query[0]->genset;
        $alarm = $query[0]->alarm;
    }

    return [
        'genset' => $genset,
        'alarm' => $alarm,
    ];
}

function flokToHasilPoDoc($grade, $jenisKandang, $ring, $prasyarat, $peralatanAlarm, $peralatanGenset) {
    if ($jenisKandang == 'CH') {
        if (in_array($grade, ['A', 'B', 'C']) && in_array($ring, [1, 2]) && $prasyarat >= 80 && $peralatanAlarm == 'Ada' && $peralatanGenset == 'Ada') {
            return 'LOLOS';
        } else if (in_array($grade, ['A', 'B', 'C']) && in_array($ring, [1, 2]) && $prasyarat >= 80 && $peralatanAlarm == 'Tidak Ada' && $peralatanGenset == 'Tidak Ada') {
            return 'REKOMENDASI KANIT';
        } else if (in_array($grade, ['D', 'BARU', '---']) && in_array($ring, [1, 2]) && $prasyarat >= 80 && in_array($peralatanAlarm, ['Ada', 'Tidak Ada']) && in_array($peralatanGenset, ['Ada', 'Tidak Ada'])) {
            return 'REKOMENDASI KANIT';
        } else if (in_array($grade, ['A', 'B', 'C', 'D', 'E', 'BARU', '---']) && $ring == 3 && $prasyarat >= 80 && in_array($peralatanAlarm, ['Ada', 'Tidak Ada']) && in_array($peralatanGenset, ['Ada', 'Tidak Ada'])) {
            return 'REKOMENDASI DIREKTUR';
        } else if ($grade == 'E' && in_array($ring, [1, 2, 3]) && $prasyarat >= 80 && in_array($peralatanAlarm, ['Ada', 'Tidak Ada']) && in_array($peralatanGenset, ['Ada', 'Tidak Ada'])) {
            return 'REKOMENDASI DIREKTUR';
        } else if (in_array($grade, ['A', 'B', 'C', 'D', 'E', 'BARU', '---']) && in_array($ring, [1, 2, 3]) && $prasyarat >= 70 && $prasyarat <= 79 && in_array($peralatanAlarm, ['Ada', 'Tidak Ada']) && in_array($peralatanGenset, ['Ada', 'Tidak Ada'])) {
            return 'REKOMENDASI DIREKTUR';
        } else if (in_array($grade, ['A', 'B', 'C', 'D', 'E', 'BARU', '---']) && in_array($ring, [1, 2, 3]) && (($prasyarat >= 0 && $prasyarat < 70) || $prasyarat == '---') && in_array($peralatanAlarm, ['Ada', 'Tidak Ada']) && in_array($peralatanGenset, ['Ada', 'Tidak Ada'])) {
            return 'PERJANJIAN GANTI RUGI';
        }
    } else if ($jenisKandang == 'OPEN') {
        if (in_array($grade, ['A', 'B', 'C']) && in_array($ring, [1, 2]) && $prasyarat >= 80) {
            return 'LOLOS';
        } else if (in_array($grade, ['D', 'BARU', '---']) && in_array($ring, [1, 2]) && $prasyarat >= 80) {
            return 'REKOMENDASI KANIT';
        } else if (in_array($grade, ['A', 'B', 'C', 'D', 'E', 'BARU', '---']) && $ring == 3 && $prasyarat >= 80) {
            return 'REKOMENDASI DIREKTUR';
        } else if ($grade == 'E' && in_array($ring, [1, 2, 3]) && $prasyarat >= 80) {
            return 'REKOMENDASI DIREKTUR';
        } else if (in_array($grade, ['A', 'B', 'C', 'D', 'E', 'BARU', '---']) && in_array($ring, [1, 2, 3]) && $prasyarat >= 70 && $prasyarat <= 79) {
            return 'REKOMENDASI DIREKTUR';
        } else if (in_array($grade, ['A', 'B', 'C', 'D', 'E', 'BARU', '---']) && in_array($ring, [1, 2, 3]) && (($prasyarat >= 0 && $prasyarat < 70) || $prasyarat == '---')) {
            return 'PERJANJIAN GANTI RUGI';
        }
    }
    return in_array(Auth::user()->roles, ['pusat', 'admin']) ? 'PERIKSA KEMBALI' : 'KONFIRMASI PUSAT';
}

function getUrlPrasyaratFlokAktif($ap) {
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
    $url = 'https://publicapi.agrinis.com/index.php/X7mtDYbC6Jr9ZRrn/ap_apis/flok_aktif_resume_checklist_prasyarat/'.$ap;
    $file = file_get_contents($url, false, $context);
    $arrayData = json_decode($file, true);
    $insert_data = [];

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
    }

    $insert_data = collect($insert_data);
    $chunks = $insert_data->chunk(500);
    foreach ($chunks as $chunk) {
        DB::table('tbl_prasyarat_flok_aktif_temp')->insert($chunk->toArray());
    }

    return count($insert_data) . ' record berhasil diupdate';
}

function insertPrasyaratFlokAktif() {
    ini_set('max_execution_time', 3600);

    DB::statement("TRUNCATE TABLE tbl_prasyarat_flok_aktif");

    $batchSize = 1000;
    $offset = 0;

    do {
        $tempData = DB::table('tbl_prasyarat_flok_aktif_temp')
        ->select(
            'ap',
            'unit',
            'perusahaan_id',
            'perusahaan_name',
            'cabang_id',
            'cabang_name',
            'flok_id',
            'flok_name',
            'kontrak_id',
            'tanggal_chickin',
            'periode',
            'jenis_kandang',
            'nilai_nilai_kandang',
            'tingkat_resiko_nilai_kandang',
            'created_at_nilai_kandang',
            'created_by_nilai_kandang',
            'keterangan_nilai_kandang',
            'nilai_kelistrikan',
            'tingkat_resiko_kelistrikan',
            'created_at_kelistrikan',
            'created_by_kelistrikan',
            'keterangan_kelistrikan',
            'nilai_penggerak_diesel',
            'tingkat_resiko_penggerak_diesel',
            'created_at_penggerak_diesel',
            'created_by_penggerak_diesel',
            'keterangan_penggerak_diesel',
            'nilai_beban_puncak',
            'tingkat_resiko_beban_puncak',
            'created_at_beban_puncak',
            'created_by_beban_puncak',
            'keterangan_beban_puncak'
        )
        ->offset($offset)
        ->limit($batchSize)
        ->get();

        $insertData = $tempData->map(function($row) {
            return (array)$row;
        })->all();

        DB::table('tbl_prasyarat_flok_aktif')->insert($insertData);

        $offset += $batchSize;
    } while (!$tempData->isEmpty());

    DB::statement("TRUNCATE TABLE tbl_prasyarat_flok_aktif_temp");
}

function avg_harga_segmen($harga1, $harga2){
    if(($harga1 != '') && ($harga2 != '')){
        $harga = ($harga1+$harga2)/2;
    }else{
        $harga = ($harga1+$harga2)/1;
    }
    return $harga;
}

function poin_komplain($kategori){
    if($kategori=='BERAT'){
        $poin = -5;
    }elseif($kategori=='SEDANG'){
        $poin = -3;
    }elseif($kategori=='RINGAN'){
        $poin = -1;
    }else{
        $poin ='---';
    }
    return $poin;
}

function singkatan($string){
    switch ($string) {
        case 'NON ZONA':
            $string = 'NON';
            break;
        case 'JATENG':
            $string = 'JTG';
            break;
        case 'JATIM':
            $string = 'JTM';
            break;
        case 'JABAR':
            $string = 'JBR';
            break;
        case 'KASELA':
            $string = 'KSL';
            break;
        default:
            return $string;
    }
    return $string;
}

function get_sertifikasi_ico_2($nik){
    $data = DB::table('table_sertifikasi')->where('nik', $nik)->get();
    if($data->count() !== 0){
        echo "<img class='ico_centang' src='".asset("tumb/ic_centang.png")."'>";
    }else{
        echo "";
    }
}

function get_sertifikasi($nik){
    $kategori = "BELUM SERTIFIKASI";
    $sql = DB::select("SELECT kategori FROM table_sertifikasi WHERE nik ='$nik' AND aktif='Y'");
    if(!empty($sql)){
        $kategori = $sql[0]->kategori;
    }else{
        $kategori = "BELUM SERTIFIKASI";
    }
    return $kategori;
}

function update_status_sertifikasi($tanggal, $nik){
    if($tanggal <= date('Y-m-d')){
        DB::table('table_sertifikasi')->where('nik', $nik)->update([
                'aktif' =>  'N',
            ]);
    }
}

function rupiah_strip($angka){
    $hasil_rupiah = "Rp " . number_format($angka, 0, ',', '.');
    echo $hasil_rupiah.",-";
}

function time_range($_from, $_to, ...$props){
    try {
        // Konfigurasi default
        $config = (object) array_merge([
            'order' => 'asc',
            'jump' => 'day',
        ], ...$props);

        // Opsi lompatan
        $jump_options = [
            'Y' => 'year',
            'm' => 'month',
            'd' => 'day',
        ];

        // Validasi opsi jump
        if (!in_array($config->jump, $jump_options)) {
            return [];
        }

        // Konversi waktu dan urutkan dari kecil ke besar
        $dest = [strtotime($_from), strtotime($_to)];
        sort($dest);
        [$from, $to] = $dest;

        // Jika waktu tidak valid
        if (!$from || !$to) {
            return [];
        }

        $list = [];
        $current = $from; // Mulai dari waktu awal

        do {
            $item = (object) [];
            foreach ($jump_options as $key => $value) {
                $item->{$value} = date($key, $current);
                if ($value === $config->jump) {
                    break;
                }
            }
            $list[] = $item;

            // Tambahkan waktu sesuai `jump`
            $current = strtotime("+1 {$config->jump}", $current);
        } while ($current <= $to);

        // Urutan descending jika diminta
        if ($config->order === 'desc') {
            $list = array_reverse($list);
        }

        return $list;
    } catch (\Throwable $th) {
        return [];
    }
}

function listunit(){
    return DB::table('units')
        ->select(DB::raw('region as ap'), DB::raw('kodeunit as unit'))
        ->get()
        ->groupBy('ap')
        ->map(fn($item) => $item->pluck('unit'));
}

function ptkode($nama_pt) {
    switch ($nama_pt) {
        case 'PT MITRA PETERNAKAN UNGGAS':
            return 'MPU';
        case 'PT MURIA JAYA RAYA':
            return 'MJR';
        case 'PT ANEKA INTAN LESTARI':
            return 'AIL';
        case 'PT SAWUNG GEMA ABADI':
            return 'SGA';
        case 'PT MITRA MAHKOTA BUANA':
            return 'MMB';
        case 'PT LAWU ABADI NUSA':
            return 'LAN';
        case 'PT BAROKAH RESTU UTAMA':
            return 'BRU';
        case 'PT MITRA UNGGAS MAKMUR':
            return 'MUM';
        case 'PT KARYA SATWA MULIA':
            return 'KSM';
        case 'PT MUSTIKA JAYA LESTARI':
            return 'MJL';
        case 'PT BINTANG TERANG BERSINAR':
            return 'BTB';
        case 'PT SLAMET AGUNG WIJAYA':
            return 'SAW';
        case 'PT GILAR PERWIRA SATRIA':
            return 'GPS';
        case 'PT KEDU LINTAS BERBINTANG':
            return 'KLB';
        case 'PT LAJU SATWA WISESA':
            return 'LSW';
        default:
            return "Kode PT tidak terdaftar";
    }
}

function kriteria_hari_panen($ekor){
    if($ekor >= 22){
        $kriteria = 'SANGAT CEPAT';
    }elseif($ekor >= 20){
        $kriteria = 'CEPAT';
    }elseif($ekor > 16){
        $kriteria = 'SEDANG';
    }elseif($ekor >= 14){
        $kriteria = 'LAMA';
    }elseif(($ekor < 14) && ($ekor > 0)){
        $kriteria = 'SANGAT LAMA';
    }else{
        $kriteria = '-';
    }
    return $kriteria;
}

function check_for_password(){
    try {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return !($user->updated_at > now()->startOfMonth());
    } catch (\Throwable $_) {
        return false;
    }
}

function kode2unit($string) {
    $decrypt=base64_decode($string);
    return $decrypt;
}

function unit2kode($string) {
    $encrypt=base64_encode($string);
    return $encrypt;
}

function count_kolom($array= [], $field){
    if (empty($array)) {
        return 0;
    }
    foreach($array as $data){
        $value[] = $data->$field;
    }
    $hasil = count(array_filter($value, function($x) { return !empty($x); }));
    return $hasil;
}

function tglIndoTahunBulan($tgl){
    if(($tgl == '') || ($tgl == '1970-01-01')){
        return '';
    }else{
        $tgl = $tgl.'01';
        $tanggal = substr($tgl, 8, 2);
        $nama_bulan = array("Jan", "Feb", "Mar", "Apr", "Mei",
            "Jun", "Jul", "Agu", "Sep",
            "Okt", "Nov", "Des");

        $bulan = $nama_bulan[substr($tgl, 5, 2) - 1];
        $tahun = substr($tgl, 0, 4);

        return $bulan . ' ' . $tahun;
    }
}