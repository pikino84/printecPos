---
name: blade
description: Convenciones de UI con Blade + Alpine.js + Tailwind para PrintecPOS. Úsalo al crear/editar vistas, componentes Blade, formularios, tablas, modales o cualquier UI del cotizador, panel admin o partner site. Incluye reglas de cache busting y SweetAlert2.
---

# UI — Blade + Alpine + Tailwind

## Stack
- Blade (`resources/views/`)
- Alpine.js 3.4 (state local)
- Tailwind 3.1 (utility-first)
- SweetAlert2 (modales / confirmaciones)
- Swiper (carousels)
- Vite 7.1 (dev/build)

## Componentes Blade

- Crear componentes en `resources/views/components/`.
- Anonymous components para UI sencilla (sin clase PHP).
- Class-based components cuando hay lógica de presentación o defaults complejos.
- Slots con nombre para layouts: `<x-slot:header>`, `<x-slot:actions>`.

```blade
<x-card>
    <x-slot:header>Cotización #{{ $quote->id }}</x-slot:header>
    {{-- contenido --}}
</x-card>
```

## Alpine.js

- `x-data` con state **local** del componente. Evitar tiendas globales.
- Inicializar listas/objetos en `x-data`, no in-line en eventos.
- Eventos personalizados con `$dispatch` y escuchar con `@event-name.window`.
- No mezclar Alpine con jQuery (este repo no usa jQuery, no introducirlo).

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>...</div>
</div>
```

## Tailwind

- Utility-first siempre. Componentes solo si la clase se repite >5 veces.
- Responsive con `sm: md: lg: xl:`. Mobile-first.
- Colores por marca tomar de `Partner` (`site_primary_color`, etc.) cuando la vista es del sitio del partner.
- Configurar nuevas clases en `tailwind.config.js`, no `<style>` inline.

## Formularios

- `@csrf` siempre en POST/PUT/DELETE (Blade lo agrega con `@method`).
- Mostrar errores con `@error('campo')` debajo del input.
- Old values con `value="{{ old('campo') }}"`.
- Validación se queda en FormRequest (ver skill `laravel`).

## SweetAlert2 — confirmaciones

```blade
<button onclick="confirmDelete({{ $item->id }})">Eliminar</button>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: '¿Eliminar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(r => {
        if (r.isConfirmed) document.getElementById('form-delete-' + id).submit();
    });
}
</script>
```

## Cache busting

Assets estáticos referenciados directamente DEBEN llevar versión:

```blade
<script src="{{ asset('js/custom.js') }}?v={{ filemtime(public_path('js/custom.js')) }}"></script>
<link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ filemtime(public_path('css/custom.css')) }}">
```

Para assets de Vite usar `@vite` directiva (ya maneja hash automáticamente).

## Anti-patrones

- ❌ Lógica de negocio en Blade (`@php $total = $items->sum(...) @endphp`) — calcular en controller/service.
- ❌ Loops anidados pesados en vista — preparar el array en el controller.
- ❌ jQuery en código nuevo.
- ❌ Estado de Alpine global; usar dispatch/eventos en su lugar.
- ❌ CSS inline (`style="..."`) salvo colores dinámicos del partner.

## Layouts

- Layout principal en `resources/views/layouts/`.
- Header/footer extraídos a componentes para reutilización.
- Vista de partner site usa branding del modelo `Partner` (logo, hero, colores).
