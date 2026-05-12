# Solución de problemas

## El popup no aparece
- Verificar que `enabled` esté activo en `Popup Widget`.
- Verificar ventana de fechas (`start_at` y `end_at`).
- Verificar regla `display_on` y URLs configuradas.
- Revisar frecuencia: si quedó marcado como visto o "No mostrar de nuevo", no reaparecerá.
- Usar botón `Reset Popup State` en admin para limpiar estado del navegador.

## El popup reaparece aunque se cerró
- Confirmar frecuencia seleccionada (`daily`, `weekly`, `once`, etc.).
- Limpiar caches de aplicación:
  - `php artisan view:clear`
  - `php artisan responsecache:clear`
- Limpiar estado local del navegador (cookies/localStorage/sessionStorage) con el botón de reset.

## La X del popup no responde
- Limpiar caches de vistas y response cache.
- Forzar recarga dura del navegador (`Ctrl + F5`).
- Verificar que no haya JS en cache del navegador.

## El footer no refleja cambios
- Confirmar que `Footer Content` esté activo.
- Guardar cambios y limpiar cache de vistas.
- Validar en móvil/desktop por separado (algunos bloques tienen estilos responsivos distintos).
