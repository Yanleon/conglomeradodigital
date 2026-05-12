# Guía para administradores

## Configurar Footer desde Admin
1. Ir a `Settings -> Themes -> Edit`.
2. Buscar bloque de personalización `Footer Content`.
3. Configurar campos principales:
   - Estado (activo/inactivo).
   - Logo, texto de descripción, teléfono y correo.
   - Títulos de columnas y título de redes.
   - Texto inferior con placeholders `{year_range}` o `{current_year}`.
   - Año inicial del rango.
   - Colores de fondo de footer y barra inferior.
4. Guardar cambios y validar en storefront (desktop/móvil).

## Configurar Popup Promocional desde Admin
1. Ir a `Settings -> Themes -> Edit`.
2. Buscar bloque `Popup Widget`.
3. Definir reglas:
   - Estado del popup.
   - Fecha de inicio/fin.
   - Dónde mostrar (`all`, `home`, `urls`).
   - Frecuencia (`session`, `once`, `daily`, `weekly`).
   - Cierre por overlay y autocierre en segundos.
4. Elegir tipo de contenido:
   - Imagen + enlace (banner + URL + texto opcional).
   - HTML + CSS (contenido personalizado, con preview).
   - Link simple.
5. Opcional: habilitar "No mostrar de nuevo".
6. Guardar y validar en storefront.

## Botón de pruebas (Popup)
- En la edición del popup existe botón `Reset Popup State`.
- Limpia cookies y storage del navegador para probar nuevamente comportamiento de frecuencia.
