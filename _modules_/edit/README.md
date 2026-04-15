## Módulo `edit`

Este módulo proporciona una interfaz web avanzada para la edición, gestión y navegación de archivos y carpetas en el servidor, orientado a desarrolladores y administradores.

### Características principales
- **Explorador de archivos**: Visualiza y navega por el sistema de archivos del servidor.
- **Editor integrado**: Edición de archivos de texto (HTML, JS, CSS, PHP, JSON, etc.) con soporte para temas y resaltado de sintaxis (Monaco Editor).
- **Gestión de archivos**: Crear, renombrar, eliminar y descargar archivos y carpetas.
- **Soporte para AJAX**: Operaciones rápidas y seguras mediante peticiones AJAX.
- **Permisos**: Acceso restringido por ACL, solo usuarios con permiso `site_edit` pueden operar.
- **Interfaz moderna**: Uso de menús contextuales, modales y temas personalizables.

### Archivos principales
- `index.php`: Controlador principal, gestiona el acceso y carga los componentes según el contexto (AJAX, PDF, test, interfaz principal).
- `run.php`: Renderiza la interfaz principal del editor y el explorador de archivos.
- `edit_ware.class.php`: Lógica de backend para operaciones sobre archivos y carpetas (listar, leer, escribir, etc.).
- `ajax.php`: Backend para operaciones AJAX (leer, guardar, borrar archivos, etc.).
- `script.js`: Funciones JS auxiliares (nada, de momento).
- `menu.js`: Menú contextual para operaciones sobre archivos/carpeta.
- `dialog.js`: Gestión de diálogos modales para crear/renombrar archivos.
- `style.css`: Estilos avanzados para la interfaz del editor y explorador.
- `head.php` y `footer.php`: Carga de recursos, inicialización de temas y editor Monaco.
- `init.php`: Configuración inicial y definición de constantes/temas.

### Requisitos
- PHP 7.4+
- Permisos de escritura en el sistema de archivos
- Sesión iniciada y permisos adecuados (`site_edit`)


### Integración con IA (Inteligencia Artificial)
El módulo incluye integración avanzada con múltiples servicios de IA generativa para asistencia al programador y chat contextual:

- **Servicios soportados:**
	- OpenAI (GPT-4, GPT-3.5)
	- Gemini (Google)
	- Claude (Anthropic)
	- Grok (xAI)
	- DeepSeek
	- Dummy (modo prueba, sin consumo de tokens)

- **Funcionalidades IA:**
	- Sugerencias de código y autocompletado contextual en el editor (Monaco).
	- Chat integrado para consultas y generación de código o explicaciones.
	- Selección de proveedor IA desde la interfaz (persistente en localStorage).
	- Historial de chat y sugerencias almacenado por usuario/sesión.
	- Estado y feedback visual de la IA en la barra de estado.
	- Backend PHP modular: cada servicio IA implementa la interfaz `AiServiceInterface` en `_modules_/edit/ai/`.
	- Seguridad: las llamadas a IA requieren sesión activa y token válido.

---
El módulo está pensado para uso interno y administración avanzada. Personalizable mediante temas y ampliable con nuevos tipos de archivo, acciones o proveedores IA.

---
Actualizado: enero 2026