@extends('dashboard.base')

@section('content')
        <div class="container-fluid">
          <div class="animated fadeIn">
            <div class="row">
              <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="card">
                    <div class="card-header">
                      <h3>
                          <i class="cil-description"></i> {{ __('DATA LOGISTIK DOC') }}
                          @if(Gate::check('isAdmin') || Gate::check('isTiwi'))
                            <button style="margin-right:10px" type="button" class="btn btn-danger float-lg-right" onclick="window.location='{{ url("logpakan/rasio/master") }}'">MASTER</button>
                          @endif
                      </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-sm table-bordered">
                        <thead class="thead-dark">
                          <tr>
                            <th style="width:30px"> NO </th>
                            <th> DATA LOGISTIK DOC </th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($logdoc as $data)
                            <tr>
                                <td>{{ ++$no }}</td>
                                <td>
                                    <div id="dilihat_{{ $data->id }}" data-link="{{ $data->link }}" data-id="{{ $data->id }}"><a href="javascript:void(0)" style="text-decoration:none">{{  $data->name }}</a> &nbsp; &nbsp; <span>{{ dilihat_menu($data->dilihat) }}</span></div>
                                </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                      {{ $logdoc->links() }}
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>

@endsection


@section('javascript')
<script>
    const arrId = @json($arrId);
    var _token = $("input[name='_token']").val();
    arrId.forEach(function(arrId) {
        $('#dilihat_'+arrId).click(function(e) {
            var id = $(this).attr('data-id');
            var linkURL = $(this).attr('data-link');
            $.post("{{ route('home.dilihat') }}", {
                _token: _token,
                id: id,
                tabel: 'menu_logdoc'
            }, function(response) {
                window.location.href = linkURL;
                // if (id == 6) {
                //     window.location.href = linkURL+'/1970-01-01/1970-01-01/0/0/0';
                // } else {
                // }
            });
        });
    });
</script>
@endsection

