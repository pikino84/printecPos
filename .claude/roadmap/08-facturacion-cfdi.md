# 08 — Facturación CFDI (Prodigia)

**Prioridad:** 🟡 Media. Eduardo: *"este mes sí estoy facturando un c***** y ya es un dolor de huevos cuando son un verguero de productos"* — no es para mañana pero pesa en su día a día.

## Problema
La facturación CFDI hoy se hace fuera del sistema; con muchos productos por pedido es lento y propenso a errores.

## Objetivo
Emitir CFDI 4.0 desde el sistema usando Prodigia (PAC ya planeado en `CLAUDE.md` → "Prodigia CFDI (planned)").

## Subtareas

### 8.1 Integración Prodigia (timbrado)
- Credenciales de prueba.
- Cliente HTTP del API de Prodigia (REST/SOAP, según su doc).
- Soportar timbrado, cancelación y consulta de estatus.

### 8.2 Datos fiscales del emisor
- Configurables en `settings`: RFC, razón social, régimen, lugar de expedición.
- Cargar el certificado CSD (vigencia, alerta cuando esté por vencer).

### 8.3 Datos fiscales del receptor
- Tomarlos del partner (deben estar al 100% — épica 05).
- Validar al momento de timbrar; si faltan, bloquear con mensaje claro.

### 8.4 Mapeo de líneas de cotización/pedido a conceptos CFDI
- Cada línea → un concepto con clave SAT, unidad SAT, descripción, cantidad, valor unitario, importe, impuestos.
- Catálogo SAT precargado o consultable en línea.
- Default razonable por categoría de producto (lonas → clave X, plumas → clave Y).

### 8.5 Emisión por pedido vs emisión consolidada mensual (épica 04)
- Por pedido: botón "Facturar este pedido" para super-admin.
- Por periodo: el cierre mensual de comisiones (épica 04) genera **un solo CFDI consolidado** por distribuidor.

### 8.6 PDF + XML
- Guardar XML timbrado y PDF generado (con el branding de Printec).
- Descarga directa desde la vista del pedido / del periodo.
- Adjuntar al correo de confirmación.

### 8.7 Cancelación
- UI para solicitar cancelación con motivo (clave SAT).
- Estado `cfdi_cancelado` en el pedido.

## Criterios de aceptación
- [ ] Se timbra un CFDI 4.0 desde el sistema con datos del emisor y receptor correctos.
- [ ] Se descargan XML y PDF.
- [ ] Cierre mensual genera un CFDI consolidado por distribuidor.
- [ ] Se puede cancelar un CFDI con motivo SAT.
- [ ] Alerta cuando el CSD esté por vencer.

## Notas
- Esto destraba la épica 04 (factura única mensual) — ambas se acoplan, mejor planearlas juntas.
- Validar con Eduardo si por ahora ya hay PAC contratado o si hay que abrir cuenta en Prodigia.
