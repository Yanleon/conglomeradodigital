# Rediseño Admin de Productos

Este documento resume los cambios aplicados en la vista de administración de productos para mejorar la experiencia de uso con productos simples y configurables.

## Objetivo

- Mejorar legibilidad y velocidad de gestión en catálogos grandes.
- Reducir confusión por variantes de productos configurables.
- Mantener compatibilidad con acciones nativas de Bagisto (editar, duplicar, eliminar, filtros, paginación).

## Archivos modificados

- `packages/Webkul/Admin/src/Resources/views/catalog/products/index.blade.php`
- `packages/Webkul/Admin/src/DataGrids/Catalog/ProductDataGrid.php`
- `packages/Webkul/Admin/src/Http/Controllers/Catalog/ProductController.php`
- `packages/Webkul/Admin/src/Resources/views/catalog/products/edit/controls.blade.php`

## Mejoras implementadas

### 1) Vista productos (Tarjetas y Tabla)

- Se habilitó selector de vista `Tarjetas` / `Tabla`.
- Se mantuvo compatibilidad visual con el admin actual.
- Se añadieron KPIs superiores:
  - Total productos
  - Activos
  - Bajo stock
  - Borradores

### 2) Tarjetas de producto

- Card compacta con imagen de alto fijo.
- Información principal:
  - Nombre
  - SKU
  - Precio
  - Cantidad disponible
  - Categoría
  - Tipo
  - Estado
- Acciones rápidas:
  - Editar
  - Duplicar
  - Eliminar

### 3) Reglas visuales de stock

- Badge `Bajo stock` cuando cantidad `<= 3` y `> 0`.
- Badge `Sin stock` cuando cantidad `<= 0`.
- Texto de disponibilidad:
  - Naranja cuando `<= 3`
  - Verde cuando `> 3`
  - Rojo para `Sin stock`

### 4) Productos configurables

- Se muestra contador de variantes en la card del configurable.
- Cuando el padre configurable tiene precio `0`, se muestra el **precio mínimo de variantes**.
- Las variantes hijas pueden ocultarse por defecto para reducir ruido en el listado.

### 5) Ajustes en vista Tabla

- Reorden de columnas:
  1. ID
  2. SKU
  3. Nombre
  4. Imagen
  5. Precio
  6. Cantidad
  7. Categoría
  8. Tipo
- Se ocultaron columnas:
  - Estado
  - Familia de atributos
- La columna imagen muestra miniatura (no ruta de archivo).
- Se aplicó versión compacta de anchos/espaciados para columnas.

### 6) Campo precio en edición

- En edición de producto, el input de precio se muestra sin decimales visibles
  (ejemplo: `39000` en lugar de `39000.0000`).

## Consultas y lógica relevantes

- En `ProductDataGrid` se añadieron cálculos adicionales:
  - `variants_count`
  - `min_variant_price`
- Se añadieron joins/campos necesarios para distinguir padre/hijo en catálogo.

## Limpieza de cache recomendada

Cuando se modifique esta vista, ejecutar:

```bash
php artisan view:clear
php artisan optimize:clear
```

Si no se reflejan cambios de UI, forzar recarga del navegador con `Ctrl + F5`.
