<nav class="pcoded-navbar sidebar-fixed">
    <div class="nav-list">
        <div class="pcoded-inner-navbar main-menu">
            <div class="pcoded-navigation-label">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu {{ menuActive(['users.*', 'permissions.*', 'roles.*', 'activity.logs.*']) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Dashboard</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Usuarios</span>
                            </a>
                        </li>                        
                        <li class="{{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                            <a href="{{ route('permissions.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Permisos</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Roles</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('activity.logs.*') ? 'active' : '' }}">
                            <a href="{{ route('activity.logs.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Historial de Actividad</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('asociados.index') }}" class="nav-link {{ request()->routeIs('asociados.*') ? 'active' : '' }}">
                                <i class="feather icon-users"></i>
                                <span class="menu-title">Asociados</span>
                            </a>
                        </li>                        
                    </ul>
                </li>
                <li class="pcoded-hasmenu {{ menuActive(['printec-categories*', 'category-mappings*', 'warehouses*', 'catalogo.*']) }}">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                        <span class="pcoded-mtext">Productos</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="nav-item {{ request()->is('printec-categories*') ? 'active' : '' }}">
                            <a href="{{ url('/printec-categories') }}" class="nav-link">
                                <span class="pcoded-mtext">Categorías Internas</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('category-mappings*') ? 'active' : '' }}">
                            <a href="{{ url('/category-mappings') }}" class="nav-link">
                                <span class="pcoded-mtext">Asignar Categorías</span>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('warehouses*') ? 'active' : '' }}">
                            <a href="{{ url('/warehouses') }}" class="nav-link">                                
                                <span class="pcoded-mtext">Almacenes</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('catalogo.*') ? 'active' : '' }}">
                            <a href="{{ route('catalogo.index') }}" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Catalogo</span>
                            </a>
                        </li>
                        <!--li class=" pcoded-hasmenu">
                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Vertical</span>
                            </a>
                            <ul class="pcoded-submenu">
                                <li class="">
                                    <a href="menu-static.html" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Static Layout</span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="menu-header-fixed.html" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Header Fixed</span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="menu-compact.html" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Compact</span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="menu-sidebar.html" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Sidebar Fixed</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class=" pcoded-hasmenu">
                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Horizontal</span>
                            </a>
                            <ul class="pcoded-submenu">
                                <li class="">
                                    <a href="menu-horizontal-static.html" target="_blank" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Static Layout</span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="menu-horizontal-fixed.html" target="_blank" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Fixed layout</span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="menu-horizontal-icon.html" target="_blank" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Static With Icon</span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="menu-horizontal-icon-fixed.html" target="_blank" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Fixed With Icon</span>
                                    </a>
                                </li>
                            </ul>
                        </li-->
                    </ul>
                </li>
                <!--li class="">
                    <a href="navbar-light.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-menu"></i>
                        </span>
                        <span class="pcoded-mtext">Navigation</span>
                    </a>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-layers"></i>
                        </span>
                        <span class="pcoded-mtext">Widget</span>
                        <span class="pcoded-badge label label-danger">100+</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="widget-statistic.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Statistic</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="widget-data.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Data</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="widget-chart.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Chart Widget</span>
                            </a>
                        </li>
                    </ul>
                </li-->
            </ul>
            <!--div class="pcoded-navigation-label">UI Element</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-box"></i>
                        </span>
                        <span class="pcoded-mtext">Basic</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="alert.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Alert</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="breadcrumb.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Breadcrumbs</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="button.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Button</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="box-shadow.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Box-Shadow</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="accordion.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Accordion</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="generic-class.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Generic Class</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="tabs.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Tabs</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="color.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Color</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="label-badge.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Label Badge</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="progress-bar.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Progress Bar</span>
                            </a>
                        </li>

                        <li class=" ">
                            <a href="list.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">List</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="tooltip.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Tooltip And Popover</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="typography.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Typography</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="other.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Other</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-gitlab"></i>
                        </span>
                        <span class="pcoded-mtext">Advance</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="draggable.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Draggable</span>
                            </a>
                        </li>


                        </li>
                        <li class=" ">
                            <a href="modal.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Modal</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="notification.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Notifications</span>
                            </a>
                        </li>

                        <li class=" ">
                            <a href="rating.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Rating</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="range-slider.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Range Slider</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="slider.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Slider</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="syntax-highlighter.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Syntax Highlighter</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="tour.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Tour</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="treeview.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Tree View</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="nestable.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Nestable</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="toolbar.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Toolbar</span>
                            </a>
                        </li>

                    </ul>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-package"></i>
                        </span>
                        <span class="pcoded-mtext">Extra</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="session-timeout.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Session Timeout</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="session-idle-timeout.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Session Idle Timeout</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="offline.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Offline</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class=" ">
                    <a href="animation.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-aperture rotate-refresh"></i>
                        </span>
                        <span class="pcoded-mtext">Animations</span>
                    </a>
                </li>

                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-command"></i>
                        </span>
                        <span class="pcoded-mtext">Icons</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="icon-font-awesome.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Font Awesome</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="icon-themify.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Themify</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="icon-simple-line.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Simple Line Icon</span>
                            </a>
                        </li>

                    </ul>
                </li>
            </ul>
            <div class="pcoded-navigation-label">Forms</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-clipboard"></i>
                        </span>
                        <span class="pcoded-mtext">Form</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="form-elements-component.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Components</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="form-elements-add-on.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Add-On</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="form-elements-advance.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Advance</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="form-validation.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Validation</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class=" ">
                    <a href="form-picker.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-edit-1"></i>
                        </span>
                        <span class="pcoded-mtext">Form Picker</span>
                        <span class="pcoded-badge label label-warning">NEW</span>
                    </a>
                </li>
                <li class=" ">
                    <a href="form-select.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-feather"></i>
                        </span>
                        <span class="pcoded-mtext">Form Select</span>
                    </a>
                </li>
                <li class=" ">
                    <a href="form-masking.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-shield"></i>
                        </span>
                        <span class="pcoded-mtext">Form Masking</span>
                    </a>
                </li>
                <li class=" ">
                    <a href="form-wizard.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-tv"></i>
                        </span>
                        <span class="pcoded-mtext">Form Wizard</span>
                    </a>
                </li>
            </ul>
            <div class="pcoded-navigation-label">Tables</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-credit-card"></i>
                        </span>
                        <span class="pcoded-mtext">Bootstrap Table</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="bs-basic-table.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Basic Table</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="bs-table-sizing.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Sizing Table</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="bs-table-border.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Border Table</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="bs-table-styling.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Styling Table</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-inbox"></i>
                        </span>
                        <span class="pcoded-mtext">Data Table</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="dt-basic.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Basic Initialization</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-advance.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Advance Initialization</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-styling.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Styling</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-api.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">API</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ajax.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Ajax</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-server-side.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Server Side</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-plugin.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Plug-In</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-data-sources.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Data Sources</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-server"></i>
                        </span>
                        <span class="pcoded-mtext">DT Extensions</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class=" ">
                            <a href="dt-ext-autofill.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">AutoFill</span>
                            </a>
                        </li>
                        <li class="pcoded-hasmenu">
                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Button</span>
                            </a>
                            <ul class="pcoded-submenu">
                                <li class=" ">
                                    <a href="dt-ext-basic-buttons.html" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Basic Button</span>
                                    </a>
                                </li>
                                <li class=" ">
                                    <a href="dt-ext-buttons-html-5-data-export.html" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Data Export</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-col-reorder.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Col Reorder</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-fixed-columns.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Fixed Columns</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-fixed-header.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Fixed Header</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-key-table.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Key Table</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-responsive.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Responsive</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-row-reorder.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Row Reorder</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-scroller.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Scroller</span>
                            </a>
                        </li>
                        <li class=" ">
                            <a href="dt-ext-select.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Select Table</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class=" ">
                    <a href="foo-table.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-hash"></i>
                        </span>
                        <span class="pcoded-mtext">FooTable</span>
                    </a>
                </li>
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-airplay"></i>
                        </span>
                        <span class="pcoded-mtext">Handson Table</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="handson-appearance.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Appearance</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-data-operation.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Data Operation</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-rows-cols.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Rows Columns</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-columns-only.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Columns Only</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-cell-features.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Cell Features</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-cell-types.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Cell Types</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-integrations.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Integrations</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-rows-only.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Rows Only</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="handson-utilities.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Utilities</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="">
                    <a href="editable-table.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-edit"></i>
                        </span>
                        <span class="pcoded-mtext">Editable Table</span>
                    </a>
                </li>
            </ul>
            <div class="pcoded-navigation-label">Chart And Maps</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-pie-chart"></i>
                        </span>
                        <span class="pcoded-mtext">Charts</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="chart-google.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Google Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-chartjs.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">ChartJs</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-list.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">List Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-float.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Float Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-knob.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Knob chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-morris.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Morris Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-nvd3.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Nvd3 Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-peity.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Peity Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-radial.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Radial Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-rickshaw.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Rickshaw Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-sparkline.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Sparkline Chart</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="chart-c3.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">C3 Chart</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-map"></i>
                        </span>
                        <span class="pcoded-mtext">Maps</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="map-google.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Google Maps</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="map-vector.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Vector Maps</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="map-api.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Google Map Search API</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="location.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Location</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="pcoded-navigation-label">Pages</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-unlock"></i>
                        </span>
                        <span class="pcoded-mtext">Authentication</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="auth-sign-in-social.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Login</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="auth-sign-up-social.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Registration</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="auth-reset-password.html" target="_blank" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Forgot Password</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="auth-lock-screen.html" target="_blank" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Lock Screen</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-sliders"></i>
                        </span>
                        <span class="pcoded-mtext">Maintenance</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="error.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Error</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="comming-soon.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Comming Soon</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="offline-ui.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Offline UI</span>
                            </a>
                        </li>
                    </ul>
                </li>


                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-mail"></i>
                        </span>
                        <span class="pcoded-mtext">Email</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="email-compose.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Compose Email</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="email-inbox.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Inbox</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="email-read.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Read Mail</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="pcoded-navigation-label">App</div>
            <ul class="pcoded-item pcoded-left-item">

                <li class="">
                    <a href="todo.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-bookmark"></i>
                        </span>
                        <span class="pcoded-mtext">To-Do</span>
                    </a>
                    
                </li>


            </ul>
            <div class="pcoded-navigation-label">Extension</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-file-plus"></i>
                        </span>
                        <span class="pcoded-mtext">Editor</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="ck-editor.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">CK-Editor</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="wysiwyg-editor.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">WYSIWYG Editor</span>
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-calendar"></i>
                        </span>
                        <span class="pcoded-mtext">Event Calendar</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="event-full-calender.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Full Calendar</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="event-clndr.html" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">CLNDER</span>
                                <span class="pcoded-badge label label-info">NEW</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="">
                    <a href="image-crop.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-scissors"></i>
                            <b>IC</b>
                        </span>
                        <span class="pcoded-mtext">Image Cropper</span>
                    </a>
                </li>
                <li class="">
                    <a href="file-upload.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-upload-cloud"></i>
                        </span>
                        <span class="pcoded-mtext">File Upload</span>
                    </a>
                </li>
                <li class="">
                    <a href="change-loges.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-briefcase"></i>
                        </span>
                        <span class="pcoded-mtext">Change Loges</span>
                        <span class="pcoded-badge label label-warning">1.0</span>
                    </a>
                </li>
            </ul>
            <div class="pcoded-navigation-label">Other</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="pcoded-hasmenu ">
                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-list"></i>
                        </span>
                        <span class="pcoded-mtext">Menu Levels</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="">
                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Menu Level 2.1</span>
                            </a>
                        </li>
                        <li class="pcoded-hasmenu ">
                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Menu Level 2.2</span>
                            </a>
                            <ul class="pcoded-submenu">
                                <li class="">
                                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                                        <span class="pcoded-mtext">Menu Level 3.1</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="">
                            <a href="javascript:void(0)" class="waves-effect waves-dark">
                                <span class="pcoded-mtext">Menu Level 2.3</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="">
                    <a href="javascript:void(0)" class="disabled waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-power"></i>
                            <b>D</b>
                        </span>
                        <span class="pcoded-mtext">Disabled Menu</span>
                    </a>
                </li>
                <li class="">
                    <a href="sample-page.html" class="waves-effect waves-dark">
                        <span class="pcoded-micon">
                            <i class="feather icon-watch"></i>
                        </span>
                        <span class="pcoded-mtext">Sample Page</span>
                    </a>
                </li>
            </ul-->
            
        </div>
    </div>
</nav>