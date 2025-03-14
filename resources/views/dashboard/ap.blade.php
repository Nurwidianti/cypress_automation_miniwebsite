@extends('dashboard.base')

@section('content')

<div class="container-fluid">
            <div class="fade-in">
              <div class="row">               
              <div class="col-lg-12">
                  <div class="card">
                  
                    <div class="card-header">
                      <strong> {{ $namaregion }} </strong> 
                    </div>
                    <div class="card-body">
                      <div class="row g-0">
                        <div class="col-md-3" style="margin-top:20px; margin-left:0px"><img class="frame" src="../assets/img/{{ $koderegion }}.png" width="160" height="160" alt=""></div>
                        <div class="col-md-9">
                          <div class="card-body" style="margin-left:-70px">
                          <table class="table table-responsive-sm table-striped table-sm">
                                  
                                    <tbody>
                                      <tr>
                                        <td style="width:150px">NAMA REGION</td>
                                        <td> : </td>
                                        <td>  {{ $namaregion }} </td>
                                      </tr>
                                      <tr>
                                        <td>JUMLAH UNIT</td>
                                        <td> : </td>
                                        <td> {{ $jml_unit }} </td>
                                      </tr>
                                      <tr>
                                        <td></td>
                                        <td></td>
                                        <td>
                                          @foreach($unit as $data)
                                          -  {{ $data->unit }}<br>
                                          @endforeach
                                        </td>
                                      </tr>
                                      
                                    </tbody>
                                  </table>                   
                          </div>
                        </div>
                      </div>                     
                    </div>
                  </div>
                </div>
                <!-- /.col-->
              </div>
              <!-- /.row-->
              <div class="row">
                <div class="col-md-12 mb-4">
                  <div class="nav-tabs-boxed">
                    <ul class="nav nav-tabs" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#qa" role="tab" aria-controls="qa">QA</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#hr" role="tab" aria-controls="hr">HR</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ld" role="tab" aria-controls="ld">LD</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#produksi" role="tab" aria-controls="produksi">Produksi</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#doc" role="tab" aria-controls="doc">Logistik DOC</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pakan" role="tab" aria-controls="pakan">Logistik Pakan</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#penjualan" role="tab" aria-controls="penjualan">Penjualan</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#akunting" role="tab" aria-controls="akunting">Akunting</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#keuangan" role="tab" aria-controls="keuangan">Keuangan</a></li>
                      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pajak" role="tab" aria-controls="pajak">Pajak</a></li>
                    </ul>
                    <div class="tab-content">
                      <div class="tab-pane active" id="qa" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN QA</div>
                      <div class="tab-pane" id="hr" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN HR</div>
                      <div class="tab-pane" id="ld" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN LD</div>
                      <div class="tab-pane" id="produksi" role="tabpanel">
                        <table class="table table-responsive-sm table-striped">
                          <thead>
                            <tr>
                              <th>NO</th>
                              <th>LAPORAN</th>
                              <th>INFORMASI YANG TERSEDIA</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>1</td>
                              <td>REKAP RHPP GABUNGAN</td>
                              <td>PERFORMANCE PER FLOK, UNIT DAN AP, DAN MARGIN PRODUKSI PER FLOK, UNIT DAN AP, RESUME PER BULAN DAN TAHUN</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                          
                              </td>
                            </tr>
                            <tr>
                              <td>2</td>
                              <td>REKAP DEMOGRAFI PLASMA</td>
                              <td>GRADE PLASMA, POPULASI PER KANDANG, BEBAN KERJA PER TS, DAN LAINNYA</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>    
                              </td>
                            </tr>
                            <tr>
                              <td>3</td>
                              <td>REKAP RAPORT PRODUKSI</td>
                              <td>EVALUASI PRODUKSI DARI SEMUA ASPEK (LAMA PANEN, % AYAM SAKIT, MARGIN PER ZONA, PFMC DOC, DLL)</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                          
                              </td>
                            </tr>
                            <tr>
                              <td>4</td>
                              <td>EVALUASI FEED COST</td>
                              <td>EVALUASI PENCAPAIAN PERFORMANCE PAKAN PER VENDOR</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                        
                              </td>
                            </tr>
                            <tr>
                              <td>5</td>
                              <td>PERBANDINGAN KONTRAK</td>
                              <td>PERBANDINGAN POTENSI KONTRAK PER UNIT DAN EVALUASI REALISASINYA</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                         
                              </td>
                            </tr>
                            <tr>
                              <td>6</td>
                              <td>REKAP INSENTIF PRODUKSI</td>
                              <td>REKAP PENCAPAIAN ISNENTIF PRODUKSI PER BULAN DAN PER UNIT</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                         
                              </td>
                            </tr>
                            <tr>
                              <td>7</td>
                              <td>REKAP PERGERAKAN PLASMA</td>
                              <td>RDATA PERGERAKAN PLASMA YAMG MASUK DAN KELUAR/OUT</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                       
                              </td>
                            </tr>
                            <tr>
                              <td>8</td>
                              <td>FILE PFMC TS DAN KP</td>
                              <td>REKAP PERFORMANCE TS DAN KP UNTUK DATA KPI (RUGI PROD, KAPASITAS, MARGIN DAN FREKUENSI RUGI)</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                        
                              </td>
                            </tr>
                            <tr>
                              <td>9</td>
                              <td>REKAP PENCAIRAN RHPP PLASMA</td>
                              <td>DATA PENCAIRAN RHPP DAN RATING PLASMA (RESPON PLASMA DI AGRINIS)</td>
                              <td>
                                <a href="{{ url('#') }}" class="btn btn-success"><i class="fa cil-folder-open"></i></a>                        
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        <ul class="pagination">
                          <li class="page-item"><a class="page-link" href="#">Prev</a></li>
                          <li class="page-item active"><a class="page-link" href="#">1</a></li>
                          <li class="page-item"><a class="page-link" href="#">2</a></li>
                          <li class="page-item"><a class="page-link" href="#">3</a></li>
                          <li class="page-item"><a class="page-link" href="#">4</a></li>
                          <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                      </div>
                      <div class="tab-pane" id="doc" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN LOGISTIK DOC</div>
                      <div class="tab-pane" id="pakan" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN LOGISTIK PAKAN</div>
                      <div class="tab-pane" id="penjualan" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN PENJUALAN</div>
                      <div class="tab-pane" id="akunting" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN AKUNTING</div>
                      <div class="tab-pane" id="keuangan" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN KEUANGAN</div>
                      <div class="tab-pane" id="pajak" role="tabpanel">INFORMASI LAPORAN TERKAIT BAGIAN PAJAK</div>
                    </div>
                  </div>
                </div>
                <!-- /.col-->
               
              </div>
              <!-- /.row-->
            </div>
          </div>

@endsection

@section('javascript')

@endsection