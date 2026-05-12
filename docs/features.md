# Funcionalidades clave

## Footer administrable (Theme Customization)
- Nuevo tipo de personalización: `footer_content`.
- Configuración desde Admin para:
  - Logo del footer.
  - Texto de acerca de la tienda.
  - Teléfono y correo de contacto.
  - Títulos de columnas del footer.
  - Título de sección social.
  - Texto inferior (`bottom_text`) con placeholders `{year_range}` y `{current_year}`.
  - Año inicial para rango dinámico.
  - Colores de fondo (`footer_bg`, `footer_bottom_bg`).
- Render condicional en storefront y ajustes de centrado en móvil para bloques principales.

## Popup promocional administrable (Theme Customization)
- Nuevo tipo de personalización: `popup_widget`.
- Configuración desde Admin para:
  - Activar/desactivar popup.
  - Ventana de fechas (`start_at`, `end_at`).
  - Segmentación por ubicación (`all`, `home`, `urls`).
  - Frecuencia (`session`, `once`, `daily`, `weekly`).
  - Cierre automático (segundos).
  - Cierre al hacer click fuera (overlay).
  - Checkbox de "No mostrar de nuevo".
  - Tipo de contenido:
    - Imagen + enlace.
    - HTML + CSS personalizado.
    - Link simple.
- Soporte de uploads:
  - `banner_file` para popup tipo imagen.
  - `html_image_files` para contenido HTML.
- Persistencia de frecuencia con `cookie + localStorage + sessionStorage`.
- Limpieza automática de response cache al crear/editar/eliminar personalizaciones del popup/footer.

## Ruta administrativa útil
- Configuración principal: `Settings -> Themes -> Edit`.
