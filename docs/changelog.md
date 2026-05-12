# Changelog resumido

## 2026-05
- Se agrega `footer_content` como Theme Customization administrable.
- Se agrega `popup_widget` como Theme Customization administrable.
- Footer: nuevos campos de logo, textos, títulos, placeholders de año y colores.
- Popup: reglas de fechas, segmentación por página, frecuencia y tipos de contenido (imagen/link/html).
- Popup HTML aislado en `iframe srcdoc` para mejorar compatibilidad de estilos.
- Persistencia de estado del popup con cookie, localStorage y sessionStorage.
- Se añade limpieza de response cache al cambiar customizaciones de popup/footer.
- Se añade botón de admin `Reset Popup State` para pruebas funcionales.
