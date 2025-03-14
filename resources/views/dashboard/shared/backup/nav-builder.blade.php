
        
        <div class="c-sidebar-brand">
            <img class="c-sidebar-brand-full" src="{{ url('/assets/img/logo-oval-small.png') }}" width="110" height="46" alt="MIS App">
            <img class="c-sidebar-brand-minimized" src="{{ url('/assets/img/logo-oval-small.png') }}" width="110" height="46" alt="MIS App">
        </div>
        <ul class="c-sidebar-nav">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="/home">                       
                    <i class="cil-speedometer c-sidebar-nav-icon"></i>Dashboard    
                </a>
            </li>
            @can('isAdmin')
            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                    <i class="cil-3d c-sidebar-nav-icon"></i>Master
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/master_am2023') }}"><span class="c-sidebar-nav-icon"></span>AM 2023</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/master_rekap_pembelian_doc') }}"><span class="c-sidebar-nav-icon"></span>Rekap Pembelian DOC</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/master_rekap_pembelian_pakan') }}"><span class="c-sidebar-nav-icon"></span>Rekap Pembelian Pakan</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/master_rekap_pembelian_ovk') }}"><span class="c-sidebar-nav-icon"></span>Rekap Pembelian OVK</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/master_harga_pakan/index') }}"><span class="c-sidebar-nav-icon"></span>Harga Pakan & DOC</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/master_pelatihan') }}"><span class="c-sidebar-nav-icon"></span>Pelatihan</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/realisasi_panen') }}"><span class="c-sidebar-nav-icon"></span>Realisasi Panen</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/harga_perhari') }}"><span class="c-sidebar-nav-icon"></span>Panen Perhari</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/tbl_std_umur') }}"><span class="c-sidebar-nav-icon"></span>Tabel STD By Umur</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/tbl_performance') }}"><span class="c-sidebar-nav-icon"></span>Tabel Performance</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/rhpp') }}"><span class="c-sidebar-nav-icon"></span>RHPP</a>
                    </li>
                     <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/kpi') }}"><span class="c-sidebar-nav-icon"></span>Master KPI KP/TS</a>
                    </li>
                    <!--
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/source-sales') }}"><span class="c-sidebar-nav-icon"></span>Source Sales</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/source-finance') }}"><span class="c-sidebar-nav-icon"></span>Source Finance</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/source-finance/bop') }}"><span class="c-sidebar-nav-icon"></span>Source BOP Total</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/source-logistik') }}"><span class="c-sidebar-nav-icon"></span>Source Logistik</a>
                    </li
                    -->
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/source-hpp') }}"><span class="c-sidebar-nav-icon"></span>Source HPP</a>
                    </li>
                    <!--
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/source-harga/list') }}"><span class="c-sidebar-nav-icon"></span>Source Harga Penjualan</a>
                    </li>
                    -->
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/source-stok-ayam') }}"><span class="c-sidebar-nav-icon"></span>Source Stok Ayam</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/video') }}"><span class="c-sidebar-nav-icon"></span>Upload Video</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/materi') }}"><span class="c-sidebar-nav-icon"></span>Upload Materi</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/podcast') }}"><span class="c-sidebar-nav-icon"></span>Video Podcast</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/grd') }}"><span class="c-sidebar-nav-icon"></span>Master Grade TS & KP</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/user') }}"><span class="c-sidebar-nav-icon"></span>Users</a>
                    </li>
                <!--<li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/grdkp') }}"><span class="c-sidebar-nav-icon"></span>Grade KP</a>
                    </li>-->
                </ul>
            </li> 
            @endcan
        <!--    
            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                    <i class="cil-puzzle c-sidebar-nav-icon"></i>Evaluasi
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/video/list') }}"><span class="c-sidebar-nav-icon"></span>Video</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/materi/list') }}"><span class="c-sidebar-nav-icon"></span>PDF</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/survei/list') }}"><span class="c-sidebar-nav-icon"></span>Survei</a>
                    </li>                
                </ul>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="{{ url('/evaluasi') }}">                       
                    <i class="cil-puzzle c-sidebar-nav-icon"></i>Evaluasi    
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="{{ url('/video/list') }}">                       
                    <i class="cil-video c-sidebar-nav-icon"></i>Video    
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="{{ url('/materi/list') }}">                       
                    <i class="cil-file c-sidebar-nav-icon"></i>Materi    
                </a>
            </li> 


            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="cil-star c-sidebar-nav-icon"></i>KPI
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/kpi-kaprod') }}"><span class="c-sidebar-nav-icon"></span>Kepala Produksi</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/kpi-ts') }}"><span class="c-sidebar-nav-icon"></span>Technical Support</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/kpi-logistik') }}"><span class="c-sidebar-nav-icon"></span>Admin Logistik</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/kpi-finace') }}"><span class="c-sidebar-nav-icon"></span>Admin Finance</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/kpi-sales') }}"><span class="c-sidebar-nav-icon"></span>Sales</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/kpi-bulan') }}"><span class="c-sidebar-nav-icon"></span>KPI Bulanan</a>
                    </li>
                </ul>
            </li>
            -->
            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                    <i class="cil-bar-chart c-sidebar-nav-icon"></i>Operation
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/produksi') }}"><span class="c-sidebar-nav-icon"></span>Produksi</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/logdoc') }}"><span class="c-sidebar-nav-icon"></span>Logistik DOC</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/logpakan') }}"><span class="c-sidebar-nav-icon"></span>Logistik Pakan</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/penjualan') }}"><span class="c-sidebar-nav-icon"></span>Penjualan</a>
                    </li>
                </ul>
            </li>

            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                    <i class="cil-voice-over-record c-sidebar-nav-icon"></i>Human Capital
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/hr') }}"><span class="c-sidebar-nav-icon"></span>Human Resources</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/ld') }}"><span class="c-sidebar-nav-icon"></span>Learning And Development</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/qa') }}"><span class="c-sidebar-nav-icon"></span>Quality Assurance</a>
                    </li>
                </ul>
            </li>

            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                    <i class="cil-dollar c-sidebar-nav-icon"></i>TAF
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/accounting') }}"><span class="c-sidebar-nav-icon"></span>Accounting</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/finance') }}"><span class="c-sidebar-nav-icon"></span>Finance</a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/tax') }}"><span class="c-sidebar-nav-icon"></span>Tax</a>
                    </li>
                </ul>
            </li>

            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                    <i class="cil-settings c-sidebar-nav-icon"></i>Settings
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="javascript:void(0)" data-toggle="modal" data-target="#password"><span class="c-sidebar-nav-icon"></span>Password</a>
                    </li>
                </ul>
            </li> 
                        
        
        </ul>

        
        <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
        
    </div>