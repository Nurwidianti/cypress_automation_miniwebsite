@extends('dashboard.base')

@section('content')
    <div class="container-fluid">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="cil-description"></i> {{ __('EDIT USER') }} </h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ url("/user/update/{$id}") }}" method="POST" enctype="multipart/form-data">
                            @csrf
                                <table class="table table-sm">
                                    <input type="hidden" class="form-control" name="id" id="id" value="{{ $id }}"/>
                                    <tr>
                                        <th> NIK </th>
                                        <th style="width:10px"> : </th>
                                        <th><input type="text" class="form-control" name="nik" id="nik" value="{{ $user->nik }}"/></th>
                                    </tr>
                                    <tr>
                                        <th> NAMA </th>
                                        <th> : </th>
                                        <th><input type="text" class="form-control" name="nama" id="nama" value="{{ $user->name }}"/></th>
                                    </tr>
                                    <tr>
                                        <th> JABATAN </th>
                                        <th> : </th>
                                        <th>
                                            <select class="form-control" name="jabatan" id="jabatan">
                                                @foreach ($jabatan as $jabatans)
                                                    <option value="{{$jabatans->nama}}" {{ $jabatans->nama == $user->jabatan ? 'selected' : '' }}>{{$jabatans->nama}}</option>
                                                @endforeach
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th> ROLES </th>
                                        <th> : </th>
                                        <th>
                                            <select class="form-control" name="roles" id="roles">
                                                {{ cboUserRoles() }}
                                                <option selected value="{{ $user->roles }}" hidden>{{ Str::upper($user->roles) }}</option>
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th> REGION </th>
                                        <th> : </th>
                                        <th>
                                            <select class="form-control" name="region" id="region">
                                                @foreach ($region as $regions)
                                                    <option value="{{$regions->koderegion}}" {{ $regions->koderegion == $user->region ? 'selected' : '' }}>{{$regions->namaregion}}</option>
                                                @endforeach
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th> UNIT </th>
                                        <th> : </th>
                                        <th>
                                            <select class="form-control" name="unit" id="unit">
                                                @foreach ($unit as $u)
                                                    <option value="{{ $u->kodeunit }}" {{ $u->kodeunit == $user->unit ? 'selected' : '' }}>{{ '['.$u->kodeunit.'] '.$u->namaunit }}</option>
                                                @endforeach
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th> UPLOAD FOTO </th>
                                        <th> : </th>
                                        <th> <input type="file" name="file" class="form-control"> </th>
                                    </tr>
                                    <tr>
                                        <th> PASSWORD </th>
                                        <th> : </th>
                                        <th>
                                            <input type="password" name="password" id="password" class="form-control">
                                        </th>
                                    </tr>
                                    <tr>
                                        <th> CONFIRM PASSWORD </th>
                                        <th> : </th>
                                        <th>
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                                            <span id='message'></span>
                                        </th>
                                    </tr>
                                </table>
                                <div class="modal-footer">
                                    <a href="{{ route('user.index') }}" class="btn btn-secondary">KEMBALI</a>
                                    <button type="submit" class="btn btn-success">SIMPAN</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
<script src="{{ asset('js/sweetalert.js') }}"></script>
<script>
    $(document).ready(function(){
        var region = $('select[name="region"]').val();
        $.ajax({
            type:"GET",
            url:"/user/"+region,
            dataType: 'JSON',
            success:function(res){
                if(res){
                    if (region == "MJL") {
                        $("#unit").append('<option value="HO">[HO] HEAD OFFICE</option>');
                    } else {
                        $("#unit").append('<option value="HO '+region+'">[HO '+region+'] HEAD OFFICE PT '+region+'</option>');
                        $.each(res,function(namaunit,kodeunit){
                            $("#unit").append('<option value="'+kodeunit+'">['+kodeunit+'] '+namaunit+'</option>');
                        });
                    }
                }else{
                    $('select[name="unit"]').empty();
                }
            }
        });
    })

    $('#region').change(function(){
        var region = $(this).val();
        console.log(region);

        if(region){
            $.ajax({
                type:"GET",
                url:"/user/"+region,
                dataType: 'JSON',
                success:function(res){
                    if(res){
                        $("#unit").empty();
                        $("#unit").append('<option selected hidden>PILIH</option>');
                        if (region == "MJL") {
                            $("#unit").append('<option value="HO">[HO] HEAD OFFICE</option>');
                        } else {
                            $("#unit").append('<option value="HO '+region+'">[HO '+region+'] HEAD OFFICE PT '+region+'</option>');
                            $.each(res,function(namaunit,kodeunit){
                                $("#unit").append('<option value="'+kodeunit+'">['+kodeunit+'] '+namaunit+'</option>');
                            });
                        }
                    }else{
                        $('#unit').empty();
                    }
                }
            });
        }
    });

    // Validasi password dan confirm password saat submit form
    $('form').on('submit', function (e) {
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== '' || confirmPassword !== '') {
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChar = /[!@#\$%\^&\*]/.test(password);

            // Validasi jika password dan konfirmasi tidak sesuai
            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Password dan Konfirmasi Password tidak sesuai!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
            // Validasi aturan password minimal 8 karakter, harus ada huruf besar, kecil, angka, dan simbol
            else if (password.length < 8 || !hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecialChar) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan karakter khusus.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        }
    });

    // Cek password dan konfirmasi password saat pengguna mengetik
    $('#confirm_password').on('keyup', function () {
        if ($('#password').val() == $('#confirm_password').val()) {
            $('#message').html('Password sama').css('color', 'green');
        } else {
            $('#message').html('Password tidak sama').css('color', 'red');
        }
    });
</script>
@endsection
