@extends('dashboard.base')

@section('content')
<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="cil-description"></i> {{ __('EDIT PURCHASE ORDER') }}</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('logdoc.poDocPerVendor.update', [$id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <table class="table table-sm">
                                <tr id="vendorContainer" name="vendorContainer">
                                    <th style="width: 20%"> VENDOR </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="vendor" name="vendor">
                                            @foreach ($vendorDoc as $vendors)
                                                <option value="{{ $vendors->kode_vendor }}">{{ $vendors->kode_vendor }}</option>
                                            @endforeach
                                            <option value="{{ $poDoc->namavendor }}" selected hidden>{{ $poDoc->namavendor }}</option>
                                        </select>
                                    </th>
                                </tr>
                            </table>
                            <table class="table table-sm" id="divFormatCp" name="divFormatCp" style="display: none">
                                <tr id="tanggalContainerCp" name="tanggalContainerCp">
                                    <th style="width: 20%"> TANGGAL </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input class="form-control" type="date" name="tanggalCp" id="tanggalCp" value="{{ $poDoc->tanggal }}">
                                        <input class="form-control" type="hidden" name="hariCp" id="hariCp" value="{{ $poDoc->hari }}">
                                    </th>
                                </tr>
                                <tr id="regionContainerCp" name="regionContainerCp">
                                    <th style="width: 20%"> NAMA DO </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            @foreach ($region as $r)
                                                <input type="text" class="form-control" id="regionTextCp" name="regionTextCp" value="{{ $r->namaregion }}" placeholder="{{ $r->namaregion }}" readonly>
                                            @endforeach
                                        @else
                                            <select class="form-control" id="regionCp" name="regionCp">
                                                @if (($jabatan != 'ADMIN LOGISTIK'))
                                                    <option value="">PILIH</option>
                                                @endif
                                                @foreach ($region as $regions)
                                                    <option value="{{$regions->koderegion}}">{{$regions->namaregion}}</option>
                                                @endforeach
                                                <option value="{{ $koderegion }}" selected hidden>{{ $namaregion }}</option>
                                            </select>
                                            <input class="form-control" type="hidden" id="regionTextCp" name="regionTextCp" value="{{ $namaregion }}">
                                        @endif
                                    </th>
                                </tr>
                                <tr id="unitContainerCp" name="unitContainerCp">
                                    <th style="width: 20%"> UNIT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            <input type="text" class="form-control" id="unitCp" name="unitCp" value="{{ $kodeUnit }}" placeholder="{{ $kodeUnit }}" readonly>
                                        @else
                                            <select class="form-control" id="unitCp" name="unitCp">
                                                <option value="{{ $poDoc->unit }}" hidden>{{ $poDoc->unit }}</option>
                                                @if (($jabatan == 'ADMIN LOGISTIK'))
                                                    <option value="{{ $kodeUnit }}" selected>{{ $kodeUnit }}</option>
                                                @endif
                                            </select>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="jenisGradeContainerCp" name="jenisGradeContainerCp">
                                    <th style="width: 20%"> JENIS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisGradeCp" name="jenisGradeCp">
                                            <option value="" hidden>PILIH</option>
                                            <option value="GRADE A">GRADE A</option>
                                            <option value="GRADE YN">GRADE YN</option>
                                            <option value="GRADE MIX">GRADE MIX</option>
                                            <option value="{{ $poDoc->gradedoc }}" selected hidden>{{ $poDoc->gradedoc }}</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="buttonCariContainerCp" name="buttonCariContainerCp">
                                    <th></th><th></th>
                                    <th>
                                        <button type="button" class="btn btn-primary" style="width: 100%" data-toggle="modal" data-target="#cariDataPeternak">CARI DATA PETERNAK</button>
                                    </th>
                                </tr>
                                <tr id="namaPeternakContainerCp" name="namaPeternakContainerCp">
                                    <th style="width: 20%"> NAMA PETERNAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" id="namaPeternakCp" name="namaPeternakCp" value="{{ $poDoc->namapeternak }}" readonly/>
                                    </th>
                                </tr>
                                <tr id="alamatKandangContainerCp" name="alamatKandangContainerCp">
                                    <th style="width: 20%"> ALAMAT KANDANG </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <div class="input-group">
                                            <textarea class="form-control" name="alamatKandangCp" id="alamatKandangCp" rows="2" readonly>{{ $poDoc->alamatkandang }}</textarea>
                                            <span class="input-group-addon btn btn-primary" id="buttonAlamatPlasmaCp" name="buttonAlamatPlasmaCp"><i class="cil-pencil align-middle"></i></span>
                                        </div>
                                    </th>
                                </tr>
                                <tr id="nomorTeleponContainerCp" name="nomorTeleponContainerCp">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PETERNAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if (($roles == 'pusat') || ($roles == 'admin'))
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="nomorTeleponCp" id="nomorTeleponCp" value="{{ $poDoc->notelepon }}" readonly/><span class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="buttonNomorPlasmaCp" name="buttonNomorPlasmaCp"><i class="cil-pencil"></i></button></span>
                                            </div>
                                        @else
                                            <input type="text" class="form-control" name="nomorTeleponCp" id="nomorTeleponCp" value="{{ $poDoc->notelepon }}" readonly/>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="kontrakContainerCp" name="kontrakContainerCp">
                                    <th style="width: 20%"> STATUS KONTRAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="kontrakCp" id="kontrakCp">
                                    </th>
                                </tr>
                                <tr id="nomorTeleponTSContainerCp" name="nomorTeleponTSContainerCp">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PPL/TS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="select2-multiple form-control" name="nomorTeleponTSCp[]" multiple="multiple" id="nomorTeleponTSCp" style="width: 100%; height: 100%">
                                            @foreach ($nomorTeleponTsSelected as $selected)
                                                @if ($selected->jabatan == 'TECHNICAL SUPPORT' || $selected->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($selected->jabatan).' : '.$selected->nowa }}" selected hidden>{{ $selected->nama." | ".getInitials($selected->jabatan).' | '.$selected->unit." | ".$selected->nowa }}</option>
                                                @endif
                                            @endforeach
                                            @foreach ($nomor_telepon_ts as $u)
                                                @if ($u->jabatan == 'TECHNICAL SUPPORT' || $u->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($u->jabatan).' : '.$u->nowa }}">{{ $u->nama." | ".getInitials($u->jabatan).' | '.$u->unit." | ".$u->nowa }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </th>
                                </tr>
                                <tr id="plastikVaccContainerCp" name="plastikVaccContainerCp">
                                    <th style="width: 20%"> PLASTIK - VACC (BOX) </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="plastikVaccCp" id="plastikVaccCp" value="{{ $poDoc->jumlahbox }}">
                                    </th>
                                </tr>
                                <tr id="plastikNonVaccContainerCp" name="plastikNonVaccContainerCp">
                                    <th style="width: 20%"> PLASTIK - NON VACC (BOX) </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="plastikNonVaccCp" id="plastikNonVaccCp" value="{{ $poDoc->nonvacc }}" disabled>
                                    </th>
                                </tr>
                                <tr id="feedGelContainerCp" name="feedGelContainerCp">
                                    <th style="width: 20%"> FEED GEL/ <br> NON FEED GEL </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" name="feedGelCp" id="feedGelCp">
                                            <option value="FEED GEL">FEED GEL</option>
                                            <option value="NON FEED GEL">NON FEED GEL</option>
                                            <option value="{{ $poDoc->feedgel }}" selected hidden>{{ $poDoc->feedgel }}</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="keteranganContainerCp" name="keteranganContainerCp">
                                    <th style="width: 20%"> KETERANGAN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <textarea class="form-control" name="keteranganCp" id="keteranganCp" rows="2">{{ $poDoc->keterangan }}</textarea>
                                    </th>
                                </tr>
                            </table>
                            <table class="table table-sm" id="divFormatCtu" name="divFormatCtu" style="display: none">
                                <tr id="regionContainerCtu" name="regionContainerCtu">
                                    <th style="width: 20%"> NAMA DO </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            @foreach ($region as $r)
                                                <input type="text" class="form-control" id="regionTextCtu" name="regionTextCtu" value="{{ $r->namaregion }}" placeholder="{{ $r->namaregion }}" readonly>
                                            @endforeach
                                        @else
                                            <select class="form-control" id="regionCtu" name="regionCtu">
                                                @if (($jabatan != 'ADMIN LOGISTIK'))
                                                    <option value="">PILIH</option>
                                                @endif
                                                @foreach ($region as $regions)
                                                    <option value="{{$regions->koderegion}}">{{$regions->namaregion}}</option>
                                                @endforeach
                                                <option value="{{ $koderegion }}" selected hidden>{{ $namaregion }}</option>
                                            </select>
                                            <input class="form-control" type="hidden" id="regionTextCtu" name="regionTextCtu" value="{{ $namaregion }}">
                                        @endif
                                    </th>
                                </tr>
                                <tr id="unitContainerCtu" name="unitContainerCtu">
                                    <th style="width: 20%"> UNIT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            <input type="text" class="form-control" id="unitCtu" name="unitCtu" value="{{ $kodeUnit }}" placeholder="{{ $kodeUnit }}" readonly>
                                        @else
                                            <select class="form-control" id="unitCtu" name="unitCtu">
                                                <option value="{{ $poDoc->unit }}" hidden>{{ $poDoc->unit }}</option>
                                                @if (($jabatan == 'ADMIN LOGISTIK'))
                                                    <option value="{{ $kodeUnit }}">{{ $kodeUnit }}</option>
                                                @endif
                                            </select>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="tanggalContainerCtu" name="tanggalContainerCtu">
                                    <th style="width: 20%"> TANGGAL </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input class="form-control" type="date" name="tanggalCtu" id="tanggalCtu" value="{{ $poDoc->tanggal }}">
                                        <input class="form-control" type="hidden" name="hariCtu" id="hariCtu" value="{{ $poDoc->hari }}">
                                    </th>
                                </tr>
                                <tr id="jenisGradeContainerCtu" name="jenisGradeContainerCtu">
                                    <th style="width: 20%"> JENIS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisGradeCtu" name="jenisGradeCtu">
                                            <option value="GRADE A">GRADE A</option>
                                            <option value="GRADE BM">GRADE BM</option>
                                            <option value="{{ $poDoc->gradedoc }}" selected hidden>{{ $poDoc->gradedoc }}</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="jenisVaksinContainerCtu" name="jenisVaksinContainerCtu">
                                    <th style="width: 20%"> VAKSIN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisVaksinCtu" name="jenisVaksinCtu">
                                            @if (!empty($poDoc->vaksin))
                                                <option value="{{ $poDoc->vaksin }}">{{ $poDoc->vaksin }}</option>
                                            @endif
                                        </select>
                                    </th>
                                </tr>
                                <tr id="buttonCariContainerCtu" name="buttonCariContainerCtu">
                                    <th></th><th></th>
                                    <th>
                                        <button type="button" class="btn btn-primary" style="width: 100%" data-toggle="modal" data-target="#cariDataPeternak">CARI DATA PETERNAK</button>
                                    </th>
                                </tr>
                                <tr id="namaPeternakContainerCtu" name="namaPeternakContainerCtu">
                                    <th style="width: 20%"> NAMA FLOK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" id="namaPeternakCtu" name="namaPeternakCtu" value="{{ $poDoc->namapeternak }}" readonly/>
                                    </th>
                                </tr>
                                <tr id="alamatContainerCtu" name="alamatContainerCtu">
                                    <th style="width: 20%"> ALAMAT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <div class="input-group">
                                            <textarea class="form-control" name="alamatCtu" id="alamatCtu" rows="2" readonly>{{ $poDoc->alamatkandang }}</textarea>
                                            <span class="input-group-addon btn btn-primary" id="buttonAlamatPlasmaCtu" name="buttonAlamatPlasmaCtu"><i class="cil-pencil align-middle"></i></span>
                                    </th>
                                </tr>
                                <tr id="nomorHpContainerCtu" name="nomorHpContainerCtu">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PETERNAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if (($roles == 'pusat') || ($roles == 'admin'))
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="nomorHpCtu" id="nomorHpCtu" value="{{ $poDoc->notelepon }}" readonly/><span class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="buttonNomorPlasmaCtu" name="buttonNomorPlasmaCtu"><i class="cil-pencil"></i></button></span>
                                            </div>
                                        @else
                                            <input type="text" class="form-control" name="nomorHpCtu" id="nomorHpCtu" value="{{ $poDoc->notelepon }}" readonly/>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="kontrakContainerCtu" name="kontrakContainerCtu">
                                    <th style="width: 20%"> STATUS KONTRAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="kontrakCtu" id="kontrakCtu">
                                    </th>
                                </tr>
                                <tr id="nomorTeleponTSContainerCtu" name="nomorTeleponTSContainerCtu">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PPL/TS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="select2-multiple form-control" name="nomorTeleponTSCtu[]" multiple="multiple" id="nomorTeleponTSCtu" style="width: 100%; height: 100%">
                                            @foreach ($nomorTeleponTsSelected as $selected)
                                                @if ($selected->jabatan == 'TECHNICAL SUPPORT' || $selected->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($selected->jabatan).' : '.$selected->nowa }}" selected>{{ $selected->nama.' | '.getInitials($selected->jabatan).' | '.$selected->unit.' | '.$selected->nowa }}</option>
                                                @endif
                                            @endforeach
                                            @foreach ($nomor_telepon_ts as $u)
                                                @if ($u->jabatan == 'TECHNICAL SUPPORT' || $u->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($u->jabatan).' : '.$u->nowa }}">{{ $u->nama.' | '.getInitials($u->jabatan).' | '.$u->unit.' | '.$u->nowa }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </th>
                                </tr>
                                <tr id="popBoxContainerCtu" name="popBoxContainerCtu">
                                    <th style="width: 20%"> POP (BOX) </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="number" class="form-control" name="popBoxCtu" id="popBoxCtu" value="{{ $poDoc->jumlahbox }}">
                                    </th>
                                </tr>
                                <tr id="keteranganContainerCtu" name="keteranganContainerCtu">
                                    <th style="width: 20%"> KETERANGAN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <textarea class="form-control" name="keteranganCtu" id="keteranganCtu" rows="2">{{ $poDoc->keterangan }}</textarea>
                                    </th>
                                </tr>
                            </table>
                            <table class="table table-sm" id="divFormatMb" name="divFormatMb" style="display: none">
                                <tr id="tanggalContainerMb" name="tanggalContainerMb">
                                    <th style="width: 20%"> TANGGAL </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input class="form-control" type="date" name="tanggalMb" id="tanggalMb" value="{{ $poDoc->tanggal }}" />
                                    </th>
                                </tr>
                                <tr id="hariContainerMb" name="hariContainerMb">
                                    <th style="width: 20%"> HARI </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input class="form-control" type="text" name="hariMb" id="hariMb" readonly value="{{ $poDoc->hari }}" />
                                    </th>
                                </tr>
                                <tr id="regionContainerMb" name="regionContainerMb">
                                    <th style="width: 20%"> NAMA DO </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            @foreach ($region as $r)
                                                <input type="text" class="form-control" id="regionTextMb" name="regionTextMb" value="{{ $r->namaregion }}" placeholder="{{ $r->namaregion }}" readonly>
                                            @endforeach
                                        @else
                                            <select class="form-control" id="regionMb" name="regionMb">
                                                @if (($jabatan != 'ADMIN LOGISTIK'))
                                                    <option value="">PILIH</option>
                                                @endif
                                                @foreach ($region as $regions)
                                                    <option value="{{$regions->koderegion}}">{{$regions->namaregion}}</option>
                                                @endforeach
                                                <option value="{{ $koderegion }}" selected hidden>{{ $namaregion }}</option>
                                            </select>
                                            <input class="form-control" type="hidden" id="regionTextMb" name="regionTextMb" value="{{ $namaregion }}">
                                        @endif
                                    </th>
                                </tr>
                                <tr id="unitContainerMb" name="unitContainerMb">
                                    <th style="width: 20%"> UNIT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            <input type="text" class="form-control" id="unitMb" name="unitMb" value="{{ $kodeUnit }}" placeholder="{{ $kodeUnit }}" readonly>
                                        @else
                                            <select class="form-control" id="unitMb" name="unitMb">
                                                <option value="{{ $poDoc->unit }}" hidden>{{ $poDoc->unit }}</option>
                                                @if (($jabatan == 'ADMIN LOGISTIK'))
                                                    <option value="{{ $kodeUnit }}">{{ $kodeUnit }}</option>
                                                @endif
                                            </select>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="jenisGradeContainerMb" name="jenisGradeContainerMb"> <!-- FORMAT MB -->
                                    <th style="width: 20%"> JENIS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisGradeMb" name="jenisGradeMb">
                                            <option value="{{ $poDoc->gradedoc }}" selected hidden>{{ $poDoc->gradedoc }}</option>
                                            <option value="SILVER">SILVER</option>
                                            <option value="GOLD">GOLD</option>
                                            <option value="PLATINUM">PLATINUM</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="jenisVaksinContainerMb" name="jenisVaksinContainerMb">
                                    <th style="width: 20%"> VAKSIN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisVaksinMb" name="jenisVaksinMb">
                                            <option value="{{ $poDoc->vaksin }}" selected hidden>{{ $poDoc->vaksin }}</option>
                                            <option value="VAKSIN A">VAKSIN A</option>
                                            <option value="VAKSIN B">VAKSIN B</option>
                                            <option value="VAKSIN C">VAKSIN C</option>
                                            <option value="VAKSIN O">VAKSIN O</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="buttonCariContainerMb" name="buttonCariContainerMb">
                                    <th></th><th></th>
                                    <th>
                                        <button type="button" class="btn btn-primary" style="width: 100%" data-toggle="modal" data-target="#cariDataPeternak">CARI DATA PETERNAK</button>
                                    </th>
                                </tr>
                                <tr id="namaPeternakContainerMb" name="namaPeternakContainerMb">
                                    <th style="width: 20%"> NAMA PETERNAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" id="namaPeternakMb" name="namaPeternakMb" value="{{ $poDoc->namapeternak }}" readonly/>
                                    </th>
                                </tr>
                                <tr id="alamatLokasiContainerMb" name="alamatLokasiContainerMb">
                                    <th style="width: 20%"> ALAMAT LOKASI </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <div class="input-group">
                                            <textarea class="form-control" name="alamatLokasiMb" id="alamatLokasiMb" rows="2" readonly>{{ $poDoc->alamatkandang }}</textarea>
                                            <span class="input-group-addon btn btn-primary" id="buttonAlamatPlasmaMb" name="buttonAlamatPlasmaMb"><i class="cil-pencil align-middle"></i></span>
                                        </div>
                                    </th>
                                </tr>
                                <tr id="nomorTeleponContainerMb" name="nomorTeleponContainerMb">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PETERNAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if (($roles == 'pusat') || ($roles == 'admin'))
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="nomorTeleponMb" id="nomorTeleponMb" value="{{ $poDoc->notelepon }}" readonly/><span class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="buttonNomorPlasmaMb" name="buttonNomorPlasmaMb"><i class="cil-pencil"></i></button></span>
                                            </div>
                                        @else
                                            <input type="text" class="form-control" name="nomorTeleponMb" id="nomorTeleponMb" value="{{ $poDoc->notelepon }}" readonly/>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="kontrakContainerMb" name="kontrakContainerMb">
                                    <th style="width: 20%"> STATUS KONTRAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="kontrakMb" id="kontrakMb">
                                    </th>
                                </tr>
                                <tr id="nomorTeleponTSContainerMb" name="nomorTeleponTSContainerMb">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PPL/TS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="select2-multiple form-control" name="nomorTeleponTSMb[]" multiple="multiple" id="nomorTeleponTSMb" style="width: 100%; height: 100%">
                                            @foreach ($nomorTeleponTsSelected as $selected)
                                                @if ($u->jabatan == 'TECHNICAL SUPPORT' || $u->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($selected->jabatan).' : '.$selected->nowa }}" selected>{{ $selected->nama.' | '.getInitials($u->jabatan).' | '.$selected->unit.' | '.$selected->nowa }}</option>
                                                @endif
                                            @endforeach
                                            @foreach ($nomor_telepon_ts as $u)
                                                @if ($u->jabatan == 'TECHNICAL SUPPORT' || $u->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($u->jabatan).' : '.$u->nowa }}">{{ $u->nama.' | '.getInitials($u->jabatan).' | '.$u->unit.' | '.$u->nowa }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </th>
                                </tr>
                                <tr id="jumlahBoxContainerMb" name="jumlahBoxContainerMb">
                                    <th style="width: 20%"> JUMLAH BOX </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="jumlahBoxMb" id="jumlahBoxMb" value="{{ $poDoc->jumlahbox }}" />
                                    </th>
                                </tr>
                                <tr id="boxPlastikContainerMb" name="boxPlastikContainerMb">
                                    <th style="width: 20%"> BOX PLASTIK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="boxPlastikMb" name="boxPlastikMb">
                                            <option value="{{ $poDoc->plastik }}" selected hidden>{{ $poDoc->plastik }}</option>
                                            <option value="PLASTIK">PLASTIK</option>
                                            <option value="KARTON">KARTON</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="perlakuanContainerMb" name="perlakuanContainerMb">
                                    <th style="width: 20%"> PERLAKUAN <br> (SEXING/NON SEXING) </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" id="perlakuanMb" name="perlakuanMb" value="NON SEXING" readonly>
                                    </th>
                                </tr>
                                <tr id="keteranganContainerMb" name="keteranganContainerMb">
                                    <th style="width: 20%"> KETERANGAN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <textarea class="form-control" name="keteranganMb" id="keteranganMb" rows="2">{{ $poDoc->keterangan }}</textarea>
                                    </th>
                                </tr>
                            </table>
                            <table class="table table-sm" id="divFormatDmc" name="divFormatDmc" style="display: none">
                                <tr id="regionContainerDmc" name="regionContainerDmc">
                                    <th style="width: 20%"> NAMA DO </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            @foreach ($region as $r)
                                                <input type="text" class="form-control" id="regionTextDmc" name="regionTextDmc" value="{{ $r->namaregion }}" placeholder="{{ $r->namaregion }}" readonly>
                                            @endforeach
                                        @else
                                            <select class="form-control" id="regionDmc" name="regionDmc">
                                                @if (($jabatan != 'ADMIN LOGISTIK'))
                                                    <option value="" selected hidden>PILIH</option>
                                                @endif
                                                @foreach ($region as $regions)
                                                    <option value="{{$regions->koderegion}}">{{$regions->namaregion}}</option>
                                                @endforeach
                                                <option value="{{ $koderegion }}" selected hidden>{{ $namaregion }}</option>
                                            </select>
                                            <input class="form-control" type="hidden" id="regionTextDmc" name="regionTextDmc" value="{{ $namaregion }}">
                                        @endif
                                    </th>
                                </tr>
                                <tr id="unitContainerDmc" name="unitContainerDmc">
                                    <th style="width: 20%"> UNIT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            <input type="text" class="form-control" id="unitDmc" name="unitDmc" value="{{ $kodeUnit }}" placeholder="{{ $kodeUnit }}" readonly>
                                        @else
                                            <select class="form-control" id="unitDmc" name="unitDmc">
                                                <option value="{{ $poDoc->unit }}" hidden>{{ $poDoc->unit }}</option>
                                                @if (($jabatan == 'ADMIN LOGISTIK'))
                                                    <option value="{{ $kodeUnit }}">{{ $kodeUnit }}</option>
                                                @endif
                                            </select>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="tanggalContainerDmc" name="tanggalContainerDmc">
                                    <th style="width: 20%"> TANGGAL </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input class="form-control" type="date" name="tanggalDmc" id="tanggalDmc" value="{{ $poDoc->tanggal }}">
                                        <input class="form-control" type="hidden" name="hariDmc" id="hariDmc" value="{{ $poDoc->hari }}">
                                    </th>
                                </tr>
                                <tr id="jenisGradeContainerDmc" name="jenisGradeContainerDmc">
                                    <th style="width: 20%"> JENIS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisGradeDmc" name="jenisGradeDmc">
                                            <option value="{{ $poDoc->gradedoc }}" selected hidden>{{ $poDoc->gradedoc }}</option>
                                            <option value="GRADE A">GRADE A</option>
                                            <option value="GRADE BM">GRADE BM</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="jenisVaksinContainerDmc" name="jenisVaksinContainerDmc">
                                    <th style="width: 20%"> VAKSIN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisVaksinDmc" name="jenisVaksinDmc">
                                            <option value="{{ $poDoc->vaksin }}" selected hidden>{{ $poDoc->vaksin }}</option>
                                            <option value="VAKSIN CEVA">VAKSIN CEVA</option>
                                            <option value="VAKSIN MEDION">VAKSIN MEDION</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="buttonCariContainerDmc" name="buttonCariContainerDmc">
                                    <th></th><th></th>
                                    <th>
                                        <button type="button" class="btn btn-primary" style="width: 100%" data-toggle="modal" data-target="#cariDataPeternak">CARI DATA PETERNAK</button>
                                    </th>
                                </tr>
                                <tr id="namaFlokContainerDmc" name="namaFlokContainerDmc">
                                    <th style="width: 20%"> NAMA FLOK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" id="namaFlokDmc" name="namaFlokDmc" value="{{ $poDoc->namapeternak }}" readonly/>
                                    </th>
                                </tr>
                                <tr id="alamatContainerDmc" name="alamatContainerDmc">
                                    <th style="width: 20%"> ALAMAT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <div class="input-group">
                                            <textarea class="form-control" name="alamatDmc" id="alamatDmc" rows="2" readonly>{{ $poDoc->alamatkandang }}</textarea>
                                            <span class="input-group-addon btn btn-primary" id="buttonAlamatPlasmaDmc" name="buttonAlamatPlasmaDmc"><i class="cil-pencil align-middle"></i></span>
                                        </div>
                                    </th>
                                </tr>
                                <tr id="nomorTeleponContainerDmc" name="nomorTeleponContainerDmc">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PETERNAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if (($roles == 'pusat') || ($roles == 'admin'))
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="nomorTeleponDmc" id="nomorTeleponDmc" value="{{ $poDoc->notelepon }}" readonly/><span class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="buttonNomorPlasmaDmc" name="buttonNomorPlasmaDmc"><i class="cil-pencil"></i></button></span>
                                            </div>
                                        @else
                                            <input type="text" class="form-control" name="nomorTeleponDmc" id="nomorTeleponDmc" value="{{ $poDoc->notelepon }}" readonly/>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="kontrakContainerDmc" name="kontrakContainerDmc">
                                    <th style="width: 20%"> STATUS KONTRAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="kontrakDmc" id="kontrakDmc">
                                    </th>
                                </tr>
                                <tr id="nomorTeleponTSContainerDmc" name="nomorTeleponTSContainerDmc">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PPL/TS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="select2-multiple form-control" name="nomorTeleponTSDmc[]" multiple="multiple" id="nomorTeleponTSDmc" style="width: 100%; height: 100%">
                                            @foreach ($nomorTeleponTsSelected as $selected)
                                                @if ($selected->jabatan == 'TECHNICAL SUPPORT' || $selected->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($selected->jabatan).' : '.$selected->nowa }}" selected>{{ $selected->nama.' | '.getInitials($selected->jabatan).' | '.$selected->unit.' | '.$selected->nowa }}</option>
                                                @endif
                                            @endforeach
                                            @foreach ($nomor_telepon_ts as $u)
                                                @if ($u->jabatan == 'TECHNICAL SUPPORT' || $u->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($u->jabatan).' : '.$u->nowa }}">{{ $u->nama.' | '.getInitials($u->jabatan).' | '.$u->unit.' | '.$u->nowa }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </th>
                                </tr>
                                <tr id="popBoxContainerDmc" name="popBoxContainerDmc">
                                    <th style="width: 20%"> POP (BOX) </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="popBoxDmc" id="popBoxDmc" value="{{ $poDoc->jumlahbox }}" />
                                    </th>
                                </tr>
                                <tr id="keteranganContainerDmc" name="keteranganContainerDmc">
                                    <th style="width: 20%"> KETERANGAN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <textarea class="form-control" name="keteranganDmc" id="keteranganDmc" rows="2">{{ $poDoc->keterangan }}</textarea>
                                    </th>
                                </tr>
                            </table>
                            <table class="table table-sm" id="divFormatSreeya" name="divFormatSreeya" style="display: none">
                                <tr id="tanggalContainerSreeya" name="tanggalContainerSreeya">
                                    <th style="width: 20%"> TANGGAL </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input class="form-control" type="date" name="tanggalSreeya" id="tanggalSreeya" value="{{ $poDoc->tanggal }}" />
                                        <input class="form-control" type="hidden" name="hariSreeya" id="hariSreeya" value="{{ $poDoc->hari }}" />
                                    </th>
                                </tr>
                                <tr id="regionContainerSreeya" name="regionContainerSreeya">
                                    <th style="width: 20%"> NAMA DO </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            @foreach ($region as $r)
                                                <input type="text" class="form-control" id="regionTextSreeya" name="regionTextSreeya" value="{{ $r->namaregion }}" placeholder="{{ $r->namaregion }}" readonly>
                                            @endforeach
                                        @else
                                            <select class="form-control" id="regionSreeya" name="regionSreeya">
                                                @if (($jabatan != 'ADMIN LOGISTIK'))
                                                    <option value="">PILIH</option>
                                                @endif
                                                @foreach ($region as $regions)
                                                    <option value="{{$regions->koderegion}}">{{$regions->namaregion}}</option>
                                                @endforeach
                                                <option value="{{ $koderegion }}" selected hidden>{{ $namaregion }}</option>
                                            </select>
                                            <input class="form-control" type="hidden" id="regionTextSreeya" name="regionTextSreeya" value="{{ $namaregion }}">
                                        @endif
                                    </th>
                                </tr>
                                <tr id="unitContainerSreeya" name="unitContainerSreeya">
                                    <th style="width: 20%"> UNIT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if ($jabatan == 'ADMIN LOGISTIK')
                                            <input type="text" class="form-control" id="unitSreeya" name="unitSreeya" value="{{ $kodeUnit }}" placeholder="{{ $kodeUnit }}" readonly>
                                        @else
                                            <select class="form-control" id="unitSreeya" name="unitSreeya">
                                                <option value="{{ $poDoc->unit }}" hidden>{{ $poDoc->unit }}</option>
                                                @if (($jabatan == 'ADMIN LOGISTIK'))
                                                    <option value="{{ $kodeUnit }}">{{ $kodeUnit }}</option>
                                                @endif
                                            </select>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="jenisGradeContainerSreeya" name="jenisGradeContainerSreeya">
                                    <th style="width: 20%"> JENIS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisGradeSreeya" name="jenisGradeSreeya">
                                            <option value="{{ $poDoc->gradedoc }}" selected hidden>{{ $poDoc->gradedoc }}</option>
                                            <option value="GRADE SUPER">GRADE SUPER</option>
                                            <option value="GRADE A">GRADE A</option>
                                            <option value="GRADE BM">GRADE BM</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="jenisVaksinContainerSreeya" name="jenisVaksinContainerSreeya">
                                    <th style="width: 20%"> VAKSIN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="form-control" id="jenisVaksinSreeya" name="jenisVaksinSreeya">
                                            <option value="{{ $poDoc->vaksin }}" selected hidden>{{ $poDoc->vaksin }}</option>
                                            <option value="VAKSIN VECTORMUNE">VAKSIN VECTORMUNE</option>
                                            <option value="VAKSIN TRIPLE">VAKSIN TRIPLE</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr id="buttonCariContainerSreeya" name="buttonCariContainerSreeya">
                                    <th></th><th></th>
                                    <th>
                                        <button type="button" class="btn btn-primary" style="width: 100%" data-toggle="modal" data-target="#cariDataPeternak">CARI DATA PETERNAK</button>
                                    </th>
                                </tr>
                                <tr id="namaFlokContainerSreeya" name="namaFlokContainerSreeya">
                                    <th style="width: 20%"> NAMA FLOK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" id="namaFlokSreeya" name="namaFlokSreeya" value="{{ $poDoc->namapeternak }}" readonly/>
                                    </th>
                                </tr>
                                <tr id="alamatContainerSreeya" name="alamatContainerSreeya">
                                    <th style="width: 20%"> ALAMAT </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <div class="input-group">
                                            <textarea class="form-control" name="alamatSreeya" id="alamatSreeya" rows="2" readonly>{{ $poDoc->alamatkandang }}</textarea>
                                            <span class="input-group-addon btn btn-primary" id="buttonAlamatPlasmaSreeya" name="buttonAlamatPlasmaSreeya"><i class="cil-pencil align-middle"></i></span>
                                        </div>
                                    </th>
                                </tr>
                                <tr id="nomorTeleponContainerSreeya" name="nomorTeleponContainerSreeya">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PETERNAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        @if (($roles == 'pusat') || ($roles == 'admin'))
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="nomorTeleponSreeya" id="nomorTeleponSreeya" value="{{ $poDoc->notelepon }}" readonly/><span class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="buttonNomorPlasmaSreeya" name="buttonNomorPlasmaSreeya"><i class="cil-pencil"></i></button></span>
                                            </div>
                                        @else
                                            <input type="text" class="form-control" name="nomorTeleponSreeya" id="nomorTeleponSreeya" value="{{ $poDoc->notelepon }}" readonly/>
                                        @endif
                                    </th>
                                </tr>
                                <tr id="kontrakContainerSreeya" name="kontrakContainerSreeya">
                                    <th style="width: 20%"> STATUS KONTRAK </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="kontrakSreeya" id="kontrakSreeya">
                                    </th>
                                </tr>
                                <tr id="nomorTeleponTSContainerSreeya" name="nomorTeleponTSContainerSreeya">
                                    <th style="width: 20%"> NOMOR TELEPON <br> PPL/TS </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <select class="select2-multiple form-control" name="nomorTeleponTSSreeya[]" multiple="multiple" id="nomorTeleponTSSreeya" style="width: 100%; height: 100%">
                                            @foreach ($nomorTeleponTsSelected as $selected)
                                                @if ($selected->jabatan == 'TECHNICAL SUPPORT' || $selected->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($selected->jabatan).' : '.$selected->nowa }}" selected>{{ $selected->nama.' | '.getInitials($selected->jabatan).' | '.$selected->unit.' | '.$selected->nowa }}</option>
                                                @endif
                                            @endforeach
                                            @foreach ($nomor_telepon_ts as $u)
                                                @if ($u->jabatan == 'TECHNICAL SUPPORT' || $u->jabatan == 'KEPALA PRODUKSI')
                                                    <option value="{{ getInitials($u->jabatan).' : '.$u->nowa }}">{{ $u->nama.' | '.getInitials($u->jabatan).' | '.$u->unit.' | '.$u->nowa }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </th>
                                </tr>
                                <tr id="popBoxContainerSreeya" name="popBoxContainerSreeya">
                                    <th style="width: 20%"> POP (EKOR) </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <input type="text" class="form-control" name="popBoxSreeya" id="popBoxSreeya" value="{{ $poDoc->jumlahbox }}" />
                                    </th>
                                </tr>
                                <tr id="keteranganContainerSreeya" name="keteranganContainerSreeya">
                                    <th style="width: 20%"> KETERANGAN </th>
                                    <th style="width: 10px"> : </th>
                                    <th>
                                        <textarea class="form-control" name="keteranganSreeya" id="keteranganSreeya" rows="2">{{ $poDoc->keterangan }}</textarea>
                                    </th>
                                </tr>
                            </table>
                            <div class="modal-footer">
                                <a href="{{ url()->previous() }}" class="btn btn-danger mr-2">KEMBALI</a>
                                <button type="submit" class="btn btn-success">SIMPAN</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal sinkron -->
<div class="modal fade" id="cariDataPeternak" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">DATA PETERNAK</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 450px; overflow-x: scroll; overflow-y: scroll">
                    <table class="table table-bordered datatable" id="cariDataPeternakTable">
                        <thead>
                            <tr>
                                <th width="30">NO</th>
                                <th>UNIT</th>
                                <th>NAMA PETERNAK</th>
                                <th>NAMA FLOK</th>
                                <th>ALAMAT</th>
                                <th>NOMOR</th>
                                <th width="150" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('javascript')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script>
$(document).ready(function() {
    var _token = $("input[name='_token']").val();
    var vendor = $('#vendor').val();

    $('#cariDataPeternakTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        pageLength: 10,
        "order": [[ 0, "asc" ]],
        ajax: {
            url: '{{ route('logdoc.poDocPerVendor.dataPeternak') }}',
            type: 'GET',
        },
        columns: [
            { data: 'no_urut', name: 'no_urut'},
            { data: 'nama_unit', name: 'nama_unit'},
            { data: 'nama_plasma', name: 'nama_plasma'},
            { data: 'nama_flok', name: 'nama_flok'},
            {
                data: null,
                name: 'alamat_flok_lengkap',
                render: function (data) {
                    return `${data.alamat_flok}, RT ${data.rt_flok} RW ${data.rw_flok}, ${data.kelurahan_or_desa_flok}, ${data.kecamatan_flok}, ${data.kota_or_kabupaten_flok}, ${data.provinsi_flok}, ${data.kode_pos_flok}`;
                }
            },
            { data: 'nomor_hp', name: 'nomor_hp'},
            {
                data: null,
                name: 'Actions',
                orderable:false,
                serachable:false,
                className:'text-center',
                render: function() {
                    return `<button type="button" class="btn btn-primary action-btn"><i class="cil-check"></i></button>`;
                }
            },
        ]
    });

    $('.select2-multiple').select2({
        placeholder : ' PILIH'
    });

    var regionField, unitField, flokField, kontrakField;

    if (vendor.includes('CP')) {
        regionField = '#regionTextCp';
        unitField = '#unitCp';
        flokField = '#namaPeternakCp';
        kontrakField = '#kontrakCp';
    } else if (vendor.includes('MB')) {
        regionField = '#regionTextMb';
        unitField = '#unitMb';
        flokField = '#namaPeternakMb';
        kontrakField = '#kontrakMb';
    } else if (vendor.includes('DMC')) {
        regionField = '#regionTextDmc';
        unitField = '#unitDmc';
        flokField = '#namaFlokDmc';
        kontrakField = '#kontrakDmc';
    } else if (vendor.includes('SREEYA')) {
        regionField = '#regionTextSreeya';
        unitField = '#unitSreeya';
        flokField = '#namaFlokSreeya';
        kontrakField = '#kontrakSreeya';
    } else {
        regionField = '#regionTextCtu';
        unitField = '#unitCtu';
        flokField = '#namaPeternakCtu';
        kontrakField = '#kontrakCtu';
    }

    $.ajax({
        url: '{{ route('logdoc.poDocPerVendor.periksaKontrak') }}',
        type: 'GET',
        data: {
            _token: _token,
            region: $(regionField).val(),
            unit: $(unitField).val(),
            nama_flok: $(flokField).val(),
        },
        success: function(response) {
            var kontrakFieldElement = $(kontrakField);
            kontrakFieldElement.prop('readonly', false);
            kontrakFieldElement.val(response.status.toUpperCase());
            kontrakFieldElement.prop('readonly', true);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
});

function fillDataPeternak(nama_flok, alamat_flok, nomor_hp, namaField, alamatField, nomorField, unitField, regionField, kontrakField) {
    $.ajax({
        url: '{{ route('logdoc.poDocPerVendor.periksaKontrak') }}',
        type: 'GET',
        data: {
            _token: '{{ csrf_token() }}',
            region: $(regionField).val(),
            unit: $(unitField).val(),
            nama_flok: nama_flok,
        },
        success: function(response) {
            $(namaField).val(nama_flok);
            $(alamatField).val(alamat_flok);
            $(nomorField).val(nomor_hp);
            $(kontrakField).val(response.status.toUpperCase());
            Swal.fire({
                title: 'Berhasil',
                text: 'Data Peternak telah terisi.\nSilakan tutup tabel data peternak.',
                icon: 'success',
                confirmButtonText: 'Confirm'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('[data-dismiss="modal"]').trigger('click');
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Data Peternak gagal diisi.\nSilakan coba lagi.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

$('#cariDataPeternakTable').on('click', '.action-btn', function() {
    var row = $(this).closest('tr');
    var nama_plasma = row.find('td:eq(2)').text();
    var nama_flok = row.find('td:eq(3)').text();
    var alamat_flok = row.find('td:eq(4)').text();
    var nomor_hp = row.find('td:eq(5)').text();
    var vendor = $('#vendor').val();

    if (vendor.includes('CP')) {
        fillDataPeternak(nama_flok, alamat_flok, nomor_hp, '#namaPeternakCp', '#alamatKandangCp', '#nomorTeleponCp', '#unitCp', '#regionTextCp', '#kontrakCp');
    } else if (vendor.includes('MB')) {
        fillDataPeternak(nama_flok, alamat_flok, nomor_hp, '#namaPeternakMb', '#alamatLokasiMb', '#nomorTeleponMb', '#unitMb', '#regionTextMb', '#kontrakMb');
    } else if (vendor.includes('DMC')) {
        fillDataPeternak(nama_flok, alamat_flok, nomor_hp, '#namaFlokDmc', '#alamatDmc', '#nomorTeleponDmc', '#unitDmc', '#regionTextDmc', '#kontrakDmc');
    } else if (vendor.includes('SREEYA')) {
        fillDataPeternak(nama_flok, alamat_flok, nomor_hp, '#namaFlokSreeya', '#alamatSreeya', '#nomorTeleponSreeya', '#unitSreeya', '#regionTextSreeya', '#kontrakSreeya');
    } else {
        fillDataPeternak(nama_flok, alamat_flok, nomor_hp, '#namaPeternakCtu', '#alamatCtu', '#nomorHpCtu', '#unitCtu', '#regionTextCtu', '#kontrakCtu');
    }
});

// Fungsi utilitas untuk toggle readonly
function toggleReadonly(buttonSelector, fieldSelector) {
    $(buttonSelector).click(function () {
        const field = $(fieldSelector);
        field.attr('readonly', !field.is('[readonly]'));
    });
}

// BUTTON FLOK CONTROL CP
toggleReadonly('#buttonAlamatPlasmaCp', '#alamatKandangCp');
toggleReadonly('#buttonNomorPlasmaCp', '#nomorTeleponCp');

// BUTTON FLOK CONTROL CTU
toggleReadonly('#buttonNomorPlasmaCtu', '#nomorHpCtu');
toggleReadonly('#buttonAlamatPlasmaCtu', '#alamatCtu');

// BUTTON FLOK CONTROL MB
toggleReadonly('#buttonNomorPlasmaMb', '#nomorTeleponMb');
toggleReadonly('#buttonAlamatPlasmaMb', '#alamatLokasiMb');

// BUTTON FLOK CONTROL DMC
toggleReadonly('#buttonNomorPlasmaDmc', '#nomorTeleponDmc');
toggleReadonly('#buttonAlamatPlasmaDmc', '#alamatDmc');

// BUTTON FLOK CONTROL SREEYA
toggleReadonly('#buttonNomorPlasmaSreeya', '#nomorTeleponSreeya');
toggleReadonly('#buttonAlamatPlasmaSreeya', '#alamatSreeya');

$(function () {
    const vendor = $('#vendor').val();

    const showDivs = (showId) => {
        const allDivs = ['#divFormatCp', '#divFormatCtu', '#divFormatMb', '#divFormatDmc', '#divFormatSreeya'];
        allDivs.forEach((div) => $(div).hide());
        $(showId).show();
    };

    const setFieldsRequired = (fields, isRequired = true) => {
        fields.forEach((field) => $(field).prop('required', isRequired));
    };

    const createSelectElement = (options, attributes = {}) => {
        const select = $('<select></select>').addClass('form-control');
        Object.entries(attributes).forEach(([key, value]) => select.attr(key, value));

        options.forEach(option => {
            select.append(`<option value="${option.value}" ${option.selected ? 'selected hidden' : ''}>${option.text}</option>`);
        });

        return select;
    };

    const createInputElement = (value, attributes = {}) => {
        const input = $('<input>').addClass('form-control').val(value);
        Object.entries(attributes).forEach(([key, value]) => input.attr(key, value));

        return input;
    };

    const handleVendorCtu = (vendor) => {
        const containerVaksinCtu = $('#jenisVaksinContainerCtu');
        const containerKetCtu = $('#keteranganContainerCtu');

        if (vendor.includes('CTU') || vendor.includes('SPC') || vendor.includes('MLD')) {
            const selectElement = createSelectElement([
                { value: '{{ $poDoc->vaksin }}', text: '{{ $poDoc->vaksin }}', selected: true },
                { value: 'VAKSIN VECTORMUNE', text: 'VAKSIN VECTORMUNE' },
                { value: 'VAKSIN TRIPLE', text: 'VAKSIN TRIPLE' },
            ], { id: 'jenisVaksinCtu', name: 'jenisVaksinCtu', required: true });

            containerVaksinCtu.find('th:last-child').html(selectElement);
            containerKetCtu.find('th:first-child').html('KETERANGAN');
        } else if (vendor.includes('MANGGIS')) {
            const inputElement = createInputElement('VAKSIN VECTORMUNE', { id: 'jenisVaksinCtu', name: 'jenisVaksinCtu', required: true, readonly: true });

            containerVaksinCtu.find('th:last-child').html(inputElement);
            containerKetCtu.find('th:first-child').html('KETERANGAN');
        } else {
            const inputElement = createInputElement('VAKSIN', { id: 'jenisVaksinCtu', name: 'jenisVaksinCtu', required: true, readonly: true });

            containerVaksinCtu.find('th:last-child').html(inputElement);
            containerKetCtu.find('th:first-child').html('KETERANGAN<br>(NAMA VENDOR)');
        }
    };

    const fields = {
        cp: ['#tanggalCp', '#regionTextCp', '#unitCp', '#jenisGradeCp', '#namaPeternakCp', '#alamatKandangCp', '#nomorTeleponCp', '#nomorTeleponTSCp', '#plastikVaccCp', '#feedGelCp'],
        mb: ['#tanggalMb', '#hariMb', '#regionTextMb', '#unitMb', '#jenisGradeMb', '#jenisVaksinMb', '#namaPeternakMb', '#alamatLokasiMb', '#nomorTeleponMb', '#nomorTeleponTSMb', '#jumlahBoxMb', '#boxPlastikMb'],
        dmc: ['#regionTextDmc', '#unitDmc', '#tanggalDmc', '#jenisGradeDmc', '#jenisVaksinDmc', '#namaFlokDmc', '#alamatDmc', '#nomorTeleponDmc', '#nomorTeleponTSDmc', '#popBoxDmc'],
        sreeya: ['#tanggalSreeya', '#regionTextSreeya', '#unitSreeya', '#jenisGradeSreeya', '#jenisVaksinSreeya', '#namaFlokSreeya', '#alamatSreeya', '#nomorTeleponSreeya', '#nomorTeleponTSSreeya', '#popBoxSreeya'],
        ctu: ['#regionTextCtu', '#unitCtu', '#tanggalCtu', '#jenisGradeCtu', '#jenisVaksinCtu', '#namaPeternakCtu', '#alamatCtu', '#nomorHpCtu', '#nomorTeleponTSCtu', '#popBoxCtu'],
    };

    // Handle specific vendor logic
    if (vendor.includes('CP')) {
        showDivs('#divFormatCp');
        setFieldsRequired(fields.cp);
    } else if (vendor.includes('MB')) {
        showDivs('#divFormatMb');
        setFieldsRequired(fields.mb);
        $('#hariContainerMb').show();
        var date = getDayOfWeek($('#tanggalMb').val());
        $('#hariMb').val(date)
    } else if (vendor.includes('DMC')) {
        showDivs('#divFormatDmc');
        setFieldsRequired(fields.dmc);
    } else if (vendor.includes('SREEYA')) {
        showDivs('#divFormatSreeya');
        setFieldsRequired(fields.sreeya);
    } else {
        showDivs('#divFormatCtu');
        setFieldsRequired(fields.ctu);
        handleVendorCtu(vendor);
    }
});

$('select[name="vendor"]').change(function(){
    const vendor = $('#vendor').val();

    const showDivs = (showId) => {
        const allDivs = ['#divFormatCp', '#divFormatCtu', '#divFormatMb', '#divFormatDmc', '#divFormatSreeya'];
        allDivs.forEach((div) => $(div).hide());
        $(showId).show();
    };

    const setFieldsRequired = (fields, isRequired = true) => {
        fields.forEach((field) => $(field).prop('required', isRequired));
    };

    const createSelectElement = (options, attributes = {}) => {
        const select = $('<select></select>').addClass('form-control');
        Object.entries(attributes).forEach(([key, value]) => select.attr(key, value));

        options.forEach(option => {
            select.append(`<option value="${option.value}" ${option.selected ? 'selected hidden' : ''}>${option.text}</option>`);
        });

        return select;
    };

    const createInputElement = (value, attributes = {}) => {
        const input = $('<input>').addClass('form-control').val(value);
        Object.entries(attributes).forEach(([key, value]) => input.attr(key, value));

        return input;
    };

    const handleVendorCtu = (vendor) => {
        const containerGradeCtu = $('#jenisGradeContainerCtu');
        const containerVaksinCtu = $('#jenisVaksinContainerCtu');
        const containerKetCtu = $('#keteranganContainerCtu');

        containerGradeCtu.find('th:last-child').empty();
        containerVaksinCtu.find('th:last-child').empty();
        containerKetCtu.find('th:first-child').empty();

        if (vendor.includes('CTU') || vendor.includes('SPC') || vendor.includes('MLD')) {
            const selectElementGrade = createSelectElement([
                { value: '', text: 'PILIH', selected: true },
                { value: 'GRADE A', text: 'GRADE A' },
                { value: 'GRADE BM', text: 'GRADE BM' },
            ], { id: 'jenisGradeCtu', name: 'jenisGradeCtu', required: true });
            const selectElement = createSelectElement([
                { value: '', text: 'PILIH', selected: true },
                { value: 'VAKSIN VECTORMUNE', text: 'VAKSIN VECTORMUNE' },
                { value: 'VAKSIN TRIPLE', text: 'VAKSIN TRIPLE' },
            ], { id: 'jenisVaksinCtu', name: 'jenisVaksinCtu', required: true });

            containerGradeCtu.find('th:last-child').html(selectElementGrade);
            containerVaksinCtu.find('th:last-child').html(selectElement);
            containerKetCtu.find('th:first-child').html('KETERANGAN');
        } else if (vendor.includes('MANGGIS')) {
            const inputElement = createInputElement('VAKSIN VECTORMUNE', { id: 'jenisVaksinCtu', name: 'jenisVaksinCtu', required: true, readonly: true });

            containerVaksinCtu.find('th:last-child').html(inputElement);
            containerKetCtu.find('th:first-child').html('KETERANGAN');
        } else {
            const inputElement = createInputElement('VAKSIN', { id: 'jenisVaksinCtu', name: 'jenisVaksinCtu', required: true, readonly: true });

            containerVaksinCtu.find('th:last-child').html(inputElement);
            containerKetCtu.find('th:first-child').html('KETERANGAN<br>(NAMA VENDOR)');
        }
    };

    const fields = {
        cp: ['#tanggalCp', '#regionTextCp', '#unitCp', '#jenisGradeCp', '#namaPeternakCp', '#alamatKandangCp', '#nomorTeleponCp', '#nomorTeleponTSCp', '#plastikVaccCp', '#feedGelCp'],
        mb: ['#tanggalMb', '#hariMb', '#regionTextMb', '#unitMb', '#jenisGradeMb', '#jenisVaksinMb', '#namaPeternakMb', '#alamatLokasiMb', '#nomorTeleponMb', '#nomorTeleponTSMb', '#jumlahBoxMb', '#boxPlastikMb'],
        dmc: ['#regionTextDmc', '#unitDmc', '#tanggalDmc', '#jenisGradeDmc', '#jenisVaksinDmc', '#namaFlokDmc', '#alamatDmc', '#nomorTeleponDmc', '#nomorTeleponTSDmc', '#popBoxDmc'],
        sreeya: ['#tanggalSreeya', '#regionTextSreeya', '#unitSreeya', '#jenisGradeSreeya', '#jenisVaksinSreeya', '#namaFlokSreeya', '#alamatSreeya', '#nomorTeleponSreeya', '#nomorTeleponTSSreeya', '#popBoxSreeya'],
        ctu: ['#regionTextCtu', '#unitCtu', '#tanggalCtu', '#jenisGradeCtu', '#jenisVaksinCtu', '#namaPeternakCtu', '#alamatCtu', '#nomorHpCtu', '#nomorTeleponTSCtu', '#popBoxCtu'],
    };

    // Handle specific vendor logic
    if (vendor.includes('CP')) {
        showDivs('#divFormatCp');
        setFieldsRequired(fields.cp);
    } else if (vendor.includes('MB')) {
        showDivs('#divFormatMb');
        setFieldsRequired(fields.mb);
        $('#hariContainerMb').show();
        var date = getDayOfWeek($('#tanggalMb').val());
        $('#hariMb').val(date)
    } else if (vendor.includes('DMC')) {
        showDivs('#divFormatDmc');
        setFieldsRequired(fields.dmc);
    } else if (vendor.includes('SREEYA')) {
        showDivs('#divFormatSreeya');
        setFieldsRequired(fields.sreeya);
    } else {
        showDivs('#divFormatCtu');
        setFieldsRequired(fields.ctu);
        handleVendorCtu(vendor);
    }
});

$('#hariContainerMb').hide();

function getDayOfWeek(date) {
    const dayOfWeek = new Date(date).getDay();
    return isNaN(dayOfWeek) ? null : ["MINGGU", "SENIN", "SELASA", "RABU", "KAMIS", "JUM'AT", "SABTU"][dayOfWeek];
}

$('#tanggalCp').change(function(){
    var date = getDayOfWeek($('#tanggalCp').val());
    $('#hariCp').val(date)
});

$('#tanggalCtu').change(function(){
    var date = getDayOfWeek($('#tanggalCtu').val());
    $('#hariCtu').val(date)
});

$('#tanggalMb').change(function(){
    $('#hariContainerMb').show();
    var date = getDayOfWeek($('#tanggalMb').val());
    $('#hariMb').val(date)
});

$('#tanggalDmc').change(function(){
    var date = getDayOfWeek($('#tanggalDmc').val());
    $('#hariDmc').val(date)
});

$('#tanggalSreeya').change(function(){
    var date = getDayOfWeek($('#tanggalSreeya').val());
    $('#hariSreeya').val(date)
});

if ('{{ $roles }}' !== 'user') {
    const setupRegionListener = (regionSelector, unitSelector, regionTextSelector) => {
        $(regionSelector).change(function () {
            const region = $(this).val();
            const regionText = $(this).find(":selected").text();
            $(regionTextSelector).val(regionText);

            const jabatan = "{{ $jabatan }}";
            const kodeUnit = "{{ $kodeUnit }}";

            if (region) {
                $.ajax({
                    type: "GET",
                    url: `/user/${region}`,
                    dataType: "JSON",
                    success: function (res) {
                        const unitDropdown = $(unitSelector);
                        unitDropdown.empty();
                        unitDropdown.append('<option value="" selected hidden>PILIH</option>');

                        if (res) {
                            if (jabatan === "ADMIN LOGISTIK") {
                                unitDropdown.append(`<option value="${kodeUnit}">${kodeUnit}</option>`);
                            } else {
                                $.each(res, function (namaunit, kodeunit) {
                                    unitDropdown.append(`<option value="${kodeunit}">${kodeunit}</option>`);
                                });
                            }
                        }
                    },
                });
            } else {
                $(unitSelector).empty().append('<option value="" selected hidden>PILIH</option>');
            }
        });
    };

    // Daftar region dan unit yang akan diatur
    const regionMappings = [
        { region: 'select[name="regionCp"]', unit: 'select[name="unitCp"]', regionText: '#regionTextCp' },
        { region: 'select[name="regionCtu"]', unit: 'select[name="unitCtu"]', regionText: '#regionTextCtu' },
        { region: 'select[name="regionMb"]', unit: 'select[name="unitMb"]', regionText: '#regionTextMb' },
        { region: 'select[name="regionDmc"]', unit: 'select[name="unitDmc"]', regionText: '#regionTextDmc' },
        { region: 'select[name="regionSreeya"]', unit: 'select[name="unitSreeya"]', regionText: '#regionTextSreeya' },
    ];

    // Pasang event listener untuk setiap region
    regionMappings.forEach(({ region, unit, regionText }) => {
        setupRegionListener(region, unit, regionText);
    });
}
</script>
@endsection

