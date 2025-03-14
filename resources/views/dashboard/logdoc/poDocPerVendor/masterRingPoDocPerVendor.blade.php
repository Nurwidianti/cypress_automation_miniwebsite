@extends('dashboard.base')

@section('title', 'MASTER RING PENJUALAN')

@section('content')

    <div class="container-fluid">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>
                                <i class="cil-description"></i>{{ __(' MASTER RING PENJUALAN') }}
                                <button type="button" class="btn btn-primary float-lg-right ml-4" onclick="window.location='{{ route('logdoc.poDocPerVendor') }}'">KEMBALI</button>
                                <button type="button" id="template" class="btn btn-danger float-lg-right">TEMPLATE</button>
                                <button type="button" class="btn btn-success float-lg-right mr-3" data-toggle="modal" data-target="#import">UPLOAD</button>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-8">
                                    <form class="row d-flex justify-content-start form-inline my-3" action="{{ route('logdoc.poDocPerVendor.masterRing') }}" method="get">@csrf
                                        <div class="form-group col-md-12 mb-2">
                                            <div class="d-inline-flex col-md-1 col-form-label">
                                                <label class="form-label mr-3" for="regionFilter">AP</label>
                                            </div>
                                            <select class="form-control"name="regionFilter" id="regionFilter" required>
                                                @if (!empty($regionFilter) && $regionFilter != 'SEMUA')
                                                    <option value="{{ $regionFilter }}" selected hidden>{{ kodept($regionFilter) }}</option>
                                                @endif
                                                <option value="SEMUA">SEMUA</option>
                                                @foreach ($regions as $item)
                                                    <option value="{{ $item->koderegion }}">{{ $item->namaregion }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12 mb-2">
                                            <div class="d-inline-flex col-md-1 col-form-label">
                                                <label class="form-label mr-3" for="unitFilter">UNIT</label>
                                            </div>
                                            <select class="form-control" name="unitFilter" id="unitFilter">
                                                @if (!empty($unitFilter) && $unitFilter != 'SEMUA')
                                                    <option value="{{ $unitFilter }}" selected hidden>{{ $unitFilter.' - '.kodeUnitLong($unitFilter) }}</option>
                                                @endif
                                                <option value="SEMUA">SEMUA</option>
                                                @if (!empty($unitSelect))
                                                    @foreach ($unitSelect as $namaunit => $kodeunit)
                                                        <option value="{{ $kodeunit }}">{{ $kodeunit.' - '.$namaunit }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-12 mb-2">
                                            <div class="col-md-1"></div>
                                            <button class="btn btn-primary mr-3" type="submit">{{ __('TAMPIL') }}</button>
                                        </div>
                                    </form>
                                    <div class="card-body">
                                        @if (!empty($regionFilter) || !empty($unitFilter))
                                            <table class="table table-responsive table-hover table-sm table-bordered" id="tableMasterRingPoDocPerVendor" name="tableMasterRingPoDocPerVendor" style="max-height: 500px; overflow-x: scroll; overflow-y: scroll">
                                                <thead>
                                                    <tr>
                                                        <th style="font-weight: bold; border: 1px solid #000000; vertical-align: middle; width:30px; text-align: center"> NO </th>
                                                        <th style="font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KABUPATEN </th>
                                                        <th style="font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> KECAMATAN </th>
                                                        <th style="font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> UNIT </th>
                                                        <th style="font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AP </th>
                                                        <th style="font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> RING </th>
                                                        @if ($jabatan == 'ADMIN LOGISTIK' || $jabatan == 'STAFF REGION' || $roles == 'admin' || $nik == '0022.MTK.1009' || $nik == '1888.MTK.0722' || $nik == '0110.MTK.0412')
                                                            <th class="noExl" style="font-weight: bold; border: 1px solid #000000; vertical-align: middle; text-align: center"> AKSI </th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($masterRingPoDocPerVendor as $data)
                                                        <tr data-id="{{ $data->id }}">
                                                            <td style="border: 1px solid #000000;">{{ ++$no }}</td>
                                                            <td style="border: 1px solid #000000;">{{ $data->kabupaten }}</td>
                                                            <td style="border: 1px solid #000000;">{{ $data->kecamatan }}</td>
                                                            <td style="border: 1px solid #000000;">{{ $data->unit}}</td>
                                                            <td style="border: 1px solid #000000;">{{ $data->ap }}</td>
                                                            <td style="border: 1px solid #000000;">{{ $data->ring }}</td>
                                                            @if ($jabatan == 'ADMIN LOGISTIK' || $jabatan == 'STAFF REGION' || $roles == 'admin' || $nik == '0022.MTK.1009' || $nik == '1888.MTK.0722' || $nik == '0110.MTK.0412')
                                                                <td style="border: 1px solid #000000;" class="noExl">
                                                                    <form action="{{ route('logdoc.poDocPerVendor.masterRing.delete', $data->id) }}" method="post">
                                                                        @csrf
                                                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#edit"><i class="cil-pencil"></i></button>
                                                                        <button type="button" class="btn btn-danger delete-button" data-id="{{ $data->id }}" data-info="{{ $data->unit.' kab. '.$data->kabupaten.' kec. '.$data->kecamatan.' ring '.$data->ring }}"><i class="cil-trash"></i></button>
                                                                    </form>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
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

    <!-- modal import -->
    <div class="modal fade" id="import" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">IMPORT DATA MASTER RING PENJUALAN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('logdoc.poDocPerVendor.masterRing.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>PILIH FILE</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
                        <button type="submit" class="btn btn-success">IMPORT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- modal edit -->
    <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">EDIT DATA MASTER RING PENJUALAN</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="editForm" name="editForm">
                    @csrf
                    <input type="text" id="idEdit" name="idEdit" class="form-control" required>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>KABUPATEN</label>
                            <input type="text" id="kabupatenEdit" name="kabupatenEdit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>KECAMATAN</label>
                            <input type="text" id="kecamatanEdit" name="kecamatanEdit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>UNIT</label>
                            <input type="text" id="unitEdit" name="unitEdit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>AP</label>
                            <input type="text" id="apEdit" name="apEdit" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>RING</label>
                            <input type="text" id="ringEdit" name="ringEdit" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
                        <button type="submit" class="btn btn-success">IMPORT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('javascript')
<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/axios.min.js') }}"></script>
<script src="{{ asset('js/sweetalert.js') }}"></script>
<script>
    $('#template').click(function(e) {
        e.preventDefault();
        var linkURL = '/template/TEMPLATE_MASTER_RING_PENJUALAN.xlsx';
        window.location.href = linkURL;
    });

    $('select[name="regionFilter"]').change(function(){
        var regionFilter = $(this).val();
        if(regionFilter){
            $.ajax({
                type:"GET",
                url:"/user/"+regionFilter,
                dataType: 'JSON',
                success:function(res){
                    if(res){
                        $('select[name="unitFilter"]').empty();
                        $('select[name="unitFilter"]').append('<option value="SEMUA">SEMUA</option>');
                        $.each(res,function(namaunit,kodeunit){
                            $('select[name="unitFilter"]').append('<option value="'+kodeunit+'">'+kodeunit+' - '+namaunit+'</option>');
                        });
                    }else{
                        $('select[name="unitFilter"]').empty();
                        $('select[name="unitFilter"]').append('<option value="" hidden>PILIH</option>');
                    }
                }
            });
        }else{
            $('select[name="unitFilter"]').empty();
            $('select[name="unitFilter"]').append('<option value="" hidden>PILIH</option>');
        }
    });

    $(document).on('click', '.btn-info', function(e) {
        e.preventDefault();
        var dataId = $(this).closest('tr').data('id'); // Ambil ID dari baris data (pastikan setiap row memiliki data ID)

        // Panggil endpoint untuk mendapatkan data yang akan diedit
        axios.get(`/logdoc/poDocPerVendor/masterRing/edit/${dataId}`)
            .then(response => {
                const data = response.data;
                // Isi data ke dalam form modal
                $('#idEdit').val(data.id);
                $('#kabupatenEdit').val(data.kabupaten);
                $('#kecamatanEdit').val(data.kecamatan);
                $('#unitEdit').val(data.unit);
                $('#apEdit').val(data.ap);
                $('#ringEdit').val(data.ring);

                // Simpan data ID untuk update
                $('#editForm').data('id', dataId);
            })
            .catch(error => console.error(error));
    });

    $('#editForm').on('submit', function(e) {
        e.preventDefault();

        // Ambil data ID untuk update
        var dataId = $(this).data('id');

        // Ambil data dari form edit
        const formData = {
            kabupaten: $('#kabupatenEdit').val(),
            kecamatan: $('#kecamatanEdit').val(),
            unit: $('#unitEdit').val(),
            ap: $('#apEdit').val(),
            ring: $('#ringEdit').val(),
        };

        // Kirim data dengan Axios ke endpoint update
        axios.post(`/logdoc/poDocPerVendor/masterRing/update/${dataId}`, formData)
            .then(response => {
                $('#edit').modal('hide'); // Tutup modal

                // Tampilkan SweetAlert sebagai notifikasi sukses
                Swal.fire({
                    title: "Berhasil!",
                    text: response.data.message,
                    icon: "success",
                    confirmButtonText: "OK",
                }).then(() => {
                    location.reload(); // Muat ulang halaman setelah notifikasi sukses
                });
            })
            .catch(error => {
                console.error(error);

                // Tampilkan SweetAlert sebagai notifikasi kesalahan
                Swal.fire({
                    title: "Gagal!",
                    text: "Terjadi kesalahan dalam mengedit data",
                    icon: "error",
                    confirmButtonText: "OK",
                });
            });
    });

    $(document).on('click', '.delete-button', function(e) {
        e.preventDefault();

        // Ambil ID dan informasi data untuk ditampilkan di SweetAlert
        const dataId = $(this).data('id');
        const dataInfo = $(this).data('info');

        // Tampilkan SweetAlert konfirmasi hapus
        Swal.fire({
            title: "Yakin ingin menghapus?",
            text: `Data ${dataInfo} akan dihapus secara permanen.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "HAPUS",
            cancelButtonText: "BATAL"
        }).then((result) => {
            if (result.isConfirmed) {
                // Kirim permintaan hapus ke server jika dikonfirmasi
                axios.post("{{ route('logdoc.poDocPerVendor.masterRing.delete', '') }}/" + dataId, {
                    _method: 'POST', // Karena route menggunakan metode POST
                    _token: '{{ csrf_token() }}' // Token CSRF
                })
                .then(response => {
                    Swal.fire({
                        title: "Terhapus!",
                        text: response.data.message || "Data berhasil dihapus.",
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then(() => {
                        location.reload(); // Muat ulang halaman untuk memperbarui tabel setelah notifikasi sukses
                    });
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire("Error", "Terjadi kesalahan dalam menghapus data.", "error");
                });
            }
        });
    });

</script>
@endsection
