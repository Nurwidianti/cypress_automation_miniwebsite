<footer class="c-footer">
    <div>MIS &copy; 2022</div>
    <div class="ml-auto">Powered by&nbsp;Tim MIS</div>
</footer>

<div class="modal fade" id="password" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <form class="modal-content" action="{{ route('password.ubah') }}" method="post">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">UBAH PASSWORD</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Password Baru</label>
                    <input
                        style="box-shadow: none !important;"
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >
                    <div class="w-100 d-flex justify-content-center">
                        <span id="password-strength" class="text-center font-weight-bold"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input
                        style="box-shadow: none !important;"
                        type="password"
                        name="re_password"
                        class="form-control"
                        required
                    >
                    <div class="w-100 d-flex justify-content-center">
                        <span id="password-confirm" class="text-center font-weight-bold d-none">Password tidak cocok</span>
                    </div>
                </div>
                <div>
                    <small class="text-danger d-none" confirm-pw>PASSWORD dan KONFIRMASI PASSWORD harus sama</small>
                </div>
                <div>
                    <small>Password minimal 6 karakter, harus mengandung angka, huruf besar, huruf kecil, dan tidak boleh sama.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
                <button type="submit" class="btn btn-primary">SIMPAN</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="whatsapp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">NOMOR WHATSAPP</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('whatsapp.ubah') }}" method="post">@csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" id="nowa" name="nowa" class="form-control" onkeypress="return hanyaNumber(event)" required>
                    </div>
                    <!--<div class="form-group">
                        <label>Ketik Ulang Password Baru</label>
                        <input type="password" id="txtPassword2" name="txtPassword" class="form-control" required>
                    </div>-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
                    <button id="simpanWa" type="button" class="btn btn-primary">SIMPAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--modal fraud-->
<div class="modal fade" id="modal_fraud" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="javascript:void(0)" id="search-form">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="input-group">
                        <input name="q" id="cari_menu" class="form-control" type="text" placeholder="Pencarian..." aria-label="Pencarian" autocomplete="off" autofocus>
                        <button type="button" class="btn btn-success"><i class="icon icon-xxl mt-5 mb-2 cil-mic"></i></button>
                        <button class="btn btn-primary" id="searchBtn" type="button"><i class="icon icon-xxl mt-5 mb-2 cil-magnifying-glass"></i></button>
                    </div>
                </div>
                <div class="row">
                    <table id ="tblcari2" class="table table-striped table-sm table-bordered mr-4 ml-4">
                        <tr></tr>
                    </table>
                </div>
                <div id="modal_startup" class="modal-body">
                    <div class="slideshow-container">
                        <img src="/poster/{{ $modal_startup ?? '' }}" style="width:100%">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="modal_close" class="btn btn-secondary d-block" data-dismiss="modal">CLOSE</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modal_view" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">TOP MENU</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <table id ="tbltop" class="table table-striped table-sm table-bordered mr-4 ml-4">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>MENU</th>
                                <th>DILIHAT</th>
                            </tr>
                        </thead>
                        <tbody id="top_view">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<!--modal cari-->
<!--<div class="modal fade" id="modal_cari" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"-->
<!--    aria-hidden="true">-->
<!--    <div class="modal-dialog" role="document">-->
<!--        <form action="javascript:void(0);" id="search-form2">-->
<!--            <div class="modal-content">-->
<!--                <div class="modal-header">-->
<!--                    <div class="input-group">-->
<!--                        <input name="q" id="cari_menu2" class="form-control" type="text" placeholder="Pencarian..." aria-label="Pencarian" autocomplete="off" autofocus>-->
<!--                        <button type="button" class="btn btn-success"><i class="icon icon-xxl mt-5 mb-2 cil-mic"></i></button>-->
<!--                        <button class="btn btn-primary" id="searchBtn2" type="button"><i class="icon icon-xxl mt-5 mb-2 cil-magnifying-glass"></i></button>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="row">-->
<!--                    <table id ="tblcari" class="table table-striped table-sm table-bordered mr-4 ml-4 mt-2">-->
<!--                        <tr></tr>-->
<!--                    </table>-->
<!--                </div>-->
<!--            </div>-->
<!--        </form>-->
<!--    </div>-->
<!--</div>-->
<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/sweetalert.js') }}"></script>
<script>
    $(document).ready(() => {
        const password_categories = [
            {
                input: 'border-secondary',
                label: 'text-secondary',
                text: 'Tidak ada password',
            },
            {
                input: 'border-danger',
                label: 'text-danger',
                text: 'Terlalu lemah',
            },
            {
                input: 'border-warning',
                label: 'text-warning',
                text: 'Lemah',
            },
            {
                input: 'border-primary',
                label: 'text-primary',
                text: 'Sedang',
            },
            {
                input: 'border-success',
                label: 'text-success',
                text: 'Kuat',
            },
            {
                input: 'border-success',
                label: 'text-success',
                text: 'Sangat kuat',
            },
        ];

        const check_pw_level = i => ([i.length >= 8, /[A-Z]/.test(i), /[a-z]/.test(i), /[0-9]/.test(i), /[\W_]/.test(i)]).filter(x => x).length;

        const check_password_match = () => $('.modal#password [name="password"]').val() === $('.modal#password [name="re_password"]').val();

        $('.modal#password [name="password"]').on('input', e => {
            const password_level = check_pw_level($(e.currentTarget).val());
            // remove
            $('.modal#password [name="password"]').removeClass(password_categories.map(({input}) => input).join(' '));
            $('#password-strength').removeClass(password_categories.map(({label}) => label).join(' '));
            // add
            $('.modal#password [name="password"]').addClass(password_categories[password_level].input);
            $('#password-strength').addClass(password_categories[password_level].label).text(password_categories[password_level].text);

            $('#password-confirm').toggleClass('d-none', check_password_match());
        });

        $('.modal#password [name="re_password"]').on('input', () => {
            $('#password-confirm').toggleClass('d-none', check_password_match());
        });
    });
</script>
<script>
function hanyaNumber(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}

$(document).ready(function() {
    modal_whatsapp();
    load_top_view();
    $("#cari_menu").keyup(function(event) {
        if (event.keyCode === 13) {
            $("#searchBtn").click();
        }
    });

    // $("#cari_menu2").keyup(function(event) {
    //     if (event.keyCode === 13) {
    //         $("#searchBtn2").click();
    //     }
    // });

    $("#searchBtn").click(function(e) {
        e.preventDefault();
        var _token = $("input[name='_token']").val();
        var transcript = $("#cari_menu").val();
        $.get('/q/' + transcript, function (data) {
            var no = 1;
            var trHTML = "";
            // $("#tblcari tr").remove();
            // $.each(JSON.parse(data), function (i, item) {
            //     trHTML += "<tr><td> &nbsp;<a href='"+ item.link +"'>" + item.name + "</a></td></tr>";
            // });
            // $('#tblcari').append(trHTML);

            $("#tblcari2 tr").remove();
            $.each(JSON.parse(data), function (i, item) {
                trHTML += "<tr><td> &nbsp;<a href='" + item.link +"'>" + item.name + "</a></td></tr>";
            });
            $('#tblcari2').append(trHTML);
            var modal_startup = document.getElementById("modal_startup");
            modal_startup.style.display = "none";
        });
    });

    $("#searchBtn2").click(function(e) {
        e.preventDefault();
        var _token = $("input[name='_token']").val();
        var transcript = $("#cari_menu2").val();
        $.get('/q/' + transcript, function (data) {
            var no = 1;
            var trHTML = "";
            // $("#tblcari tr").remove();
            // $.each(JSON.parse(data), function (i, item) {
            //     trHTML += "<tr><td> &nbsp;<a href='"+ item.link +"'>" + item.name + "</a></td></tr>";
            // });
            // $('#tblcari').append(trHTML);

            $("#tblcari2 tr").remove();
            $.each(JSON.parse(data), function (i, item) {
                trHTML += "<tr><td> &nbsp;<a href='" + item.link +"'>" + item.name + "</a></td></tr>";
            });
            $('#tblcari2').append(trHTML);
            var modal_startup = document.getElementById("modal_startup");
            modal_startup.style.display = "none";
        });
    });

    // $("#simpanPassword").click(function(e) {
    //     e.preventDefault();
    //     var _token = $("input[name='_token']").val();
    //     var nik = "{{ Auth::user()->nik }}";
    //     var password = $("#txtPassword").val();
    //     $.ajax({
    //         url: "{{ route('password.ubah') }}",
    //         type: "POST",
    //         dataType: 'json',
    //         data: {
    //             _token: _token,
    //             nik: nik,
    //             password: password
    //         },
    //         success: function(data) {
    //             alert("Password berhasil diubah");
    //             $('.close').click();
    //         }
    //     });
    // });

    $("#modal_close").click(function(e) {
        e.preventDefault();
        $('#modal_fraud').modal('hide');
    });

    function modal_whatsapp(){
        var _token = $("input[name='_token']").val();
        $.post("{{ route('whatsapp.modal') }}", {
            _token: _token,
        }, function(response) {
            $('[name="nowa"]').val(response[0].nowa);
        });
    }

    $("#simpanWa").click(function(e) {
        e.preventDefault();
        var _token = $("input[name='_token']").val();
        var nowa = $("#nowa").val();
        $.ajax({
            url: "{{ route('whatsapp.ubah') }}",
            type: "POST",
            dataType: 'json',
            data: {
                _token: _token,
                nowa: nowa
            },
            success: function(response) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 2500
                })
                $('.close').click();
            }
        });
    });

    function load_top_view(){
        $.get('/top_view', function (data) {
            var no = 1;
            var trHTML = "";
            $("#top_view tr").remove();
                $.each(JSON.parse(data), function (i, item) {
                    trHTML += "<tr style='text-align:center'><td>" + no++ + "</td><td style='text-align:left'>" + item.name + "</td><td style='width:50px'>" + numberWithCommas(item.dilihat) + "</td></tr>";
                });
            $('#top_view').append(trHTML);
        });
    }

    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
});

    function exportToExcel(tableId,JudulLaporan){
            let tableData = document.getElementById(tableId).outerHTML;
            tableData = '<strong>'+JudulLaporan+'</strong>'+tableData;
            tableData = tableData.replace(/<A[^>]*>|<\/A>/g, ""); //remove if u want links in your table
            tableData = tableData.replace(/<input[^>]*>|<\/input>/gi, ""); //remove input params


            let a = document.createElement('a');
            a.href = `data:application/vnd.ms-excel, ${encodeURIComponent(tableData)}`
            a.download = JudulLaporan+ '_' + getRandomNumbers() + '.xls'
            a.click()
        }

        function getRandomNumbers() {
            let dateObj = new Date()
            let dateTime = `${dateObj.getHours()}${dateObj.getMinutes()}${dateObj.getSeconds()}`
            return `${dateTime}${Math.floor((Math.random().toFixed(2)*100))}`
    }
</script>

<script>
    $(document).ready(() => {
        const check_for_password = @json(check_for_password());
        if (/* check_for_password */ false) {
            const modalPasswordElement = $('.modal#password');
            modalPasswordElement.modal({backdrop: 'static'});
            modalPasswordElement.modal('show');

            // const pw = modalPasswordElement.find('[name="password"]');
            // const re_pw = modalPasswordElement.find('[name="re_password"]');

            // pw.on('input', () => {$('small[confirm-pw]').toggleClass('d-none', pw.val() === re_pw.val())});
            // re_pw.on('input', () => {$('small[confirm-pw]').toggleClass('d-none', pw.val() === re_pw.val())});
        }
    });
</script>