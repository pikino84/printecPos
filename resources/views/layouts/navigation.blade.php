<nav class="pcoded-navbar sidebar-fixed">
    <div class="nav-list">
        <div class="pcoded-inner-navbar main-menu">
            <div class="pcoded-navigation-label">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                {{-- ========== ADMINISTRACIÓN ========== --}}
                <li class="pcoded-hasmenu {{ menuActive([
                    'partners.*', 'users.*', 'my-users.*', 'permissions.*', 'roles.*',
                    'activity.logs.*', 'clients.*', 'warehouses.*', 'my-warehouses.*',
                    'pricing-dashboard.*', 'pricing-tiers.*', 'partner-pricing.*',
                    'pricing-reports.*', 'pricing-settings.*',
                    'printec-cities*', 'activity-logs*'
                ]) }}">
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

                        {{-- Solo mostrar "Mis Usuarios" para roles de Asociado --}}
                        @if(auth()->user()->hasRole('Asociado Administrador|Asociado Vendedor') && !auth()->user()->hasRole('super admin'))
                        <li class="{{ request()->routeIs('my-users.*') ? 'active' : '' }}">
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

                        @can('activity-logs.view')
                        <li class="{{ request()->is('activity-logs*') || request()->routeIs('activity.logs.*') ? 'active' : '' }}">
                            <a href="{{ route('activity.logs.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Historial de Actividad</span>
                            </a>
                        </li>
                        @endcan

                        @can('clients.view')
                        <li class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                            <a href="{{ route('clients.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Clientes</span>
                            </a>
                        </li>
                        @endcan

                        @can('ciudades')
                        <li class="{{ request()->is('printec-cities*') ? 'active' : '' }}">
                            <a href="{{ url('/printec-cities') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Ciudades</span>
                            </a>
                        </li>
                        @endcan

                        @can('almacenes')
                        <li class="{{ request()->is('warehouses*') && !request()->is('*my-warehouses*') ? 'active' : '' }}">
                            <a href="{{ url('/warehouses') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Almacenes</span>
                            </a>
                        </li>
                        @endcan

                        {{-- Almacenes del partner - Solo para Asociados (no super admin) --}}
                        @if(auth()->user()->hasRole('Asociado Administrador|Asociado Vendedor') && !auth()->user()->hasRole('super admin'))
                        <li class="{{ request()->routeIs('my-warehouses.*') ? 'active' : '' }}">
                            <a href="{{ route('my-warehouses.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Almacenes</span>
                            </a>
                        </li>
                        @endif

                        {{-- ========== PRICING ========== --}}
                        @can('pricing-dashboard.view')
                        <li class="{{ request()->routeIs('pricing-dashboard.*') || request()->is('pricing-dashboard*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-dashboard.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Dashboard de Pricing</span>
                            </a>
                        </li>
                        @endcan

                        @can('pricing-tiers.view')
                        <li class="{{ request()->routeIs('pricing-tiers.*') || request()->is('pricing-tiers*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-tiers.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Niveles de Precio</span>
                            </a>
                        </li>
                        @endcan

                        @can('partner-pricing.view')
                        <li class="{{ request()->routeIs('partner-pricing.*') || request()->is('partner-pricing*') ? 'active' : '' }}">
                            <a href="{{ route('partner-pricing.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Pricing de Partners</span>
                            </a>
                        </li>
                        @endcan

                        @can('pricing-reports.view')
                        <li class="{{ request()->routeIs('pricing-reports.tier-history') || request()->is('pricing-reports/tier-history*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-reports.tier-history') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Historial de Niveles</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('pricing-reports.monthly-purchases') || request()->is('pricing-reports/monthly-purchases*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-reports.monthly-purchases') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Compras Mensuales</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('pricing-reports.partner-evolution') || request()->is('pricing-reports/partner-evolution*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-reports.partner-evolution') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Evolución por Partner</span>
                            </a>
                        </li>
                        @endcan

                        @can('pricing-settings.view')
                        <li class="{{ request()->routeIs('pricing-settings.*') || request()->is('pricing-settings*') ? 'active' : '' }}">
                            <a href="{{ route('pricing-settings.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Config. de Pricing</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>

                {{-- ========== DISTRIBUIDOR ========== --}}
                @if(auth()->user()->can('razones-sociales.view') || auth()->user()->can('cuentas-bancarias.view') || auth()->user()->hasRole('Asociado Administrador|Asociado Vendedor'))
                <li class="pcoded-hasmenu {{ menuActive([
                    'my-entities.*', 'my-bank-accounts.*', 'my-markup.*',
                    'razones-sociales*', 'cuentas-bancarias*', 'mi-ganancia*'
                ]) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-briefcase"></i></span>
                        <span class="pcoded-mtext">Distribuidor</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @can('razones-sociales.view')
                        <li class="{{ request()->routeIs('my-entities.*') || request()->is('razones-sociales*') ? 'active' : '' }}">
                            <a href="{{ route('my-entities.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Razones Sociales</span>
                            </a>
                        </li>
                        @endcan

                        @can('cuentas-bancarias.view')
                        <li class="{{ request()->routeIs('my-bank-accounts.*') || request()->is('cuentas-bancarias*') ? 'active' : '' }}">
                            <a href="{{ route('my-bank-accounts.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Cuentas Bancarias</span>
                            </a>
                        </li>
                        @endcan

                        @if(auth()->user()->hasRole('Asociado Administrador|Asociado Vendedor'))
                        <li class="{{ request()->routeIs('my-markup.*') || request()->is('mi-ganancia*') ? 'active' : '' }}">
                            <a href="{{ route('my-markup.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Mi Ganancia</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- ========== PRODUCTOS ========== --}}
                <li class="pcoded-hasmenu {{ menuActive([
                    'catalogo.*', 'quotes.*', 'own-products.*', 'my-categories.*',
                    'printec-categories*', 'category-mappings*', 'own-products*'
                ]) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                        <span class="pcoded-mtext">Productos</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @can('categorias internas')
                        <li class="{{ request()->is('printec-categories*') ? 'active' : '' }}">
                            <a href="{{ url('/printec-categories') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Categorías Internas</span>
                            </a>
                        </li>
                        @endcan

                        {{-- Categorías del partner - Solo para Asociados (no super admin) --}}
                        @if(auth()->user()->hasRole('Asociado Administrador|Asociado Vendedor') && !auth()->user()->hasRole('super admin'))
                        <li class="{{ request()->routeIs('my-categories.*') || request()->is('mis-categorias*') ? 'active' : '' }}">
                            <a href="{{ route('my-categories.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Categorías</span>
                            </a>
                        </li>
                        @endif

                        @can('asignar categoria')
                        <li class="{{ request()->is('category-mappings*') ? 'active' : '' }}">
                            <a href="{{ url('/category-mappings') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Asignar Categorías</span>
                            </a>
                        </li>
                        @endcan

                        @can('catalog.view')
                        <li class="{{ request()->routeIs('catalogo.*') || request()->is('catalogo*') ? 'active' : '' }}">
                            <a href="{{ route('catalogo.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Catalogo</span>
                            </a>
                        </li>
                        @endcan

                        @can('quotes.view')
                        <li class="{{ request()->routeIs('quotes.*') || request()->is('quotes*') ? 'active' : '' }}">
                            <a href="{{ route('quotes.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Mis Cotizaciones</span>
                            </a>
                        </li>
                        @endcan

                        @can('view-own-products')
                        <li class="{{ request()->is('own-products*') ? 'active' : '' }}">
                            <a href="{{ route('own-products.index') }}" class="waves-effect waves-dark">
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
