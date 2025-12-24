# Historial de Cambios - Sistema de Repertorio

Todas las actualizaciones notables de este proyecto serán documentadas en este archivo.

## [1.5.0] - 2025-12-24
### Añadido
- Filtros por recurso en el Dashboard (Midi y ProPresenter).
- Estadísticas automáticas para enlaces externos vinculados.
- Iconografía descriptiva (🎹 y 📺) en la lista de canciones.

### Cambiado
- Título visual de "Biblioteca Digital" a "Repertorio".
- Migración de almacenamiento local a enlaces externos para MIDI y ProPresenter.
- Layout del modal para agrupar links de recursos externos.

### Corregido
- Estandarización de la etiqueta "multitrack" en minúsculas.
- Persistencia de los campos midi_path y propresenter_path en la base de datos.


## [1.4.0] - 2025-12-23
### Añadido
- **Ordenamiento Dinámico**: Implementación de clics en encabezados de tabla (ID, Artista, Tono, Prioridad) para ordenar A-Z y Z-A.
- **Filtro por Clic**: Función `filterByArtist` que permite filtrar toda la lista simplemente tocando el nombre de un artista.
- **Limpieza de Buscador**: Botón "✕" integrado en el input de búsqueda para resetear la vista rápidamente.

### Corregido
- **Validación de Recursos**: Uso de `TRIM()` en PHP para asegurar que los contadores de "Sin YouTube" y "Sin PDF" no cuenten celdas con espacios vacíos.
- **Lógica de Atributos**: Ajuste en los `data-attributes` de la tabla para asegurar compatibilidad total con el filtrado JavaScript.

---

## [1.3.0] - 2025-12-21
### Añadido
- **ID Manual**: Posibilidad de asignar y editar el ID de la canción manualmente desde el modal, manteniendo la integridad en la base de datos.
- **Dashboard Proactivo**: Nuevas tarjetas que cuentan cuántos links de YouTube y PDF faltan por completar.

### Cambios
- **Estandarización**: Se cambió la etiqueta global de "Track" a "**Multitrack**" por requerimiento del sistema.

---

## [1.2.0] - 2025-12-19
### Añadido
- **Interfaz UI/UX**: Rediseño completo con Tailwind CSS usando estética de tarjetas redondeadas y sombras profundas.
- **Buscador en Tiempo Real**: Filtrado de la tabla mediante JavaScript sin recarga de página.
- **Sistema de Prioridades**: Clasificación visual por colores (High/Medium/Low).

---

## [1.0.0] - 2025-12-15
### Añadido
- Versión inicial del sistema con CRUD básico (Crear, Leer, Actualizar, Borrar).
- Conexión a base de datos mediante PDO.