# 🎵 Sistema de Gestión de Repertorio Musical

Este proyecto es un panel administrativo avanzado diseñado para gestionar una biblioteca de canciones, enfocándose en la velocidad de búsqueda, organización por prioridades y control de recursos multimedia.

**Versión:** 1.4.0  
**Última Actualización:** 2025-12-23  

---

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