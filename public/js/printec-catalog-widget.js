/**
 * Printec Catalog Widget v1.0.1
 * Widget embebible para mostrar el catálogo de productos de Printec en sitios externos
 *
 * Uso:
 * <div id="printec-catalog"></div>
 * <script src="https://tu-dominio.com/js/printec-catalog-widget.js?v=1.0.1"></script>
 * <script>
 *   PrintecCatalog.init({
 *     apiKey: 'TU_API_KEY',
 *     container: '#printec-catalog',
 *     apiUrl: 'https://tu-dominio.com/api/public/catalog'
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
            primaryColor: '#007bff',
            language: 'es'
        },

        state: {
            products: [],
            categories: [],
            currentPage: 1,
            totalPages: 1,
            currentCategory: '',
            searchQuery: '',
            loading: false,
            partnerInfo: null
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
                poweredBy: 'Catálogo de Printec'
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
                poweredBy: 'Printec Catalog'
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

            this.injectStyles();
            await this.loadPartnerInfo();
            await this.loadCategories();
            this.render();
            this.renderCategories();
            await this.loadProducts();
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
                .pc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 24px; }
                .pc-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; }
                .pc-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
                .pc-card-image { width: 100%; height: 200px; object-fit: contain; background: #f8f9fa; padding: 15px; }
                .pc-card-body { padding: 16px; }
                .pc-card-title { font-size: 16px; font-weight: 600; color: #333; margin: 0 0 8px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
                .pc-card-category { font-size: 12px; color: #666; margin-bottom: 8px; }
                .pc-card-price { font-size: 18px; font-weight: 700; color: ${this.config.primaryColor}; }
                .pc-card-model { font-size: 12px; color: #888; margin-top: 4px; }
                .pc-loading { text-align: center; padding: 60px 20px; color: #666; }
                .pc-loading-spinner { width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid ${this.config.primaryColor}; border-radius: 50%; animation: pc-spin 1s linear infinite; margin: 0 auto 15px; }
                @keyframes pc-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                .pc-empty { text-align: center; padding: 60px 20px; color: #666; }
                .pc-pagination { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
                .pc-btn { padding: 10px 20px; border: 1px solid ${this.config.primaryColor}; background: white; color: ${this.config.primaryColor}; border-radius: 6px; cursor: pointer; font-size: 14px; transition: all 0.2s; }
                .pc-btn:hover:not(:disabled) { background: ${this.config.primaryColor}; color: white; }
                .pc-btn:disabled { opacity: 0.5; cursor: not-allowed; }
                .pc-page-info { font-size: 14px; color: #666; }
                .pc-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 10000; padding: 20px; }
                .pc-modal { background: white; border-radius: 16px; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; }
                .pc-modal-close { position: absolute; top: 15px; right: 15px; width: 36px; height: 36px; border: none; background: #f1f1f1; border-radius: 50%; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; z-index: 1; }
                .pc-modal-close:hover { background: #e0e0e0; }
                .pc-modal-content { padding: 30px; }
                .pc-modal-image { width: 100%; max-height: 350px; object-fit: contain; background: #f8f9fa; border-radius: 12px; }
                .pc-modal-gallery { display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; padding-bottom: 10px; }
                .pc-modal-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: border-color 0.2s; }
                .pc-modal-thumb:hover, .pc-modal-thumb.active { border-color: ${this.config.primaryColor}; }
                .pc-modal-info { margin-top: 25px; }
                .pc-modal-title { font-size: 24px; font-weight: 700; color: #333; margin: 0 0 10px; }
                .pc-modal-category { font-size: 14px; color: #666; margin-bottom: 15px; }
                .pc-modal-price { font-size: 28px; font-weight: 700; color: ${this.config.primaryColor}; margin-bottom: 20px; }
                .pc-modal-description { font-size: 15px; color: #555; line-height: 1.6; margin-bottom: 20px; }
                .pc-modal-variants { margin-top: 20px; }
                .pc-modal-variants-title { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 10px; }
                .pc-variant-list { display: flex; flex-wrap: wrap; gap: 8px; }
                .pc-variant-chip { padding: 6px 12px; background: #f1f1f1; border-radius: 20px; font-size: 13px; color: #555; }
                .pc-variant-chip.in-stock { background: #e8f5e9; color: #2e7d32; }
                .pc-footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999; }
                @media (max-width: 600px) {
                    .pc-header { flex-direction: column; }
                    .pc-search, .pc-categories { width: 100%; }
                    .pc-modal-content { padding: 20px; }
                    .pc-modal-title { font-size: 20px; }
                    .pc-modal-price { font-size: 24px; }
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
                    </div>
                    <div class="pc-grid" id="pc-products-grid"></div>
                    <div class="pc-pagination" id="pc-pagination"></div>
                    <div class="pc-footer">${this.t('poweredBy')}</div>
                </div>
            `;

            this.bindEvents();
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
                card.addEventListener('click', () => {
                    const productId = card.dataset.productId;
                    this.showProductDetail(productId);
                });
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
                            ${product.categories?.length ? `<div class="pc-modal-category">${product.categories.map(c => c.name).join(', ')}</div>` : ''}
                            ${product.price !== undefined ? `<div class="pc-modal-price">$${this.formatPrice(product.price)}</div>` : ''}
                            ${product.description ? `<div class="pc-modal-description">${product.description}</div>` : ''}
                            ${product.variants?.length ? `
                                <div class="pc-modal-variants">
                                    <div class="pc-modal-variants-title">${this.t('colors')}</div>
                                    <div class="pc-variant-list">
                                        ${product.variants.map(v => `
                                            <span class="pc-variant-chip ${v.in_stock ? 'in-stock' : ''}">${v.color || v.code || v.sku}</span>
                                        `).join('')}
                                    </div>
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

            // Close on ESC
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
        },

        formatPrice(price) {
            return parseFloat(price).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };

    window.PrintecCatalog = PrintecCatalog;

})(window);
