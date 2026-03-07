/**
 * Printec Site Widget v1.0.0
 * Widget para generar un sitio web completo con catalogo de productos
 * Incluye: Header con logo, Hero responsivo, Catalogo (reutiliza PrintecCatalog), Footer con contacto
 *
 * Uso:
 * <div id="printec-site"></div>
 * <script src="https://tu-dominio.com/js/printec-catalog-widget.js"></script>
 * <script src="https://tu-dominio.com/js/printec-site-widget.js"></script>
 * <script>
 *   PrintecSite.init({
 *     apiKey: 'TU_API_KEY',
 *     apiUrl: 'https://tu-dominio.com/api/public/catalog',
 *     container: '#printec-site'
 *   });
 * </script>
 */
(function(window) {
    'use strict';

    const PrintecSite = {
        config: {
            apiKey: '',
            apiUrl: '',
            container: '#printec-site',
            language: 'es'
        },

        siteInfo: null,

        async init(options) {
            Object.assign(this.config, options);

            if (!this.config.apiKey || !this.config.apiUrl) {
                console.error('PrintecSite: apiKey and apiUrl are required');
                return;
            }

            const container = document.querySelector(this.config.container);
            if (!container) {
                console.error('PrintecSite: Container not found:', this.config.container);
                return;
            }

            // Show loading
            container.innerHTML = '<div style="text-align:center;padding:60px;font-family:sans-serif;color:#666;">Cargando sitio...</div>';

            // Fetch partner info
            try {
                const response = await fetch(this.config.apiUrl + '/info', {
                    headers: { 'X-API-Key': this.config.apiKey }
                });
                if (!response.ok) throw new Error('API error');
                const json = await response.json();
                this.siteInfo = json.data;
            } catch (e) {
                container.innerHTML = '<div style="text-align:center;padding:60px;font-family:sans-serif;color:#c00;">Error al cargar la informacion del sitio.</div>';
                console.error('PrintecSite: Could not load site info', e);
                return;
            }

            const colors = this.siteInfo.site_colors || {};
            const primary = colors.primary || '#007bff';
            const secondary = colors.secondary || '#6c757d';
            const accent = colors.accent || '#28a745';
            const headerFooterBg = colors.header_footer_bg || '#ffffff';
            const catalogBg = colors.catalog_bg || '#f8f9fa';

            this.injectStyles(primary, secondary, accent, headerFooterBg, catalogBg);

            // Build site structure
            container.innerHTML = '';

            // Header
            if (this.siteInfo.logo_url) {
                const header = document.createElement('div');
                header.className = 'ps-header';
                header.innerHTML = `<div class="ps-header-inner">
                    <img src="${this.escapeAttr(this.siteInfo.logo_url)}" alt="Logo" class="ps-logo">
                </div>`;
                container.appendChild(header);
            }

            // Hero
            if (this.siteInfo.hero_desktop_url || this.siteInfo.hero_mobile_url) {
                const hero = document.createElement('div');
                hero.className = 'ps-hero';
                let heroHtml = '';
                if (this.siteInfo.hero_desktop_url) {
                    heroHtml += `<img src="${this.escapeAttr(this.siteInfo.hero_desktop_url)}" alt="Banner" class="ps-hero-desktop">`;
                }
                if (this.siteInfo.hero_mobile_url) {
                    heroHtml += `<img src="${this.escapeAttr(this.siteInfo.hero_mobile_url)}" alt="Banner" class="ps-hero-mobile">`;
                } else if (this.siteInfo.hero_desktop_url) {
                    // If no mobile hero, show desktop on mobile too
                    heroHtml = `<img src="${this.escapeAttr(this.siteInfo.hero_desktop_url)}" alt="Banner" class="ps-hero-all">`;
                }
                hero.innerHTML = heroHtml;
                container.appendChild(hero);
            }

            // Catalog container
            const catalogSection = document.createElement('div');
            catalogSection.className = 'ps-catalog-section';
            const catalogDiv = document.createElement('div');
            catalogDiv.id = 'printec-catalog-embedded';
            catalogSection.appendChild(catalogDiv);
            container.appendChild(catalogSection);

            // Footer
            if (this.siteInfo.contact_info) {
                const footer = document.createElement('div');
                footer.className = 'ps-footer';
                footer.innerHTML = `<div class="ps-footer-inner">${this.siteInfo.contact_info}</div>`;
                container.appendChild(footer);
            }

            // Initialize catalog widget inside the catalog section
            if (typeof window.PrintecCatalog !== 'undefined') {
                window.PrintecCatalog.init({
                    apiKey: this.config.apiKey,
                    apiUrl: this.config.apiUrl,
                    container: '#printec-catalog-embedded',
                    primaryColor: primary,
                    language: this.config.language
                });
            } else {
                catalogDiv.innerHTML = '<div style="text-align:center;padding:40px;color:#c00;">Error: printec-catalog-widget.js no fue cargado.</div>';
                console.error('PrintecSite: PrintecCatalog not found. Make sure printec-catalog-widget.js is loaded before printec-site-widget.js');
            }
        },

        injectStyles(primary, secondary, accent, headerFooterBg, catalogBg) {
            if (document.getElementById('printec-site-styles')) return;

            // Calculate contrast text color
            const headerTextColor = this.getContrastColor(headerFooterBg);
            const footerTextColor = this.getContrastColor(headerFooterBg);

            const styles = document.createElement('style');
            styles.id = 'printec-site-styles';
            styles.textContent = `
                .ps-header {
                    background-color: ${headerFooterBg};
                    border-bottom: 1px solid ${this.adjustBrightness(headerFooterBg, -20)};
                }
                .ps-header-inner {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 15px 20px;
                    display: flex;
                    align-items: center;
                }
                .ps-logo {
                    max-height: 60px;
                    max-width: 250px;
                    width: auto;
                    height: auto;
                }

                .ps-hero {
                    width: 100%;
                    line-height: 0;
                    overflow: hidden;
                }
                .ps-hero img {
                    width: 100%;
                    height: auto;
                    display: block;
                }
                .ps-hero-desktop {
                    display: block;
                }
                .ps-hero-mobile {
                    display: none;
                }
                .ps-hero-all {
                    display: block;
                }

                .ps-catalog-section {
                    background-color: ${catalogBg};
                    min-height: 400px;
                    padding: 20px 0;
                }

                .ps-footer {
                    background-color: ${headerFooterBg};
                    color: ${footerTextColor};
                    border-top: 1px solid ${this.adjustBrightness(headerFooterBg, -20)};
                }
                .ps-footer-inner {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 30px 20px;
                }
                .ps-footer-inner a {
                    color: ${primary};
                }
                .ps-footer-inner a:hover {
                    color: ${accent};
                }

                /* Override catalog widget footer to hide "Powered by" */
                #printec-catalog-embedded .pc-footer {
                    display: none;
                }

                /* Override catalog widget accent color for prices */
                #printec-catalog-embedded .pc-card-price,
                #printec-catalog-embedded .pc-modal-price,
                #printec-catalog-embedded .pc-cart-item-price,
                #printec-catalog-embedded .pc-cart-total-value {
                    color: ${accent} !important;
                }

                /* Category tags use secondary color */
                #printec-catalog-embedded .pc-card-category {
                    color: ${secondary};
                }

                @media (max-width: 768px) {
                    .ps-hero-desktop {
                        display: none !important;
                    }
                    .ps-hero-mobile {
                        display: block !important;
                    }
                    .ps-logo {
                        max-height: 40px;
                    }
                    .ps-footer-inner {
                        padding: 20px 15px;
                        font-size: 14px;
                    }
                }
            `;
            document.head.appendChild(styles);
        },

        getContrastColor(hexColor) {
            const hex = hexColor.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            return luminance > 0.5 ? '#333333' : '#f5f5f5';
        },

        adjustBrightness(hexColor, amount) {
            const hex = hexColor.replace('#', '');
            let r = Math.max(0, Math.min(255, parseInt(hex.substr(0, 2), 16) + amount));
            let g = Math.max(0, Math.min(255, parseInt(hex.substr(2, 2), 16) + amount));
            let b = Math.max(0, Math.min(255, parseInt(hex.substr(4, 2), 16) + amount));
            return '#' + [r, g, b].map(c => c.toString(16).padStart(2, '0')).join('');
        },

        escapeAttr(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML.replace(/"/g, '&quot;');
        }
    };

    window.PrintecSite = PrintecSite;

})(window);
