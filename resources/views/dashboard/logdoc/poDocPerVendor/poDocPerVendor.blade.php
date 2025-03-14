@extends('dashboard.base')

@section('title', 'PO DOC')

@section('content')
    <div id="session-data" data-region="{{ $region }}" data-unit="{{ $unit }}" data-roles="{{ $roles }}" data-jabatan="{{ $jabatan }}" data-nik="{{ $nik }}"></div>
    <input type="hidden" id="csrf-token" value="{{ csrf_token() }}">
    <div class="container-fluid">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>
                                <i class="cil-description"></i>{{ __('PO DOC') }}
                                <button type="button" class="btn btn-primary float-lg-right" onclick="window.location='{{ url('logdoc') }}'">KEMBALI</button>
                                @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                    <a class="btn btn-success float-lg-right mr-3" href="{{route('logdoc.poDocPerVendor.create')}}" style="margin-right: 10px">TAMBAH</a>
                                    @if ($roles == 'admin' || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || $jabatan == 'STAFF OPERASIONAL')
                                        <button type="button" class="btn btn-danger float-lg-right mr-3" data-toggle="modal" data-target="#hapus">HAPUS</button>
                                        <button type="button" class="btn btn-warning float-lg-right mr-3" data-toggle="modal" data-target="#masterModal">MASTER</button>
                                    @endif
                                    <button type="button" class="btn btn-danger float-lg-right mr-3" id="template">TEMPLATE SURAT</button>
                                    @if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK')
                                        <button type="button" class="btn btn-warning float-lg-right mr-3" data-toggle="modal" data-target="#sinkronModal">SINKRON</button>
                                    @endif
                                    @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || $jabatan == 'STAFF OPERASIONAL')
                                        <a class="btn btn-success float-lg-right mr-3" href="{{route('logdoc.poDocPerVendor.arsip')}}" style="margin-right: 10px">ARSIP</a>
                                    @endif
                                @endif
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-8">
                                    <form class="row d-flex justify-content-start form-inline" action="{{ route('logdoc.poDocPerVendor') }}" method="get" style="margin-top:10px; margin-bottom: 10px">@csrf
                                        <div class="form-group col-md-12 mb-2">
                                            <div class="d-inline-flex col-md-1 col-form-label">
                                                <label class="form-label mr-3" for="tanggalAwalFilter">TANGGAL</label>
                                            </div>
                                            @if (!empty($tanggalAwalFilter))
                                                <input class="form-control" type="date" name="tanggalAwalFilter" id="tanggalAwalFilter" value="{{ $tanggalAwalFilter }}">
                                            @else
                                                <input class="form-control" type="date" name="tanggalAwalFilter" id="tanggalAwalFilter">
                                            @endif
                                            <label class="my-3 mr-3 ml-3" for="tanggalAkhirFilter">SAMPAI</label>
                                            @if (!empty($tanggalAkhirFilter))
                                                <input class="form-control" type="date" name="tanggalAkhirFilter" id="tanggalAkhirFilter" value="{{ $tanggalAkhirFilter }}">
                                            @else
                                                <input class="form-control" type="date" name="tanggalAkhirFilter" id="tanggalAkhirFilter">
                                            @endif
                                        </div>
                                        <div class="form-group col-md-12 mb-2">
                                            <div class="d-inline-flex col-md-1 col-form-label">
                                                <label class="form-label mr-3" for="vendorFilter">VENDOR</label>
                                            </div>
                                            <select class="form-control" name="vendorFilter" id="vendorFilter" required>
                                            <option value="" hidden>PILIH</option>
                                                @if (!empty($vendorFilter) || $vendorFilter == 'SEMUA')
                                                    <option value="{{ $vendorFilter }}" hidden selected>{{ $vendorFilter }}</option>
                                                @endif
                                                <option value="SEMUA">SEMUA</option>
                                                @foreach ($vendorDoc as $v)
                                                    <option value="{{ $v->kode_vendor }}">{{ $v->kode_vendor }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                            <div class="form-group col-md-12 mb-2">
                                                <div class="d-inline-flex col-md-1 col-form-label">
                                                    <label class="form-label mr-3" for="regionFilter">AP</label>
                                                </div>
                                                @if ($roles == 'sr' || $jabatan == 'ADMIN LOGISTIK')
                                                    <input type="text" class="form-control" name="regionFilter" id="regionFilter" value="{{ $reg }}" readonly>
                                                @else
                                                    <select class="select2-multiple form-control" multiple="multiple" name="regionFilter[]" id="regionFilter[]" required>
                                                        <option value="SEMUA">SEMUA</option>
                                                        @foreach ($regions as $item)
                                                            <option value="{{ $item->namaregion }}">{{ $item->namaregion }}</option>
                                                        @endforeach
                                                        @if (!empty($regionFilter) || $regionFilter == 'SEMUA')
                                                            @foreach ($regionFilter as $region)
                                                                <option value="{{ $region }}" selected hidden>{{ $region }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @endif
                                            </div>
                                            <div class="form-group col-md-12 mb-2">
                                                <div class="d-inline-flex col-md-1 col-form-label">
                                                    <label class="form-label mr-3" for="unitFilter">UNIT</label>
                                                </div>
                                                @if ($jabatan == 'ADMIN LOGISTIK')
                                                    <input type="text" class="form-control" name="unitFilter" id="unitFilter" value="{{ $unit }}" readonly>
                                                @else
                                                    <select class="form-control" name="unitFilter" id="unitFilter">
                                                        @if (!empty($unitFilter) && $unitFilter != 'SEMUA')
                                                            <option value="{{ $unitFilter }}" selected hidden>{{ !empty($unitFilter) ? $unitFilter.' - '.kodeUnitArca($unitFilter) : $unitFilter }}</option>
                                                        @endif
                                                        <option value="SEMUA">SEMUA</option>
                                                        @if (!empty($unitSelect) || $roles == 'sr')
                                                            @foreach ($unitSelect as $namaunit => $kodeunit)
                                                                <option value="{{ $kodeunit }}">{{ $kodeunit.' - '.kodeUnitArca($kodeunit) }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="form-group col-md-12 mb-2">
                                            <div class="col-md-1"></div>
                                            <button class="btn btn-primary mr-3" type="submit">{{ __('TAMPIL') }}</button>
                                            @if (!empty($vendorFilter))
                                                @if (strpos($vendorFilter, 'CP') !== false)
                                                    @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                        <button class="mr-3 btn btn-success" type="button" id="exportFormatCp" name="exportFormatCp">EXPORT</button>
                                                        <button class="mr-3 btn btn-warning" type="button" id="exportTxtFormatCp" name="exportTxtFormatCp">EXPORT NOTEPAD</button>
                                                    @endif
                                                @elseif (strpos($vendorFilter, 'MB') !== false)
                                                    @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                        <button class="mr-3 btn btn-success" type="button" id="exportFormatMb" name="exportFormatMb">EXPORT</button>
                                                        <button class="mr-3 btn btn-warning" type="button" id="exportTxtFormatMb" name="exportTxtFormatMb">EXPORT NOTEPAD</button>
                                                    @endif
                                                @elseif (strpos($vendorFilter, 'DMC') !== false)
                                                    @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                        <button class="mr-3 btn btn-success" type="button" id="exportFormatDmc" name="exportFormatDmc">EXPORT</button>
                                                        <button class="mr-3 btn btn-warning" type="button" id="exportTxtFormatDmc" name="exportTxtFormatDmc">EXPORT NOTEPAD</button>
                                                    @endif
                                                @elseif (strpos($vendorFilter, 'SREEYA') !== false)
                                                    @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                        <button class="mr-3 btn btn-success" type="button" id="exportFormatSreeya" name="exportFormatSreeya">EXPORT</button>
                                                        <button class="mr-3 btn btn-warning" type="button" id="exportTxtFormatSreeya" name="exportTxtFormatSreeya">EXPORT NOTEPAD</button>
                                                    @endif
                                                @else
                                                    @if (strpos($vendorFilter, 'SEMUA') !== false)
                                                        @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                            <button class="mr-3 btn btn-success" type="button" id="export" name="export">EXPORT</button>
                                                        @endif
                                                    @else
                                                        @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                            <button class="mr-3 btn btn-success" type="button" id="exportFormatCtu" name="exportFormatCtu">EXPORT</button>
                                                            <button class="mr-3 btn btn-warning" type="button" id="exportTxtFormatCtu" name="exportTxtFormatCtu">EXPORT NOTEPAD</button>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endif
                                        </div>
                                    </form>
                                    <div class="card-body">
                                        @if (!empty($vendorFilter))
                                            @if (strpos($vendorFilter, 'CP') !== false)
                                                <input type="hidden" id="tableTitleCp" name="tableTitleCp" value="{{ 'REKAP PO DOC '.$vendorFilter }}">
                                                <table class="table table-responsive table-hover table-sm table-bordered" id="tableFormatCp" name="tableFormatCp" style="max-height: 500px; overflow-x: scroll; overflow-y: scroll">
                                                    <thead>
                                                        @if ($vendorFilter == 'CP JBR')
                                                            <tr>
                                                                <th colspan="26" style="background-color: #ffffff; border: 1px solid #000000; font-weight: bold; text-align: center">{{ 'REKAP PO DOC '.$vendorFilter }}</th>
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <th colspan="25" style="background-color: #ffffff; border: 1px solid #000000; font-weight: bold; text-align: center">{{ 'REKAP PO DOC '.$vendorFilter }}</th>
                                                            </tr>
                                                        @endif
                                                        <tr>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; width:30px; text-align: center"> NO </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HARI </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TANGGAL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NAMA DO </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> UNIT </th>
                                                            @if ($vendorFilter == 'CP JBR')
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KODE CUSTOMER </th>
                                                            @endif
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center; position: sticky; left: 0px"> NAMA PETERNAK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALAMAT KANDANG </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NOMOR TELEPON <br> PTRNK / TS / PPL </th>
                                                            <th colspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PLASTIK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center; min-width: 130px"> FEED GEL <br> NON FEED GEL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KETERANGAN </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GRADE FLOK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> RING </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> DENSITY </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS KANDANG </th>
                                                            <th colspan="3" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PRASYARAT </th>
                                                            <th colspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERALATAN CH </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HASIL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> STATUS <br> PENGIRIMAN </th>
                                                            @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                <th class="noExl" rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AKSI </th>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> VACC </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NON </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERKANDANGAN </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KELISTRIKAN/<br>DIESEL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TOTAL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALARM </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GENSET </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS', "JUM'AT", 'SABTU', 'MINGGU'];
                                                            $totals = [
                                                                'SENIN' => $totalSenin ?? null,
                                                                'SELASA' => $totalSelasa ?? null,
                                                                'RABU' => $totalRabu ?? null,
                                                                'KAMIS' => $totalKamis ?? null,
                                                                "JUM'AT" => $totalJumat ?? null,
                                                                'SABTU' => $totalSabtu ?? null,
                                                                'MINGGU' => $totalMinggu ?? null
                                                            ];
                                                        @endphp

                                                        @foreach($days as $day)
                                                            @foreach($poDocFilter as $data)
                                                                @if ($data->hari == $day)
                                                                    @php
                                                                        $prasyarat = flokToPrasyarat($data->unit, $data->namapeternak);
                                                                        $grade = flokToGrade($data->unit, $data->namapeternak);
                                                                        $ring = unitToRing($data->unit, $data->alamatkandang);
                                                                        $density = flokToDensity($data->unit, $data->namapeternak, strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox / 100 : $data->jumlahbox);
                                                                        $peralatanCH = flokToPeralatanCH($data->unit, $data->namapeternak);
                                                                        $peralatanCHAlarm = $peralatanCH['alarm'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $peralatanCHGenset = $peralatanCH['genset'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $hasil = flokToHasilPoDoc($grade, $prasyarat['jenis_kandang'], $ring, $prasyarat['total_score'], ($peralatanCH['alarm'] == 'Ada' ? 'Ada' : 'Tidak Ada'), ($peralatanCH['genset'] == 'Ada' ? 'Ada' : 'Tidak Ada'));
                                                                        $hasilTidakLolos = in_array($hasil, ['REKOMENDASI KANIT', 'REKOMENDASI DIREKTUR', 'PERJANJIAN GANTI RUGI']);
                                                                    @endphp
                                                                    <tr>
                                                                        <td style="border: 1px solid #000000;">{{ $no++ }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->hari }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ date("d-m-Y", strtotime($data->tanggal)) }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->namado }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->unit }}</td>
                                                                        @if ($vendorFilter == 'CP JBR')
                                                                            <td style="border: 1px solid #000000;">{{ $data->kodecustomer }}</td>
                                                                        @endif
                                                                        <td style="border: 1px solid #000000; background-color: #ffffff; position: sticky; left: 0px">{{ $data->namapeternak }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->alamatkandang }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ !is_null($data->noteleponppl) && $data->noteleponppl !== 'null' ? 'PETERNAK : '.$data->notelepon.', '.implode(', ', json_decode($data->noteleponppl)) : $data->notelepon }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->jumlahbox }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->nonvacc }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->feedgel }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->gradedoc }}{{ empty($data->keterangan) ? '' : ' | '.$data->keterangan }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $grade }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $ring }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $density }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $prasyarat['jenis_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['nilai_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['kelistrikan'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['total_score'] }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHAlarm }}</span></td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHGenset }}</span></td>
                                                                        <td style="border: 1px solid #000000;">
                                                                            <b class="{{ $hasilTidakLolos ? 'text-danger' : '' }}">{{ $hasil }}</b>
                                                                            @if ($hasilTidakLolos)
                                                                                <br>
                                                                                @if (!empty($data->filebukti))
                                                                                    <a href="{{ asset('bukti-rekomendasi/' . $data->filebukti) }}" class="btn btn-info my-2" download>DOWNLOAD</a>
                                                                                    <br>
                                                                                @endif
                                                                                <button class="btn btn-warning openModal" data-vendor="{{ $data->namavendor }}" data-tanggal="{{ $data->tanggal }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}" data-toggle="modal" data-target="#uploadModal">UPLOAD</button>
                                                                            @endif
                                                                        </td>
                                                                        <td style="border: 1px solid #000000; text-align: center">
                                                                            @if (empty($data->statuspengiriman))
                                                                                <button class="btn btn-danger ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ 'BELUM DIKIRIM' }}</button>
                                                                            @else
                                                                                <button class="btn btn-{{ $data->statuspengiriman == 'DIKIRIM' ? 'success' : 'danger' }} ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ $data->statuspengiriman }}</button>
                                                                            @endif
                                                                        </td>
                                                                        @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                            <td class="noExl" style="border: 1px solid #000000;">
                                                                                <form action="{{ route('logdoc.poDocPerVendor.delete', $data->id) }}" method="post">
                                                                                    @csrf
                                                                                    <a href="{{ route('logdoc.poDocPerVendor.edit', [$data->id]) }}" class="btn btn-info"><i class="cil-pencil"></i></a>
                                                                                    <button class="btn btn-danger" onClick="return confirm('Yakin ingin hapus Purchase Order {{ $data->namapeternak.' '.date("d-m-Y", strtotime($data->tanggal)) }}?')"><i class="cil-trash"></i></button>
                                                                                </form>
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                            @if (!empty($totals[$day]))
                                                                <tr>
                                                                    @if ($vendorFilter == 'CP JBR')
                                                                        <td colspan="9" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL {{ strtoupper($day) }}</td>
                                                                    @else
                                                                        <td colspan="8" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL {{ strtoupper($day) }}</td>
                                                                    @endif
                                                                    <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totals[$day] }}</td>
                                                                    <td colspan="16" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                                </tr>
                                                            @endif
                                                        @endforeach

                                                        @if (!empty($totalSetting))
                                                            <tr>
                                                                @if ($vendorFilter == 'CP JBR')
                                                                    <td colspan="9" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL SETTING</td>
                                                                @else
                                                                    <td colspan="8" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL SETTING</td>
                                                                @endif
                                                                <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totalSetting }}</td>
                                                                <td colspan="16" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            @elseif (strpos($vendorFilter, 'MB') !== false)
                                                <input type="hidden" id="tableTitleMb" name="tableTitleMb" value="{{ 'REKAP PO DOC '.$vendorFilter }}">
                                                <table class="table table-responsive table-hover table-sm table-bordered" id="tableFormatMb" name="tableFormatMb" style="max-height: 500px; overflow-x: scroll; overflow-y: scroll">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="27" style="background-color: #ffffff; border: 1px solid #000000; font-weight: bold; text-align: center">{{ 'REKAP PO DOC '.$vendorFilter }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; width:30px; text-align: center"> NO </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HARI </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TANGGAL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NAMA DO </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> UNIT </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center; max-width: 200px; position: sticky; left: 0px"> NAMA PETERNAK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALAMAT LOKASI </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NO TLP <br> (MINIMAL 2 NO YANG BISA DIHUBUNGI) </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JUMLAH BOX </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> BOX PLASTIK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS GRADE </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS PAKET VAKSIN </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERLAKUAN <br> (SEXING/NON SEXING) </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KETERANGAN </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GRADE FLOK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> RING </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> DENSITY </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS KANDANG </th>
                                                            <th colspan="3" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PRASYARAT </th>
                                                            <th colspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERALATAN CH </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HASIL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> STATUS <br> PENGIRIMAN </th>
                                                            @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                <th class="noExl" rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AKSI </th>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERKANDANGAN </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KELISTRIKAN/<br>DIESEL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TOTAL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALARM </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GENSET </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS', "JUM'AT", 'SABTU', 'MINGGU'];
                                                            $totals = [
                                                                'SENIN' => $totalSenin ?? null,
                                                                'SELASA' => $totalSelasa ?? null,
                                                                'RABU' => $totalRabu ?? null,
                                                                'KAMIS' => $totalKamis ?? null,
                                                                "JUM'AT" => $totalJumat ?? null,
                                                                'SABTU' => $totalSabtu ?? null,
                                                                'MINGGU' => $totalMinggu ?? null
                                                            ];
                                                        @endphp

                                                        @foreach($days as $day)
                                                            @foreach($poDocFilter as $data)
                                                                @if ($data->hari == $day)
                                                                    @php
                                                                        $prasyarat = flokToPrasyarat($data->unit, $data->namapeternak);
                                                                        $grade = flokToGrade($data->unit, $data->namapeternak);
                                                                        $ring = unitToRing($data->unit, $data->alamatkandang);
                                                                        $density = flokToDensity($data->unit, $data->namapeternak, strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox / 100 : $data->jumlahbox);
                                                                        $peralatanCH = flokToPeralatanCH($data->unit, $data->namapeternak);
                                                                        $peralatanCHAlarm = $peralatanCH['alarm'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $peralatanCHGenset = $peralatanCH['genset'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $hasil = flokToHasilPoDoc($grade, $prasyarat['jenis_kandang'], $ring, $prasyarat['total_score'], ($peralatanCH['alarm'] == 'Ada' ? 'Ada' : 'Tidak Ada'), ($peralatanCH['genset'] == 'Ada' ? 'Ada' : 'Tidak Ada'));
                                                                        $hasilTidakLolos = in_array($hasil, ['REKOMENDASI KANIT', 'REKOMENDASI DIREKTUR', 'PERJANJIAN GANTI RUGI']);
                                                                    @endphp
                                                                    <tr>
                                                                        <td style="border: 1px solid #000000;">{{ $no++ }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->hari }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ date("d-m-Y", strtotime($data->tanggal)) }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->namado }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->unit }}</td>
                                                                        <td style="border: 1px solid #000000; background-color: #ffffff; position: sticky; left: 0px">{{ $data->namapeternak }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->alamatkandang }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ !is_null($data->noteleponppl) && $data->noteleponppl !== 'null' ? 'PETERNAK : '.$data->notelepon.', '.implode(', ', json_decode($data->noteleponppl)) : $data->notelepon }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->jumlahbox }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->plastik }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->gradedoc }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->vaksin }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->perlakuan }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->keterangan }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $grade }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $ring }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $density }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $prasyarat['jenis_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['nilai_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['kelistrikan'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['total_score'] }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHAlarm }}</span></td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHGenset }}</span></td>
                                                                        <td style="border: 1px solid #000000;">
                                                                            <b class="{{ $hasilTidakLolos ? 'text-danger' : '' }}">{{ $hasil }}</b>
                                                                            @if ($hasilTidakLolos)
                                                                                <br>
                                                                                @if (!empty($data->filebukti))
                                                                                    <a href="{{ asset('bukti-rekomendasi/' . $data->filebukti) }}" class="btn btn-info my-2" download>DOWNLOAD</a>
                                                                                    <br>
                                                                                @endif
                                                                                <button class="btn btn-warning openModal" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}" data-toggle="modal" data-target="#uploadModal">UPLOAD</button>
                                                                            @endif
                                                                        </td>
                                                                        <td style="border: 1px solid #000000; text-align: center">
                                                                            @if (empty($data->statuspengiriman))
                                                                                <button class="btn btn-danger ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ 'BELUM DIKIRIM' }}</button>
                                                                            @else
                                                                                <button class="btn btn-{{ $data->statuspengiriman == 'DIKIRIM' ? 'success' : 'danger' }} ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ $data->statuspengiriman }}</button>
                                                                            @endif
                                                                        </td>
                                                                        @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                            <td style="border: 1px solid #000000;" class="noExl">
                                                                                <form action="{{ route('logdoc.poDocPerVendor.delete', $data->id) }}" method="post">
                                                                                    @csrf
                                                                                    <a href="{{ route('logdoc.poDocPerVendor.edit', [$data->id]) }}" class="btn btn-info"><i class="cil-pencil"></i></a>
                                                                                    <button class="btn btn-danger" onClick="return confirm('Yakin ingin hapus Purchase Order {{ $data->namapeternak.' '.date("d-m-Y", strtotime($data->tanggal)) }}?')"><i class="cil-trash"></i></button>
                                                                                </form>
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                            @if (!empty($totals[$day]))
                                                                <tr>
                                                                    <td colspan="8" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL {{ strtoupper($day) }}</td>
                                                                    <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totals[$day] }}</td>
                                                                    <td colspan="18" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                                </tr>
                                                            @endif
                                                        @endforeach

                                                        @if (!empty($totalSetting))
                                                            <tr>
                                                                <td colspan="8" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL SETTING</td>
                                                                <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totalSetting }}</td>
                                                                <td colspan="18" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            @elseif (strpos($vendorFilter, 'DMC') !== false)
                                                <input type="hidden" id="tableTitleDmc" name="tableTitleDmc" value="{{ 'REKAP PO DOC '.$vendorFilter }}">
                                                <table class="table table-responsive table-hover table-sm table-bordered" id="tableFormatDmc" name="tableFormatDmc" style="max-height: 500px; overflow-x: scroll; overflow-y: scroll">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="24" style="background-color: #ffffff; border: 1px solid #000000; font-weight: bold; text-align: center">{{ 'REKAP PO DOC '.$vendorFilter }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; width:30px; text-align: center"> NO </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NAMA DO </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> UNIT </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HARI </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TANGGAL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> VAKSIN </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center; max-width: 200px; position: sticky; left: 0px"> NAMA FLOK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> POP (BOX) </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALAMAT </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NO TLP </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KETERANGAN </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GRADE FLOK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> RING </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> DENSITY </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS KANDANG </th>
                                                            <th colspan="3" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PRASYARAT </th>
                                                            <th colspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERALATAN CH </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HASIL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> STATUS <br> PENGIRIMAN </th>
                                                            @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                <th rowspan="2" class="noExl" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AKSI </th>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERKANDANGAN </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KELISTRIKAN/<br>DIESEL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TOTAL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALARM </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GENSET </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS', "JUM'AT", 'SABTU', 'MINGGU'];
                                                            $totals = [
                                                                'SENIN' => $totalSenin ?? null,
                                                                'SELASA' => $totalSelasa ?? null,
                                                                'RABU' => $totalRabu ?? null,
                                                                'KAMIS' => $totalKamis ?? null,
                                                                "JUM'AT" => $totalJumat ?? null,
                                                                'SABTU' => $totalSabtu ?? null,
                                                                'MINGGU' => $totalMinggu ?? null
                                                            ];
                                                        @endphp

                                                        @foreach($days as $day)
                                                            @foreach($poDocFilter as $data)
                                                                @if ($data->hari == $day)
                                                                    @php
                                                                        $prasyarat = flokToPrasyarat($data->unit, $data->namapeternak);
                                                                        $grade = flokToGrade($data->unit, $data->namapeternak);
                                                                        $ring = unitToRing($data->unit, $data->alamatkandang);
                                                                        $density = flokToDensity($data->unit, $data->namapeternak, strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox / 100 : $data->jumlahbox);
                                                                        $peralatanCH = flokToPeralatanCH($data->unit, $data->namapeternak);
                                                                        $peralatanCHAlarm = $peralatanCH['alarm'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $peralatanCHGenset = $peralatanCH['genset'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $hasil = flokToHasilPoDoc($grade, $prasyarat['jenis_kandang'], $ring, $prasyarat['total_score'], ($peralatanCH['alarm'] == 'Ada' ? 'Ada' : 'Tidak Ada'), ($peralatanCH['genset'] == 'Ada' ? 'Ada' : 'Tidak Ada'));
                                                                        $hasilTidakLolos = in_array($hasil, ['REKOMENDASI KANIT', 'REKOMENDASI DIREKTUR', 'PERJANJIAN GANTI RUGI']);
                                                                    @endphp
                                                                    <tr>
                                                                        <td style="border: 1px solid #000000;">{{ $no++ }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->namado }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->unit }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->hari }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ date("d-m-Y", strtotime($data->tanggal)) }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->vaksin }}</td>
                                                                        <td style="border: 1px solid #000000; background-color: #ffffff; position: sticky; left: 0px">{{ $data->namapeternak }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->jumlahbox }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->alamatkandang }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ !is_null($data->noteleponppl) && $data->noteleponppl !== 'null' ? 'PETERNAK : '.$data->notelepon.', '.implode(', ', json_decode($data->noteleponppl)) : $data->notelepon }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->gradedoc }}{{ empty($data->keterangan) ? '' : ' | '.$data->keterangan }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $grade }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $ring }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $density }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $prasyarat['jenis_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['nilai_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['kelistrikan'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['total_score'] }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHAlarm }}</span></td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHGenset }}</span></td>
                                                                        <td style="border: 1px solid #000000;">
                                                                            <b class="{{ $hasilTidakLolos ? 'text-danger' : '' }}">{{ $hasil }}</b>
                                                                            @if ($hasilTidakLolos)
                                                                                <br>
                                                                                @if (!empty($data->filebukti))
                                                                                    <a href="{{ asset('bukti-rekomendasi/' . $data->filebukti) }}" class="btn btn-info my-2" download>DOWNLOAD</a>
                                                                                    <br>
                                                                                @endif
                                                                                <button class="btn btn-warning openModal" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}" data-toggle="modal" data-target="#uploadModal">UPLOAD</button>
                                                                            @endif
                                                                        </td>
                                                                        <td style="border: 1px solid #000000; text-align: center">
                                                                            @if (empty($data->statuspengiriman))
                                                                                <button class="btn btn-danger ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ 'BELUM DIKIRIM' }}</button>
                                                                            @else
                                                                                <button class="btn btn-{{ $data->statuspengiriman == 'DIKIRIM' ? 'success' : 'danger' }} ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ $data->statuspengiriman }}</button>
                                                                            @endif
                                                                        </td>
                                                                        @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                            <td class="noExl" style="border: 1px solid #000000;">
                                                                                <form action="{{ route('logdoc.poDocPerVendor.delete', $data->id) }}" method="post">
                                                                                    @csrf
                                                                                    <a href="{{ route('logdoc.poDocPerVendor.edit', [$data->id]) }}" class="btn btn-info"><i class="cil-pencil"></i></a>
                                                                                    <button class="btn btn-danger" onClick="return confirm('Yakin ingin hapus Purchase Order {{ $data->namapeternak.' '.date("d-m-Y", strtotime($data->tanggal)) }}?')"><i class="cil-trash"></i></button>
                                                                                </form>
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                            @if (!empty($totals[$day]))
                                                                <tr>
                                                                    <td colspan="7" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL {{ strtoupper($day) }}</td>
                                                                    <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totals[$day] }}</td>
                                                                    <td colspan="16" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                                </tr>
                                                            @endif
                                                        @endforeach

                                                        @if (!empty($totalSetting))
                                                            <tr>
                                                                <td colspan="7" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL SETTING</td>
                                                                <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totalSetting }}</td>
                                                                <td colspan="16" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            @elseif (strpos($vendorFilter, 'SREEYA') !== false)
                                                <input type="hidden" id="tableTitleSreeya" name="tableTitleSreeya" value="{{ 'REKAP PO DOC '.$vendorFilter }}">
                                                <table class="table table-responsive table-hover table-sm table-bordered" id="tableFormatSreeya" name="tableFormatSreeya" style="max-height: 500px; overflow-x: scroll; overflow-y: scroll">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="21" style="background-color: #ffffff; border: 1px solid #000000; font-weight: bold; text-align: center">{{ 'REKAP PO DOC '.$vendorFilter }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; width:30px; text-align: center"> NO </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HARI </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TANGGAL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> UNIT </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center; max-width: 200px; position: sticky; left: 0px"> CUST/KDG/WILAYAH </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALAMAT KANDANG & NO TELEPON </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> POP (EKOR) </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KET (VAKSIN) </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GRADE FLOK </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> RING </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> DENSITY </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS KANDANG </th>
                                                            <th colspan="3" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PRASYARAT </th>
                                                            <th colspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERALATAN CH </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HASIL </th>
                                                            <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> STATUS <br> PENGIRIMAN </th>
                                                            @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                <th class="noExl" rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AKSI </th>
                                                            @endif
                                                        </tr>
                                                        <tr>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERKANDANGAN </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KELISTRIKAN/<br>DIESEL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TOTAL </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALARM </th>
                                                            <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GENSET </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS', "JUM'AT", 'SABTU', 'MINGGU'];
                                                            $totals = [
                                                                'SENIN' => $totalSenin ?? null,
                                                                'SELASA' => $totalSelasa ?? null,
                                                                'RABU' => $totalRabu ?? null,
                                                                'KAMIS' => $totalKamis ?? null,
                                                                "JUM'AT" => $totalJumat ?? null,
                                                                'SABTU' => $totalSabtu ?? null,
                                                                'MINGGU' => $totalMinggu ?? null
                                                            ];
                                                        @endphp

                                                        @foreach($days as $day)
                                                            @foreach($poDocFilter as $data)
                                                                @if ($data->hari == $day)
                                                                    @php
                                                                        $prasyarat = flokToPrasyarat($data->unit, $data->namapeternak);
                                                                        $grade = flokToGrade($data->unit, $data->namapeternak);
                                                                        $ring = unitToRing($data->unit, $data->alamatkandang);
                                                                        $density = flokToDensity($data->unit, $data->namapeternak, strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox / 100 : $data->jumlahbox);
                                                                        $peralatanCH = flokToPeralatanCH($data->unit, $data->namapeternak);
                                                                        $peralatanCHAlarm = $peralatanCH['alarm'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $peralatanCHGenset = $peralatanCH['genset'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                        $hasil = flokToHasilPoDoc($grade, $prasyarat['jenis_kandang'], $ring, $prasyarat['total_score'], ($peralatanCH['alarm'] == 'Ada' ? 'Ada' : 'Tidak Ada'), ($peralatanCH['genset'] == 'Ada' ? 'Ada' : 'Tidak Ada'));
                                                                        $hasilTidakLolos = in_array($hasil, ['REKOMENDASI KANIT', 'REKOMENDASI DIREKTUR', 'PERJANJIAN GANTI RUGI']);
                                                                    @endphp
                                                                    <tr>
                                                                        <td style="border: 1px solid #000000;">{{ $no++ }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->hari }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ date("d-m-Y", strtotime($data->tanggal)) }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->unit }}</td>
                                                                        <td style="border: 1px solid #000000; background-color: #ffffff; position: sticky; left: 0px">{{ $data->namado.' / '.$data->namapeternak }}</td>
                                                                        <td style="border: 1px solid #000000;">
                                                                            {{ !is_null($data->noteleponppl) && $data->noteleponppl !== 'null' ? $data->alamatkandang.' / PETERNAK : '.$data->notelepon.', '.implode(', ', json_decode($data->noteleponppl)) : $data->alamatkandang.' / '.$data->notelepon }}
                                                                        </td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->jumlahbox }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $data->gradedoc.' | '.$data->vaksin }}{{ empty($data->keterangan) ? '' : ' | '.$data->keterangan }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $grade }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $ring }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $density }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center">{{ $prasyarat['jenis_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['nilai_kandang'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['kelistrikan'] }}</td>
                                                                        <td style="border: 1px solid #000000;">{{ $prasyarat['total_score'] }}</td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHAlarm }}</span></td>
                                                                        <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHGenset }}</span></td>
                                                                        <td style="border: 1px solid #000000;">
                                                                            <b class="{{ $hasilTidakLolos ? 'text-danger' : '' }}">{{ $hasil }}</b>
                                                                            @if ($hasilTidakLolos)
                                                                                <br>
                                                                                @if (!empty($data->filebukti))
                                                                                    <a href="{{ asset('bukti-rekomendasi/' . $data->filebukti) }}" class="btn btn-info my-2" download>DOWNLOAD</a>
                                                                                    <br>
                                                                                @endif
                                                                                <button class="btn btn-warning openModal" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}" data-toggle="modal" data-target="#uploadModal">UPLOAD</button>
                                                                            @endif
                                                                        </td>
                                                                        <td style="border: 1px solid #000000; text-align: center">
                                                                            @if (empty($data->statuspengiriman))
                                                                                <button class="btn btn-danger ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ 'BELUM DIKIRIM' }}</button>
                                                                            @else
                                                                                <button class="btn btn-{{ $data->statuspengiriman == 'DIKIRIM' ? 'success' : 'danger' }} ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ $data->statuspengiriman }}</button>
                                                                            @endif
                                                                        </td>
                                                                        @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                            <td style="border: 1px solid #000000;" class="noExl">
                                                                                <form action="{{ route('logdoc.poDocPerVendor.delete', $data->id) }}" method="post">
                                                                                    @csrf
                                                                                    <a href="{{ route('logdoc.poDocPerVendor.edit', [$data->id]) }}" class="btn btn-info"><i class="cil-pencil"></i></a>
                                                                                    <button class="btn btn-danger" onClick="return confirm('Yakin ingin hapus Purchase Order {{ $data->namapeternak.' '.date("d-m-Y", strtotime($data->tanggal)) }}?')">
                                                                                        <i class="cil-trash"></i>
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                        @endif
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                            @if (!empty($totals[$day]))
                                                                <tr>
                                                                    <td colspan="6" style="border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL {{ strtoupper($day) }}</td>
                                                                    <td style="border: 1px solid #000000; background-color: #57d3ff;">{{ $totals[$day] }}</td>
                                                                    <td colspan="14" style="border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                                </tr>
                                                            @endif
                                                        @endforeach

                                                        @if (!empty($totalSetting))
                                                            <tr>
                                                                <td colspan="6" style="border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL SETTING</td>
                                                                <td style="border: 1px solid #000000; background-color: #57d3ff;">{{ $totalSetting }}</td>
                                                                <td colspan="14" style="border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            @else
                                                @if (strpos($vendorFilter, 'SEMUA') !== false)
                                                    <table class="table table-responsive table-hover table-sm table-bordered" id="tableSemua" name="tableSemua" style="max-height: 800px; overflow-x: scroll; overflow-y: scroll">
                                                        <thead>
                                                            <tr>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; width:30px; text-align: center"> NO </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NAMA VENDOR </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TANGGAL </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HARI </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NAMA DO </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> UNIT </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center; position: sticky; left: 0px"> NAMA FLOK </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALAMAT KANDANG </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NO TELEPON </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> POP (BOX) </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GRADE DOC </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> VAKSIN </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> BOX PLASTIK </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERLAKUAN </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> FEED GEL <br> NON FEED GEL </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KETERANGAN </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GRADE FLOK </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> RING </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> DENSITY </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS KANDANG </th>
                                                                <th colspan="3" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PRASYARAT </th>
                                                                <th colspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERALATAN CH </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HASIL </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> STATUS <br> PENGIRIMAN </th>
                                                                @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                    <th class="noExl" rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AKSI </th>
                                                                @endif
                                                            </tr>
                                                            <tr>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERKANDANGAN </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KELISTRIKAN/<br>DIESEL </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TOTAL </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALARM </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GENSET </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $totalPopulasi = 0;
                                                            @endphp
                                                            @foreach($poDocFilter as $data)
                                                                @php
                                                                    $prasyarat = flokToPrasyarat($data->unit, $data->namapeternak);
                                                                    $grade = flokToGrade($data->unit, $data->namapeternak);
                                                                    $ring = unitToRing($data->unit, $data->alamatkandang);
                                                                    $density = flokToDensity($data->unit, $data->namapeternak, strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox / 100 : $data->jumlahbox);
                                                                    $peralatanCH = flokToPeralatanCH($data->unit, $data->namapeternak);
                                                                    $peralatanCHAlarm = $peralatanCH['alarm'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                    $peralatanCHGenset = $peralatanCH['genset'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                    $hasil = flokToHasilPoDoc($grade, $prasyarat['jenis_kandang'], $ring, $prasyarat['total_score'], ($peralatanCH['alarm'] == 'Ada' ? 'Ada' : 'Tidak Ada'), ($peralatanCH['genset'] == 'Ada' ? 'Ada' : 'Tidak Ada'));
                                                                    $hasilTidakLolos = in_array($hasil, ['REKOMENDASI KANIT', 'REKOMENDASI DIREKTUR', 'PERJANJIAN GANTI RUGI']);
                                                                @endphp
                                                                <tr>
                                                                    <td style="border: 1px solid #000000;">{{ $no++ }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->namavendor }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ date("d-m-Y", strtotime($data->tanggal)) }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->hari }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->namado}}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->unit }}</td>
                                                                    <td style="border: 1px solid #000000; background-color: #ffffff; position: sticky; left: 0px"><b>{{ $data->namapeternak }}</b></td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->alamatkandang }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ !is_null($data->noteleponppl) && $data->noteleponppl !== 'null' ? 'PETERNAK : '.$data->notelepon.', '.implode(', ', json_decode($data->noteleponppl)) : $data->notelepon }}</td>
                                                                    <td style="border: 1px solid #000000;" class="text-right">{{ strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox/100 : $data->jumlahbox }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->gradedoc }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->vaksin }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->plastik }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->perlakuan }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->feedgel }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $data->keterangan }}</td>
                                                                    <td style="border: 1px solid #000000; text-align: center">{{ $grade }}</td>
                                                                    <td style="border: 1px solid #000000; text-align: center">{{ $ring }}</td>
                                                                    <td style="border: 1px solid #000000; text-align: center">{{ $density }}</td>
                                                                    <td style="border: 1px solid #000000; text-align: center">{{ $prasyarat['jenis_kandang'] }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $prasyarat['nilai_kandang'] }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $prasyarat['kelistrikan'] }}</td>
                                                                    <td style="border: 1px solid #000000;">{{ $prasyarat['total_score'] }}</td>
                                                                    <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHAlarm }}</span></td>
                                                                    <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHGenset }}</span></td>
                                                                    <td style="border: 1px solid #000000;">
                                                                        <b class="{{ $hasilTidakLolos ? 'text-danger' : '' }}">{{ $hasil }}</b>
                                                                        @if ($hasilTidakLolos)
                                                                            <br>
                                                                            @if (!empty($data->filebukti))
                                                                                <a href="{{ asset('bukti-rekomendasi/' . $data->filebukti) }}" class="btn btn-info my-2" download>DOWNLOAD</a>
                                                                                <br>
                                                                            @endif
                                                                            <button class="btn btn-warning openModal" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}" data-toggle="modal" data-target="#uploadModal">UPLOAD</button>
                                                                        @endif
                                                                    </td>
                                                                    <td style="border: 1px solid #000000; text-align: center">
                                                                        @if (empty($data->statuspengiriman))
                                                                            <button class="btn btn-danger ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ 'BELUM DIKIRIM' }}</button>
                                                                        @else
                                                                            <button class="btn btn-{{ $data->statuspengiriman == 'DIKIRIM' ? 'success' : 'danger' }} ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ $data->statuspengiriman }}</button>
                                                                        @endif
                                                                    </td>
                                                                    @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                        <td style="border: 1px solid #000000;" class="noExl">
                                                                            <form action="{{ route('logdoc.poDocPerVendor.delete', $data->id) }}" method="post">
                                                                                @csrf
                                                                                <a href="{{ route('logdoc.poDocPerVendor.edit', [$data->id]) }}" class="btn btn-info"><i class="cil-pencil"></i></a>
                                                                                <button class="btn btn-danger" onClick="return confirm('Yakin ingin hapus Purchase Order {{ $data->namapeternak.' '.date("d-m-Y", strtotime($data->tanggal)) }}?')"><i class="cil-trash"></i></button>
                                                                            </form>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                                @php
                                                                    $totalPopulasi += strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox/100 : $data->jumlahbox
                                                                @endphp
                                                            @endforeach
                                                            <tr>
                                                                <td colspan="9" style="border: 1px solid #000000; font-weight: bold; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL POPULASI</td>
                                                                <td style="border: 1px solid #000000; font-weight: bold; background-color: #57d3ff;" class="text-right">{{ $totalPopulasi }}</td>
                                                                <td colspan="13" style="border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <input type="hidden" id="tableTitleCtu" name="tableTitleCtu" value="{{ 'REKAP PO DOC '.$vendorFilter }}">
                                                    <table class="table table-responsive table-hover table-sm table-bordered" id="tableFormatCtu" name="tableFormatCtu" style="max-height: 500px; overflow-x: scroll; overflow-y: scroll">
                                                        <thead>
                                                            <tr>
                                                                <th colspan="24" style="background-color: #ffffff; border: 1px solid #000000; font-weight: bold; text-align: center">{{ 'REKAP PO DOC '.$vendorFilter }}</th>
                                                            </tr>
                                                            <tr>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; width:30px; text-align: center"> NO </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NAMA DO </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> UNIT </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HARI </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TANGGAL </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center; position: sticky; left: 0px"> NAMA FLOK </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> POP (BOX) </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALAMAT </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> NO HP </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KETERANGAN </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GRADE FLOK </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> RING </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> DENSITY </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> JENIS KANDANG </th>
                                                                <th colspan="3" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PRASYARAT </th>
                                                                <th colspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERALATAN CH </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> HASIL </th>
                                                                <th rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> STATUS <br> PENGIRIMAN </th>
                                                                @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                    <th class="noExl" rowspan="2" style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AKSI </th>
                                                                @endif
                                                            </tr>
                                                            <tr>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> PERKANDANGAN </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KELISTRIKAN/<br>DIESEL </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> TOTAL </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> ALARM </th>
                                                                <th style="background-color: #ffab61; font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> GENSET </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $days = ['SENIN', 'SELASA', 'RABU', 'KAMIS', "JUM'AT", 'SABTU', 'MINGGU'];
                                                                $totals = [
                                                                    'SENIN' => $totalSenin ?? null,
                                                                    'SELASA' => $totalSelasa ?? null,
                                                                    'RABU' => $totalRabu ?? null,
                                                                    'KAMIS' => $totalKamis ?? null,
                                                                    "JUM'AT" => $totalJumat ?? null,
                                                                    'SABTU' => $totalSabtu ?? null,
                                                                    'MINGGU' => $totalMinggu ?? null
                                                                ];
                                                            @endphp

                                                            @foreach($days as $day)
                                                                @foreach($poDocFilter as $data)
                                                                    @if ($data->hari == $day)
                                                                        @php
                                                                            $prasyarat = flokToPrasyarat($data->unit, $data->namapeternak);
                                                                            $grade = flokToGrade($data->unit, $data->namapeternak);
                                                                            $ring = unitToRing($data->unit, $data->alamatkandang);
                                                                            $density = flokToDensity($data->unit, $data->namapeternak, strpos($data->namavendor, 'SREEYA') !== false ? $data->jumlahbox / 100 : $data->jumlahbox);
                                                                            $peralatanCH = flokToPeralatanCH($data->unit, $data->namapeternak);
                                                                            $peralatanCHAlarm = $peralatanCH['alarm'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                            $peralatanCHGenset = $peralatanCH['genset'] == 'Ada' ? html_entity_decode('&#9989;') : ($prasyarat['jenis_kandang'] == 'CH' ? html_entity_decode('&#10060;') : '---');
                                                                            $hasil = flokToHasilPoDoc($grade, $prasyarat['jenis_kandang'], $ring, $prasyarat['total_score'], ($peralatanCH['alarm'] == 'Ada' ? 'Ada' : 'Tidak Ada'), ($peralatanCH['genset'] == 'Ada' ? 'Ada' : 'Tidak Ada'));
                                                                            $hasilTidakLolos = in_array($hasil, ['REKOMENDASI KANIT', 'REKOMENDASI DIREKTUR', 'PERJANJIAN GANTI RUGI']);
                                                                        @endphp
                                                                        <tr>
                                                                            <td style="border: 1px solid #000000;">{{ $no++ }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $data->namado }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $data->unit }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $data->hari }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ date("d-m-Y", strtotime($data->tanggal)) }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $data->gradedoc }}</td>
                                                                            <td style="border: 1px solid #000000; background-color: #ffffff; position: sticky; left: 0px">{{ $data->namapeternak }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $data->jumlahbox }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $data->alamatkandang }}</td>
                                                                            <td style="border: 1px solid #000000;">
                                                                                {{ !is_null($data->noteleponppl) && $data->noteleponppl !== 'null' ? 'PETERNAK : '.$data->notelepon.', '.implode(', ', json_decode($data->noteleponppl)) : $data->notelepon }}
                                                                            </td>
                                                                            <td style="border: 1px solid #000000;">{{ $data->vaksin }}{{ empty($data->keterangan) ? '' : ' | '.$data->keterangan }}</td>
                                                                            <td style="border: 1px solid #000000; text-align: center">{{ $grade }}</td>
                                                                            <td style="border: 1px solid #000000; text-align: center">{{ $ring }}</td>
                                                                            <td style="border: 1px solid #000000; text-align: center">{{ $density }}</td>
                                                                            <td style="border: 1px solid #000000; text-align: center">{{ $prasyarat['jenis_kandang'] }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $prasyarat['nilai_kandang'] }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $prasyarat['kelistrikan'] }}</td>
                                                                            <td style="border: 1px solid #000000;">{{ $prasyarat['total_score'] }}</td>
                                                                            <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHAlarm }}</span></td>
                                                                            <td style="border: 1px solid #000000; text-align: center;"><span style="font-size:24px;">{{ $peralatanCHGenset }}</span></td>
                                                                            <td style="border: 1px solid #000000;">
                                                                                <b class="{{ $hasilTidakLolos ? 'text-danger' : '' }}">{{ $hasil }}</b>
                                                                                @if ($hasilTidakLolos)
                                                                                    <br>
                                                                                    @if (!empty($data->filebukti))
                                                                                        <a href="{{ asset('bukti-rekomendasi/' . $data->filebukti) }}" class="btn btn-info my-2" download>DOWNLOAD</a>
                                                                                        <br>
                                                                                    @endif
                                                                                    <button class="btn btn-warning openModal" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}" data-toggle="modal" data-target="#uploadModal">UPLOAD</button>
                                                                                @endif
                                                                            </td>
                                                                            <td style="border: 1px solid #000000; text-align: center">
                                                                                @if (empty($data->statuspengiriman))
                                                                                    <button class="btn btn-danger ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ 'BELUM DIKIRIM' }}</button>
                                                                                @else
                                                                                    <button class="btn btn-{{ $data->statuspengiriman == 'DIKIRIM' ? 'success' : 'danger' }} ubahStatusPengiriman" data-vendor="{{ $data->namavendor }}" data-unit="{{ $data->unit }}" data-flok="{{ $data->namapeternak }}">{{ $data->statuspengiriman }}</button>
                                                                                @endif
                                                                            </td>
                                                                            @if (in_array($roles, ['admin', 'sr']) || in_array($nik, ['0022.MTK.1009', '1872.MTK.0622', '0110.MTK.0412', '0315.MTK.1213']) || in_array($jabatan, ['ADMIN LOGISTIK', 'STAFF OPERASIONAL']))
                                                                                <td class="noExl" style="border: 1px solid #000000;">
                                                                                    <form action="{{ route('logdoc.poDocPerVendor.delete', $data->id) }}" method="post">
                                                                                        @csrf
                                                                                        <a href="{{ route('logdoc.poDocPerVendor.edit', [$data->id]) }}" class="btn btn-info"><i class="cil-pencil"></i></a>
                                                                                        <button class="btn btn-danger" onClick="return confirm('Yakin ingin hapus Purchase Order {{ $data->namapeternak.' '.date("d-m-Y", strtotime($data->tanggal)) }}?')"><i class="cil-trash"></i></button>
                                                                                    </form>
                                                                                </td>
                                                                            @endif
                                                                        </tr>
                                                                    @endif
                                                                @endforeach

                                                                @if (!empty($totals[$day]))
                                                                    <tr>
                                                                        <td colspan="7" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL {{ strtoupper($day) }}</td>
                                                                        <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totals[$day] }}</td>
                                                                        <td colspan="16" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                            @if (!empty($totalSetting))
                                                                <tr>
                                                                    <td colspan="7" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff; vertical-align: middle; text-align: center">TOTAL SETTING</td>
                                                                    <td style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;">{{ $totalSetting }}</td>
                                                                    <td colspan="16" style="font-weight: bold; border: 1px solid #000000; background-color: #57d3ff;"></td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal hapus -->
    <div class="modal fade" id="hapus" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">HAPUS DATA PO DOC</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('logdoc.poDocPerVendor.deleteRange') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>TANGGAL</label>
                            <input type="date" name="tanggalAwalDelete" id="tanggalAwalDelete" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>SAMPAI</label>
                            <input type="date" name="tanggalAkhirDelete" id="tanggalAkhirDelete" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
                        <button type="submit" class="btn btn-success">HAPUS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- modal upload -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">UPLOAD BUKTI SKET REKOMENDASI</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="uploadBuktiRekomendasi" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="vendor">VENDOR</label>
                            <input type="text" class="form-control" id="vendor" name="vendor" readonly>
                        </div>
                        <div class="form-group">
                            <label for="unit">UNIT</label>
                            <input type="text" class="form-control" id="unit" name="unit" readonly>
                        </div>
                        <div class="form-group">
                            <label for="flok">FLOK</label>
                            <input type="text" class="form-control" id="flok" name="flok" readonly>
                        </div>
                        <div class="form-group">
                            <label for="tanggal">TANGGAL</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" readonly>
                        </div>
                        <div class="form-group">
                            <label for="file">UPLOAD FILE</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
                        <button type="submit" class="btn btn-success">SUBMIT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- modal master button -->
    <div class="modal fade" id="masterModal" tabindex="-1" role="dialog" aria-labelledby="masterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">MASTER</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <a class="btn btn-warning mx-2" href="{{ parse_url(url()->current(), PHP_URL_HOST) === 'localhost' ? url('http://localhost:8001') : url('https://sin.ptmjl.co.id') }}">SINKRON ARCA</a>
                    <a class="btn btn-warning mx-2" href="{{ parse_url(url()->current(), PHP_URL_HOST) === 'localhost' ? url('http://localhost:8001/opr/logistik_doc/prasyaratFlokAktif') : url('https://sin.ptmjl.co.id/opr/logistik_doc/prasyaratFlokAktif') }}">PRASYARAT</a>
                    <a class="btn btn-secondary mx-2" href="{{ route('logdoc.poDocPerVendor.masterRing') }}">MASTER RING</a>
                </div>
            </div>
        </div>
    </div>

    <!-- modal sinkron untuk unit -->
    <div class="modal fade" id="sinkronModal" tabindex="-1" role="dialog" aria-labelledby="sinkronModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mPlasmaTitle">SINKRON API</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover text-nowrap">
                        <thead class="text-center">
                            <th>NO</th>
                            <th>API</th>
                            <th>PROGRES</th>
                        </thead>
                        <tbody>
                            @php
                                $listSinkron = [
                                    ['no' => 1, 'name' => 'databasePlasma', 'label' => 'DATABASE PLASMA'],
                                    ['no' => 2, 'name' => 'gradePlasma', 'label' => 'GRADE PLASMA'],
                                    ['no' => 3, 'name' => 'plasmaAktif', 'label' => 'PLASMA AKTIF'],
                                    ['no' => 4, 'name' => 'prasyaratFlokAktif', 'label' => 'PRASYARAT FLOK AKTIF']
                                ];
                            @endphp
                            @foreach ($listSinkron as $sync)
                                <tr>
                                    <td style="text-align: center">{{ $sync['no'] }}</td>
                                    <td>{{ $sync['label'] }}</td>
                                    <td class="text-center" name="count-{{ $sync['name'] }}"></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="text-center" colspan="2">TOTAL</td>
                                <td class="text-center" name="total">0</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="progress progress-primary progress-sm">
                        <div class="progress-bar" role="progressbar"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="top-right" type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <span class="d-none d-sm-block">TUTUP</span>
                    </button>
                    <button id="buttonSinkronModal" type="button" class="btn btn-primary ms-1 d-flex align-items-center">
                        <span class="d-none d-sm-block">SINKRON</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('javascript')
<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/jquery.table2excel.js') }}"></script>
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/sweetalert.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            placeholder : ' PILIH',
            multiple: true
        });
    });

    const sessionData = $('#session-data').data();
    var _token = $('#csrf-token').val();
    const listSinkron = ['databasePlasma', 'gradePlasma', 'plasmaAktif', 'prasyaratFlokAktif'];

    const SetDisable = (element, isDisabled = false) => {
        if (isDisabled) {
            element.attr('disabled', true);
            element.prepend('<i class="spinner-border spinner-border-sm me-2"></i>');
            element.find('span').text('LOADING');
        } else {
            element.attr('disabled', false);
            element.find('i').remove();
            element.find('span').text('SINKRON');
        }
    };

    const persen = (val, max) => {
        if (isNaN(val) || isNaN(max)) {
            throw new Error('Input harus angka');
        }
        if (max <= 0) {
            throw new Error('Nilai max harus lebih besar dari 0');
        }
        return (val / max) * 100;
    };

    $('#buttonSinkronModal').click(async e => {
        const tombol = $(e.currentTarget);
        try {
            SetDisable(tombol, true);
            let initProgress = 0;
            let total = 0;
            for (const sync of listSinkron) {
                const res = await $.ajax({
                    url: '/logdoc/poDocPerVendor/'+sync,
                    type: 'POST',
                    data: {
                        _token: _token,
                        ap: sessionData.region,
                        unit: sessionData.unit
                    }
                });
                console.log(res, res.data);

                total += (parseInt(res.data) || 0);
                $('#sinkronModal [name="count-' + sync + '"]').html(res.data);
                $('#sinkronModal [name="total"]').html(total);
                initProgress++;
                $('#sinkronModal .progress-bar').width(persen(initProgress, 4) + '%');
            }
            SetDisable(tombol, false);
            location.reload();
        } catch (err) {
            SetDisable(tombol, false);
            console.log(err);
            alert(err.message || err);
        }
    });

    $('#template').click(function(e) {
        e.preventDefault();
        var linkURL = '/template/TEMPLATE SURAT PERJANJIAN DAN REKOMENDASI.docx';
        window.location.href = linkURL;
    });

    var tanggalAwal = $('#tanggalAwal').val();
    var tanggalAkhir = $('#tanggalAkhir').val();
    var vendor = $('#vendorFilter').val();

    $('#tanggalAwal').change(function(){
        if ($('#tanggalAwal').val() != '') {
            $('#tanggalAkhir').prop('required',true);
        } else {
            $('#tanggalAkhir').prop('required',false);
        }
    });

    var namaregionlist = {
        'PT ANEKA INTAN LESTARI': 'AIL',
        'PT BAROKAH RESTU UTAMA': 'BRU',
        'PT BINTANG TERANG BERSINAR': 'BTB',
        'PT GILAR PERWIRA SATRIA': 'GPS',
        'PT KEDU LINTAS BERBINTANG': 'KLB',
        'PT KARYA SATWA MULIA': 'KSM',
        'PT LAWU ABADI NUSA': 'LAN',
        'PT LAJU SATWA WISESA': 'LSW',
        'PT MURIA JAYA RAYA': 'MJR',
        'PT MITRA MAHKOTA BUANA': 'MMB',
        'PT MITRA PETERNAKAN UNGGAS': 'MPU',
        'PT MITRA UNGGAS MAKMUR': 'MUM',
        'PT SAWUNG GEMA ABADI': 'SGA'
    };

    function regionToKode(namaregion) {
        if (namaregionlist.hasOwnProperty(namaregion)) {
            return namaregionlist[namaregion];
        }
    }

    function kodeUnitArca(kodeunit) {
        var units = {
            // PT MUM
            'SMN': 'A1', 'GKD': 'A3', 'KLP': 'A4', 'BTL': 'A5', 'KTA': 'A6', 'MKS' : 'A7',

            // PT SGA
            'SLG': 'B1', 'BYL': 'B3', 'KRD': 'B4', 'BWN': 'B6', 'GRB': 'B7', 'BSN': 'B8',

            // PT MPU
            'CRB': 'C1', 'IDM': 'C3', 'PTR': 'C4', 'LSR': 'C5', 'PDG': 'C6', 'PPT': 'C7', 'TSM': 'C8', 'BNJ': 'C9', 'BGR': 'C10',

            // PT MMB
            'SMG': 'D1', 'UNG': 'D2', 'DMK': 'D3', 'GDO': 'D5', 'BJA': 'D6', 'KLA': 'D7', 'BDL': 'D8', 'BSW': 'D9',

            // PT AIL
            'PKL': 'E1', 'PML': 'E2', 'BTG': 'E3', 'KJN': 'E5', 'PNK' : 'E6',

            // PT MJR
            'KDS': 'F1', 'PTI': 'F2', 'JPR': 'F3', 'PWD': 'F4', 'BLR': 'F5', 'RBG': 'F6', 'BJO': 'F7', 'TBN': 'F8', 'GSK': 'F9',

            // PT LAN
            'SKH': 'G1', 'WNG': 'G2', 'SGN': 'G3', 'KLT': 'G4', 'MTG': 'G5', 'GML': 'G6', 'KRA': 'G7', 'JMR': 'G8', 'MYR': 'G9',

            // PT BRU
            'BDG': 'H1', 'SBG': 'H2', 'CJR': 'H3', 'MJK': 'H5', 'SMD': 'H6', 'TRG': 'H7',

            // PT KSM
            'MDN': 'I1', 'PNG': 'I2', 'MGT': 'I3', 'NGW': 'I4',

            // PT KLB
            'TMG': 'J1', 'WNB': 'J2', 'MGL': 'J3', 'KBM': 'J4',

            // PT BTB
            'BRB': 'L1', 'TGL': 'L2', 'BMA': 'L3',

            // PT GPS
            'PBG': 'M1', 'PWT': 'M2', 'BJN': 'M3', 'CLP': 'M4',

            // PT LSW
            'KDR': 'N1', 'JBG': 'N2', 'TLA': 'N3',
        };

        if (units.hasOwnProperty(kodeunit)) {
            return units[kodeunit];
        } else {
            return "Unit tidak terdaftar";
        }
    }

    $('#regionFilter\\[\\]').change(function(){
        console.log($('#regionFilter\\[\\]').val(), $(this).val().length);
        var region = regionToKode($(this).val());
        if ($(this).val().length == 1) {
            if(region){
                $.ajax({
                    type:"GET",
                    url:"/user/"+region,
                    dataType: 'JSON',
                    success:function(res){
                        if(res){
                            $('select[name="unitFilter"]').empty();
                            $('select[name="unitFilter"]').append('<option value="SEMUA">SEMUA</option>');
                            $.each(res,function(namaunit,kodeunit){
                                $('select[name="unitFilter"]').append('<option value="'+kodeunit+'">'+kodeunit+' - '+kodeUnitArca(kodeunit)+'</option>');
                            });
                        }else{
                            $('select[name="unitFilter"]').empty();
                        }
                    }
                });
            }else{
                $('select[name="unitFilter"]').empty();
                $('select[name="unitFilter"]').append('<option value="SEMUA">SEMUA</option>');
            }
        }else{
            $('select[name="unitFilter"]').empty();
            $('select[name="unitFilter"]').append('<option value="SEMUA">SEMUA</option>');
        }
    });

    $("#exportFormatCtu").click(function(){
        $("#tableFormatCtu").table2excel({
            name: "Worksheet Name",
            filename: "REKAP_PO_DOC_"+vendor+".xls",
            preserveColors:true,
            exclude: ".noExl"
        });
    });

    $("#exportFormatCp").click(function(){
        $("#tableFormatCp").table2excel({
            name: "Worksheet Name",
            filename: "REKAP_PO_DOC_"+vendor+".xls",
            preserveColors:true,
            exclude: ".noExl"
        });
    });

    $("#exportFormatMb").click(function(){
        $("#tableFormatMb").table2excel({
            name: "Worksheet Name",
            filename: "REKAP_PO_DOC_"+vendor+".xls",
            preserveColors:true,
            exclude: ".noExl"
        });
    });

    $("#exportFormatDmc").click(function(){
        $("#tableFormatDmc").table2excel({
            name: "Worksheet Name",
            filename: "REKAP_PO_DOC_"+vendor+".xls",
            preserveColors:true,
            exclude: ".noExl"
        });
    });

    $("#exportFormatSreeya").click(function(){
        $("#tableFormatSreeya").table2excel({
            name: "Worksheet Name",
            filename: "REKAP_PO_DOC_"+vendor+".xls",
            preserveColors:true,
            exclude: ".noExl"
        });
    });

    $("#export").click(function(){
        $("#tableSemua").table2excel({
            name: "Worksheet Name",
            filename: "REKAP_PO_DOC.xls",
            preserveColors:true,
            exclude: ".noExl"
        });
    });

    $("#exportTxtFormatCtu").click(function() {
        var reversedTanggalAwal = new Date(tanggalAwal).toLocaleDateString('en-GB');
        var reversedTanggalAkhir = new Date(tanggalAkhir).toLocaleDateString('en-GB');

        // Ganti "table" dengan "#tableFormatCtu" untuk memilih tabel dengan id "tableFormatCtu"
        var tableData = [];

        // Mendapatkan nilai dari baris pertama tabel
        var tableTitle = $('#tableTitleCtu').val().trim();

        // Menggunakan tableTitle sebagai judul file
        if (!tanggalAwal && !tanggalAkhir) {
            var fileName = tableTitle.replace(/\s+/g, '_')+'.txt';
        } else {
            var fileName = tableTitle.replace(/\s+/g, '_')+'_'+reversedTanggalAwal+'-'+reversedTanggalAkhir+'.txt';
        }

        $('#tableFormatCtu tbody tr').each(function(row, tr) {
            var rowData = {};

            // Menangani setiap sel di dalam baris
            $(tr).find('td').each(function(col, td) {
            // Menetapkan data pada objek rowData berdasarkan indeks kolom
            switch (col) {
                case 0:
                    rowData.NO = $(td).text().trim();
                break;
                case 1:
                    rowData.NAMA_DO = $(td).text().trim();
                break;
                case 2:
                    rowData.HARI = $(td).text().trim();
                break;
                case 3:
                    rowData.TANGGAL = $(td).text().trim();
                break;
                case 4:
                    rowData.JENIS = $(td).text().trim();
                break;
                case 5:
                    rowData.NAMA_FLOK = $(td).text().trim();
                break;
                case 6:
                    rowData.POP_BOX = $(td).text().trim();
                break;
                case 7:
                    rowData.ALAMAT = $(td).text().trim();
                break;
                case 8:
                    rowData.NO_HP = $(td).text().trim();
                break;
                case 9:
                    rowData.KETERANGAN = $(td).text().trim();
                break;
            }
            });

            // Label yang ingin dihindari
            var avoidLabels = ['TOTAL SENIN', 'TOTAL SELASA', 'TOTAL RABU', 'TOTAL KAMIS', "TOTAL JUM'AT", 'TOTAL SABTU', 'TOTAL MINGGU', 'TOTAL SETTING'];

            // Cek apakah data memiliki label yang ingin dihindari
            if (!avoidLabels.includes(rowData.NO)) {
                // Mengumpulkan objek rowData ke dalam array tableData
                tableData.push(rowData);
            }
        });

        // Membuat string teks dari objek tableData
        var textData = tableTitle + '\n\n' + tableData.map(function(row) {
            return Object.entries(row).map(function([key, value]) {
                return key.toUpperCase() + " : " + value;
            }).join('\n');
        }).join('\n\n');

        // Buat elemen unduh dan atur atributnya
        var downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(textData));
        downloadLink.setAttribute('download', fileName);

        // Simulasikan klik pada elemen unduh untuk memulai unduhan
        downloadLink.click();
    });

    $("#exportTxtFormatCp").click(function() {
        var reversedTanggalAwal = new Date(tanggalAwal).toLocaleDateString('en-GB');
        var reversedTanggalAkhir = new Date(tanggalAkhir).toLocaleDateString('en-GB');

        // Ganti "table" dengan "#tableFormatCp" untuk memilih tabel dengan id "tableFormatCp"
        var tableData = [];

        // Mendapatkan nilai dari baris pertama tabel
        var tableTitle = $('#tableTitleCp').val().trim();

        // Menggunakan tableTitle sebagai judul file
        if (!tanggalAwal && !tanggalAkhir) {
            var fileName = tableTitle.replace(/\s+/g, '_')+'.txt';
        } else {
            var fileName = tableTitle.replace(/\s+/g, '_')+'_'+reversedTanggalAwal+'-'+reversedTanggalAkhir+'.txt';
        }

        $('#tableFormatCp tbody tr').each(function(row, tr) {
            var rowData = {};

            // Menangani setiap sel di dalam baris
            $(tr).find('td').each(function(col, td) {
            // Menetapkan data pada objek rowData berdasarkan indeks kolom
            switch (col) {
                case 0:
                    rowData.NO = $(td).text().trim();
                break;
                case 1:
                    rowData.HARI = $(td).text().trim();
                break;
                case 2:
                    rowData.TANGGAL = $(td).text().trim();
                break;
                case 3:
                    rowData.NAMA_DO = $(td).text().trim();
                break;
                case 4:
                    rowData.NAMA_PETERNAK = $(td).text().trim();
                break;
                case 5:
                    rowData.ALAMAT_KANDANG = $(td).text().trim();
                break;
                case 6:
                    rowData.NOMOR_TELEPON = $(td).text().trim();
                break;
                case 7:
                    rowData.VACC = $(td).text().trim();
                break;
                case 8:
                    rowData.NON = $(td).text().trim();
                break;
                case 9:
                    rowData.FEED_GEL = $(td).text().trim();
                break;
                case 10:
                    rowData.KETERANGAN = $(td).text().trim();
                break;
            }
            });

            // Label yang ingin dihindari
            var avoidLabels = ['TOTAL SENIN', 'TOTAL SELASA', 'TOTAL RABU', 'TOTAL KAMIS', "TOTAL JUM'AT", 'TOTAL SABTU', 'TOTAL MINGGU', 'TOTAL SETTING'];

            // Cek apakah data memiliki label yang ingin dihindari
            if (!avoidLabels.includes(rowData.NO)) {
                // Mengumpulkan objek rowData ke dalam array tableData
                tableData.push(rowData);
            }
        });

        // Membuat string teks dari objek tableData
        var textData = tableTitle + '\n\n' + tableData.map(function(row) {
            return Object.entries(row).map(function([key, value]) {
                return key.toUpperCase() + " : " + value;
            }).join('\n');
        }).join('\n\n');

        // Buat elemen unduh dan atur atributnya
        var downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(textData));
        downloadLink.setAttribute('download', fileName);

        // Simulasikan klik pada elemen unduh untuk memulai unduhan
        downloadLink.click();
    });

    $("#exportTxtFormatMb").click(function() {
        var reversedTanggalAwal = new Date(tanggalAwal).toLocaleDateString('en-GB');
        var reversedTanggalAkhir = new Date(tanggalAkhir).toLocaleDateString('en-GB');

        // Ganti "table" dengan "#tableFormatMb" untuk memilih tabel dengan id "tableFormatMb"
        var tableData = [];

        // Mendapatkan nilai dari baris pertama tabel
        var tableTitle = $('#tableTitleMb').val().trim();

        // Menggunakan tableTitle sebagai judul file
        if (!tanggalAwal && !tanggalAkhir) {
            var fileName = tableTitle.replace(/\s+/g, '_')+'.txt';
        } else {
            var fileName = tableTitle.replace(/\s+/g, '_')+'_'+reversedTanggalAwal+'-'+reversedTanggalAkhir+'.txt';
        }

        $('#tableFormatMb tbody tr').each(function(row, tr) {
            var rowData = {};

            // Menangani setiap sel di dalam baris
            $(tr).find('td').each(function(col, td) {
            // Menetapkan data pada objek rowData berdasarkan indeks kolom
            switch (col) {
                case 0:
                    rowData.NO = $(td).text().trim();
                break;
                case 1:
                    rowData.HARI = $(td).text().trim();
                break;
                case 2:
                    rowData.TANGGAL = $(td).text().trim();
                break;
                case 3:
                    rowData.NAMA_DO = $(td).text().trim();
                break;
                case 4:
                    rowData.NAMA_PETERNAK = $(td).text().trim();
                break;
                case 5:
                    rowData.ALAMAT_LOKASI = $(td).text().trim();
                break;
                case 6:
                    rowData.NOMOR_TLP = $(td).text().trim();
                break;
                case 7:
                    rowData.JUMLAH_BOX = $(td).text().trim();
                break;
                case 8:
                    rowData.BOX_PLASTIK = $(td).text().trim();
                break;
                case 9:
                    rowData.JENIS_GRADE = $(td).text().trim();
                break;
                case 10:
                    rowData.JENIS_PAKET_VAKSIN = $(td).text().trim();
                break;
                case 11:
                    rowData.PERLAKUAN = $(td).text().trim();
                break;
                case 12:
                    rowData.KETERANGAN = $(td).text().trim();
                break;
            }
            });

            // Label yang ingin dihindari
            var avoidLabels = ['TOTAL SENIN', 'TOTAL SELASA', 'TOTAL RABU', 'TOTAL KAMIS', "TOTAL JUM'AT", 'TOTAL SABTU', 'TOTAL MINGGU', 'TOTAL SETTING'];

            // Cek apakah data memiliki label yang ingin dihindari
            if (!avoidLabels.includes(rowData.NO)) {
                // Mengumpulkan objek rowData ke dalam array tableData
                tableData.push(rowData);
            }
        });

        // Membuat string teks dari objek tableData
        var textData = tableTitle + '\n\n' + tableData.map(function(row) {
            return Object.entries(row).map(function([key, value]) {
                return key.toUpperCase() + " : " + value;
            }).join('\n');
        }).join('\n\n');

        // Buat elemen unduh dan atur atributnya
        var downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(textData));
        downloadLink.setAttribute('download', fileName);

        // Simulasikan klik pada elemen unduh untuk memulai unduhan
        downloadLink.click();
    });

    $("#exportTxtFormatDmc").click(function() {
        var reversedTanggalAwal = new Date(tanggalAwal).toLocaleDateString('en-GB');
        var reversedTanggalAkhir = new Date(tanggalAkhir).toLocaleDateString('en-GB');

        // Ganti "table" dengan "#tableFormatDmc" untuk memilih tabel dengan id "tableFormatDmc"
        var tableData = [];

        // Mendapatkan nilai dari baris pertama tabel
        var tableTitle = $('#tableTitleDmc').val().trim();

        // Menggunakan tableTitle sebagai judul file
        if (!tanggalAwal && !tanggalAkhir) {
            var fileName = tableTitle.replace(/\s+/g, '_')+'.txt';
        } else {
            var fileName = tableTitle.replace(/\s+/g, '_')+'_'+reversedTanggalAwal+'-'+reversedTanggalAkhir+'.txt';
        }

        $('#tableFormatDmc tbody tr').each(function(row, tr) {
            var rowData = {};

            // Menangani setiap sel di dalam baris
            $(tr).find('td').each(function(col, td) {
                // Menetapkan data pada objek rowData berdasarkan indeks kolom
                switch (col) {
                    case 0:
                        rowData.NO = $(td).text().trim();
                    break;
                    case 1:
                        rowData.NAMA_DO = $(td).text().trim();
                    break;
                    case 2:
                        rowData.HARI = $(td).text().trim();
                    break;
                    case 3:
                        rowData.TANGGAL = $(td).text().trim();
                    break;
                    case 4:
                        rowData.VAKSIN = $(td).text().trim();
                    break;
                    case 5:
                        rowData.NAMA_FLOK = $(td).text().trim();
                    break;
                    case 6:
                        rowData.POP_BOX = $(td).text().trim();
                    break;
                    case 7:
                        rowData.ALAMAT = $(td).text().trim();
                    break;
                    case 8:
                        rowData.NO_TLP = $(td).text().trim();
                    break;
                    case 9:
                        rowData.KETERANGAN = $(td).text().trim();
                    break;
                }
            });

            // Label yang ingin dihindari
            var avoidLabels = ['TOTAL SENIN', 'TOTAL SELASA', 'TOTAL RABU', 'TOTAL KAMIS', "TOTAL JUM'AT", 'TOTAL SABTU', 'TOTAL MINGGU', 'TOTAL SETTING'];

            // Cek apakah data memiliki label yang ingin dihindari
            if (!avoidLabels.includes(rowData.NO)) {
                // Mengumpulkan objek rowData ke dalam array tableData
                tableData.push(rowData);
            }
        });

        // Membuat string teks dari objek tableData
        var textData = tableTitle + '\n\n' + tableData.map(function(row) {
            return Object.entries(row).map(function([key, value]) {
                return key.toUpperCase() + " : " + value;
            }).join('\n');
        }).join('\n\n');

        // Buat elemen unduh dan atur atributnya
        var downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(textData));
        downloadLink.setAttribute('download', fileName);

        // Simulasikan klik pada elemen unduh untuk memulai unduhan
        downloadLink.click();
    });

    $("#exportTxtFormatSreeya").click(function() {
        var reversedTanggalAwal = new Date(tanggalAwal).toLocaleDateString('en-GB');
        var reversedTanggalAkhir = new Date(tanggalAkhir).toLocaleDateString('en-GB');

        // Ganti "table" dengan "#tableFormatSreeya" untuk memilih tabel dengan id "tableFormatSreeya"
        var tableData = [];

        // Mendapatkan nilai dari baris pertama tabel
        var tableTitle = $('#tableTitleSreeya').val().trim();

        // Menggunakan tableTitle sebagai judul file
        if (!tanggalAwal && !tanggalAkhir) {
            var fileName = tableTitle.replace(/\s+/g, '_')+'.txt';
        } else {
            var fileName = tableTitle.replace(/\s+/g, '_')+'_'+reversedTanggalAwal+'-'+reversedTanggalAkhir+'.txt';
        }

        $('#tableFormatSreeya tbody tr').each(function(row, tr) {
            var rowData = {};

            // Menangani setiap sel di dalam baris
            $(tr).find('td').each(function(col, td) {
                // Menetapkan data pada objek rowData berdasarkan indeks kolom
                switch (col) {
                    case 0:
                        rowData.NO = $(td).text().trim();
                    break;
                    case 1:
                        rowData.HARI = $(td).text().trim();
                    break;
                    case 2:
                        rowData.TANGGAL = $(td).text().trim();
                    break;
                    case 3:
                        rowData.CUST_KDG_WILAYAH = $(td).text().trim();
                    break;
                    case 4:
                        rowData.ALAMAT_KANDANG_NO_TLP = $(td).text().trim();
                    break;
                    case 5:
                        rowData.POP_EKOR = $(td).text().trim();
                    break;
                    case 6:
                        rowData.KET_VAKSIN = $(td).text().trim();
                    break;
                }
            });

            // Label yang ingin dihindari
            var avoidLabels = ['TOTAL SENIN', 'TOTAL SELASA', 'TOTAL RABU', 'TOTAL KAMIS', "TOTAL JUMAT", "TOTAL JUM'AT", 'TOTAL SABTU', 'TOTAL MINGGU', 'TOTAL SETTING'];

            // Cek apakah data memiliki label yang ingin dihindari
            if (!avoidLabels.includes(rowData.NO)) {
                // Mengumpulkan objek rowData ke dalam array tableData
                tableData.push(rowData);
            }
        });

        // Membuat string teks dari objek tableData
        var textData = tableTitle + '\n\n' + tableData.map(function(row) {
            return Object.entries(row).map(function([key, value]) {
                return key.toUpperCase() + " : " + value;
            }).join('\n');
        }).join('\n\n');

        // Buat elemen unduh dan atur atributnya
        var downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(textData));
        downloadLink.setAttribute('download', fileName);

        // Simulasikan klik pada elemen unduh untuk memulai unduhan
        downloadLink.click();
    });

    $('.openModal').on('click', function () {
        var vendor = $(this).data('vendor');
        var flok = $(this).data('flok');
        var unit = $(this).data('unit');
        var tanggal = $(this).data('tanggal');

        $('#vendor').val(vendor);
        $('#flok').val(flok);
        $('#unit').val(unit);
        $('#tanggal').val(tanggal);
    });

    $('#uploadBuktiRekomendasi').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "{{ route('logdoc.poDocPerVendor.uploadBuktiRekomendasi') }}",
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'BERHASIL',
                        text: response.success.toUpperCase(),
                        confirmButtonText: 'OK',
                    }).then(function() {
                        location.reload();
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('sklkdnfovhdbs', xhr.responseJSON);
                if (xhr.responseJSON.message == 'The given data was invalid.') {
                    console.log('gagal upload data');
                    if (xhr.responseJSON.errors.file.length <= 1) {
                        Swal.fire({
                            icon: 'error',
                            title: 'GAGAL',
                            text: xhr.responseJSON.errors.file[0].toUpperCase(),
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'GAGAL',
                            html: xhr.responseJSON.errors.file[0].toUpperCase()+'<br>'+xhr.responseJSON.errors.file[1].toUpperCase(),
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'GAGAL',
                        text: xhr.responseJSON.error.toUpperCase(),
                    });
                }
            }
        });
    });

    $('.ubahStatusPengiriman').on('click', function () {
        var vendor = $(this).data('vendor');
        var flok = $(this).data('flok');
        var unit = $(this).data('unit');
        var _token = $('#csrf-token').val();
        var button = $(this);
        var statuspengiriman = button.text().trim() === 'BELUM DIKIRIM' ? 'DIKIRIM' : 'BELUM DIKIRIM';

        $.ajax({
            url: "{{ route('logdoc.poDocPerVendor.ubahStatusPengiriman') }}",
            method: 'POST',
            data: {
                vendor: vendor,
                flok: flok,
                unit: unit,
                statuspengiriman: statuspengiriman,
                _token: _token
            },
            success: function(response) {
                button.text(statuspengiriman);
                button.removeClass('btn-danger btn-success').addClass(statuspengiriman === 'DIKIRIM' ? 'btn-success' : 'btn-danger');

                Swal.fire({
                    icon: 'success',
                    title: 'BERHASIL',
                    text: response.success.toUpperCase(),
                    confirmButtonText: 'OK',
                });
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'GAGAL',
                    text: xhr.responseJSON.error.toUpperCase(),
                });
            }
        });
    });
</script>
@endsection
