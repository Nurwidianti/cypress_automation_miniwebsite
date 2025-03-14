
    @php

    @endphp
    <div class="c-wrapper">
      <header class="c-header c-header-light c-header-fixed c-header-with-subheader">
        <button class="c-header-toggler c-class-toggler d-lg-none mr-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show"><span class="c-header-toggler-icon"></span></button><a class="c-header-brand d-sm-none" href="#"><img class="c-header-brand" src="{{ url('/assets/img/logo-oval-small.png') }}" width="97" height="46" alt="MIS App"></a>
        <button id="nav" class="c-header-toggler c-class-toggler ml-3 d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true"><span class="c-header-toggler-icon"></span></button>
        <?php
            use App\MenuBuilder\FreelyPositionedMenus;
            if(isset($appMenus['top menu'])){
                FreelyPositionedMenus::render( $appMenus['top menu'] , 'c-header-', 'd-md-down-none');
            }
        ?>
        <ul class="c-header-nav ml-auto mr-4">
            <!-- yaqin start -->
            <style>
                #show-notif {
                    max-height: 400px;
                    overflow-y: auto;
                }
                /* #dropdown-notif::-webkit-scrollbar {
                    display: none;
                } */
            </style>
            <li class="nav-item">
                <div id="dropdown-notifikasi" class="dropdown">
                    <button style="outline: none; box-shadow: none;" class="btn align-items-center mr-3" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="cil-bell"></i>
                        <span id="count-notifikasi" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white"></span>
                    </button>
                    <div id="show-notif" class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                        <!-- <a href="#" class="dropdown-item">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">MOTION</span>
                                <span>Seseorang mengirimkan sesuatu</span>
                            </div>
                        </a> -->
                        <!-- <span class="dropdown-item">Tidak ada notifikasi</span> -->
                    </div>
                </div>
            </li>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const maxLength = 40; // jumlah karakter yang ditampilkan
                    var elements = document.querySelectorAll(".dropdown-item span");
                    elements.forEach(function(element) {
                        if (element.textContent) {
                        var text = element.textContent;
                            if (text.length > maxLength) {
                                element.textContent = text.substring(0, maxLength) + "...";
                            }
                        }
                    });
                });
            </script>
            <!-- yaqin end -->

          <li class="c-header-nav-item d-md-down-none mx-2">
            <a class="c-header-nav-link"><b>{{ strtoupper(Auth::user()->name) }}</b></a>
          </li>
          <li class="c-header-nav-item dropdown"><a style="text-decoration:none" class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
              <div class="c-avatar">
                @if(file_exists(public_path('users/'.Auth::user()->nik.'_'.Auth::user()->name.'.png')))
                    <img class="c-avatar-img" src="{{ asset('users/'.Auth::user()->nik.'_'.Auth::user()->name.'.png') }}" alt="IMG"></div>
                @elseif(file_exists(public_path('assets/img/users/'.Auth::user()->nik.'_'.Auth::user()->name.'.png')))
                    <img class="c-avatar-img" src="{{ asset('assets/img/users/'.Auth::user()->nik.'_'.Auth::user()->name.'.png') }}" alt="IMG"></div>
                @else
                    <img class="c-avatar-img" src="{{ asset('assets/img/users/none.png') }}" alt="IMG"></div>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right pt-0">

              <div class="dropdown-header bg-light py-2"><strong>Settings</strong></div>
              <a class="dropdown-item" href="javascript:void(0)">
                <svg class="c-icon mr-2">
                  <use xlink:href="{{ url('/icons/sprites/free.svg#cil-user') }}"></use>
                </svg> Profile
              </a>

              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#password">
                <svg class="c-icon mr-2">
                  <use xlink:href="{{ url('/icons/sprites/free.svg#cil-lock-locked') }}"></use>
                </svg>
                Password
              </a>
              <a class="dropdown-item" href="{{ url('/logout') }}">
                <svg class="c-icon mr-2">
                  <use xlink:href="{{ url('/icons/sprites/free.svg#cil-account-logout') }}"></use>
                </svg>
                Logout
              </a>
            </div>
          </li>
        </ul>

    </header>
