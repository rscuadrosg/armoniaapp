# Historial de Cambios - Sistema de Repertorio

Todas las actualizaciones notables de este proyecto serán documentadas en este archivo.

## [1.9.0] - 2026-01-06
### Sistema de Etiquetas (Tags)
- **Etiquetado Dinámico**: Se reemplazó el sistema rígido de Prioridades (Alta/Media/Baja) por un sistema de Etiquetas flexible y personalizable.
- **Gestión de Etiquetas**: Nueva página `settings_tags.php` donde los administradores pueden crear, editar y colorear etiquetas personalizadas.
- **Asignación Múltiple**: Capacidad de asignar múltiples etiquetas a una sola canción desde los formularios de creación y edición.

### UI / UX
- **Filtros Avanzados**: Rediseño de la barra de herramientas en `repertorio_lista.php` con sistema de "Chips" para filtrado múltiple (Lógica AND).
- **Limpieza Visual**: Simplificación de los contadores superiores en el repertorio para reducir el ruido visual.

## [1.8.0] - 2026-01-06
### Seguridad y Arquitectura
- **Estandarización de Autenticación**: Se implementó `auth.php` en todos los archivos críticos (`add_event.php`, `view_event.php`, `settings_band.php`) eliminando verificaciones de sesión manuales y redundantes.
- **Protección de Escritura**: Se corrigió una vulnerabilidad en `view_event.php` donde las solicitudes POST se procesaban antes de verificar los permisos de administrador. Ahora la verificación ocurre al inicio del script.
- **Login Modular**: El sistema de roles ahora es completamente centralizado. Cualquier cambio futuro en la lógica de usuarios solo requerirá editar `auth.php`.

### UI / UX
- **Botón de Salida**: Se añadió un botón "SALIR" en el header para cerrar sesión de forma segura.
- **Redirección Inteligente**: El login ahora redirige a los músicos directamente a su Dashboard personal y a los administradores al Panel General.

## [1.7.0] - 2025-12-28
### Añadido
Módulo de Visualización de Servicio (view_event_musico.php):
- Implementación de una vista optimizada para músicos que muestra el repertorio y el equipo asignado.
- Integración de lógica de "Avatar por Defecto": genera un icono con la inicial del nombre si el usuario no tiene foto de perfil.
-Visualización de instrumentos específicos por evento recuperados de la tabla event_assignments.

Seguridad de Acceso:
- Implementación de validación de rol admin en archivos sensibles (members.php, settings_band.php) para restringir el acceso a usuarios no autorizados.

🔧 Corregido
Mapeo de Base de Datos:
- Se corrigió el error de tabla inexistente cambiando la referencia de users a la tabla correcta members.
- Se actualizaron las consultas SQL para usar la columna full_name en lugar de name.
- Se reparó la consulta de equipo para apuntar a la tabla de unión correcta: event_assignments.

Flujo de Navegación en Dashboard:
- Se corrigió el enlace del botón "Ver Resumen" en index.php que redirigía incorrectamente a la configuración del servicio para administradores.
- Se eliminó el bucle de redirección en event_details.php que impedía a los administradores ver la vista de resumen del músico.

Manejo de Sesiones:
- Se añadió una comprobación de session_status() antes de session_start() para evitar el error Warning: session already started detectado en el header.

### Cambiado
Interfaz de Usuario (UI):
- Actualización de la lista de miembros para usar tarjetas redondeadas (rounded-[2rem]) y tipografía black italic consistente con el resto del Dashboard.
- Mejora en la visualización de canciones: ahora incluyen el tono (musical_key) resaltado en etiquetas de color azul.

📊 Estado de la Estructura de Datos
Tabla members: Activa. Columnas principales: id, full_name, email, role, profile_photo.
Tabla event_assignments: Activa. Relaciona event_id con member_id e incluye la columna instrument.




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