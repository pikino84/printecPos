# 01 — Impresión / grabado sugerido en cotizaciones

**Prioridad:** 🔥 Alta — foco explícito del mes según Eduardo.

## Problema
Cuando un distribuidor cotiza un producto del catálogo (DobleVela u otros), no hay forma de sugerirle automáticamente qué tipo de personalización aplica (grabado láser, DTF, Tampo, serigrafía, full color rotativo, etc.). Hoy Printec se lo dice manualmente afuera del sistema y se pierde la venta de impresión.

## Objetivo
Que al seleccionar un producto del catálogo el sistema:
1. Sugiera automáticamente los tipos de impresión compatibles.
2. Muestre los productos propios de Printec asociados (los "grabados") como sugeridos / relacionados.
3. Permita al distribuidor agregarlos al cuerpo de la cotización con un clic.

## Subtareas

### 1.1 Investigar API DobleVela — ¿trae tipo de impresión?
- Revisar la documentación (memoria `reference_doblevela_api.md` + nota `project_doblevela_api_update.md`).
- Llamar el método de detalle del producto y ver si entre los campos viene "tipo de impresión" / "tipo de grabado".
- **Si viene en el SOAP** → mapearlo automáticamente al sincronizar; ahorra mucho trabajo.
- **Si NO viene** → tabular manualmente con un editor (ver 1.2). Eduardo aceptó que sería "una chambota, pero solo una vez".
- Nota concreta de Eduardo: el producto **MALVA** en la ficha técnica de DobleVela ya muestra el tipo de impresión sugerido. Validar primero con ese SKU.
- El producto **DENCEL** muestra "grabado láser, serigrafía, full color rotativo" en imagen/PDF. Si es PDF de texto se podría parsear; si es imagen no extraíble, descartar esta vía.

### 1.2 Editor de productos DobleVela (tipo impresión)
- Hoy los productos sincronizados desde DobleVela no se editan en local. Falta una vista de edición acotada para asignar etiquetas: `laser`, `dtf`, `dtfv`, `tampo`, `serigrafia`, `full_color_rotativo`, `sublimacion`, etc.
- UI: checkboxes (el producto puede aceptar varios tipos).
- Conservar el `partner_id`/origen, no romper la sincronización entrante.
- Pensar en una migración que agregue una tabla pivote `product_print_types` (o columna JSON `compatible_print_types`).

### 1.3 Productos propios marcados como "grabados"
- Categorizar productos propios de Printec por tipo de grabado (mismo vocabulario de 1.2).
- Hoy hay 387 productos propios y 26 con la palabra "grabado" — usarlos como semilla.
- Hacer que productos propios tipo `laser` se vinculen automáticamente a productos DobleVela marcados como aceptan `laser`. Sin tener que pintar a mano cada relación.

### 1.4 UI de sugerencia en el cotizador
- Al seleccionar un producto en el cotizador, mostrar un bloque "Personalización compatible" con los tipos sugeridos.
- Al seleccionar un tipo, mostrar abajo los productos propios "grabado" que cumplen ese tipo, con su precio.
- Botón para agregarlos como línea hija al producto del cotizador (mantiene la cotización en una sola vista).

### 1.5 Parámetros del producto propio
Eduardo: *"Hay que agregar parámetros al producto propio para que el sistema lo jale, ¿no?"*
- Definir qué campos: tipo de impresión, área máxima de impresión, número de tintas, costo base, costo por color/extra.
- Estos parámetros alimentan el cálculo del costo de personalización en el cotizador.

## Criterios de aceptación
- [ ] En el detalle de cualquier producto del catálogo aparecen los tipos de impresión compatibles.
- [ ] El editor permite asignar tipos a productos DobleVela sin que la próxima sincronización borre la edición.
- [ ] Productos propios tipo "grabado láser" aparecen como sugeridos cuando el producto seleccionado acepta láser.
- [ ] Si la API de DobleVela trae el tipo de impresión, está mapeado y la primera carga se hace sin captura manual.

## Riesgos
- Si la API de DobleVela no trae el tipo de impresión, hay que clasificar manualmente >5000 productos. Mitigación: hacerlo por categoría (todas las plumas → láser/tampo, todas las tazas → serigrafía/full color, etc.) en lugar de uno por uno.
- Sincronización pisando ediciones manuales — la columna/tabla de tipos debe vivir aparte del payload upstream.
