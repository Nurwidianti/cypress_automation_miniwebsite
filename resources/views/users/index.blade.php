@extends('dashboard.base')

@section('content')
 <div class="container-fluid">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="cil-description"></i> {{ __('MARGIN') }} 
                        <button type="button" class="btn btn-primary float-lg-right" onclick="window.location='{{ url("produksi") }}'">KEMBALI</button></h3>
                    </div>
                    <div class="card-body">
                      <div class="container-fluid">
                          {{$dataTable->table()}}
                      </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
  </div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<script src="/vendor/datatables/buttons.server-side.js"></script>
{!! $dataTable->scripts() !!}
@endpush