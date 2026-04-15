# ExtFW Framework 3.0

**ExtFW** es un framework PHP modular y listo para producción, diseñado para el desarrollo rápido de aplicaciones web. Basado en el principio KISS, prioriza la productividad y una arquitectura clara sobre sistemas de enrutamiento complejos.

> © 2010–2026 Nailuj2k — Todos los derechos reservados.

---

## Características principales

- **Scaffold automático** — Generación de CRUD completo a partir de definiciones de tablas
- **Autenticación múltiple** — MySQL, SQLite, LDAP, Passwordless, Nostr  y OAuth (Google)
- **Control de acceso (ACL)** — Roles y permisos por usuario, rol y elemento
- **Multi-output** — La misma lógica de módulo sirve HTML, JSON, PDF, CSV, API, SSE, etc.
- **Sistema de plugins** — Shortcodes y extensiones via `APP::$shortcodes`
- **Internacionalización (i18n)** — Soporte multiidioma (ES/EN y extensible)
- **Gestión de paquetes y versiones** — Generación de ZIPs y actualizaciones desde panel de control
- **Sistema Karma** — Gamificación integrada (puntos, valoraciones)
- **Seguridad** — HTTPS forzado, rate limiting, PHPIDS, cabeceras HTTP, sanitización de entradas

---

## Requisitos del sistema

| Componente | Versión mínima |
|---|---|
| PHP | 8.0+ |
| MySQL / MariaDB | 5.7+ / 10.2+ |
| SQLite (alternativa) | 3 |
| Apache | 2.2+ con `mod_rewrite` |
| Nginx | Equivalente a mod_rewrite |

**Extensiones PHP necesarias:** `PDO`, `pdo_mysql` o `pdo_sqlite`, `mbstring`, `json`, `session`

**Recomendadas:** `gd`, `curl`, `ldap`, `redis`, `zip`

---

## Instalación

1. Descarga `extfw_installer.zip` desde el repositorio
2. Sube `install.php` a la raíz del servidor
3. Accede a `https://tudominio.com/install.php` y sigue el asistente:
   - Nombre del sitio, email de administrador
   - Tipo y credenciales de base de datos
   - Usuario y contraseña de administrador
4. El sistema genera `configuration.php`, crea las tablas e inicializa el usuario

---

## Configuración básica

El archivo `configuration.php` centraliza toda la configuración:

```php
$cfg['prefix']         = 'pm';            // Prefijo de tablas en BD
$cfg['debug']          = false;
$cfg['timezone']       = 'Europe/Madrid';
$cfg['default_module'] = 'page';          // Módulo por defecto
$cfg['default_theme']  = 'default';
$cfg['default_lang']   = 'es';
$cfg['langs']          = ['es', 'en'];
$cfg['auth']           = 'mysql';         // mysql | sqlite | ldap | demo

$cfg['db']['type']     = 'mysql';
$cfg['db']['host']     = 'localhost';
$cfg['db']['name']     = 'database';
$cfg['db']['user']     = 'user';
$cfg['db']['pass']     = 'password';
```

**OAuth con Google:**
```php
$cfg['oauth']['google'] = [
    'id'     => 'xxx.apps.googleusercontent.com',
    'secret' => 'xxxxx',
];
```

**LDAP:**
```php
$cfg['ldap_server'] = 'ldaps://ad.empresa.com';
```

---

## Estructura del proyecto

```
/
├── index.php               # Controlador frontal (único punto de entrada)
├── configuration.php       # Configuración de BD y aplicación
├── .htaccess               # Rewrite rules, cabeceras de seguridad y caché
│
├── _classes_/              # Clases PHP del núcleo (88+)
├── _includes_/             # Bootstrap: autoloader, seguridad, auth, enrutamiento
├── _modules_/              # Módulos funcionales (mini-aplicaciones)
├── _themes_/               # Temas visuales
├── _outputs_/              # Manejadores de formato de salida
├── _plugins_/              # Plugins de extensión
├── _js_/                   # Librerías JavaScript propias
├── _lib_/                  # Librerías de terceros
├── _i18n_/                 # Traducciones
├── _images_/               # Assets del sistema
└── media/                  # Subidas de usuarios (requiere escritura)

```

---

## Enrutamiento

Las URLs se mapean automáticamente sin configuración adicional:

```
/modulo/param1/param2/clave=valor
```

| Segmento | Descripción |
|---|---|
| `$_ARGS[0]` | Nombre del módulo |
| `$_ARGS[1]`, `[2]`... | Parámetros secuenciales |
| `$_ARGS['clave']` | Parámetros con nombre |
| `/theme/nombre` | Cambio de tema |
| Cualquier segmento igual a un código de idioma válido (`es`, `en`, etc.) | Cambio de idioma |
| Cualquier segmento igual a un output válido (`json`, `pdf`, `raw`, etc.) | Cambio de formato de salida |

---

## Scaffold — CRUD automático

Define una tabla y el framework genera el CRUD completo:

```php
// _modules_/productos/TABLE_PREFIX_PRODUCTOS.php
$tabla = new TableMysql('PREFIX_PRODUCTOS');

$id = new Field();
$id->pk = true;
$id->tyoe='int';
$id->editable = false;
$tabla->addCol($id);

$nombre = new Field();
$nombre->fieldanem ='nombre'
$nombre->type ='varchar';
$nombre->len = 100;
$nombre->searchable = true;
$tabla->addCol($nombre);

$tabla->perms = [
    'list'   => true,
    'add'    => $_ACL->userHasRoleName('Oper') ,
    'edit'   => $_ACL->userHasRoleName('Oper'),
    'delete' => $_ACL->userHasRoleName('Admin'),
];
```

```php
// _modules_/productos/run.php
Table::init();
include __DIR__.'/TABLE_PREFIX_PRODUCTOS.php';
Table::show_table('PREFIX_PRODUCTOS');
```

```php
// _modules_/productos/ajax.php
include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');
```

---

## Módulos desarrollados

| Módulo | Descripción |
|---|---|
| `page` | Páginas y blog con edición inline |
| `login` | Autenticación, registro, OAuth |
| `control_panel` | Panel de administración y gestión de versiones |
| `acl` | Gestión de roles y permisos |
| `comments` | Comentarios anidados con moderación y karma |
| `news` | Noticias y artículos con categorías |
| `blog` | Blog, con comentarios,rating, karttma, etc|
| `newsletter` | Campañas de email |
| `shop` | E-commerce con integración Redsys |
| `erp` | erp + gestor de tareas tipo kanban |
| `qr` | Generación y gestión de códigos QR |
| `dbadmin` | Administración directa de base de datos |
| `wallet` | Monedero de pagos / criptomonedas |
| `timextamping` | Sellado de tiempo de documentos |
| `alerts` | Sistema de notificaciones |
| `codepen` | Editor de código / playground |
| `pads` | Pads colaborativos |
| `marketplace` | Distribución de módulos y temas |
| `contact` | Formularios de contacto |
| `mapa-web` | Generación de sitemaps |

---

## Formatos de salida

El mismo módulo puede servir en múltiples formatos. No hace falta usar `/output/{tipo}`: basta con que la URL contenga cualquier segmento que coincida con un output válido.

`html` · `json` · `pdf` · `csv` · `ajax` · `api` · `sse` · `txt` · `raw` · `file` · `qrcode`

---

## Plugins y hooks

```php
// _plugins_/miplugin/config.json
{
    "name": "Mi Plugin",
    "version": "1.0",
    "enabled": true
}

// _plugins_/miplugin/main.php

// Shortcodes: contenido dinámico en páginas
APP::$shortcodes->add_shortcode('year', function () {
    return date('Y');
});

// Hooks: reaccionar a eventos del ciclo de vida
Hook::add_action('before_output', function () {
    // se ejecuta justo antes de renderizar el theme
});

Hook::add_filter('page_title', function ($title) {
    return $title . ' · Mi Sitio';   // modifica el título de página
});
```

Prefija el directorio del plugin con `_` para deshabilitarlo sin eliminarlo.

---

## Seguridad

- HTTPS forzado vía `.htaccess`
- Rate limiting con backend MySQL o Redis
- Integración opcional con PHPIDS
- Cabeceras HTTP de seguridad (`X-Frame-Options`, `CSP`, cookies `HttpOnly`/`Secure`)
- Bloqueo de acceso a archivos `.bak`, `.sql`, `.env`, `.tpl`
- Sanitización de entradas en todas las capas

---

## Gestión de versiones y actualizaciones

Desde el **Panel de Control > ZIP** se generan paquetes:

| Paquete | Contenido |
|---|---|
| `extfw_base.zip` | Release completa (incrementa versión) |
| `extfw_update.zip` | Solo archivos actualizados |
| `extfw_installer.zip` | Instalador independiente |
| `extfw_{modulo}.zip` | Módulo individual |
| `extfw_{tema}.zip` | Tema individual |
| `extfw_vendor.X.Y.zip` | Dependencias por versión de PHP |

Las instalaciones cliente pueden actualizarse desde **Panel de Control > Actualizaciones**, descargando e instalando el paquete automáticamente.


---

## Librerías incluidas

**Frontend:** Chart.js · Cropper.js · Dropzone · Prism · QRCode

**Backend:** SimpleHTMLDom · Redisent (Redis) · PHPIDS · Redsys (pagos) · EPUB reader

**JS propios:** wquery (sustituto ligero de jQuery, compatible en lo esencial) · WYSIWYG editor · Enhanced Select · Image Editor · SSE client · Crypto.js · Passwordless.js

---

## Casos de uso

- Portales CMS y blogs corporativos
- Tiendas online con pasarela de pago
- Aplicaciones de gestión con LDAP y ACL
- Plataformas de comunidad con karma y comentarios
- Paneles de administración con CRUD generado automáticamente
- Backends API con soporte JSON y Server-Sent Events
- Proyectos blockchain / criptomonedas (wallet, timestamping, nostr)


