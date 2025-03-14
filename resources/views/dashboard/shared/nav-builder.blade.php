

        <div class="c-sidebar-brand">

        </div>
        <ul class="c-sidebar-nav">
            <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="/home">
                    <i class="cil-speedometer c-sidebar-nav-icon"></i>Dashboard
                </a>
            </li>
            @if(Gate::check('isAdmin') || Gate::check('isDewi'))
                <li class="c-sidebar-nav-dropdown">
                    <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                        <i class="cil-3d c-sidebar-nav-icon"></i>Master
                    </a>
                    <ul class="c-sidebar-nav-dropdown-items">
                        <li class="c-sidebar-nav-item">
                            <a class="c-sidebar-nav-link" href="{{ url('/user') }}"><span class="c-sidebar-nav-icon"></span>Users</a>
                        </li>
                    </ul>
                </li>
            @endif
            <li class="c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="javascript:void(0)">
                    <i class="cil-bar-chart c-sidebar-nav-icon"></i>Operation
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="{{ url('/logdoc') }}"><span class="c-sidebar-nav-icon"></span>Logistik DOC</a>
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
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a class="c-sidebar-nav-link" href="javascript:void(0)" data-toggle="modal" data-target="#whatsapp"><span class="c-sidebar-nav-icon"></span>WhatsApp</a>
                    </li>
                </ul>
            </li>
        </ul>
        <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
    </div>
