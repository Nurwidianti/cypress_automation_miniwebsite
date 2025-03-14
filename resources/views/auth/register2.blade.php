@extends('dashboard.authBase')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
@section('content')
    {{-- <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}"> --}}
<div class="container" style="margin-bottom: 50px">
    <div id="home-konten" style="display: none;" class="konten">
        <div class="col-md-12">
            <div class="card" style="margin-top: 10px">
                <div class="card-header text-center" style="background: #204594; color:white">
                    <h5><strong>SELAMAT DATANG</strong></h5>
                </div>
                <div class="card-body p-3">
                    <form method="POST" action="{{ route('reg.post') }}">
                        @csrf
                        <div class="card-body text-center">
                            <img src="{{ url('/assets/img/logo_am24.png') }}">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg class="c-icon">
                                        <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-address-book"></use>
                                    </svg>
                                </span>
                            </div>
                            <input class="form-control" type="text" placeholder="{{ __('Nomor Induk Karyawan') }}" name="nik" required autofocus>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg class="c-icon">
                                        <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-user"></use>
                                    </svg>
                                </span>
                            </div>
                            <input class="form-control" type="text" placeholder="{{ __('Nama Lengkap') }}" name="nama" required>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg class="c-icon">
                                        <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-tag"></use>
                                    </svg>
                                </span>
                            </div>
                            <input class="form-control" type="text" placeholder="{{ __('Contoh Unit : SMG / Asdir : HO MMB') }}" name="unit" required>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg class="c-icon">
                                        <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-tags"></use>
                                    </svg>
                                </span>
                            </div>
                            <input class="form-control" type="text" placeholder="{{ __('Contoh Ap : MMB') }}" name="ap" required>
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg class="c-icon">
                                        <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-phone"></use>
                                    </svg>
                                </span>
                            </div>
                            <input class="form-control" type="text" placeholder="{{ __('08122xxxxxx') }}" name="nohp" required>
                        </div>
                        @if(session('msg'))
                            <div class="alert {{ session('success') ? 'alert-success' : 'alert-danger' }}">{{ session('msg') }}</div>
                        @endif
                        <button class="btn btn-block btn-success" type="submit">{{ __('DAFTAR') }}</button>
                    </form><br>
                    <div class="alert alert-danger" role="alert" style="font-weight: bold">Syarat & Ketentuan : <br>
                        1. Akomodasi ditanggung oleh unit<br>
                        2. Dresscode Full Hitam<br> &nbsp;  &nbsp; (No Kaos, No Sandal)<br>
                        3. Kapasitas terbatas, bagi pendaftar yang <br> &nbsp;  &nbsp; terpilih akan dihubungi ulang oleh <br> &nbsp;  &nbsp; panitia<br>
                        4. Undangan ini tidak wajib
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="jadwal-konten" style="display: none;" class="konten">
        <div class="card" style="margin-top: 10px">
            <div class="card-header text-center" style="background: #204594; color:white">
                <h5><strong>JAWAL PELAKSANAAN</strong></h5>
            </div>
            <div class="card-body p-3">
                <table style="border: none; font-weight:bold">
                    <tr>
                        <td><div style="width:75px">HARI/TGL</div></td>
                        <td><div style="width:5px">:</div></td>
                        <td>KAMIS, 1 FEBRUARI 2024</td>
                    </tr>
                    <tr>
                        <td>TEMPAT</td>
                        <td>:</td>
                        <td>HOTEL GRIYA PERSADA BANDUNGAN</td>
                    </tr>
                    <tr>
                        <td>PUKUL</td>
                        <td>:</td>
                        <td>18.30 WIB</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="alert alert-danger" role="alert" style="font-weight: bold">Catatan : <br>
            1. Harap datang tepat waktu.<br>
            2. Jika ingin membatalkan bisa kirim WA melalui klik tombol BATAL HADIR dibawah ini.<br>
        </div>
        <a href="https://wa.me/62882007021086?text=SAYA%20TIDAK%20HADIR" class="btn btn-block btn-lg" style="background: #204594; color:white">BATAL HADIR</a>
    </div>

    <div id="dresscode-konten" style="display: none; overflow:scroll; height:1400px;" class="konten">
        <div class="card" style="margin-top: 10px">
            <div class="card-header text-center" style="background: #204594; color:white">
                <h5><strong>DRESSCODE</strong></h5>
            </div>
            <div class="card-body p-3">
                <table style="border: none; font-weight:bold; text-align:center">
                    <tr>
                        <td><img src="{{ url('/assets/img/dresscode/pria.png') }}"></td>
                    </tr>
                    <tr style="background: black; color:white">
                        <td>PRIA</td>
                    </tr>
                    <tr>
                        <td><img src="{{ url('/assets/img/dresscode/wanita.png') }}"></td>
                    </tr>
                    <tr style="background: black; color:white">
                        <td>WANITA</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div id="peserta-konten" style="display: none; overflow:scroll; height:1400px;" class="konten">
        <div class="card" style="margin-top: 10px">
            <div class="card-header text-center" style="background: #204594; color:white">
                <h5><strong>CALON PESERTA</strong></h5>
            </div>
            <div class="card-body p-3">
                <div id="result" style="margin-top:10px"></div>
                <table class="table table-bordered table-sm datatable">
                    <thead class="thead-dark">
                        <tr>
                            <th>NAMA</th>
                            <th width="50">UNIT</th>
                            <th width="50">AP</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <nav style="background: #204594" class="p-1 navbar navbar-dark navbar-expand fixed-bottom d-md-none d-lg-none d-xl-none">
        <ul class="p-1 navbar-nav nav-justified w-100">
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link text-center" id="home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="white" class="bi bi-house" viewBox="0 0 16 16">
                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                    </svg>
                    <span style="color:white" class="small d-block">Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link text-center" id="jadwal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="white" class="bi bi-journals" viewBox="0 0 16 16">
                        <path d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2 2 2 0 0 1-2 2H3a2 2 0 0 1-2-2h1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1H1a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v9a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2"/>
                        <path d="M1 6v-.5a.5.5 0 0 1 1 0V6h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V9h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 2.5v.5H.5a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1H2v-.5a.5.5 0 0 0-1 0"/>
                    </svg>
                    <span style="color:white" class="small d-block">Jadwal</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link text-center" id="dresscode">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="white" class="bi bi-person-standing-dress" viewBox="0 0 16 16">
                        <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m-.5 12.25V12h1v3.25a.75.75 0 0 0 1.5 0V12h1l-1-5v-.215a.285.285 0 0 1 .56-.078l.793 2.777a.711.711 0 1 0 1.364-.405l-1.065-3.461A3 3 0 0 0 8.784 3.5H7.216a3 3 0 0 0-2.868 2.118L3.283 9.079a.711.711 0 1 0 1.365.405l.793-2.777a.285.285 0 0 1 .56.078V7l-1 5h1v3.25a.75.75 0 0 0 1.5 0Z"/>
                    </svg>
                    <span style="color:white" class="small d-block">Dresscode</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link text-center" id="peserta">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" fill="white" class="bi bi-people-fill" viewBox="0 0 16 16">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                    </svg>
                    <span style="color:white" class="small d-block">Pendaftar</span>
                </a>
            </li>
        </ul>
    </nav>

</div>


@endsection

@section('javascript')
<script src="{{ asset('js/jquery-1.9.1.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(() => {
        resume_ap();
        $("#home-konten").show();
        const listUser = @json($luser);
        $('[name="nik"]').autocomplete({
            source: (req, res) => res(listUser.filter(item => item.nik.includes(req.term)).slice(0, 10).map(item => item.nik)),
            select: (event, ui) => $('[name="nik"]').val(ui.item.value).trigger('input'),
            delay: 0,
        });

        $('[name="nik"]').on('input', (e) => {
            const result = listUser.find(item => item.nik === $(e.currentTarget).val().trim());
            if (result) {
                $('[name="nama"]').val(result.name).attr('readonly', true);
                $('[name="unit"]').val(result.unit).attr('readonly', true);
                $('[name="ap"]').val(result.region).attr('readonly', true);
                $('[name="nohp"]').val(result.nowa);
            } else {
                $('[name="nama"]').val('').attr('readonly', false);
                $('[name="unit"]').val('').attr('readonly', false);
                $('[name="ap"]').val('').attr('readonly', false);
                $('[name="nohp"]').val('');
            }
        });
    });

    function resume_ap(){
        const params = {
            _token: $("input[name='_token']").val(),
        }
        $.ajax({
            url: "/reg/resume_ap",
            method: "get",
            data: params,
            dataType: "json"
        }).done(function(res){
            console.log(res);
            $('#result').append(`
                <table class="table table-bordered table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th width="30">NO</th>
                            <th>AP</th>
                            <th width="50">JML</th>
                        </tr>
                    </thead>
                    <tbody id="data-res"></tbody>
                    <tfoot id="data-total"></tfoot>
                </table>
            `);
            var total = 0;
            $.each(res.data, function(i, items){
                $('#data-res').append(`
                    <tr>
                        <td>`+(i+1)+`</td>
                        <td>`+(items.region)+`</td>
                        <td style="text-align:center">`+(items.jml)+`</td>
                    </tr>
                `);
                total =  parseInt(total) + parseInt(items.jml);
            });
            $('#data-total').append(`
                <tr style="font-weight: bold">
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td style="text-align:center; width:50px">`+(total)+`</td>
                </tr>
            `);
        })
        .fail(function(err){
            $('#result').append(`
                <div class="konten-data card-body">
                    <h3>Sinkron tidak berhasil</h3>
                    <p>code: `+(err.status)+`</p>
                    <p>error: `+(err.statusText)+`</p>
                </div>
            `);
        });
    }

    var _token = $("input[name='_token']").val();
    var dataTable = $('.datatable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        "searching": false,
        "paging": false,
        "bPaginate": false,
        "bInfo": false,
        "order": [[ 0, "desc" ]],
        ajax: '{{ route('reg.peserta') }}',
        columns: [
            // {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'unit', name: 'unit'},
            {data: 'region', name: 'region'},
        ]
    });

    $("#home").on('click', function (e) {
        e.preventDefault();
        $("#home-konten").show();
        $("#jadwal-konten").hide();
        $("#dresscode-konten").hide();
        $("#peserta-konten").hide();
    });

    $("#jadwal").on('click', function (e) {
        e.preventDefault();
        $("#home-konten").hide();
        $("#jadwal-konten").show();
        $("#dresscode-konten").hide();
        $("#peserta-konten").hide();
    });

    $("#dresscode").on('click', function (e) {
        e.preventDefault();
        $("#home-konten").hide();
        $("#jadwal-konten").hide();
        $("#dresscode-konten").show();
        $("#peserta-konten").hide();
    });

    $("#peserta").on('click', function (e) {
        e.preventDefault();
        $('.datatable').DataTable().ajax.reload();
        $("#home-konten").hide();
        $("#jadwal-konten").hide();
        $("#dresscode-konten").hide();
        $("#peserta-konten").show();
    });
</script>
@endsection
