@extends('dashboard.base')

@section('content')
    <div class="container-fluid">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-header"> 
                            <form class="" action="{{ route('slideshow.list') }}" method="get">@csrf
                                <div class="form-group float-lg-right">
                                  <div class="input-group">
                                    <input class="form-control" type="text" name="cari" placeholder="Pencarian" value="{{ $cari }}"><span class="input-group-append">
                                    <button class="btn btn-primary" type="submit">Cari</button></span>
                                  </div>
                                </div>                 
                            </form>
                            <h3><i class="cil-description"></i> {{ __('ARSIP SLIDE') }}</h3>   
                        </div>
                        <div class="card-body">    
                            <div class="row"> 
                            @foreach($picture as $data)
                                <div class="col-md-3"> 
                                    <div class="card" style="text-align:center"> 
                                        <a href="#" style="text-decoration:none" data-toggle="modal" data-target="#img_{{ RemTitikSpasi($data->file) }}">
                                            <div style="margin:10px 0px 0px 0px">
                                                <img class="frame" src="/slideshow/{{ $data->file }}" style="width:201px;height:133px;margin-top:3px">
                                            </div>
                                            <div class="card-body">
                                                <h6 class="card-tittle">{{ $data->nama }}</h6>
                                            </div>
                                        </a>
                                    </div> 
                                </div>
                                
                              <!--modal-->
                              <div class="modal fade" id="img_{{ RemTitikSpasi($data->file) }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                  <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                                  <form action="{{ route('slideshow.hapus', $data->file) }}" method="post">@csrf
                                      <div class="modal-content">
                                          <div class="modal-header">
                                              <h5 class="modal-title">{{ $data->nama }}</h5>
                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                  <span aria-hidden="true">&times;</span>
                                              </button>
                                          </div>                                         
                                          <div class="modal-body">
                                            <img src="/slideshow/{{ $data->file }}" style="width:762px;height:428px">
                                          </div>
                                          @if(Gate::check('isAdmin'))
                                          <div class="modal-footer">
                                              <a href="{{ route('slideshow.edit', $data->file) }}" class="btn btn-primary">  UBAH  </a>
                                              <button type="submit" class="btn btn-danger">HAPUS</button>
                                          </div>  
                                          @endif 
                                      </div>
                                  </form>
                                  </div>
                              </div>

                            @endforeach
                        </div>                
                        <div class="table-bottom">
                            <div class="float-lg-left">
                                <strong>Jumlah : {{ $jml }}</strong>
                            </div>
                            <div class="float-lg-right">
                                {{ $picture->links() }}
                            </div>
                        </div>
                        </br> </br>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection


@section('javascript')

@endsection

