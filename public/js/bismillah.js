function hanyaNumber(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}

function rmsFcrPakan(KgPakan, KgPanen) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 3,
			});

	var pakan 	= parseFloat(KgPakan);
	var panen 	= parseFloat(KgPanen);
	var hasil	= pakan/panen;
	return format(hasil);
}

function rmsDiffFcr(Fcr, tblFcr) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 3,
			});

	var fcr 	= parseFloat(Fcr).toFixed(3);
	var tblfcr 	= parseFloat(tblFcr).toFixed(3);
	var hasil	= fcr-tblfcr;
	return format(hasil);
}

function rmsFI(KgPakan, Kapasitas) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 3,
			});

	var kgpakan 	= parseFloat(KgPakan);
	var kapasitas 	= parseFloat(Kapasitas);
	var hasil	= kgpakan/kapasitas;
	return format(hasil);
}

function rmsAvgBw(TotKg, TotEk) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 2,
			});

	var totkg 	= parseFloat(TotKg);
	var totek 	= parseFloat(TotEk);
	var hasil	= totkg/totek;
	return format(hasil);
}

function rmsDpls(EkMati, TotKapasitas) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var ekmati 	= parseFloat(EkMati);
	var totkapasitas 	= parseFloat(TotKapasitas);
	var hasil	= ekmati/totkapasitas*100;
	return format(hasil);
}

function rmsLive(Dpls) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var dpls 	= parseFloat(Dpls).toFixed(1);
	var hasil	= 100-dpls;
	return format(hasil);
}

function rmsIp(Live, Bw, Fcr, Umur) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});

	var live 	= parseFloat(Live).toFixed(1);
	var bw 		= parseFloat(Bw).toFixed(2);
	var fcr 	= parseFloat(Fcr).toFixed(3);
	var umur 	= parseFloat(Umur).toFixed(1);
	var hasil	= (((live*bw))/fcr)/umur*100 ;
	return format(hasil);
}

function rmsHppPlasma(hargadoc, hargaovk, bw, live, fcr, hargapakan) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});
	var hargadoc 	= parseFloat(hargadoc);
	var hargaovk 	= parseFloat(hargaovk);
	var bw 			= parseFloat(bw).toFixed(2);
	var live 		= parseFloat(live).toFixed(1);
	var fcr 		= parseFloat(fcr).toFixed(3);
	var hargapakan 	= parseFloat(hargapakan);
	var hasil	= ((hargadoc+hargaovk)/bw/(live/100))+(fcr*hargapakan);
	return format(hasil);
}

function rmsRhppKg(HargaDaging, HppPlasma, Bonus) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});

	var hargadaging 	= parseFloat(HargaDaging);
	var hppplasma 	= parseFloat(HppPlasma);
	var bonus 	= parseFloat(Bonus);
	var hasil	= hargadaging-hppplasma+bonus ;
	return format(hasil);
}

function rmsRhppEk(RhppKg, Bw, Live) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});

	var rhppkg 	= parseFloat(RhppKg);
	var bw 	= parseFloat(Bw);
	var live 	= parseFloat(Live);
	var hasil	= rhppkg*bw*(live/100) ;
	return format(hasil);
}

function rmsMarginKg(HargaLb, HppInti) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var hargalb = parseFloat(HargaLb);
	var hppinti = parseFloat(HppInti);
	var hasil	= hargalb-hppinti;
	return format(hasil);
}

function rmsMarginEk(Margin, Bw, Live) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});

	var margin = parseFloat(Margin);
	var bw 	= parseFloat(Bw);
	var live 	= parseFloat(Live);
	var hasil	= margin*bw*(live/100);
	return format(hasil);
}

function rmsBonus1(BnsOperasional, Bw, Live) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var bnsoperasional = parseFloat(BnsOperasional);
	var bw 	= parseFloat(Bw);
	var live 	= parseFloat(Live);
	var hasil	= bnsoperasional/bw/(live/100);
	return format(hasil);
}

function rmsBonus2(Ip, persenIp3, bonusIp3, persenIp2, bonusIp2, persenIp1, bonusIp1) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var ip = parseFloat(Ip);
	var persenip3 	= parseFloat(persenIp3);
	var persenip2 	= parseFloat(persenIp2);
	var persenip1 	= parseFloat(persenIp1);
	var bonusip3 	= parseFloat(bonusIp3);
	var bonusip2 	= parseFloat(bonusIp2);
	var bonusip1 	= parseFloat(bonusIp1);

	if(ip>=persenip3){
		hasil = bonusip3;
	}else if(ip>=persenip2){
		hasil = bonusip2;
	}else if(ip>=persenip1){
		hasil = bonusip1;
	}else{
		hasil = 0;
	}
	return format(hasil);
}

function rmsBonus3(diffFcr, persenFcr3, bonusFcr3, persenFcr2, bonusFcr2, persenFcr1, bonusFcr1) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var difffcr = parseFloat(diffFcr);
	var persenfcr3 	= parseFloat(persenFcr3);
	var persenfcr2 	= parseFloat(persenFcr2);
	var persenfcr1 	= parseFloat(persenFcr1);
	var bonusfcr3 	= parseFloat(bonusFcr3);
	var bonusfcr2 	= parseFloat(bonusFcr2);
	var bonusfcr1 	= parseFloat(bonusFcr1);

	if(difffcr<=(persenfcr3/100)){
            hasil = bonusfcr3;
        }else if(difffcr<=(persenfcr2/100)){
            hasil = bonusfcr2;
        }else if(difffcr<=(persenfcr1/100)){
            hasil = bonusfcr1;
        }else{
            hasil = 0;
        }
	return format(hasil);
}

function rmsBonus4(hargaLb, hargaDaging, bonusPasar) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var hargalb = parseFloat(hargaLb);
	var hargadaging 	= parseFloat(hargaDaging);
	var bonuspasar 	= parseFloat(bonusPasar);

	if(hargalb<hargadaging){
            hasil= 0;
        }else{
            hasil = (hargalb-hargadaging)*(bonuspasar/100);
        }
	return format(hasil);
}

function rmsBiayaOvk(HargaOvk, Bw, Live) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var hargaovk 	= parseFloat(HargaOvk);
	var bw 	= parseFloat(Bw);
	var live 	= parseFloat(Live);
	var hasil	= hargaovk/bw/(live/100);
	return format(hasil);
}

function rmsBiayaDoc(HargaDoc, Bw, Live) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});

	var hargadoc 	= parseFloat(HargaDoc);
	var bw 	= parseFloat(Bw);
	var live 	= parseFloat(Live);
	var hasil	= hargadoc/bw/(live/100);
	return format(hasil);
}

function rmsBiayaRhpp(Rhpp, Bw, Live) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var rhpp 	= parseFloat(Rhpp);
	var bw 	= parseFloat(Bw);
	var live 	= parseFloat(Live);
	var hasil	= rhpp/bw/(live/100);
	return format(hasil);
}

function rmsBiayaHpp(Doc, Pakan, Ovk, Rhpp) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var doc 	= parseFloat(Doc);
	var pakan 	= parseFloat(Pakan);
	var ovk 	= parseFloat(Ovk);
	var rhpp 	= parseFloat(Rhpp);
	var hasil	= doc+pakan+ovk+rhpp;
	return format(hasil);
}

function rmsBiayaPakan(HargaPakan, Fcr) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});

	var hargapakan 	= parseFloat(HargaPakan);
	var fcr 	= parseFloat(Fcr);
	var hasil	= hargapakan*fcr;
	return format(hasil);
}

function rmsHppByDoc(HargaDoc, HargaOvk, HargaRhpp, Bw, Dpls, HargaPakan, Fcr) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var hargadoc 	= parseFloat(HargaDoc);
	var hargaovk 	= parseFloat(HargaOvk);
	var hargarhpp 	= parseFloat(HargaRhpp);
	var bw 	= parseFloat(Bw);
	var dpls 	= parseFloat(Dpls);
	var hargapakan 	= parseFloat(HargaPakan);
	var fcr 	= parseFloat(Fcr);

    var hasil = (hargadoc+hargaovk+hargarhpp)/bw/((100-dpls)/100)+(hargapakan*fcr);
	return format(hasil);
}

function rmsHppByPakan(HargaPakan, HargaOvk, HargaRhpp, Bw, Dpls, HargaDoc, Fcr) {
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 1,
			   maximumFractionDigits: 1,
			});

	var hargadoc 	= parseFloat(HargaDoc);
	var hargaovk 	= parseFloat(HargaOvk);
	var hargarhpp 	= parseFloat(HargaRhpp);
	var bw 	= parseFloat(Bw);
	var dpls 	= parseFloat(Dpls);
	var hargapakan 	= parseFloat(HargaPakan);
	var fcr 	= parseFloat(Fcr);
    var hasil = (hargadoc+hargaovk+hargarhpp)/bw/((100-dpls)/100)+(hargapakan*fcr);
	return format(hasil);
}

function hanyaNumber(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
	return true;
}

function koma2titik(angka) {
	var hasil = angka.toString();
	hasil = hasil.replace(",",".");
	return hasil;
}

function titik2koma(angka) {
	var hasil = angka.toString();
	hasil = hasil.replace(".",",");
	return hasil;
}

function remall(angka) {
	var hasil = angka.toString();
	hasil = hasil.replace(".","");
	hasil = hasil.replace(",","");
	hasil = hasil.replace(" ","");
	hasil = hasil.replace("/Kg","");
	return hasil;
}

function formatRupiah(angka){
	const format = (num, decimals) => num.toLocaleString('id-ID', {
			   minimumFractionDigits: 0,
			   maximumFractionDigits: 0,
			});
	return format(angka);
}
