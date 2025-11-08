<nav class="pcoded-navbar sidebar-fixed">
    <div class="nav-list">
        <div class="pcoded-inner-navbar main-menu">
            <div class="pcoded-navigation-label">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu {{ menuActive(['partners.*', 'users.*', 'permissions.*', 'roles.*', 'activity.logs.*']) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Administración</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @can('partners_index')
                            <li class="{{ request()->routeIs('partners.*') ? 'active' : '' }}">
                                <a href="{{ route('partners.index') }}" class="waves-effect waves-dark">
                                    <span class="pcoded-mtext">Partners</span>
                                </a>
                            </li>
                        @endcan
                        @can('manage users')
                        <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Usuarios</span>
                            </a>
                        </li>
                        @endcan
                        
                        
                        @can('permisos')                   
                        <li class="{{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                            <a href="{{ route('permissions.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Permisos</span>
                            </a>
                        </li>
                        @endcan
                        @can('roles')
                        <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Roles</span>
                            </a>
                        </li>
                        @endcan
                        @can('actividad')
                        <li class="{{ request()->routeIs('activity.logs.*') ? 'active' : '' }}">
                            <a href="{{ route('activity.logs.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Historial de Actividad</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                <li class="pcoded-hasmenu {{ menuActive(['my-entities.*', 'my-bank-accounts.*', 'my-users.*', 'my-warehouses.*', 'my-categories.*', 'own-products.*']) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                        <span class="pcoded-mtext">Asociado</span>
                    </a>
                    <ul class="pcoded-submenu">
                    <li class="{{ menuActive(['my-entities.*']) }}">
                        <a href="{{ route('my-entities.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-mtext">Razones Sociales</span>
                        </a>
                    </li>
                    <li class="{{ menuActive(['my-bank-accounts.*']) }}">
                        <a href="{{ route('my-bank-accounts.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-mtext">Cuentas Bancarias</span>
                        </a>
                    </li>
                    {{-- Solo mostrar "Usuarios" si NO es super admin --}}
                    @if(!auth()->user()->hasRole('super admin'))
                    <li class="{{ menuActive(['my-users.*']) }}">
                        <a href="{{ route('my-users.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-mtext">Usuarios</span>
                        </a>
                    </li>
                    @endif
                    {{-- 🆕 Almacenes --}}
                    @if(!auth()->user()->hasRole('super admin'))
                    <li class="{{ menuActive(['my-warehouses.*']) }}">
                        <a href="{{ route('my-warehouses.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-mtext">Almacenes</span>
                        </a>
                    </li>
                    @endif
                    {{-- 🆕 Categorías --}}
                    @if(!auth()->user()->hasRole('super admin'))
                    <li class="{{ menuActive(['my-categories.*']) }}">
                        <a href="{{ route('my-categories.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-mtext">Categorías</span>
                        </a>
                    </li>
                    @endif
                     @can('view-own-products')
                    <li class="nav-item {{ request()->is('own-products*') ? 'active' : '' }}">
                        <a href="{{ route('own-products.index') }}" class="nav-link">
                            <span class="pcoded-mtext">Productos Propios</span>
                        </a>
                    </li>
                    @endcan
                </ul>
                </li>
                <li class="pcoded-hasmenu {{ menuActive(['printec-cities*', 'printec-categories*', 'category-mappings*', 'warehouses*', 'catalogo.*', 'quotes.*', 'clients.*']) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                        <span class="pcoded-mtext">Productos</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @can('ciudades')
                            <li class="nav-item {{ request()->is('printec-cities*') ? 'active' : '' }}">
                                <a href="{{ url('/printec-cities') }}" class="nav-link">
                                    <span class="pcoded-mtext">Ciudades</span>
                                </a>
                            </li>
                        @endcan
                        @can('almacenes')
                            <li class="nav-item {{ request()->is('warehouses*') ? 'active' : '' }}">
                                <a href="{{ url('/warehouses') }}" class="nav-link">
                                    <span class="pcoded-mtext">Almacenes</span>
                                </a>
                            </li>
                        @endcan
                        @can('categorias internas')
                            <li class="nav-item {{ request()->is('printec-categories*') ? 'active' : '' }}">
                                <a href="{{ url('/printec-categories') }}" class="nav-link">
                                    <span class="pcoded-mtext">Categorías Internas</span>
                                </a>
                            </li>
                        @endcan
                        <li class="nav-item {{ request()->is('category-mappings*') ? 'active' : '' }}">
                            <a href="{{ url('/category-mappings') }}" class="nav-link">
                                <span class="pcoded-mtext">Asignar Categorías</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('catalogo.*') ? 'active' : '' }}">
                            <a href="{{ route('catalogo.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Catalogo</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                            <a href="{{ route('quotes.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Mis Cotizaciones</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <a href="{{ route('clients.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Clientes</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>