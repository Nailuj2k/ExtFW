<?php
// Documentación del módulo ACL (Áreas, Aplicaciones, Grupos, Permisos)
// Basado en README y areas_acl.class.php v2.1
?>

<div class="container">
  <h1>ACL por Áreas — Documentación</h1>
  <style>
    .copy-btn{display:inline-block;margin:4px 0 8px 0;padding:4px 8px;font-size:12px;color:#fff;background:#1976D2;border:none;border-radius:4px;cursor:pointer}
    .copy-btn:active{opacity:.85}
    pre{position:relative}
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      function copyText(text, btn){
        function done(){ if(btn){ btn.textContent='Copiado'; setTimeout(()=>btn.textContent='Copiar',1200);} }
        if (navigator.clipboard && navigator.clipboard.writeText){
          navigator.clipboard.writeText(text).then(done).catch(fallback);
        } else { fallback(); }
        function fallback(){
          const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select();
          try{ document.execCommand('copy'); }catch(e){}
          document.body.removeChild(ta); done();
        }
      }
      document.querySelectorAll('.copy-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
          const id=this.getAttribute('data-target'); const el=document.getElementById(id);
          if(!el) return; copyText(el.innerText, this);
        });
      });
    });
  </script>

  <div class="section">
    <h2>Resumen</h2>
    <p>
      Este módulo implementa un sistema de control de acceso basado en Áreas, Aplicaciones, Grupos y Permisos.
      La clase <b>AreasACL</b> centraliza las consultas de verificación de pertenencia, administración y permisos.
      La interfaz del módulo permite gestionar aplicaciones, permisos, áreas, grupos y la asignación de usuarios.
    </p>
    <p>
      Accesos rápidos: <a href="acl/doc">acl/doc</a> (esta página) · <a href="acl/test">acl/test</a> (test interactivo)
    </p>
  </div>

  <div class="section">
    <h2>Conceptos</h2>
    <ul>
      <li><b>Áreas</b>: dominios organizativos. Cada usuario puede pertenecer a cero, una o varias áreas. Algunos usuarios pueden ser <i>admin</i> del área.</li>
      <li><b>Aplicaciones</b>: cada app define sus propios permisos funcionales (crear, ver, eliminar, etc.).</li>
      <li><b>Grupos</b>: agrupaciones de usuarios dentro de un área (p. ej. backend, frontend…).</li>
      <li><b>Permisos</b>: acciones disponibles en cada aplicación. Se asignan en el contexto Área→App a Grupos y (opcionalmente) a Usuarios.</li>
    </ul>
    <p><b>Roles globales</b>: el acceso al módulo se otorga via roles <code>Area_Admin</code> y <code>Area_User</code> (ver <code>_modules_/acl/index.php</code>).</p>
  </div>

  <div class="section">
    <h2>Esquema de datos</h2>
    <p>Tablas principales (nomenclatura de README):</p>
<pre><code>Aplicaciones y Permisos
CFG_APPS              CFG_APPS_PERMS
ID                    ID
NAME                  ID_APP
APPKEY                NAME
                      PERMKEY
                      DESCRIPTION
                      BYDEFAULT

Áreas y Usuarios por Área
CFG_AREAS             CFG_AREAS_USERS
ID                    ID
NAME                  ID_AREA
AREAKEY               ID_USER   -&gt; CLI_USER.user_id
ID_ROLE               ADMIN

Grupos por Área       Usuarios por Grupo y Área
CFG_AREAS_GROUPS      CFG_AREAS_GROUPS_USERS
ID                    ID
ID_AREA               ID_AREA_GROUP
NAME                  ID_USER
GROUPKEY

Apps por Área         Permisos por App y Área
CFG_AREAS_APPS        CFG_AREAS_APPS_PERMS
ID                    ID
ID_AREA               ID_AREA_APP
ID_APP                ID_APP_PERM
                      BYDEFAULT

Grupos (Área+App)     Permisos Grupo (Área+App)
CFG_AREAS_APPS_GROUPS CFG_AREAS_APPS_GROUPS_PERMS
ID                    ID
ID_AREA_APP           ID_AREA_APP_GROUP
ID_GROUP              ID_AREA_APP_PERM</code></pre>
    <p>
      Usuarios y roles globales se gestionan fuera del módulo: <code>CLI_USER</code>, <code>ACL_ROLES</code>, <code>ACL_USER_ROLES</code>.
    </p>
  </div>

  <div class="section">
    <h2>Flujo de permisos</h2>
    <ul>
      <li><b>Visibilidad de Apps</b>: un Área habilita Apps en <code>CFG_AREAS_APPS</code>.</li>
      <li><b>Catálogo de permisos</b>: cada App define sus permisos en <code>CFG_APPS_PERMS</code>.</li>
      <li><b>Contexto Área→App</b>: se referencian permisos de app via <code>CFG_AREAS_APPS_PERMS</code>.</li>
      <li><b>Asignación</b>: permisos de ese contexto se asignan a Grupos (<code>CFG_AREAS_APPS_GROUPS_PERMS</code>).</li>
      <li><b>Administradores de Área</b>: los usuarios con <code>AU.ADMIN=1</code> poseen todos los permisos de la App en su Área <b>si la App está habilitada en el Área</b>.</li>
    </ul>
  </div>

  <div class="section">
    <h2>Autogestión por Área</h2>
    <ul>
      <li><b>Delegación</b>: cada Área tiene administradores que crean grupos, asignan usuarios y ajustan permisos de sus Apps.</li>
      <li><b>Menos carga central</b>: el equipo de informática/administración se libera de tareas de alta/edición diaria.</li>
      <li><b>Flexibilidad</b>: cada Área adapta grupos y permisos a su operativa sin afectar a otras.</li>
      <li><b>Simplicidad</b>: sin permisos directos a usuario; todo por grupos y por defecto (BYDEFAULT) cuando aplique.</li>
      <li><b>Control</b>: el acceso al módulo se limita por roles globales, y cada Área gestiona su ámbito.</li>
    </ul>
  </div>

  <div class="section">
    <h2>Clase AreasACL (v2.1)</h2>
    <p>Archivo: <code>_modules_/acl/areas_acl.class.php</code>. Métodos principales:</p>
    <ul>
      <li><code>hasPermissionByArea($areaKey, $appKey, $permKey, $userId=null)</code> — valida si el usuario tiene el permiso en el contexto Área→App.</li>
      <li><code>hasPermission($appKey, $permKey, $userId=null)</code> — variante simple, válida si lo tiene en cualquiera de sus áreas.</li>
      <li><code>userInArea($areaKey, $userId=null): bool</code> — comprueba pertenencia del usuario a un Área.</li>
      <li><code>isAreaAdmin($areaKey, $userId=null): bool</code> — indica si el usuario es admin del Área.</li>
      <li><code>getUserAreas($userId=null): array</code> — devuelve áreas del usuario (incluye flag ADMIN).</li>
      <li><code>getAreaApps($areaKey): array</code> — apps disponibles en un Área.</li>
      <li><code>getAreaUsers($areaKey): array</code> — usuarios directos del Área.</li>
      <li><code>getUserPermissions($areaKey, $appKey, $userId=null): array</code> — permisos efectivos (si es admin: todos; si no: derivados de grupos).</li>
      <li><code>getUsersWithPermission($areaKey, $appKey, $permKey): array</code> — usuarios con un permiso concreto (admin, grupos y por defecto) deduplicados por prioridad.</li>
      <li><code>getAreaGroups($areaKey): array</code> — grupos del Área.</li>
      <li><code>getAreaAdmins($areaKey): array</code> — administradores del Área.</li>
      <li><code>hasPermissionAnyArea($appKey, $permKey, $userId=null)</code> — true si el usuario lo tiene en al menos un Área.</li>
      <li><code>getAreasWithPermission($appKey, $permKey, $userId=null)</code> — lista de AREAKEY donde el usuario lo tiene.</li>
      <li><code>isAppEnabledInArea($areaKey, $appKey)</code> — true si la App está habilitada en el Área.</li>
      <li><code>getAppPermissionsCatalog($appKey)</code> — catálogo de permisos definidos por la aplicación.</li>
      <li><code>addApp($name, $appKey=null, $active=1)</code> — crea/actualiza una App en CFG_APPS (idempotente).</li>
      <li><code>addAppPermission($appKey, $name, $permKey=null, $description='', $byDefault=0, $active=1)</code> — crea/actualiza un permiso (idempotente).</li>
      <li><code>ensureAreaApp($areaKey, $appKey, $active=1)</code> — habilita una App en un Área (idempotente).</li>
      <li><code>ensureArea($areaKey, $name, $active=1)</code> — crea/actualiza un Área (idempotente).</li>
      <li><code>validateSqlIdentifier($string): string</code> — valida identificadores (a-z, A-Z, 0-9, _; 1–50 chars).</li>
    </ul>

    <h3>Requisitos de sesión</h3>
    <p>
      Para los métodos con <code>$userId = null</code>, la sesión debe incluir <code>$_SESSION['valid_user'] === true</code> y <code>$_SESSION['userid']</code> numérico &gt; 0.
    </p>

    <h3>Ejemplos de uso (PHP)</h3>
<button type="button" class="copy-btn" data-target="doc-snip-area">Copiar</button>
<pre><code id="doc-snip-area">// Comprobar permiso en la acción de un controlador
include_once SCRIPT_DIR_MODULE . '/areas_acl.class.php';

$area = 'fac';        // AREAKEY
$app  = 'dokument';   // APPKEY
$perm = 'create_task';// PERMKEY definido en CFG_APPS_PERMS

if (!AreasACL::userInArea($area)) {
    http_response_code(403);
    exit('No pertenece al área');
}

if (!AreasACL::hasPermissionByArea($area, $app, $perm)) {
    http_response_code(403);
    exit('Permiso denegado');
}

// ... ejecutar acción protegida
</code></pre>

<button type="button" class="copy-btn" data-target="doc-snip-listperms">Copiar</button>
<pre><code id="doc-snip-listperms">// Listar permisos efectivos del usuario logado en un contexto
$perms = AreasACL::getUserPermissions('fac', 'dokument');
foreach ($perms as $p) {
    echo $p['PERMKEY'], ' (', $p['SOURCE'], ")\n"; // SOURCE: area_admin | group_derived | default
}
</code></pre>

<button type="button" class="copy-btn" data-target="doc-snip-any">Copiar</button>
<pre><code id="doc-snip-any">// Comprobación área-agnóstica (módulos que no manejan área)
if (!AreasACL::hasPermission('dokument', 'view')) {
    http_response_code(403);
    exit('Acceso denegado');
}
</code></pre>

<button type="button" class="copy-btn" data-target="doc-snip-areas">Copiar</button>
<pre><code id="doc-snip-areas">// Áreas donde el usuario tiene permiso para una app (útil para selector de contexto)
$areasOK = AreasACL::getAreasWithPermission('dokument', 'edit');
</code></pre>

<button type="button" class="copy-btn" data-target="doc-snip-admins">Copiar</button>
<pre><code id="doc-snip-admins">// Administración: verificar administradores de un área
$admins = AreasACL::getAreaAdmins('fac');
</code></pre>

<button type="button" class="copy-btn" data-target="doc-snip-install">Copiar</button>
<pre><code id="doc-snip-install">// Instalación de una app y sus permisos (idempotente)
$appId = AreasACL::addApp('Tareas', 'tasks');
AreasACL::addAppPermission('tasks', 'Crear', 'create');
AreasACL::addAppPermission('tasks', 'Ver', 'view', '', 1);
AreasACL::addAppPermission('tasks', 'Cerrar', 'close');
// Habilitar la App 'tasks' en el área 'informatica'
AreasACL::ensureAreaApp('informatica', 'tasks', 1);
// Crear/asegurar el área 'informatica'
AreasACL::ensureArea('informatica', 'Informática', 1);
</code></pre>
  </div>

  <div class="section">
    <h2>Interfaz del módulo</h2>
    <ul>
      <li><code>acl/</code> — vista principal (tabs de Aplicaciones/Permisos y Áreas).</li>
      <li><code>acl/doc</code> — esta documentación.</li>
      <li><code>acl/test</code> — test interactivo de <code>AreasACL</code> con selectores dinámicos.</li>
    </ul>
    <p>
      La UI usa el motor <code>Table::show_table()</code> para CRUD de las tablas <code>CFG_*</code> y organiza la gestión en pestañas.
      Los usuarios con rol <code>Area_Admin</code> pueden definir aplicaciones y sus permisos; con <code>Area_User</code> gestionan usuarios/grupos/permisos dentro de sus áreas.
    </p>
  </div>

  <div class="section">
    <h2>Buenas prácticas</h2>
    <ul>
      <li><b>Identificadores seguros</b>: valide claves con <code>validateSqlIdentifier()</code> al aceptar input.</li>
      <li><b>Consultas preparadas</b>: use <code>Table::sqlQueryPrepared()</code> para parámetros.</li>
      <li><b>Permisos por defecto</b>: el campo <code>BYDEFAULT</code> existe en varias tablas. Defina su semántica en negocio (p. ej. permisos iniciales al activar un Área→App).</li>
      <li><b>Cobertura de paths</b>: proteja rutas/acciones backend usando <code>hasPermission()</code> y no solo checks de UI.</li>
    </ul>
  </div>

  <div class="section">
    <h2>Decisiones</h2>
    <ul>
      <li><b>Sin permisos directos a usuario</b>: se eliminan las tablas <code>CFG_AREAS_APPS_USERS</code> y <code>CFG_AREAS_APPS_USERS_PERMS</code>. Todo acceso se modela vía grupos.</li>
      <li><b>BYDEFAULT sencillo</b>: si un permiso está marcado como <code>BYDEFAULT=1</code> en <code>CFG_AREAS_APPS_PERMS</code> y el usuario pertenece al Área, lo tiene por defecto (fuente: <code>default</code>).</li>
      <li><b>Precedencia</b>: <code>admin</code> &gt; <code>group_derived</code> &gt; <code>default</code>.</li>
      <li><b>Roles de acceso parametrizables</b>: <code>ACL_ACCESS_ROLE_ADMIN</code> y <code>ACL_ACCESS_ROLE_USER</code> por defecto apuntan a <code>Area_Admin</code> y <code>Area_User</code>.</li>
    </ul>
  </div>

  <div class="summary">
    <p>
      Módulo ACL por Áreas listo para uso: gestione Apps, Permisos, Áreas y Grupos desde la UI, y aplique guardas con <code>AreasACL</code> en backend.
      Use <a href="acl/test">acl/test</a> para validar combinaciones de Área-Usuario-App-Permiso en su entorno.
    </p>
  </div>
</div>
