@extends('dashboard.authBase')

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card-group">
          <div class="card p-4">
            <div class="card-body">
              <h1>Masukan Kode</h1>
              <p class="text-muted">Masukan kode verifikasi</p>
              <form method="POST" action="{{ route('password_reset_finalize') }}">
                @csrf
                <div class="input-group mb-2">
                  <input type="hidden" name="nik" value="{{ session('nik_reset') }}">
                  <input class="form-control" type="text" placeholder="{{ __('KODE') }}"
                    name="kode" required autofocus>
                  <div class="input-group-append">
                    <button class="btn btn-light text-primary" type="button" id="send-code">Kirim Kode</button>
                  </div>
                </div>
                <div class="input-group mb-4">
                  @if (session('msg') !== null)
                  <div class="p-2 alert alert-danger">
                    <span class="text-danger">{{ session('msg') }}</span>
                  </div>
                  @endif
                </div>
                <div class="row">
                  <div class="col-6">
                    <button class="btn btn-primary px-4" type="submit">{{ __('Reset') }}</button>
                  </div>
              </form>
              <div class="col-6 text-right">
                <a href="#"
                  class="btn btn-link px-0 text-nowrap">{{ __('Login with account') }}</a>
              </div>
            </div>
          </div>
        </div>
        <div class="card text-white bg-primary py-5 d-md-down-none" style="width:44%">
          <div class="card-body text-center">
            <div>
              <h4><b>MANAGEMENT INFORMATION SYSTEM</b></h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
@endsection

@section('javascript')
<script src="{{ asset('js/jquery.js') }}"></script>
<script>
  $(document).ready(() => {
    const nik = @json(session('nik_reset') ?? null);
    const first_squish = @json(session('first_squish'));

    const sendCode = () => {
      if (nik !== null) {
        const token = $('input[name="_token"]').val();
        $.ajax({
          type: 'POST',
          url: '/password_reset_send_code_by_wa',
          data: {
            _token: token,
            nik,
          },
          success: (res) => {
            console.log(res);
          },
          error: (err) => {
            console.log(err);
          },
        });
      }
    };

    $('#send-code').click((e) => {
      const button = $(e.currentTarget);
      sendCode();
      button.prop('disabled', true);
      let delay = 30;
      const exec = setInterval(() => {
        button.text(`Kirim lagi ${delay}`);
        if (delay <= 0) {
          button.prop('disabled', false);
          button.text(`Kirim Kode`);
          clearInterval(exec);
        }
        delay -= 1;
      }, 1000);
    });

    if (first_squish) {
      $('#send-code').trigger('click');
    }
  });
</script>
@endsection
