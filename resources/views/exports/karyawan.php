{{
  header("Content-Type: application/force-download");
	header("Cache-Control: no-cache, must-revalidate");
  header("Expires: Sat, 16 Mei 2016 12:00:00 GMT");
  header("content-disposition: attachment;filename=rekap_notulen.xls");
}}

 <center><h2><?php echo $title; ?></h2></center>
                                
                                <table class="table table-striped table-sm table-bordered">
                                  <thead>
                                    <tr style="text-align: center;">
                                      <th class="align-middle">NO</th>
                                      <th class="align-middle">NIK</th>
                                      <th class="align-middle">NAMA</th>
                                      <th class="align-middle">JABATAN</th>
                                      <th class="align-middle">UNIT</th>
                                      <th class="align-middle">AP</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($karyawan as $data)
                                      <tr style="text-align: center;">
                                        <td>{{ ++$no }}</td>
                                        <td>{{ $data->nik }}</td>
                                        <td style="text-align: left;"><a href="{{ route('karyawan.profil',encrypt($data->kodeusers)) }}" style="text-decoration:none">{{ $data->name }}</a>@csrf</td>
                                        <td>{{ $data->jabatan }}</td>
                                        <td>{{ $data->unit }}</td>
                                        <td>{{ $data->region }}</td>                                 
                                      </tr>                                
                                    @endforeach
                                  </tbody>
                                </table>
                               