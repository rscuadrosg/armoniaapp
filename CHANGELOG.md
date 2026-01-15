# Historial de Cambios - Sistema de Repertorio

Todas las actualizaciones notables de este proyecto serán documentadas en este archivo.

## [1.15.0] - 2026-01-14
### Arquitectura Modular
- **Hub Central**: Transformación de la página de inicio en un selector de módulos (Alabanza, Ujieres, Niños).
- **Módulo de Alabanza**: Espacio dedicado (`worship.php`) con barra lateral de navegación exclusiva para la gestión musical.
- **Navegación Jerárquica**: Separación clara entre el menú global (Top Bar) y el menú del módulo (Sidebar).

### Personalización (White Label)
- **Identidad de Marca**: Nuevo panel de `Configuración General` para subir Logo, Favicon y cambiar el nombre de la aplicación.
- **Adaptabilidad UI**: El encabezado y la barra lateral se ajustan automáticamente para mostrar el logo o el texto según la configuración.

### UI / UX
- **Header Oscuro**: Rediseño de la barra superior con tonos oscuros para mayor profesionalismo.
- **Navegación Móvil**: Menú hamburguesa rediseñado para incluir acceso rápido a herramientas, cambio de módulo y cierre de sesión.

## [1.14.0] - 2026-01-06
### Gestión de Equipo y Automatización
- **Roles y Liderazgo**: Implementación del rol "Líder de Instrumento" con permisos delegados para gestionar secciones específicas.
- **Matriz de Habilidades**: Registro de instrumentos que toca cada miembro para filtrado inteligente en asignaciones.
- **Control de Disponibilidad**: Configuración por músico de días disponibles y límite máximo de servicios mensuales.
- **Auto-Equipo**: Nueva herramienta para rellenar automáticamente los roles vacíos del calendario basándose en disponibilidad y habilidades.

### Experiencia de Usuario (UX)
- **Dashboard Unificado**: La página de inicio (`index.php`) ahora se adapta dinámicamente: muestra estadísticas para Admins y la agenda personal para Músicos.
- **Edición de Perfiles**: Gestión completa de miembros (crear/editar) mediante modales, incluyendo cambio de contraseña y roles.

## [1.13.0] - 2026-01-06
### Experiencia en Vivo (Live View)
- **Diseño de Escenario**: Rediseño total de `live_view.php` para máxima legibilidad. Tono (Key) resaltado en gran tamaño y alto contraste.
- **Lista Ultra-Compacta**: Optimización del espacio para mostrar más canciones por pantalla, priorizando Título y Tono sobre otros metadatos.

### Vista de Músico
- **Reorganización**: El repertorio ahora aparece antes que la lista de equipo en `view_event_musico.php` por prioridad de uso.
- **Estilo Unificado**: La lista de canciones adopta el diseño compacto de la vista en vivo, con acceso a recursos mediante modal.
- **Tabla de Equipo**: Nueva visualización de integrantes en formato de tabla de 3 columnas (Rol | Músico | Estado).

### Gestión y UI
- **Importación Inteligente (Upsert)**: El importador de CSV ahora actualiza los datos de las canciones si encuentra un ID existente, permitiendo ediciones masivas.
- **Iconografía**: Se reemplazó el icono genérico de video por el logo oficial de YouTube en todas las vistas.
- **Correcciones Responsive**: Ajuste en el formulario de "Instrumento Extra" en `view_event.php` para evitar desbordamiento en móviles.

## [1.12.0] - 2026-01-06
### UI / UX (Mobile First)
- **Diseño Responsivo**: Reestructuración completa de las vistas principales (`index.php`, `repertorio_lista.php`, `events.php`) para optimizar la experiencia en dispositivos móviles.
- **Menú Hamburguesa**: Nueva navegación colapsable en móviles para ahorrar espacio.
- **Listas Compactas**: Se reemplazaron las tarjetas grandes por listas de alta densidad en el repertorio y eventos.
- **Modales de Detalle**: En móviles, las canciones ahora abren una ficha de detalle (modal) optimizada.
- **Botones de Acción**: Rediseño de los botones de herramientas en el Dashboard para mayor claridad y contraste.

### Funcionalidad
- **Gestión en Modales**: Creación de Eventos, Miembros y Roles directamente desde modales sin salir de la página.
- **Drag & Drop**: Nueva funcionalidad en `settings_band.php` para reordenar los roles de la banda arrastrando y soltando.
- **Historial de Eventos**: Filtro rápido en la página de servicios para alternar entre eventos futuros y pasados.
- **Codificación UTF-8**: Solución definitiva a problemas de caracteres especiales en toda la aplicación.

## [1.11.0] - 2026-01-06
### Automatización e Inteligencia
- **Generador de Temporadas**: Nuevo módulo `generate_schedule.php` unificado que permite planificar múltiples servicios recurrentes definiendo días, horarios y estructura del repertorio en un solo paso.
- **Títulos Dinámicos**: Los eventos generados ahora incluyen automáticamente el día y la fecha en su nombre (ej: "Servicio Dom-01-feb-2026") para facilitar la identificación.
- **Smart Shuffle (Rotación Inteligente)**: Algoritmo avanzado que asigna canciones basándose en etiquetas específicas (ej: 2 de Adoración, 1 de Alabanza) priorizando las menos tocadas para garantizar variedad.
- **Magic Fill**: Nueva funcionalidad en la vista de evento (`view_event.php`) que permite auto-generar o reemplazar el setlist de un servicio específico usando la lógica de rotación inteligente.

## [1.10.0] - 2026-01-06
### Gestión de Datos (Importar / Exportar)
- **Importación Masiva**: Nuevo módulo `import_songs.php` para cargar canciones desde CSV. Soporta detección automática de etiquetas y asignación de ID manual.
- **Copia de Seguridad**: Función de exportación completa (`export_songs.php`) que genera un CSV compatible con la plantilla de importación.
- **Plantillas Inteligentes**: Generador de plantillas CSV (`download_template.php`) para facilitar la carga de datos sin errores de formato.

### Mejoras en Repertorio
- **Ordenamiento Interactivo**: Ahora es posible ordenar la tabla de canciones haciendo clic en los encabezados (ID, Artista, Tono).
- **Notación Musical Precisa**: Se eliminó la transformación forzada a mayúsculas en el campo de Tono para respetar notaciones como "Cm" o "F#m".

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