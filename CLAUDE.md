# PrintecPOS — Contexto del proyecto

POS y cotizador B2B para distribuidores de promocionales. Cada distribuidor (`partner`) tiene su propia configuración de precios, branding y catálogo accesible. Printec actúa simultáneamente como proveedor y como administrador del sistema.

## Stack real

- **Backend:** Laravel `^10.0`, PHP `^8.1`
- **Frontend:** Blade + Alpine.js 3.4, Tailwind 3.1, Vite 7.1
- **Auth:** Laravel Breeze + Sanctum (sesiones web + API tokens por partner)
- **RBAC:** `spatie/laravel-permission` `^6.16`
- **Audit log:** `spatie/laravel-activitylog` `^4.10` (registrar usos relevantes con `LogsActivity`)
- **PDF:** `barryvdh/laravel-dompdf` `^3.1`
- **HTTP cliente:** Guzzle `^7.2`
- **Tests:** PHPUnit `^10.0` + Mockery
- **Code style:** Laravel Pint `^1.0` (auto-aplicado en hook PostToolUse a archivos PHP)
- **UI extras:** SweetAlert2, Swiper
- **Queue:** driver `database`
- **Locale / TZ:** `es` / `America/Cancun`

## Roles del sistema

| Rol | Quién |
|---|---|
| `super_admin` | Equipo Printec (Eduardo y staff) |
| `admin` | Administrador con permisos elevados |
| `asociado_administrador` | Dueño de cuenta de un partner / distribuidor |
| `asociado_vendedor` | Vendedor dentro de un partner |

Permisos vía Spatie. Las vistas y controllers verifican con `can:`/policies.

## Conceptos de dominio

- **Multi-tenant por `partner_id`**: la mayoría de tablas asociadas a un distribuidor llevan FK a `partners.id`. El scoping ocurre a nivel controller/policy, no por trait global (verificar al tocar queries nuevas).
- **Multi-supplier**: catálogo unificado desde DobleVela (SOAP) e Innovation (`Services/Innovation/InnovationService.php`, `InnovationV3Service.php`). Productos propios de Printec viven en la tabla `products` con `provider` distintivo.
- **Pricing tiers** por distribuidor (`PartnerPricing`, `PricingTier`) — precios escalonados por volumen.
- **Cotizaciones** (`Quote`, `QuoteItem`) → PDF DomPDF → flujo a pedido (en construcción, ver roadmap).
- **API por partner**: cada `Partner` tiene `api_key` y branding (`site_primary_color`, `hero_*`, etc) — alimenta widgets/sitio web del distribuidor.

## Modelos principales

`Partner`, `PartnerEntity`, `PartnerEntityBankAccount`, `PartnerPricing`, `PartnerTierHistory` · `Product`, `ProductCategory`, `ProductImpressionTechnique`, `ProductProvider`, `ProductStock`, `ProductVariant`, `ProductWarehouse` · `Quote`, `QuoteItem`, `CartSession` · `Client`, `User` · `Warehouse`, `PrintecCategory`, `PrintecCity` · `PricingTier`, `PricingSetting`, `AcquisitionChannel`.

> `ProductImpressionTechnique` ya existe (FK a producto, columnas `code` + `name`). Reusar antes de inventar tablas para la épica de impresión sugerida.

## Convenciones

### Código
- **Controllers thin** — la lógica vive en `app/Services/` (ya hay `Services/DobleVela/`, `Services/Innovation/`, etc).
- **FormRequest** para validación de inputs, no validar dentro del controller.
- **Policies** para autorización (`app/Policies/`), no `if ($user->role === 'X')` en controllers.
- **Eloquent relationships** definidas en el modelo, sin queries crudas salvo en reportes/jobs.
- **Helpers globales** en `app/Helpers/helpers.php` (cargado vía `composer.json` `files`).
- **Cache busting** en assets estáticos: usar `?v=filemtime(...)` en `<script>`/`<link>` (no confiar en headers del browser).
- **Sincronizaciones**: corren por cron/job, **sin botones en UI** que las disparen manualmente.

### Migraciones
- Una migración por cambio. Nombrarla descriptivamente: `add_X_to_Y_table` o `create_Y_table`.
- Default seguros: `nullable()` o defaults explícitos para no romper filas existentes en prod.
- Usar `Schema::hasColumn()` en queries/stats antes de filtrar por columnas que recién se agregan, para no romper el panel mientras la migración rodea producción.

### Tests
- Suite Feature/Unit con PHPUnit 10. Hoy solo hay tests de Breeze auth + ejemplos.
- Para tests de dominio, escribir Feature tests bajo `tests/Feature/<Modulo>/`.
- Usar `RefreshDatabase` o `DatabaseTransactions`. **No mockear DB en tests de integración.**

### Frontend
- Componentes Blade (`resources/views/components/`). Composiciones complejas → componentes anónimos.
- Alpine.js mínimo: `x-data` con state local, evitar tiendas globales.
- Tailwind utility-first; preferir clases sobre CSS custom.

## Integraciones

| Integración | Estado | Notas |
|---|---|---|
| DobleVela SOAP | Activo | Memoria con doc oficial: `~/.claude/.../memory/reference_doblevela_api.md` |
| Innovation SOAP+REST | Pausada / a reactivar | `Services/Innovation/InnovationService.php` y `InnovationV3Service.php` ya existen |
| SMTP Hostinger | Activo | Config en `.env` |
| Google reCAPTCHA | Activo | En forms públicos |
| Prodigia CFDI | Planeado | Ver roadmap épica 08 |

## Roadmap activo

Ver `.claude/roadmap/README.md` y archivos `01-impresion-grabado.md` … `09-sitio-web-publico.md`.

**Foco mayo 2026:** Sistema de impresión sugerida (épica 01) + flujo cotización→pedido (épica 02). Quick wins paralelos: lonas decimales (03), barra de progreso de perfil (05).

Cuando trabajes en una épica, **abre el archivo correspondiente primero** — tiene problema, objetivo, subtareas y criterios de aceptación.

## Lo que Claude debe (y no debe) hacer

**Sí:**
- Generar controllers, migraciones, FormRequests, policies, services, blades.
- Escribir tests Feature/Unit para nueva lógica de dominio.
- Revisión de seguridad en cambios sensibles (auth, datos fiscales, pagos).
- Documentar APIs en `docs/api/` cuando se exponen endpoints nuevos.

**No:**
- No crear archivos `.md` de planning/decisión salvo que se pida explícitamente.
- No mockear DB en tests de integración.
- No agregar botones de "sincronizar manualmente" en UI — todo cron/job.
- No romper el contrato de API pública del partner (`api_key` con `api_show_prices`).
