/**
 * Sistema de Tours Guiados - PrintecPOS
 * Configuración de tours por rol usando Intro.js
 */

const PrintecTour = {
    // Configuración global de Intro.js
    options: {
        nextLabel: 'Siguiente',
        prevLabel: 'Anterior',
        skipLabel: 'Omitir',
        doneLabel: '¡Listo!',
        hidePrev: true,
        hideNext: false,
        showProgress: true,
        showBullets: true,
        showStepNumbers: false,
        scrollToElement: true,
        scrollPadding: 80,
        exitOnOverlayClick: false,
        exitOnEsc: true,
        disableInteraction: false,
    },

    // Tours por rol
    tours: {
        // ========================================
        // SUPER ADMIN - Tour completo
        // ========================================
        'super-admin': {
            dashboard: [
                {
                    title: '¡Bienvenido a PrintecPOS!',
                    intro: 'Este tour te guiará por las principales funcionalidades del sistema. Como Super Administrador, tienes acceso completo a todas las funciones.'
                },
                {
                    element: '.pcoded-navbar',
                    title: 'Menú de Navegación',
                    intro: 'Aquí encontrarás todas las secciones del sistema organizadas por categorías: Administración, Distribuidor y Productos.',
                    position: 'right'
                },
                {
                    element: '.pcoded-hasmenu:first-child',
                    title: 'Sección Administración',
                    intro: 'Gestiona Partners, Usuarios, Permisos, Roles, Historial de Actividad, Clientes, Ciudades, Almacenes y todo el módulo de Pricing.',
                    position: 'right'
                },
                {
                    element: '#user-dropdown',
                    title: 'Menú de Usuario',
                    intro: 'Desde aquí puedes acceder a tu perfil, cotizaciones y cerrar sesión. También puedes repetir este tour cuando quieras.',
                    position: 'bottom'
                }
            ],
            partners: [
                {
                    element: '.card',
                    title: 'Lista de Partners',
                    intro: 'Aquí puedes ver todos los partners (socios comerciales) registrados en el sistema con su información de contacto y estado.'
                },
                {
                    element: '.btn-primary',
                    title: 'Crear Nuevo Partner',
                    intro: 'Haz clic aquí para registrar un nuevo partner en el sistema.',
                    position: 'left'
                },
                {
                    element: '.btn-info',
                    title: 'Ver Detalle',
                    intro: 'Visualiza la información completa del partner, incluyendo usuarios y productos asociados.',
                    position: 'top'
                },
                {
                    element: '.btn-success, .btn-outline-secondary',
                    title: 'Configurar API',
                    intro: 'Genera o revoca la API Key del partner para acceso al catálogo y obtén el código del widget para integrar en su sitio web. Verde indica API activa.',
                    position: 'top'
                },
                {
                    element: '.btn-outline-primary',
                    title: 'Razones Sociales',
                    intro: 'Administra las razones sociales (entidades fiscales) del partner para facturación y sus cuentas bancarias.',
                    position: 'top'
                },
                {
                    element: '.btn-warning',
                    title: 'Editar Partner',
                    intro: 'Modifica la información del partner: nombre, contacto, tipo y estado.',
                    position: 'top'
                },
                {
                    element: '.btn-danger',
                    title: 'Eliminar Partner',
                    intro: 'Elimina el partner del sistema. Esta acción requiere confirmación.',
                    position: 'top'
                }
            ],
            users: [
                {
                    element: '.card',
                    title: 'Gestión de Usuarios',
                    intro: 'Administra todos los usuarios del sistema, sus roles y permisos.'
                },
                {
                    element: '.btn-primary',
                    title: 'Crear Usuario',
                    intro: 'Crea nuevos usuarios y asígnales roles específicos.',
                    position: 'left'
                }
            ],
            roles: [
                {
                    element: '.card',
                    title: 'Gestión de Roles',
                    intro: 'Los roles definen qué puede hacer cada tipo de usuario en el sistema.'
                },
                {
                    element: '.btn-primary',
                    title: 'Crear Rol',
                    intro: 'Define nuevos roles con permisos específicos.',
                    position: 'left'
                }
            ],
            permissions: [
                {
                    element: '.card',
                    title: 'Permisos del Sistema',
                    intro: 'Los permisos son las acciones específicas que pueden realizarse. Se asignan a los roles.'
                }
            ],
            'activity-logs': [
                {
                    element: '.card',
                    title: 'Historial de Actividad',
                    intro: 'Aquí puedes ver un registro de todas las acciones importantes realizadas en el sistema.'
                }
            ],
            clients: [
                {
                    element: '.card',
                    title: 'Gestión de Clientes',
                    intro: 'Administra la información de los clientes finales.'
                },
                {
                    element: '.btn-primary',
                    title: 'Nuevo Cliente',
                    intro: 'Registra nuevos clientes con su información de contacto.',
                    position: 'left'
                }
            ],
            'printec-cities': [
                {
                    element: '.card',
                    title: 'Ciudades',
                    intro: 'Gestiona las ciudades donde operan los almacenes de los proveedores.'
                }
            ],
            warehouses: [
                {
                    element: '.card',
                    title: 'Almacenes',
                    intro: 'Administra los almacenes de los proveedores y su inventario.'
                }
            ],
            'pricing-dashboard': [
                {
                    element: '.card',
                    title: 'Dashboard de Pricing',
                    intro: 'Vista general del sistema de precios escalonados basado en volumen de compras.'
                }
            ],
            'pricing-tiers': [
                {
                    element: '.card',
                    title: 'Niveles de Precio',
                    intro: 'Define los niveles de precio según el volumen de compras de cada partner.'
                }
            ],
            'partner-pricing': [
                {
                    element: '.card',
                    title: 'Pricing por Partner',
                    intro: 'Visualiza y gestiona los precios asignados a cada partner.'
                }
            ],
            'pricing-settings': [
                {
                    element: '.card',
                    title: 'Configuración de Pricing',
                    intro: 'Configura los parámetros globales del sistema de precios. Solo accesible para Super Admin.'
                }
            ],
            'razones-sociales': [
                {
                    element: '.card',
                    title: 'Razones Sociales',
                    intro: 'Gestiona las entidades fiscales de los partners.'
                }
            ],
            'cuentas-bancarias': [
                {
                    element: '.card',
                    title: 'Cuentas Bancarias',
                    intro: 'Administra las cuentas bancarias asociadas a cada razón social.'
                }
            ],
            'printec-categories': [
                {
                    element: '.card',
                    title: 'Categorías Internas',
                    intro: 'Define las categorías propias de Printec para organizar los productos.'
                }
            ],
            'category-mappings': [
                {
                    element: '.card',
                    title: 'Asignar Categorías',
                    intro: 'Mapea las categorías de los proveedores a las categorías internas de Printec.'
                }
            ],
            'own-products': [
                {
                    element: '.card',
                    title: 'Productos Propios',
                    intro: 'Gestiona los productos propios del partner, diferentes a los del catálogo general.'
                }
            ],
            catalogo: [
                {
                    element: '.card, .products-container, .row',
                    title: 'Catálogo de Productos',
                    intro: 'Explora todo el catálogo de productos disponibles. Puedes filtrar por categoría, proveedor y más.'
                }
            ],
            quotes: [
                {
                    element: '.card',
                    title: 'Mis Cotizaciones',
                    intro: 'Aquí puedes ver todas las cotizaciones que has creado, enviarlas por correo o descargarlas en PDF.'
                }
            ]
        },

        // ========================================
        // ADMIN - Tour sin roles/permisos/pricing-settings
        // ========================================
        'admin': {
            dashboard: [
                {
                    title: '¡Bienvenido a PrintecPOS!',
                    intro: 'Este tour te guiará por las principales funcionalidades del sistema. Como Administrador, tienes acceso a la mayoría de funciones.'
                },
                {
                    element: '.pcoded-navbar',
                    title: 'Menú de Navegación',
                    intro: 'Aquí encontrarás las secciones del sistema: Administración, Distribuidor y Productos.',
                    position: 'right'
                },
                {
                    element: '.pcoded-hasmenu:first-child',
                    title: 'Sección Administración',
                    intro: 'Gestiona Partners, Usuarios, Historial de Actividad, Clientes, Ciudades y Almacenes.',
                    position: 'right'
                },
                {
                    element: '#user-dropdown',
                    title: 'Menú de Usuario',
                    intro: 'Accede a tu perfil, cotizaciones y repite este tour cuando lo necesites.',
                    position: 'bottom'
                }
            ],
            partners: [
                {
                    element: '.card',
                    title: 'Lista de Partners',
                    intro: 'Visualiza y gestiona todos los partners registrados con su información de contacto.'
                },
                {
                    element: '.btn-info',
                    title: 'Ver Detalle',
                    intro: 'Visualiza la información completa del partner.',
                    position: 'top'
                },
                {
                    element: '.btn-outline-primary',
                    title: 'Razones Sociales',
                    intro: 'Administra las razones sociales del partner para facturación y sus cuentas bancarias.',
                    position: 'top'
                },
                {
                    element: '.btn-warning',
                    title: 'Editar Partner',
                    intro: 'Modifica la información del partner.',
                    position: 'top'
                }
            ],
            users: [
                {
                    element: '.card',
                    title: 'Gestión de Usuarios',
                    intro: 'Administra los usuarios del sistema y sus asignaciones.'
                }
            ],
            clients: [
                {
                    element: '.card',
                    title: 'Gestión de Clientes',
                    intro: 'Registra y administra la información de los clientes.'
                }
            ],
            warehouses: [
                {
                    element: '.card',
                    title: 'Almacenes',
                    intro: 'Gestiona los almacenes de los proveedores.'
                }
            ],
            'razones-sociales': [
                {
                    element: '.card',
                    title: 'Razones Sociales',
                    intro: 'Administra las entidades fiscales de los partners.'
                }
            ],
            'cuentas-bancarias': [
                {
                    element: '.card',
                    title: 'Cuentas Bancarias',
                    intro: 'Gestiona las cuentas bancarias de cada razón social.'
                }
            ],
            'printec-categories': [
                {
                    element: '.card',
                    title: 'Categorías Internas',
                    intro: 'Organiza los productos en categorías internas.'
                }
            ],
            'category-mappings': [
                {
                    element: '.card',
                    title: 'Asignar Categorías',
                    intro: 'Vincula categorías de proveedores con categorías internas.'
                }
            ],
            'own-products': [
                {
                    element: '.card',
                    title: 'Productos Propios',
                    intro: 'Gestiona productos propios del partner.'
                }
            ],
            catalogo: [
                {
                    element: '.card, .products-container, .row',
                    title: 'Catálogo de Productos',
                    intro: 'Explora el catálogo completo de productos disponibles.'
                }
            ],
            quotes: [
                {
                    element: '.card',
                    title: 'Mis Cotizaciones',
                    intro: 'Gestiona tus cotizaciones, envíalas por correo o descárgalas.'
                }
            ]
        },

        // ========================================
        // ASOCIADO ADMINISTRADOR
        // ========================================
        'asociado-administrador': {
            dashboard: [
                {
                    title: '¡Bienvenido a PrintecPOS!',
                    intro: 'Como Asociado Administrador, puedes gestionar tu equipo, clientes, cotizaciones y acceder al sistema de pricing.'
                },
                {
                    element: '.pcoded-navbar',
                    title: 'Menú de Navegación',
                    intro: 'Tu menú incluye: Administración (usuarios, clientes, almacenes, pricing), Distribuidor (razones sociales, cuentas, ganancia) y Productos.',
                    position: 'right'
                },
                {
                    element: '#user-dropdown',
                    title: 'Menú de Usuario',
                    intro: 'Accede a tu perfil y cotizaciones. Puedes repetir este tour cuando quieras.',
                    position: 'bottom'
                }
            ],
            'mis-usuarios': [
                {
                    element: '.card',
                    title: 'Mis Usuarios',
                    intro: 'Gestiona los usuarios de tu organización. Puedes crear vendedores y asignarles acceso.'
                },
                {
                    element: '.btn-primary',
                    title: 'Crear Usuario',
                    intro: 'Agrega nuevos usuarios a tu equipo.',
                    position: 'left'
                }
            ],
            clients: [
                {
                    element: '.card',
                    title: 'Mis Clientes',
                    intro: 'Registra y gestiona la información de tus clientes.'
                },
                {
                    element: '.btn-primary',
                    title: 'Nuevo Cliente',
                    intro: 'Agrega nuevos clientes a tu cartera.',
                    position: 'left'
                }
            ],
            'mis-almacenes': [
                {
                    element: '.card',
                    title: 'Mis Almacenes',
                    intro: 'Gestiona los almacenes asociados a tu organización.'
                }
            ],
            'pricing-dashboard': [
                {
                    element: '.card',
                    title: 'Dashboard de Pricing',
                    intro: 'Visualiza tu nivel de precios actual y el progreso hacia el siguiente nivel.'
                }
            ],
            'pricing-tiers': [
                {
                    element: '.card',
                    title: 'Niveles de Precio',
                    intro: 'Consulta los diferentes niveles de precio disponibles según el volumen de compras.'
                }
            ],
            'partner-pricing': [
                {
                    element: '.card',
                    title: 'Mi Pricing',
                    intro: 'Visualiza tu nivel de pricing actual y tu historial.'
                }
            ],
            'razones-sociales': [
                {
                    element: '.card',
                    title: 'Mis Razones Sociales',
                    intro: 'Administra las entidades fiscales de tu organización para facturación. Aquí puedes ver el logo, RFC, correo y estado de cada razón social.'
                },
                {
                    element: '.btn-primary',
                    title: 'Nueva Razón Social',
                    intro: 'Registra una nueva entidad fiscal con sus datos de facturación.',
                    position: 'left'
                },
                {
                    element: '.btn-warning',
                    title: 'Editar Razón Social',
                    intro: 'Modifica los datos de la razón social: nombre, RFC, dirección, teléfono, correo y logo.',
                    position: 'top'
                },
                {
                    element: '.btn-info',
                    title: 'Configurar Correo SMTP',
                    intro: 'Configura el servidor de correo (SMTP) para enviar cotizaciones desde tu propio dominio. Puedes probar la configuración antes de guardar.',
                    position: 'top'
                },
                {
                    element: '.btn-danger',
                    title: 'Eliminar',
                    intro: 'Elimina la razón social. Esta acción requiere confirmación.',
                    position: 'top'
                }
            ],
            'cuentas-bancarias': [
                {
                    element: '.card',
                    title: 'Mis Cuentas Bancarias',
                    intro: 'Gestiona las cuentas bancarias que aparecerán en tus cotizaciones para que tus clientes puedan realizar pagos. Puedes tener cuentas en diferentes monedas (MXN, USD, EUR).'
                },
                {
                    element: '.btn-primary',
                    title: 'Nueva Cuenta',
                    intro: 'Agrega una nueva cuenta bancaria asociada a una razón social.',
                    position: 'left'
                },
                {
                    element: '.btn-warning',
                    title: 'Editar Cuenta',
                    intro: 'Modifica los datos de la cuenta: banco, titular, número de cuenta, CLABE, SWIFT/IBAN y moneda.',
                    position: 'top'
                }
            ],
            'cuentas-bancarias-edit': [
                {
                    element: '.card',
                    title: 'Editar Cuenta Bancaria',
                    intro: 'Aquí puedes modificar todos los datos de la cuenta bancaria. Los campos se mostrarán en el PDF de tus cotizaciones.'
                },
                {
                    element: 'select[name="partner_entity_id"]',
                    title: 'Razón Social',
                    intro: 'Selecciona a qué razón social pertenece esta cuenta. La cuenta aparecerá en las cotizaciones que usen esta razón social.',
                    position: 'bottom'
                },
                {
                    element: 'input[name="bank_name"]',
                    title: 'Banco',
                    intro: 'Nombre del banco (ej: BBVA, Santander, Banamex).',
                    position: 'bottom'
                },
                {
                    element: 'input[name="clabe"]',
                    title: 'CLABE Interbancaria',
                    intro: 'Los 18 dígitos para transferencias SPEI en México. Se usa para pagos en pesos mexicanos (MXN).',
                    position: 'bottom'
                },
                {
                    element: 'input[name="swift"]',
                    title: 'Código SWIFT/BIC',
                    intro: 'Código internacional del banco. Necesario para recibir transferencias en dólares (USD) o euros (EUR) desde el extranjero.',
                    position: 'bottom'
                },
                {
                    element: 'input[name="iban"]',
                    title: 'IBAN',
                    intro: 'Número de cuenta internacional. Usado principalmente para transferencias en euros (EUR) desde Europa.',
                    position: 'bottom'
                },
                {
                    element: 'select[name="currency"]',
                    title: 'Moneda',
                    intro: 'Selecciona la moneda de la cuenta: MXN (Pesos), USD (Dólares) o EUR (Euros). En las cotizaciones se mostrarán las cuentas que coincidan con la moneda seleccionada.',
                    position: 'bottom'
                },
                {
                    element: '#is_default',
                    title: 'Cuenta Principal',
                    intro: 'Si marcas esta opción, esta cuenta se usará por defecto en las cotizaciones de esta razón social.',
                    position: 'top'
                }
            ],
            'mi-ganancia': [
                {
                    element: '.card',
                    title: 'Mi Ganancia',
                    intro: 'Configura el margen de ganancia (markup) que aplicarás sobre los productos del catálogo.'
                }
            ],
            'mis-categorias': [
                {
                    element: '.card',
                    title: 'Mis Categorías',
                    intro: 'Organiza tus productos en categorías personalizadas.'
                }
            ],
            'own-products': [
                {
                    element: '.card',
                    title: 'Mis Productos Propios',
                    intro: 'Agrega productos propios que no están en el catálogo general.'
                },
                {
                    element: '.btn-primary',
                    title: 'Nuevo Producto',
                    intro: 'Crea un nuevo producto propio.',
                    position: 'left'
                }
            ],
            catalogo: [
                {
                    element: '.card, .products-container, .row',
                    title: 'Catálogo de Productos',
                    intro: 'Explora el catálogo de productos. Los precios ya incluyen tu nivel de pricing.'
                }
            ],
            quotes: [
                {
                    element: '.card',
                    title: 'Mis Cotizaciones',
                    intro: 'Gestiona las cotizaciones para tus clientes. Puedes enviarlas por correo o descargarlas en PDF.'
                }
            ],
            cart: [
                {
                    element: '.card',
                    title: 'Carrito de Cotización',
                    intro: 'Aquí aparecen los productos que agregas desde el catálogo. Puedes ajustar cantidades y generar la cotización.'
                }
            ]
        },

        // ========================================
        // ASOCIADO VENDEDOR
        // ========================================
        'asociado-vendedor': {
            dashboard: [
                {
                    title: '¡Bienvenido a PrintecPOS!',
                    intro: 'Como Asociado Vendedor, puedes gestionar clientes, explorar el catálogo y crear cotizaciones.'
                },
                {
                    element: '.pcoded-navbar',
                    title: 'Menú de Navegación',
                    intro: 'Tu menú incluye: Clientes, Almacenes, Distribuidor (tu ganancia) y Productos (catálogo, cotizaciones).',
                    position: 'right'
                },
                {
                    element: '#user-dropdown',
                    title: 'Menú de Usuario',
                    intro: 'Accede a tu perfil y cotizaciones desde aquí.',
                    position: 'bottom'
                }
            ],
            clients: [
                {
                    element: '.card',
                    title: 'Mis Clientes',
                    intro: 'Registra y gestiona la información de tus clientes.'
                },
                {
                    element: '.btn-primary',
                    title: 'Nuevo Cliente',
                    intro: 'Agrega nuevos clientes para crear cotizaciones.',
                    position: 'left'
                }
            ],
            'mis-almacenes': [
                {
                    element: '.card',
                    title: 'Almacenes',
                    intro: 'Consulta los almacenes disponibles para tu organización.'
                }
            ],
            'mi-ganancia': [
                {
                    element: '.card',
                    title: 'Mi Ganancia',
                    intro: 'Consulta el margen de ganancia configurado por tu administrador.'
                }
            ],
            'mis-categorias': [
                {
                    element: '.card',
                    title: 'Categorías',
                    intro: 'Consulta las categorías de productos de tu organización.'
                }
            ],
            'own-products': [
                {
                    element: '.card',
                    title: 'Productos Propios',
                    intro: 'Consulta los productos propios de tu organización.'
                }
            ],
            catalogo: [
                {
                    element: '.card, .products-container, .row',
                    title: 'Catálogo de Productos',
                    intro: 'Explora el catálogo completo. Agrega productos al carrito para crear cotizaciones.'
                }
            ],
            quotes: [
                {
                    element: '.card',
                    title: 'Mis Cotizaciones',
                    intro: 'Aquí están todas tus cotizaciones. Puedes enviarlas por correo a tus clientes.'
                }
            ],
            cart: [
                {
                    element: '.card',
                    title: 'Carrito de Cotización',
                    intro: 'Los productos que agregues aparecerán aquí. Ajusta cantidades y genera la cotización.'
                }
            ]
        }
    },

    /**
     * Inicializar el sistema de tours
     */
    init: function() {
        // Verificar estado del tour al cargar
        this.checkTourStatus();

        // Configurar evento para botón de tour manual
        this.setupTourTrigger();
    },

    /**
     * Verificar si el usuario necesita ver el tour
     */
    checkTourStatus: function() {
        $.ajax({
            url: '/tour/status',
            type: 'GET',
            success: (response) => {
                const localStorageKey = `tour_completed_${response.role}`;
                const completedInLocalStorage = localStorage.getItem(localStorageKey) === 'true';

                // Si no ha completado el tour (ni en BD ni localStorage), iniciarlo automáticamente
                if (!response.tour_completed && !completedInLocalStorage) {
                    // Pequeño delay para que la página termine de cargar
                    setTimeout(() => {
                        this.startTour(response.role);
                    }, 1000);
                }

                // Guardar el rol actual para uso posterior
                this.currentRole = response.role;
            }
        });
    },

    /**
     * Configurar el botón para iniciar el tour manualmente
     */
    setupTourTrigger: function() {
        $(document).on('click', '#start-tour-btn', (e) => {
            e.preventDefault();

            // Resetear el tour en el servidor
            $.ajax({
                url: '/tour/reset',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: () => {
                    // Limpiar localStorage también
                    if (this.currentRole) {
                        localStorage.removeItem(`tour_completed_${this.currentRole}`);
                    }
                    this.startTour(this.currentRole || 'user');
                }
            });
        });
    },

    /**
     * Iniciar el tour para un rol específico
     */
    startTour: function(role) {
        const currentPage = this.getCurrentPage();
        const roleTours = this.tours[role];

        if (!roleTours) {
            console.warn('No hay tour definido para el rol:', role);
            return;
        }

        // Obtener los pasos del tour para la página actual
        let steps = roleTours[currentPage] || roleTours['dashboard'];

        if (!steps || steps.length === 0) {
            // Si no hay tour específico para esta página, usar el dashboard
            steps = roleTours['dashboard'];
        }

        // Filtrar pasos cuyos elementos no existen en la página
        steps = steps.filter(step => {
            if (!step.element) return true; // Pasos sin elemento (intro general)
            return $(step.element).length > 0;
        });

        if (steps.length === 0) {
            console.warn('No hay pasos válidos para el tour en esta página');
            return;
        }

        // Iniciar Intro.js con los pasos filtrados
        const intro = introJs();
        intro.setOptions({
            ...this.options,
            steps: steps
        });

        // Evento al completar el tour
        intro.oncomplete(() => {
            this.markTourComplete(role);
        });

        // Evento al salir del tour (skip)
        intro.onexit(() => {
            this.markTourComplete(role);
        });

        intro.start();
    },

    /**
     * Obtener el nombre de la página actual basado en la URL
     */
    getCurrentPage: function() {
        const path = window.location.pathname;

        // Detectar rutas de edición específicas primero
        if (path.match(/\/cuentas-bancarias\/\d+\/edit/)) {
            return 'cuentas-bancarias-edit';
        }
        if (path.match(/\/razones-sociales\/\d+\/edit/)) {
            return 'razones-sociales-edit';
        }
        if (path.match(/\/razones-sociales\/\d+\/mail-config/)) {
            return 'razones-sociales-mail-config';
        }

        // Mapeo de rutas a nombres de página
        const routeMap = {
            '/dashboard': 'dashboard',
            '/partners': 'partners',
            '/users': 'users',
            '/roles': 'roles',
            '/permissions': 'permissions',
            '/activity-logs': 'activity-logs',
            '/clients': 'clients',
            '/printec-cities': 'printec-cities',
            '/warehouses': 'warehouses',
            '/pricing-dashboard': 'pricing-dashboard',
            '/pricing-tiers': 'pricing-tiers',
            '/partner-pricing': 'partner-pricing',
            '/pricing-settings': 'pricing-settings',
            '/razones-sociales': 'razones-sociales',
            '/cuentas-bancarias': 'cuentas-bancarias',
            '/mi-ganancia': 'mi-ganancia',
            '/mis-usuarios': 'mis-usuarios',
            '/mis-almacenes': 'mis-almacenes',
            '/mis-categorias': 'mis-categorias',
            '/printec-categories': 'printec-categories',
            '/category-mappings': 'category-mappings',
            '/own-products': 'own-products',
            '/catalogo': 'catalogo',
            '/quotes': 'quotes',
            '/cart': 'cart'
        };

        // Buscar coincidencia exacta o parcial
        for (const [route, page] of Object.entries(routeMap)) {
            if (path === route || path.startsWith(route + '/')) {
                return page;
            }
        }

        return 'dashboard';
    },

    /**
     * Marcar el tour como completado
     */
    markTourComplete: function(role) {
        // Guardar en localStorage
        localStorage.setItem(`tour_completed_${role}`, 'true');

        // Guardar en el servidor
        $.ajax({
            url: '/tour/complete',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }
};

// Inicializar cuando el DOM esté listo
$(document).ready(function() {
    PrintecTour.init();
});
