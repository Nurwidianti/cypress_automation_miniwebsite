@extends('dashboard.base')

@section('content')

<div class="container-fluid">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <strong><i class="fa fa-align-justify"></i>{{ __('DATA USER') }}</strong>
                        <div class="col-md-12">
                            <form class="form-inline" action="" method="" style="margin-top:10px">@csrf                               
                               <a href="{{route('user.create')}}" class="btn btn-primary">TAMBAH</a></h3>
                                  &nbsp; &nbsp;
                                <div class="form-group" class="float-lg-right">
                                    <button id="excel" class="btn btn-block btn-success" target="_blank">EXPORT EXCEL</button>
                                </div>
                                  &nbsp; &nbsp;
                                <!-- <div class="float-lg-right">-->
                                <!--   <button id="simmo" class="btn btn-block btn-danger">IMPORT SIMMO</button>-->
                                <!--</div>-->
                            </form>
                        </div>
                        <div class="col-md-12" style="margin-top:-35px">
                            <form class="" action="{{ route('user.cari') }}" method="get">
                                <div class="form-group float-lg-right">
                                    <div class="input-group">
                                        <input class="form-control" type="text" name="cari"
                                            placeholder="Pencarian"><span class="input-group-append">
                                            <button class="btn btn-primary" type="submit">Cari</button></span> &nbsp; 
                                    </div>                                   
                                </div>                                
                            </form>
                        </div>
                        <br>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-12 mb-8">
                                <div class="nav-tabs-boxed">
                                  
                                            <table class="table table-striped table-sm table-bordered">
                                                <thead class="thead-dark">
                                                    <tr style="text-align: center;">
                                                        <th class="align-middle">NO</th>
                                                        <th class="align-middle">NIK</th>
                                                        <th class="align-middle">NAMA</th>
                                                        <th class="align-middle">JABATAN</th>
                                                        <th class="align-middle">UNIT</th>
                                                        <th class="align-middle">AP/PT</th>
                                                        <th> AKSI </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($karyawan as $data)
                                                    <tr style="text-align: center;">
                                                        <td>{{ ++$no }}</td>
                                                        <td>{{ $data->nik }}</td>
                                                        <td style="text-align: left;"><a
                                                                href="{{ route('karyawan.profil',encrypt($data->name)) }}"
                                                                style="text-decoration:none">{{ $data->name }}</a>@csrf
                                                        </td>
                                                        <td>{{ $data->jabatan }}</td>
                                                        <td>{{ $data->unit }}</td>
                                                        <td>{{ $data->region }}</td>
                                                        <td style="width:150px">
                                                            <form action="{{ route('user.hapus', $data->id) }}" method="post">@csrf
                                                                <a href="{{ route('user.edit', $data->id) }}" class="btn btn-primary"><i class="cil-pencil"></i></a>
                                                                <button class="btn btn-danger" onClick="return confirm('Yakin ingin hapus ?')"><i class="cil-trash"></i></button>
                                                                <a href="{{ route('user.reset', $data->id) }}" onClick="return confirm('Yakin ingin reset password ?')" class="btn btn-success"><i class="cil-lock-locked"></i></a>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div class="table-bottom">
                                                <div class="float-lg-left">
                                                    <strong>Jumlah User : {{ $jml_karyawan }}</strong>
                                                </div>
                                                <div class="float-lg-right">
                                                    {{ $karyawan->links() }}
                                                </div>
                                            </div>
                                            </br> </br>
                                        

                                </div>
                            </div>
                        </div>
                        <!-- /.col-->
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script>
$('#excel').click(function(e) {
    e.preventDefault();
    var unit = $('[name="unit"]').val();
    if (unit == '') {
        unit = 'SEMUA';
    }
    var linkURL = '/karyawan/excel/' + unit;
    window.location.href = linkURL;
});

const insertStok = (_token, callback) => {
    $.post("{{ route('source-stok-ayam.insert') }}",{_token:_token}, function(data){
        document.getElementById("simmo").disabled = false; 
        $('#simmo').html("IMPORT SIMMO");
    });
    callback();
};

$('#simmo').click(function(e) {
    var _token = $("input[name='_token']").val();
    document.getElementById("simmo").disabled = true; 
    $('#simmo').html("<span  class='spinner-border spinner-border-sm'></span > <span class='visually-hidden'>Sinkronisasi...</span>");
    e.preventDefault();
    $.post("{{ route('karyawan.sinkron') }}",{_token:_token}, function(data){
        document.getElementById("simmo").disabled = false; 
        $('#simmo').html("IMPORT SIMMO");
    });
});
</script>
@endsection