/**
 * Printec Catalog Widget v2.0.0
 * Widget embebible para mostrar el catálogo de productos de Printec en sitios externos
 * Incluye carrito de compras con exportación a PDF y JSON
 *
 * Uso:
 * <div id="printec-catalog"></div>
 * <script src="https://tu-dominio.com/js/printec-catalog-widget.js?v=2.0.0"></script>
 * <script>
 *   PrintecCatalog.init({
 *     apiKey: 'TU_API_KEY',
 *     container: '#printec-catalog',
 *     apiUrl: 'https://tu-dominio.com/api/public/catalog',
 *     showCart: true,           // Mostrar carrito (default: true)
 *     companyName: 'Mi Empresa', // Nombre para el PDF
 *     companyEmail: 'ventas@miempresa.com' // Email para el PDF
 *   });
 * </script>
 */
(function(window) {
    'use strict';

    const PrintecCatalog = {
        config: {
            apiKey: '',
            apiUrl: '',
            container: '#printec-catalog',
            perPage: 12,
            showSearch: true,
            showCategories: true,
            showCart: true,
            primaryColor: '#007bff',
            language: 'es',
            companyName: '',
            companyEmail: ''
        },

        state: {
            products: [],
            categories: [],
            currentPage: 1,
            totalPages: 1,
            currentCategory: '',
            searchQuery: '',
            loading: false,
            partnerInfo: null,
            cart: [],
            cartOpen: false
        },

        translations: {
            es: {
                search: 'Buscar productos...',
                allCategories: 'Todas las categorías',
                loading: 'Cargando...',
                noProducts: 'No se encontraron productos',
                viewDetails: 'Ver detalles',
                previous: 'Anterior',
                next: 'Siguiente',
                price: 'Precio',
                colors: 'Colores disponibles',
                inStock: 'En stock',
                outOfStock: 'Sin stock',
                close: 'Cerrar',
                page: 'Página',
                of: 'de',
                poweredBy: 'Catálogo de Printec',
                cart: 'Carrito',
                addToCart: 'Agregar al carrito',
                removeFromCart: 'Quitar',
                emptyCart: 'El carrito está vacío',
                clearCart: 'Vaciar carrito',
                subtotal: 'Subtotal',
                total: 'Total',
                quantity: 'Cantidad',
                downloadPdf: 'Descargar PDF',
                downloadJson: 'Descargar JSON',
                product: 'Producto',
                unitPrice: 'Precio Unit.',
                items: 'artículos',
                quoteRequest: 'Solicitud de Cotización',
                date: 'Fecha',
                code: 'Código',
                color: 'Color',
                selectVariant: 'Seleccionar variante',
                added: 'Agregado',
                continueShopping: 'Seguir comprando',
                viewCart: 'Ver carrito',
                exportOrder: 'Exportar pedido',
                orderSummary: 'Resumen del pedido',
                sendByEmail: 'Envía el archivo JSON por correo para procesar tu pedido'
            },
            en: {
                search: 'Search products...',
                allCategories: 'All categories',
                loading: 'Loading...',
                noProducts: 'No products found',
                viewDetails: 'View details',
                previous: 'Previous',
                next: 'Next',
                price: 'Price',
                colors: 'Available colors',
                inStock: 'In stock',
                outOfStock: 'Out of stock',
                close: 'Close',
                page: 'Page',
                of: 'of',
                poweredBy: 'Printec Catalog',
                cart: 'Cart',
                addToCart: 'Add to cart',
                removeFromCart: 'Remove',
                emptyCart: 'Cart is empty',
                clearCart: 'Clear cart',
                subtotal: 'Subtotal',
                total: 'Total',
                quantity: 'Quantity',
                downloadPdf: 'Download PDF',
                downloadJson: 'Download JSON',
                product: 'Product',
                unitPrice: 'Unit Price',
                items: 'items',
                quoteRequest: 'Quote Request',
                date: 'Date',
                code: 'Code',
                color: 'Color',
                selectVariant: 'Select variant',
                added: 'Added',
                continueShopping: 'Continue shopping',
                viewCart: 'View cart',
                exportOrder: 'Export order',
                orderSummary: 'Order summary',
                sendByEmail: 'Send the JSON file by email to process your order'
            }
        },

        t(key) {
            return this.translations[this.config.language]?.[key] || this.translations.es[key] || key;
        },

        async init(options) {
            Object.assign(this.config, options);

            if (!this.config.apiKey) {
                console.error('PrintecCatalog: API key is required');
                return;
            }

            if (!this.config.apiUrl) {
                console.error('PrintecCatalog: API URL is required');
                return;
            }

            this.loadCartFromStorage();
            this.injectStyles();
            await this.loadPartnerInfo();
            await this.loadCategories();
            this.render();
            this.renderCategories();
            await this.loadProducts();
        },

        loadCartFromStorage() {
            try {
                const saved = localStorage.getItem('printec_cart_' + this.config.apiKey);
                if (saved) {
                    this.state.cart = JSON.parse(saved);
                }
            } catch (e) {
                console.warn('PrintecCatalog: Could not load cart from storage');
            }
        },

        saveCartToStorage() {
            try {
                localStorage.setItem('printec_cart_' + this.config.apiKey, JSON.stringify(this.state.cart));
            } catch (e) {
                console.warn('PrintecCatalog: Could not save cart to storage');
            }
        },

        injectStyles() {
            if (document.getElementById('printec-catalog-styles')) return;

            const styles = document.createElement('style');
            styles.id = 'printec-catalog-styles';
            styles.textContent = `
                .pc-container { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; box-sizing: border-box; }
                .pc-container * { box-sizing: border-box; }
                .pc-header { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; align-items: center; }
                .pc-search { flex: 1; min-width: 200px; }
                .pc-search input { width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; }
                .pc-search input:focus { outline: none; border-color: ${this.config.primaryColor}; box-shadow: 0 0 0 3px ${this.config.primaryColor}22; }
                .pc-categories { min-width: 180px; }
                .pc-categories select { width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background: white; cursor: pointer; }
                .pc-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
                .pc-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; }
                .pc-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
                .pc-card-image { width: 100%; height: 200px; object-fit: contain; background: #f8f9fa; padding: 15px; cursor: pointer; }
                .pc-card-body { padding: 16px; }
                .pc-card-title { font-size: 16px; font-weight: 600; color: #333; margin: 0 0 8px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer; }
                .pc-card-title:hover { color: ${this.config.primaryColor}; }
                .pc-card-category { font-size: 12px; color: #666; margin-bottom: 8px; }
                .pc-card-price { font-size: 18px; font-weight: 700; color: ${this.config.primaryColor}; }
                .pc-card-model { font-size: 12px; color: #888; margin-top: 4px; }
                .pc-card-stock { font-size: 11px; margin-top: 6px; padding: 3px 8px; border-radius: 4px; display: inline-block; }
                .pc-card-stock.in-stock { background: #e8f5e9; color: #2e7d32; }
                .pc-card-stock.out-of-stock { background: #ffebee; color: #c62828; }
                .pc-card-footer { padding: 0 16px 16px; }
                .pc-loading { text-align: center; padding: 60px 20px; color: #666; }
                .pc-loading-spinner { width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid ${this.config.primaryColor}; border-radius: 50%; animation: pc-spin 1s linear infinite; margin: 0 auto 15px; }
                @keyframes pc-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                .pc-empty { text-align: center; padding: 60px 20px; color: #666; }
                .pc-pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
                .pc-btn { padding: 10px 20px; border: 1px solid ${this.config.primaryColor}; background: white; color: ${this.config.primaryColor}; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.2s; }
                .pc-btn:hover:not(:disabled) { background: ${this.config.primaryColor}; color: white; }
                .pc-btn:disabled { opacity: 0.5; cursor: not-allowed; }
                .pc-btn-primary { background: ${this.config.primaryColor}; color: white; }
                .pc-btn-primary:hover:not(:disabled) { background: ${this.config.primaryColor}dd; }
                .pc-btn-sm { padding: 6px 12px; font-size: 12px; }
                .pc-btn-block { width: 100%; }
                .pc-page-info { font-size: 14px; color: #666; }

                /* Modal styles */
                .pc-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 10000; padding: 20px; }
                .pc-modal { background: white; border-radius: 16px; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; }
                .pc-modal-close { position: absolute; top: 15px; right: 15px; width: 36px; height: 36px; border: none; background: transparent; border-radius: 50%; cursor: pointer; font-size: 24px; color: #000; display: flex; align-items: center; justify-content: center; z-index: 1; }
                .pc-modal-close:hover { background: rgba(0,0,0,0.05); }
                .pc-modal-content { padding: 20px; }
                .pc-modal-image { width: 100%; max-height: 175px; object-fit: contain; background: #f8f9fa; border-radius: 8px; }
                .pc-modal-gallery { display: flex; gap: 6px; margin-top: 8px; overflow-x: auto; padding-bottom: 5px; }
                .pc-modal-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: border-color 0.2s; }
                .pc-modal-thumb:hover, .pc-modal-thumb.active { border-color: ${this.config.primaryColor}; }
                .pc-modal-info { margin-top: 12px; }
                .pc-modal-title { font-size: 16px; font-weight: 700; color: #333; margin: 0 0 4px; }
                .pc-modal-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 10px; }
                .pc-modal-meta-right { display: flex; flex-wrap: wrap; gap: 8px; margin-left: auto; }
                .pc-modal-category { font-size: 11px; color: #666; background: #f5f5f5; padding: 2px 8px; border-radius: 4px; display: inline-block; }
                .pc-modal-category strong { color: #333; }
                .pc-modal-codes { font-size: 11px; color: #666; display: flex; gap: 8px; flex-wrap: wrap; }
                .pc-modal-codes span { background: #f5f5f5; padding: 2px 8px; border-radius: 4px; }
                .pc-modal-codes strong { color: #333; }
                .pc-modal-price { font-size: 20px; font-weight: 700; color: ${this.config.primaryColor}; }
                .pc-modal-description { font-size: 12px; color: #555; line-height: 1.5; margin-bottom: 15px; }
                .pc-modal-variants { margin-top: 12px; }
                .pc-modal-variants-title { font-size: 12px; font-weight: 600; color: #333; margin-bottom: 6px; }
                .pc-variant-list { display: flex; flex-wrap: wrap; gap: 5px; }
                .pc-variant-chip { padding: 6px 10px; background: #f1f1f1; border-radius: 8px; font-size: 11px; color: #555; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; min-width: 60px; }
                .pc-variant-chip .pc-variant-color { font-weight: 600; }
                .pc-variant-chip .pc-variant-stock { font-size: 9px; color: #333; margin-top: 1px; }
                .pc-variant-chip.selected .pc-variant-stock { color: white; }
                .pc-variant-chip:hover { border-color: ${this.config.primaryColor}; }
                .pc-variant-chip.selected { background: ${this.config.primaryColor}; color: white; border-color: ${this.config.primaryColor}; }
                .pc-variant-chip.in-stock { background: #f0f0f0; color: #333; }
                .pc-variant-chip.in-stock.selected { background: ${this.config.primaryColor}; color: white; border-color: ${this.config.primaryColor}; }
                .pc-variant-chip.out-of-stock { opacity: 0.5; cursor: not-allowed; }
                .pc-footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999; }

                /* Cart button in header */
                .pc-cart-btn { position: relative; padding: 12px 20px; background: ${this.config.primaryColor}; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px; }
                .pc-cart-btn:hover { background: ${this.config.primaryColor}dd; }
                .pc-cart-btn svg { width: 20px; height: 20px; }
                .pc-cart-badge { position: absolute; top: -8px; right: -8px; background: #e53935; color: white; font-size: 11px; font-weight: 700; min-width: 20px; height: 20px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

                /* Cart sidebar */
                .pc-cart-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10001; opacity: 0; visibility: hidden; transition: all 0.3s; }
                .pc-cart-overlay.open { opacity: 1; visibility: visible; }
                .pc-cart-sidebar { position: fixed; top: 0; right: -420px; width: 420px; max-width: 100%; height: 100%; background: white; z-index: 10002; box-shadow: -4px 0 20px rgba(0,0,0,0.15); transition: right 0.3s; display: flex; flex-direction: column; }
                .pc-cart-sidebar.open { right: 0; }
                .pc-cart-header { padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
                .pc-cart-header h3 { margin: 0; font-size: 18px; color: #333; }
                .pc-cart-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; padding: 0; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
                .pc-cart-close:hover { background: #f1f1f1; }
                .pc-cart-items { flex: 1; overflow-y: auto; padding: 20px; }
                .pc-cart-empty { text-align: center; padding: 40px 20px; color: #666; }
                .pc-cart-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #eee; }
                .pc-cart-item:last-child { border-bottom: none; }
                .pc-cart-item-image { width: 70px; height: 70px; object-fit: contain; background: #f8f9fa; border-radius: 8px; flex-shrink: 0; }
                .pc-cart-item-info { flex: 1; min-width: 0; }
                .pc-cart-item-name { font-size: 14px; font-weight: 600; color: #333; margin: 0 0 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                .pc-cart-item-variant { font-size: 12px; color: #666; margin-bottom: 8px; }
                .pc-cart-item-price { font-size: 14px; font-weight: 700; color: ${this.config.primaryColor}; }
                .pc-cart-item-actions { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
                .pc-qty-control { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 6px; }
                .pc-qty-btn { width: 28px; height: 28px; border: none; background: none; cursor: pointer; font-size: 16px; color: #666; }
                .pc-qty-btn:hover { background: #f5f5f5; }
                .pc-qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }
                .pc-qty-value { width: 40px; text-align: center; font-size: 14px; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; }
                .pc-cart-item-remove { color: #e53935; font-size: 12px; cursor: pointer; background: none; border: none; padding: 4px 8px; }
                .pc-cart-item-remove:hover { text-decoration: underline; }
                .pc-cart-footer { padding: 20px; border-top: 1px solid #eee; background: #f9f9f9; }
                .pc-cart-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
                .pc-cart-total-label { font-size: 16px; color: #333; }
                .pc-cart-total-value { font-size: 24px; font-weight: 700; color: ${this.config.primaryColor}; }
                .pc-cart-actions { display: flex; flex-direction: column; gap: 10px; }
                .pc-cart-actions .pc-btn { justify-content: center; display: flex; align-items: center; gap: 8px; }
                .pc-cart-clear { text-align: center; margin-top: 10px; }
                .pc-cart-clear button { background: none; border: none; color: #999; font-size: 12px; cursor: pointer; text-decoration: underline; }
                .pc-cart-clear button:hover { color: #e53935; }

                /* Add to cart section in modal */
                .pc-add-to-cart-section { margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee; }
                .pc-qty-selector { display: flex; align-items: center; gap: 10px; }
                .pc-qty-selector label { font-size: 12px; color: #333; }
                .pc-qty-selector .pc-btn { margin-left: auto; }
                .pc-qty-input { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
                .pc-qty-input button { width: 32px; height: 32px; border: none; background: #f5f5f5; cursor: pointer; font-size: 16px; color: #333; font-weight: 600; }
                .pc-qty-input button:hover { background: #e0e0e0; }
                .pc-qty-input input { width: 50px; height: 32px; border: none; text-align: center; font-size: 14px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; }
                .pc-added-message { display: flex; align-items: center; gap: 10px; padding: 12px; background: #e8f5e9; border-radius: 8px; margin-top: 15px; }
                .pc-added-message svg { color: #2e7d32; width: 20px; height: 20px; flex-shrink: 0; }
                .pc-added-message span { color: #2e7d32; font-size: 14px; }
                .pc-added-actions { display: flex; gap: 10px; margin-left: auto; }

                /* Export info */
                .pc-export-info { font-size: 12px; color: #666; text-align: center; margin-top: 15px; padding: 10px; background: #fff3e0; border-radius: 6px; }

                @media (max-width: 1024px) {
                    .pc-grid { grid-template-columns: repeat(3, 1fr); }
                }
                @media (max-width: 768px) {
                    .pc-grid { grid-template-columns: repeat(2, 1fr); }
                }
                @media (max-width: 600px) {
                    .pc-header { flex-direction: column; }
                    .pc-search, .pc-categories { width: 100%; }
                    .pc-grid { grid-template-columns: 1fr; }
                    .pc-modal-content { padding: 20px; }
                    .pc-modal-title { font-size: 20px; }
                    .pc-modal-price { font-size: 24px; }
                    .pc-cart-sidebar { width: 100%; right: -100%; }
                    .pc-cart-btn span { display: none; }
                }
            `;
            document.head.appendChild(styles);
        },

        async apiRequest(endpoint, params = {}) {
            const url = new URL(`${this.config.apiUrl}${endpoint}`);
            Object.keys(params).forEach(key => {
                if (params[key]) url.searchParams.append(key, params[key]);
            });

            const response = await fetch(url.toString(), {
                headers: { 'X-API-Key': this.config.apiKey }
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'API Error');
            }

            return response.json();
        },

        async loadPartnerInfo() {
            try {
                const result = await this.apiRequest('/info');
                this.state.partnerInfo = result.data;
            } catch (error) {
                console.error('PrintecCatalog: Error loading partner info', error);
            }
        },

        async loadCategories() {
            try {
                const result = await this.apiRequest('/categories');
                this.state.categories = result.data;
                this.renderCategories();
            } catch (error) {
                console.error('PrintecCatalog: Error loading categories', error);
            }
        },

        async loadProducts() {
            this.state.loading = true;
            this.renderProducts();

            try {
                const result = await this.apiRequest('/products', {
                    page: this.state.currentPage,
                    per_page: this.config.perPage,
                    category: this.state.currentCategory,
                    search: this.state.searchQuery
                });

                this.state.products = result.data;
                this.state.totalPages = result.meta.last_page;
            } catch (error) {
                console.error('PrintecCatalog: Error loading products', error);
                this.state.products = [];
            }

            this.state.loading = false;
            this.renderProducts();
            this.renderPagination();
        },

        render() {
            const container = document.querySelector(this.config.container);
            if (!container) {
                console.error('PrintecCatalog: Container not found');
                return;
            }

            container.innerHTML = `
                <div class="pc-container">
                    <div class="pc-header">
                        ${this.config.showSearch ? `
                            <div class="pc-search">
                                <input type="text" id="pc-search-input" placeholder="${this.t('search')}">
                            </div>
                        ` : ''}
                        ${this.config.showCategories ? `
                            <div class="pc-categories">
                                <select id="pc-category-select">
                                    <option value="">${this.t('allCategories')}</option>
                                </select>
                            </div>
                        ` : ''}
                        ${this.config.showCart ? `
                            <button class="pc-cart-btn" id="pc-cart-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>${this.t('cart')}</span>
                                <span class="pc-cart-badge" id="pc-cart-badge" style="display: none;">0</span>
                            </button>
                        ` : ''}
                    </div>
                    <div class="pc-grid" id="pc-products-grid"></div>
                    <div class="pc-pagination" id="pc-pagination"></div>
                </div>
                ${this.config.showCart ? this.renderCartSidebar() : ''}
            `;

            this.bindEvents();
            this.updateCartBadge();
        },

        renderCartSidebar() {
            return `
                <div class="pc-cart-overlay" id="pc-cart-overlay"></div>
                <div class="pc-cart-sidebar" id="pc-cart-sidebar">
                    <div class="pc-cart-header">
                        <h3>${this.t('cart')}</h3>
                        <button class="pc-cart-close" id="pc-cart-close">&times;</button>
                    </div>
                    <div class="pc-cart-items" id="pc-cart-items"></div>
                    <div class="pc-cart-footer" id="pc-cart-footer"></div>
                </div>
            `;
        },

        bindEvents() {
            const searchInput = document.getElementById('pc-search-input');
            if (searchInput) {
                let timeout;
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.state.searchQuery = e.target.value;
                        this.state.currentPage = 1;
                        this.loadProducts();
                    }, 300);
                });
            }

            const categorySelect = document.getElementById('pc-category-select');
            if (categorySelect) {
                categorySelect.addEventListener('change', (e) => {
                    this.state.currentCategory = e.target.value;
                    this.state.currentPage = 1;
                    this.loadProducts();
                });
            }

            // Cart events
            const cartBtn = document.getElementById('pc-cart-btn');
            const cartOverlay = document.getElementById('pc-cart-overlay');
            const cartClose = document.getElementById('pc-cart-close');

            if (cartBtn) {
                cartBtn.addEventListener('click', () => this.openCart());
            }
            if (cartOverlay) {
                cartOverlay.addEventListener('click', () => this.closeCart());
            }
            if (cartClose) {
                cartClose.addEventListener('click', () => this.closeCart());
            }
        },

        renderCategories() {
            const select = document.getElementById('pc-category-select');
            if (!select) return;

            select.innerHTML = `<option value="">${this.t('allCategories')}</option>` +
                this.state.categories.map(cat =>
                    `<option value="${cat.slug}">${cat.name}</option>`
                ).join('');
        },

        renderProducts() {
            const grid = document.getElementById('pc-products-grid');
            if (!grid) return;

            if (this.state.loading) {
                grid.innerHTML = `
                    <div class="pc-loading" style="grid-column: 1/-1;">
                        <div class="pc-loading-spinner"></div>
                        <div>${this.t('loading')}</div>
                    </div>
                `;
                return;
            }

            if (!this.state.products.length) {
                grid.innerHTML = `<div class="pc-empty" style="grid-column: 1/-1;">${this.t('noProducts')}</div>`;
                return;
            }

            grid.innerHTML = this.state.products.map(product => `
                <div class="pc-card" data-product-id="${product.id}">
                    <img class="pc-card-image" src="${product.main_image || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2250%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-size=%2212%22%3ESin imagen%3C/text%3E%3C/svg%3E'}" alt="${product.name}" loading="lazy">
                    <div class="pc-card-body">
                        <h3 class="pc-card-title">${product.name}</h3>
                        ${product.categories?.length ? `<div class="pc-card-category">${product.categories[0].name}</div>` : ''}
                        ${product.price !== undefined ? `<div class="pc-card-price">$${this.formatPrice(product.price)}</div>` : ''}
                        ${product.model_code ? `<div class="pc-card-model">${product.model_code}</div>` : ''}
                    </div>
                </div>
            `).join('');

            grid.querySelectorAll('.pc-card').forEach(card => {
                const img = card.querySelector('.pc-card-image');
                const title = card.querySelector('.pc-card-title');

                const showDetail = () => {
                    const productId = card.dataset.productId;
                    this.showProductDetail(productId);
                };

                img.addEventListener('click', showDetail);
                title.addEventListener('click', showDetail);
            });
        },

        renderPagination() {
            const pagination = document.getElementById('pc-pagination');
            if (!pagination || this.state.totalPages <= 1) {
                if (pagination) pagination.innerHTML = '';
                return;
            }

            pagination.innerHTML = `
                <button class="pc-btn" id="pc-prev-btn" ${this.state.currentPage <= 1 ? 'disabled' : ''}>${this.t('previous')}</button>
                <span class="pc-page-info">${this.t('page')} ${this.state.currentPage} ${this.t('of')} ${this.state.totalPages}</span>
                <button class="pc-btn" id="pc-next-btn" ${this.state.currentPage >= this.state.totalPages ? 'disabled' : ''}>${this.t('next')}</button>
            `;

            document.getElementById('pc-prev-btn')?.addEventListener('click', () => {
                if (this.state.currentPage > 1) {
                    this.state.currentPage--;
                    this.loadProducts();
                }
            });

            document.getElementById('pc-next-btn')?.addEventListener('click', () => {
                if (this.state.currentPage < this.state.totalPages) {
                    this.state.currentPage++;
                    this.loadProducts();
                }
            });
        },

        async showProductDetail(productId) {
            try {
                const result = await this.apiRequest(`/products/${productId}`);
                const product = result.data;
                this.renderModal(product);
            } catch (error) {
                console.error('PrintecCatalog: Error loading product detail', error);
            }
        },

        renderModal(product) {
            const existingModal = document.querySelector('.pc-modal-overlay');
            if (existingModal) existingModal.remove();

            const hasVariants = product.variants?.length > 0;
            const defaultVariant = hasVariants ? product.variants[0] : null;

            const modal = document.createElement('div');
            modal.className = 'pc-modal-overlay';
            modal.innerHTML = `
                <div class="pc-modal">
                    <button class="pc-modal-close">&times;</button>
                    <div class="pc-modal-content">
                        <img class="pc-modal-image" id="pc-modal-main-image" src="${product.main_image || ''}" alt="${product.name}">
                        ${product.images?.length > 1 ? `
                            <div class="pc-modal-gallery">
                                ${product.images.map((img, i) => `
                                    <img class="pc-modal-thumb ${i === 0 ? 'active' : ''}" src="${img}" alt="Imagen ${i + 1}" data-src="${img}">
                                `).join('')}
                            </div>
                        ` : ''}
                        <div class="pc-modal-info">
                            <h2 class="pc-modal-title">${product.name}</h2>
                            <div class="pc-modal-meta">
                                ${product.price !== undefined ? `<div class="pc-modal-price">$${this.formatPrice(product.price)}</div>` : ''}
                                <div class="pc-modal-meta-right">
                                    ${product.categories?.length ? `<span class="pc-modal-category"><strong>Categoría:</strong> ${product.categories.map(c => c.name).join(', ')}</span>` : ''}
                                    ${product.model_code ? `<span class="pc-modal-category"><strong>Modelo:</strong> ${product.model_code}</span>` : ''}
                                    ${product.sku ? `<span class="pc-modal-category"><strong>SKU:</strong> ${product.sku}</span>` : ''}
                                </div>
                            </div>
                            ${product.description ? `<div class="pc-modal-description">${product.description}</div>` : ''}
                            ${hasVariants ? `
                                <div class="pc-modal-variants">
                                    <div class="pc-modal-variants-title">${this.t('selectVariant')}</div>
                                    <div class="pc-variant-list" id="pc-variant-list">
                                        ${product.variants.map((v, i) => `
                                            <span class="pc-variant-chip ${v.in_stock ? 'in-stock' : 'out-of-stock'} ${i === 0 ? 'selected' : ''}"
                                                  data-variant-id="${v.id}"
                                                  data-variant-sku="${v.sku || ''}"
                                                  data-variant-color="${v.color || ''}"
                                                  data-variant-price="${v.price || product.price}"
                                                  data-variant-in-stock="${v.in_stock ? '1' : '0'}">
                                                <span class="pc-variant-color">${v.color || v.code || v.sku}</span>
                                                <span class="pc-variant-stock">${v.stock !== undefined ? (v.stock > 0 ? 'Stock: ' + v.stock : 'Sin Stock') : '-'}</span>
                                            </span>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            ${this.config.showCart ? `
                                <div class="pc-add-to-cart-section">
                                    <div class="pc-qty-selector">
                                        <label>${this.t('quantity')}:</label>
                                        <div class="pc-qty-input">
                                            <button type="button" id="pc-qty-minus">-</button>
                                            <input type="number" id="pc-qty-value" value="1" min="1" max="9999">
                                            <button type="button" id="pc-qty-plus">+</button>
                                        </div>
                                        <button class="pc-btn pc-btn-primary" id="pc-add-to-cart-btn"
                                                data-product-id="${product.id}"
                                                data-product-name="${this.escapeHtml(product.name)}"
                                                data-product-image="${product.main_image || ''}"
                                                data-product-price="${product.price}"
                                                data-product-model="${product.model_code || ''}"
                                                ${hasVariants ? `data-variant-id="${defaultVariant.id}" data-variant-color="${defaultVariant.color || ''}" data-variant-sku="${defaultVariant.sku || ''}"` : ''}>
                                            ${this.t('addToCart')}
                                        </button>
                                    </div>
                                    <div id="pc-added-message" style="display: none;"></div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Close button
            modal.querySelector('.pc-modal-close').addEventListener('click', () => modal.remove());

            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });

            // Gallery thumbnails
            modal.querySelectorAll('.pc-modal-thumb').forEach(thumb => {
                thumb.addEventListener('click', () => {
                    modal.querySelector('#pc-modal-main-image').src = thumb.dataset.src;
                    modal.querySelectorAll('.pc-modal-thumb').forEach(t => t.classList.remove('active'));
                    thumb.classList.add('active');
                });
            });

            // Variant selection
            if (hasVariants) {
                modal.querySelectorAll('.pc-variant-chip').forEach(chip => {
                    chip.addEventListener('click', () => {
                        if (chip.dataset.variantInStock === '0') return;

                        modal.querySelectorAll('.pc-variant-chip').forEach(c => c.classList.remove('selected'));
                        chip.classList.add('selected');

                        const addBtn = modal.querySelector('#pc-add-to-cart-btn');
                        addBtn.dataset.variantId = chip.dataset.variantId;
                        addBtn.dataset.variantColor = chip.dataset.variantColor;
                        addBtn.dataset.variantSku = chip.dataset.variantSku;

                        // Reset quantity to 1
                        const qtyInput = modal.querySelector('#pc-qty-value');
                        if (qtyInput) qtyInput.value = 1;
                    });
                });
            }

            // Quantity controls
            const qtyInput = modal.querySelector('#pc-qty-value');
            const qtyMinus = modal.querySelector('#pc-qty-minus');
            const qtyPlus = modal.querySelector('#pc-qty-plus');

            if (qtyMinus) {
                qtyMinus.addEventListener('click', () => {
                    const val = parseInt(qtyInput.value) || 1;
                    if (val > 1) qtyInput.value = val - 1;
                });
            }
            if (qtyPlus) {
                qtyPlus.addEventListener('click', () => {
                    const val = parseInt(qtyInput.value) || 1;
                    qtyInput.value = val + 1;
                });
            }

            // Add to cart
            const addBtn = modal.querySelector('#pc-add-to-cart-btn');
            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    const qty = parseInt(qtyInput.value) || 1;
                    this.addToCart({
                        productId: addBtn.dataset.productId,
                        variantId: addBtn.dataset.variantId || null,
                        name: addBtn.dataset.productName,
                        image: addBtn.dataset.productImage,
                        price: parseFloat(addBtn.dataset.productPrice),
                        model: addBtn.dataset.productModel,
                        color: addBtn.dataset.variantColor || '',
                        sku: addBtn.dataset.variantSku || '',
                        quantity: qty
                    });

                    // Show added message
                    const msgDiv = modal.querySelector('#pc-added-message');
                    msgDiv.innerHTML = `
                        <div class="pc-added-message">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>${this.t('added')}</span>
                            <div class="pc-added-actions">
                                <button class="pc-btn pc-btn-sm" id="pc-continue-btn">${this.t('continueShopping')}</button>
                                <button class="pc-btn pc-btn-sm pc-btn-primary" id="pc-view-cart-btn">${this.t('viewCart')}</button>
                            </div>
                        </div>
                    `;
                    msgDiv.style.display = 'block';

                    msgDiv.querySelector('#pc-continue-btn').addEventListener('click', () => modal.remove());
                    msgDiv.querySelector('#pc-view-cart-btn').addEventListener('click', () => {
                        modal.remove();
                        this.openCart();
                    });
                });
            }

            // Close on ESC
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
        },

        // Cart methods
        addToCart(item) {
            const cartKey = `${item.productId}-${item.variantId || 'default'}`;
            const existingIndex = this.state.cart.findIndex(i =>
                `${i.productId}-${i.variantId || 'default'}` === cartKey
            );

            if (existingIndex >= 0) {
                this.state.cart[existingIndex].quantity += item.quantity;
            } else {
                this.state.cart.push({
                    ...item,
                    addedAt: new Date().toISOString()
                });
            }

            this.saveCartToStorage();
            this.updateCartBadge();
            this.renderCartItems();
        },

        updateCartItem(index, quantity) {
            if (quantity <= 0) {
                this.removeCartItem(index);
            } else {
                this.state.cart[index].quantity = quantity;
                this.saveCartToStorage();
                this.renderCartItems();
            }
        },

        removeCartItem(index) {
            this.state.cart.splice(index, 1);
            this.saveCartToStorage();
            this.updateCartBadge();
            this.renderCartItems();
        },

        clearCart() {
            this.state.cart = [];
            this.saveCartToStorage();
            this.updateCartBadge();
            this.renderCartItems();
        },

        getCartTotal() {
            return this.state.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        getCartItemsCount() {
            return this.state.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        updateCartBadge() {
            const badge = document.getElementById('pc-cart-badge');
            if (!badge) return;

            const count = this.getCartItemsCount();
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        },

        openCart() {
            this.state.cartOpen = true;
            document.getElementById('pc-cart-overlay')?.classList.add('open');
            document.getElementById('pc-cart-sidebar')?.classList.add('open');
            this.renderCartItems();
        },

        closeCart() {
            this.state.cartOpen = false;
            document.getElementById('pc-cart-overlay')?.classList.remove('open');
            document.getElementById('pc-cart-sidebar')?.classList.remove('open');
        },

        renderCartItems() {
            const itemsContainer = document.getElementById('pc-cart-items');
            const footerContainer = document.getElementById('pc-cart-footer');

            if (!itemsContainer || !footerContainer) return;

            if (this.state.cart.length === 0) {
                itemsContainer.innerHTML = `<div class="pc-cart-empty">${this.t('emptyCart')}</div>`;
                footerContainer.innerHTML = '';
                return;
            }

            itemsContainer.innerHTML = this.state.cart.map((item, index) => `
                <div class="pc-cart-item">
                    <img class="pc-cart-item-image" src="${item.image || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22100%22 height=%22100%22/%3E%3C/svg%3E'}" alt="${item.name}">
                    <div class="pc-cart-item-info">
                        <div class="pc-cart-item-name" title="${item.name}">${item.name}</div>
                        ${item.color || item.sku ? `<div class="pc-cart-item-variant">${item.color || item.sku}</div>` : ''}
                        <div class="pc-cart-item-price">$${this.formatPrice(item.price * item.quantity)}</div>
                        <div class="pc-cart-item-actions">
                            <div class="pc-qty-control">
                                <button class="pc-qty-btn" data-action="minus" data-index="${index}">-</button>
                                <input type="text" class="pc-qty-value" value="${item.quantity}" readonly>
                                <button class="pc-qty-btn" data-action="plus" data-index="${index}">+</button>
                            </div>
                            <button class="pc-cart-item-remove" data-index="${index}">${this.t('removeFromCart')}</button>
                        </div>
                    </div>
                </div>
            `).join('');

            // Bind quantity and remove events
            itemsContainer.querySelectorAll('.pc-qty-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const index = parseInt(btn.dataset.index);
                    const action = btn.dataset.action;
                    const currentQty = this.state.cart[index].quantity;

                    if (action === 'minus') {
                        this.updateCartItem(index, currentQty - 1);
                    } else {
                        this.updateCartItem(index, currentQty + 1);
                    }
                });
            });

            itemsContainer.querySelectorAll('.pc-cart-item-remove').forEach(btn => {
                btn.addEventListener('click', () => {
                    const index = parseInt(btn.dataset.index);
                    this.removeCartItem(index);
                });
            });

            // Footer with totals and export buttons
            const total = this.getCartTotal();
            const count = this.getCartItemsCount();

            footerContainer.innerHTML = `
                <div class="pc-cart-total">
                    <span class="pc-cart-total-label">${this.t('total')} (${count} ${this.t('items')})</span>
                    <span class="pc-cart-total-value">$${this.formatPrice(total)}</span>
                </div>
                <div class="pc-cart-actions">
                    <button class="pc-btn pc-btn-primary" id="pc-download-pdf">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        ${this.t('downloadPdf')}
                    </button>
                    <button class="pc-btn" id="pc-download-json">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        ${this.t('downloadJson')}
                    </button>
                </div>
                <div class="pc-export-info">${this.t('sendByEmail')}</div>
                <div class="pc-cart-clear">
                    <button id="pc-clear-cart">${this.t('clearCart')}</button>
                </div>
            `;

            document.getElementById('pc-download-pdf')?.addEventListener('click', () => this.downloadPdf());
            document.getElementById('pc-download-json')?.addEventListener('click', () => this.downloadJson());
            document.getElementById('pc-clear-cart')?.addEventListener('click', () => {
                if (confirm('¿Estás seguro de vaciar el carrito?')) {
                    this.clearCart();
                }
            });
        },

        // Export methods
        downloadJson() {
            const orderData = {
                version: '1.0',
                partner_api_key: this.config.apiKey,
                partner_name: this.state.partnerInfo?.name || '',
                created_at: new Date().toISOString(),
                items: this.state.cart.map(item => ({
                    product_id: item.productId,
                    variant_id: item.variantId,
                    name: item.name,
                    model_code: item.model,
                    color: item.color,
                    sku: item.sku,
                    quantity: item.quantity,
                    unit_price: item.price,
                    subtotal: item.price * item.quantity
                })),
                totals: {
                    items_count: this.getCartItemsCount(),
                    subtotal: this.getCartTotal()
                }
            };

            const blob = new Blob([JSON.stringify(orderData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `pedido-${this.formatDateForFilename()}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },

        downloadPdf() {
            // Create a printable HTML and use browser print
            const printWindow = window.open('', '_blank');
            const partnerName = this.config.companyName || this.state.partnerInfo?.name || 'Catálogo';
            const partnerEmail = this.config.companyEmail || '';

            const html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>${this.t('quoteRequest')} - ${this.formatDateForFilename()}</title>
                    <style>
                        * { box-sizing: border-box; margin: 0; padding: 0; }
                        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 40px; color: #333; font-size: 12px; }
                        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid ${this.config.primaryColor}; }
                        .header h1 { font-size: 24px; color: ${this.config.primaryColor}; margin-bottom: 5px; }
                        .header-info { text-align: right; }
                        .header-info p { margin: 3px 0; color: #666; }
                        .company-info { margin-bottom: 30px; padding: 15px; background: #f9f9f9; border-radius: 8px; }
                        .company-info h3 { font-size: 14px; margin-bottom: 5px; }
                        .company-info p { color: #666; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                        th { background: ${this.config.primaryColor}; color: white; padding: 12px 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
                        td { padding: 12px 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
                        tr:nth-child(even) { background: #f9f9f9; }
                        .product-name { font-weight: 600; }
                        .product-variant { font-size: 11px; color: #666; }
                        .text-right { text-align: right; }
                        .text-center { text-align: center; }
                        .totals { margin-left: auto; width: 300px; }
                        .totals-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                        .totals-row.total { font-size: 18px; font-weight: 700; color: ${this.config.primaryColor}; border-bottom: none; border-top: 2px solid ${this.config.primaryColor}; margin-top: 10px; padding-top: 15px; }
                        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 11px; }
                        .note { margin-top: 30px; padding: 15px; background: #fff3e0; border-radius: 8px; font-size: 11px; color: #e65100; }
                        @media print {
                            body { padding: 20px; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div>
                            <h1>${this.t('quoteRequest')}</h1>
                            <p>${partnerName}</p>
                        </div>
                        <div class="header-info">
                            <p><strong>${this.t('date')}:</strong> ${this.formatDate(new Date())}</p>
                            ${partnerEmail ? `<p><strong>Email:</strong> ${partnerEmail}</p>` : ''}
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40%">${this.t('product')}</th>
                                <th class="text-center">${this.t('code')}</th>
                                <th class="text-center">${this.t('quantity')}</th>
                                <th class="text-right">${this.t('unitPrice')}</th>
                                <th class="text-right">${this.t('subtotal')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.state.cart.map(item => `
                                <tr>
                                    <td>
                                        <div class="product-name">${item.name}</div>
                                        ${item.color ? `<div class="product-variant">${this.t('color')}: ${item.color}</div>` : ''}
                                    </td>
                                    <td class="text-center">${item.model || item.sku || '-'}</td>
                                    <td class="text-center">${item.quantity}</td>
                                    <td class="text-right">$${this.formatPrice(item.price)}</td>
                                    <td class="text-right">$${this.formatPrice(item.price * item.quantity)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <div class="totals">
                        <div class="totals-row">
                            <span>${this.t('items')}:</span>
                            <span>${this.getCartItemsCount()}</span>
                        </div>
                        <div class="totals-row total">
                            <span>${this.t('total')}:</span>
                            <span>$${this.formatPrice(this.getCartTotal())}</span>
                        </div>
                    </div>

                    <div class="note">
                        ${this.t('sendByEmail')}
                    </div>

                    <div class="footer">
                        <p>${this.t('poweredBy')}</p>
                    </div>

                    <script>
                        window.onload = function() { window.print(); }
                    </script>
                </body>
                </html>
            `;

            printWindow.document.write(html);
            printWindow.document.close();
        },

        // Utility methods
        formatPrice(price) {
            return parseFloat(price).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        formatDate(date) {
            return date.toLocaleDateString('es-MX', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatDateForFilename() {
            const now = new Date();
            return `${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}-${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}`;
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    window.PrintecCatalog = PrintecCatalog;

})(window);
