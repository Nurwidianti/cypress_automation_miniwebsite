@extends('dashboard.authBase')

@section('content')

    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card-group">
            <div class="card p-4">
              <div class="card-body">
                <h1>Reset Password</h1>
                <p class="text-muted">Reset your password</p>
                <form method="POST" action="{{ route('password_reset_finalize') }}">
                    @csrf
                    <div class="input-group mb-2">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <svg class="c-icon">
                          <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-user"></use>
                        </svg>
                      </span>
                    </div>
                    <input value="{{ session('nik') ?? '' }}" class="form-control" type="text" placeholder="{{ __('NIK') }}" name="nik" required autofocus>
                    </div>
                    <div class="input-group mb-4">
                      @if (session('msg') !== null)
                      <div class="p-2 alert alert-{{ session('success') ? 'success' : 'danger' }}">
                        <span class="text-{{ session('success') ? 'success' : 'danger' }}">{{ session('msg') }}</span>
                      </div>
                      @endif
                    </div>
                    <div class="row">
                    <div class="col-6">
                        <button class="btn btn-primary px-4" type="submit">{{ __('Reset') }}</button>
                    </div>
                    </form>
                    <div class="col-6 text-right">
                      <a href="{{ route('login') }}" class="btn btn-link px-0 text-nowrap">{{ __('Login with account') }}</a>
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

@endsection

