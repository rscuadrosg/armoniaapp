# 🎵 Sistema de Gestión de Repertorio Musical

Este proyecto es un panel administrativo avanzado diseñado para gestionar una biblioteca de canciones, enfocándose en la velocidad de búsqueda, organización por prioridades y control de recursos multimedia.

-----------------------------------------------------------------------------------------------------------------------------------------------------------------

**Versión:** 1.9.0  
**Última Actualización:** 2026-01-06 

### 🚀 Nuevas Funcionalidades (v1.9.0)
1. Sistema de Etiquetas Dinámicas
   - **Flexibilidad Total**: Se eliminó la restricción de prioridades fijas. Ahora puedes crear etiquetas personalizadas (ej: "Navidad", "Adoración", "Rápida").
   - **Filtrado Multi-Criterio**: El buscador del repertorio permite activar múltiples filtros de etiquetas simultáneamente (ej. buscar canciones que sean "Adoración" Y "Navidad").
   - **Gestión Visual**: Los administradores pueden asignar colores específicos a cada etiqueta para una rápida identificación.

2. Mejoras en Interfaz
   - **Barra de Herramientas Unificada**: Se consolidaron los filtros de búsqueda, recursos y etiquetas en una barra más limpia y funcional.

-----------------------------------------------------------------------------------------------------------------------------------------------------------------

**Versión:** 1.8.0  
**Última Actualización:** 2026-01-06 

### 🚀 Nuevas Funcionalidades (v1.8.0)
1. Seguridad y Arquitectura
   - **Estandarización de Autenticación**: Se implementó `auth.php` en todos los archivos críticos (`add_event.php`, `view_event.php`, `settings_band.php`) eliminando verificaciones de sesión manuales.
   - **Protección de Escritura**: Se corrigió una vulnerabilidad en `view_event.php` validando permisos de administrador antes de procesar solicitudes POST.
   - **Login Modular**: El sistema de roles ahora es completamente centralizado en un único archivo de autenticación.

2. UI / UX
   - **Botón de Salida**: Se añadió un botón "SALIR" en el header para cerrar sesión de forma segura.
   - **Redirección Inteligente**: El login ahora redirige a los músicos directamente a su Dashboard personal y a los administradores al Panel General.

-----------------------------------------------------------------------------------------------------------------------------------------------------------------

**Versión:** 1.7.0  
**Última Actualización:** 2025-12-28 

### 🚀 Nuevas Funcionalidades (v1.7.0)
1. Correcciones de Base de Datos (SQL)
Se identificaron y corrigieron errores de vinculación con la base de datos bsyhfyoq_armoniadb:
Renombramiento de Tablas: Se migró la lógica de users a la tabla real members.
Mapeo de Columnas: Se corrigió el uso de name por full_name y se integró la columna profile_photo para las imágenes de perfil.
Tabla de Asignaciones: Se identificó que la tabla para vincular músicos con eventos es event_assignments, la cual contiene la columna clave instrument.

2. Gestión de Equipo (members.php)
Visualización Dinámica: Se implementó la lista de integrantes recuperando datos reales de la DB.
Lógica de Imágenes: Se añadió un sistema de verificación que busca el archivo en uploads/profiles/. Si no existe o es NULL, genera automáticamente un avatar con la inicial del músico sobre un fondo negro estilizado.
Seguridad: Se añadió un bloqueo para que solo usuarios con role = 'admin' puedan eliminar integrantes.

3. Dashboard y Redirecciones (index.php)
Corrección de Botones: Se separaron las funciones de los botones en la tarjeta de "Próximos Servicios":
Botón Gris (Configurar): Exclusivo para Admins, lleva a event_setup.php.
Botón Azul (Ver Resumen): Disponible para todos, ahora apunta correctamente a event_details.php con el parámetro view_event_musico.
Solución de Bucle para Admin: Se eliminó la redirección forzada que enviaba al Admin a la edición cuando intentaba ver el resumen como músico.

4. Vista de Resumen de Servicio (view_event_musico.php)
Se reconstruyó totalmente la página para ser el centro de información del músico:
Sección de Equipo: Ahora muestra a todos los músicos asignados al evento mediante un JOIN con la tabla event_assignments.
Instrumentos Específicos: A diferencia del rol general, ahora se muestra qué instrumento tocará el músico en ese servicio específico (ej. Un bajista que ese día hace voces).
Repertorio: Listado de canciones con tono (musical_key) y botón de acceso directo a YouTube.
Estética: Diseño optimizado para móviles con tarjetas redondeadas (rounded-[2.5rem]) y sombras suaves.

🚀 Próximos Pasos definidos:
Sistema de Login: Implementar login.php y logout.php utilizando las credenciales de la tabla members.
Seguridad de Sesiones: Reemplazar el selector manual de Admin/Músico del header por una sesión real basada en el login.
Formulario de Alta: Ajustar add_member.php para que incluya la subida de fotos a la carpeta correcta.


-----------------------------------------------------------------------------------------------------------------------------------------------------------------


**Versión:** 1.6.0  
**Última Actualización:** 2025-12-27 

### 🚀 Nuevas Funcionalidades (v1.6.0)
Documentación del Proyecto: ArmoníaApp
Control de Roles y Vistas
Se ha implementado una lógica de acceso basada en la variable `$isAdmin` (gestionada mediante sesiones PHP) para diferenciar entre Administrador y Músico.

1. Vista de Administrador
• Repertorio: Acceso total. Botón "+ Nueva" visible, opciones de edición (✎) y eliminación (✕) activas.
• Dashboard: Puede ver todas las métricas y tiene acceso al botón "Configurar" en la sección de Próximos Servicios.
• Gestión: Capacidad para procesar cambios en la base de datos (POST/GET).

2. Vista de Músico (Solo Lectura)
• Repertorio: Solo visualización. Se ocultan los botones de creación, edición y borrado.
• Dashboard: Acceso a métricas generales. En "Próximos Servicios", solo ve el botón "Ver Resumen".
• Filtros: Mantiene la capacidad de usar los filtros rápidos del dashboard y el buscador.

Convenciones de Nomenclatura
Siguiendo las instrucciones del usuario:
• multitrack: Se utiliza este término en lugar de "track" en toda la interfaz.
• propresenter lyrics: Nombre asignado a los campos y recursos relacionados con archivos de ProPresenter.

Estructura de Archivos (Vistas)
• `header.php`: Contiene el selector de roles temporal para pruebas y la codificación UTF-8/Entidades HTML para evitar errores de visualización (rombos).
• `repertorio_lista.php`: Lista principal con lógica de visualización condicional según el rol.
• `index.php`: Dashboard principal con tarjetas de resumen y acceso a servicios.

Manejo de Recursos
• Los campos de recursos (Midi, ProPresenter, YouTube, PDF) funcionan actualmente como links externos hacia Google Drive para facilitar el acceso sin gestión de archivos local por ahora.

-----------------------------------------------------------------------------------------------------------------------------------------------------------------


**Versión:** 1.5.0  
**Última Actualización:** 2025-12-24 

### 🚀 Nuevas Funcionalidades (v1.5.0)
* **Gestión de Recursos Externos**: Transición total a enlaces externos (Drive, Web) para MIDI y ProPresenter.
* **Dashboard de Auditoría Interactivo**: Tarjetas de estadísticas con filtros funcionales para archivos Midi y ProPresenter.
* **Nomenclatura Actualizada**: El encabezado principal ahora es **"Repertorio"**.
* **Estandarización**: La etiqueta "track" ahora se muestra siempre como **"multitrack"**.
* **Nuevos Campos**: Integración de `midi_path` y `propresenter_path` (etiquetado como "ProPresenter file").

### 📂 Estructura del Proyecto
/
├── db_config.php       # Configuración de conexión PDO.
├── header.php          # Estilos globales y navegación.
├── repertorio_lista.php # Interfaz principal, Dashboard y Modal.
└── sql/
    └── database.sql    # Esquema con soporte para midi_path y propresenter_path.

 

-----------------------------------------------------------------------------------------------------------------------------------------------------------------
**Versión:** 1.4.0  
**Última Actualización:** 2025-12-23 

## 🚀 Funcionalidades Principales

### 1. Panel de Control (Dashboard Dinámico)
* **Métricas en Tiempo Real:** Visualización instantánea del total de canciones, multitracks y niveles de prioridad.
* **Filtros de Auditoría:** Tarjetas interactivas para identificar rápidamente qué canciones carecen de **YouTube Link** o **PDF de Letras/Cifrados**, facilitando el trabajo de completar la base de datos.
* **Integración de UI:** Interfaz basada en Tailwind CSS con un diseño moderno, tarjetas redondeadas (`2rem/3rem`) y efectos de desenfoque (`backdrop-blur`).

### 2. Gestión de Biblioteca (Tabla Avanzada)
* **Identificación Manual:** Implementación de IDs manuales editables que permiten mantener una numeración personalizada independiente del autoincremento de la base de datos.
* **Ordenamiento Multicolumna:** Capacidad de ordenar la lista por **ID, Artista, Tono o Prioridad** mediante clics en los encabezados, con lógica específica para datos numéricos y alfabéticos.
* **Filtro por Clic en Artista:** Al seleccionar el nombre de un artista en la tabla, el sistema filtra automáticamente todas sus canciones y actualiza el buscador.
* **Buscador Global:** Filtrado instantáneo por texto que procesa artista y título simultáneamente.

### 3. Recursos y Estado
* **Control de Multitracks:** Identificación visual clara de canciones que cuentan con secuencias multitrack.
* **Acceso Directo a Recursos:** Columnas con iconos directos (🎬, 📄) para previsualizar videos de YouTube o abrir archivos PDF sin salir de la lista.
* **Sistema de Prioridades:** Clasificación por niveles (High, Medium, Low) con códigos de colores para una gestión visual de la importancia de ensayo o producción.

---

## 🛠 Detalles Técnicos

### Arquitectura
* **Backend:** PHP 8.x con PDO para conexiones seguras a bases de datos MySQL.
* **Frontend:** HTML5, JavaScript (ES6+) y Tailwind CSS para el estilizado.
* **Base de Datos:** Estructura optimizada en la tabla `songs` incluyendo campos para BPM, Tono Musical y enlaces externos.

### Lógica de Filtrado (JavaScript)
El sistema utiliza una función de filtrado personalizada que interactúa con `data-attributes` en el DOM, permitiendo una experiencia de usuario fluida sin recargas de página (SPA-like feeling).

```javascript
// Ejemplo de lógica implementada para recursos faltantes
if (type === 'no-yt') row.style.display = (hasYt === '0') ? '' : 'none';