function number_indo(bilangan) {
    if (bilangan) {
      rupiah = bilangan.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
      if (bilangan < 0) {
        return "<span style='color:red'>"+rupiah+"</span>";
      } else {
        return rupiah;
      }       
    } else {
        return '';
    }
}

function tglIndo(tgl) {
    const myArray = tgl.split("-");
    var tahun = myArray[0];
    var bulan = myArray[1];
    var tanggal = myArray[2];

    switch (bulan) {
        case '01':
            bulan = "Jan";
            break;
        case '02':
            bulan = "Feb";
            break;
        case '03':
            bulan = "Mar";
            break;
        case '04':
            bulan = "Apr";
            break;
        case '05':
            bulan = "Mei";
            break;
        case '06':
            bulan = "Jun";
            break;
        case '07':
            bulan = "Jul";
            break;
        case '08':
            bulan = "Agu";
            break;
        case '09':
            bulan = "Sep";
            break;
        case '10':
            bulan = "Okt";
            break;
        case '11':
            bulan = "Nov";
            break;
        case '12':
            bulan = "Des";
            break;
    }
    return tanggal + ' ' + bulan + ' ' + tahun;
}

function titik2koma(strValue) {
    if (strValue) {
        return strValue.replace(".", ",");
    } else {
        return '';
    }
}

function rekomendasiDataTelat(value){
    if((6 <= value) && (value <= 8)){
        return "<span style='color:blue; font-weight:bold'>SEGERA KUNJUNGI</span>";
    }else{
        return "<span style='color:red; font-weight:bold'>KUNJUNGI SEKARANG</span>";
    }
} 

function rekomendasiRhppMinus(value){
    if(value < -1000){
        return "<span style='color:red; font-weight:bold'>SEGERA PANEN</span>";
    }else if(value > -1000){
        return "<span style='color:blue; font-weight:bold'>PANTAU KETAT</span>";
    }
} 

function rekomendasiAyamClosing(value){
    if(value >= 10){
        return "<span style='color:red; font-weight:bold'>CLOSING HARI INI</span>";
    }else if(value < 10){
        return "<span style='color:blue; font-weight:bold'>SEGERA CLOSING</span>";
    }else{
        return "";
    }
}

function rekomendasiAyamSakit(value){
    if(value <= 1){
        return "<span style='color:blue; font-weight:bold'>OBATI</span>";
    }else if(value > 1){
        return "<span style='color:red; font-weight:bold'>JUAL</span>";
    }else{
        return "";
    }
}

