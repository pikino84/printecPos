<nav class="pcoded-navbar sidebar-fixed">
    <div class="nav-list">
        <div class="pcoded-inner-navbar main-menu">
            <div class="pcoded-navigation-label">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu {{ menuActive(['partners.*', 'users.*','my-users.*', 'permissions.*', 'roles.*', 'activity.logs.*', 'clients.*','printec-cities.*', 'warehouses.*', 'my-warehouses.*']) }}">
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
                        {{-- Solo mostrar "Usuarios" si NO es super admin --}}
                        @if(!auth()->user()->hasRole('super admin'))
                        <li class="{{ menuActive(['my-users.*']) }}">
                            <a href="{{ route('my-users.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Usuarios</span>
                            </a>
                        </li>
                        @endif
                        
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
                        <li class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <a href="{{ route('clients.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Clientes</span>
                            </a>
                        </li>
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
                        {{-- 🆕 Almacenes --}}
                        @if(!auth()->user()->hasRole('super admin'))
                        <li class="{{ menuActive(['my-warehouses.*']) }}">
                            <a href="{{ route('my-warehouses.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Almacenes</span>
                            </a>
                        </li>
                        @endif
                        {{-- 🆕 NIVELES DE PRECIO --}}
                        @can('manage users')
                        <li class="{{ request()->routeIs('pricing-dashboard.*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-dashboard.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Dashboard de Pricing</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('pricing-tiers.*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-tiers.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Niveles de Precio</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('partner-pricing.*') ? 'active' : '' }}">
                            <a href="{{ route('partner-pricing.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Pricing de Partners</span>
                            </a>
                        </li>
                        {{-- Reportes de Pricing --}}
                        <li class="{{ request()->routeIs('pricing-reports.tier-history') ? 'active' : '' }}">
                            <a href="{{ route('pricing-reports.tier-history') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Historial de Niveles</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('pricing-reports.monthly-purchases') ? 'active' : '' }}">
                            <a href="{{ route('pricing-reports.monthly-purchases') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Compras Mensuales</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('pricing-reports.partner-evolution') ? 'active' : '' }}">
                            <a href="{{ route('pricing-reports.partner-evolution') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Evolución por Partner</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                <li class="pcoded-hasmenu {{ menuActive(['my-entities.*', 'my-bank-accounts.*']) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                        <span class="pcoded-mtext">Asociado</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @can('my-entities')
                        <li class="{{ menuActive(['my-entities.*']) }}">
                            <a href="{{ route('my-entities.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Razones Sociales</span>
                            </a>
                        </li>
                        @endcan
                        <li class="{{ menuActive(['my-bank-accounts.*']) }}">
                            <a href="{{ route('my-bank-accounts.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Cuentas Bancarias</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu {{ menuActive([ 'printec-categories*', 'my-categories.*', 'category-mappings*', 'catalogo.*', 'quotes.*', 'own-products.*']) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                        <span class="pcoded-mtext">Productos</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @can('categorias internas')
                            <li class="nav-item {{ request()->is('printec-categories*') ? 'active' : '' }}">
                                <a href="{{ url('/printec-categories') }}" class="nav-link">
                                    <span class="pcoded-mtext">Categorías Internas</span>
                                </a>
                            </li>
                        @endcan
                        {{-- 🆕 Categorías --}}
                        @if(!auth()->user()->hasRole('super admin'))
                        <li class="{{ menuActive(['my-categories.*']) }}">
                            <a href="{{ route('my-categories.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Categorías</span>
                            </a>
                        </li>
                        @endif
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
                        @can('view-own-products')
                        <li class="nav-item {{ request()->is('own-products*') ? 'active' : '' }}">
                            <a href="{{ route('own-products.index') }}" class="nav-link">
                                <span class="pcoded-mtext">Productos Propios</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>