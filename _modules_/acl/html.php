<style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; color: #222; margin: 2em; }
    h1, h2 { color: #2c3e50; }
    code, pre { background: #f4f4f4; border-radius: 4px; padding: 2px 6px; }
    .block { background: #f4f4f4; border-left: 4px solid #3498db; padding: 1em; margin: 1em 0; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 2em; }
    th, td { border: 1px solid #ccc; padding: 6px 10px; }
    th { background: #e9ecef; }
    ul { margin-top: 0; }
  </style>
    <h1>Cheatsheet: Uso de <code>AreasACL</code></h1>
  <p><b>AreasACL</b> es la clase principal para gestionar permisos de usuarios, grupos, áreas y aplicaciones en el framework ExtFW.</p>

  <h2>Principales métodos de uso</h2>
  <table>
    <tr><th>Método</th><th>Descripción</th><th>Ejemplo</th></tr>
    <tr>
      <td><code>hasPermission($app, $perm, $userId = null)</code></td>
      <td>¿El usuario tiene el permiso en <b>alguna</b> de sus áreas?</td>
      <td><code>AreasACL::hasPermission('ventas', 'ver', 5)</code></td>
    </tr>
    <tr>
      <td><code>hasPermissionByArea($area, $app, $perm, $userId = null)</code></td>
      <td>¿El usuario tiene el permiso en un área concreta?</td>
      <td><code>AreasACL::hasPermissionByArea('norte', 'ventas', 'ver', 5)</code></td>
    </tr>
    <tr>
      <td><code>hasPermissionAnyArea($app, $perm, $userId = null)</code></td>
      <td>¿El usuario tiene el permiso en <b>cualquier</b> área?</td>
      <td><code>AreasACL::hasPermissionAnyArea('ventas', 'ver', 5)</code></td>
    </tr>
    <tr>
      <td><code>getAreasWithPermission($app, $perm, $userId = null)</code></td>
      <td>Devuelve las <code>AREAKEY</code> donde el usuario tiene el permiso.</td>
      <td><code>AreasACL::getAreasWithPermission('ventas', 'ver', 5)</code></td>
    </tr>
    <tr>
      <td><code>getUserAreas($userId = null)</code></td>
      <td>Áreas a las que pertenece el usuario.</td>
      <td><code>AreasACL::getUserAreas(5)</code></td>
    </tr>
    <tr>
      <td><code>getAreaApps($area)</code></td>
      <td>Apps disponibles en un área.</td>
      <td><code>AreasACL::getAreaApps('norte')</code></td>
    </tr>
    <tr>
      <td><code>getUserPermissions($area, $app, $userId = null)</code></td>
      <td>Permisos del usuario en un área/app.</td>
      <td><code>AreasACL::getUserPermissions('norte', 'ventas', 5)</code></td>
    </tr>
    <tr>
      <td><code>getUsersWithPermission($area, $app, $perm)</code></td>
      <td>Usuarios con un permiso en área/app.</td>
      <td><code>AreasACL::getUsersWithPermission('norte', 'ventas', 'ver')</code></td>
    </tr>
    <tr>
      <td><code>getAreaGroups($area)</code></td>
      <td>Grupos de un área.</td>
      <td><code>AreasACL::getAreaGroups('norte')</code></td>
    </tr>
    <tr>
      <td><code>getAreaAdmins($area)</code></td>
      <td>Admins de un área.</td>
      <td><code>AreasACL::getAreaAdmins('norte')</code></td>
    </tr>
    <tr>
      <td><code>getAreaGroupUsers($area, $group)</code></td>
      <td>Usuarios de un grupo en un área.</td>
      <td><code>AreasACL::getAreaGroupUsers('norte', 'supervisores')</code></td>
    </tr>
  </table>

  <h2>Notas rápidas</h2>
  <ul>
    <li>Los métodos aceptan <code>$userId = null</code> para usar el usuario de la sesión actual.</li>
    <li>La precedencia de permisos es: <b>admin área</b> &gt; <b>grupo</b> &gt; <b>por defecto</b>.</li>
    <li>Los identificadores (<code>AREAKEY</code>, <code>APPKEY</code>, <code>PERMKEY</code>) deben ser alfanuméricos y guion bajo.</li>
    <li>Para crear apps, áreas o permisos, usar: <code>addApp()</code>, <code>addAppPermission()</code>, <code>ensureArea()</code>, <code>ensureAreaApp()</code>.</li>
    <li>Para validar si una app está habilitada en un área: <code>isAppEnabledInArea($area, $app)</code>.</li>
  </ul>

  <h2>Ejemplo de uso</h2>
  <div class="block">
    <pre><code>// Verificar si el usuario actual puede "editar" en la app "ventas" en el área "norte"
if (AreasACL::hasPermissionByArea('norte', 'ventas', 'editar')) {
    // Mostrar botón de edición
}

// Listar permisos del usuario 5 en el área "norte" para la app "ventas"
$permisos = AreasACL::getUserPermissions('norte', 'ventas', 5);
foreach ($permisos as $perm) {
    echo $perm['PERMKEY'] . ': ' . $perm['PERMNAME'] . "<br>";
}
</code></pre>
  </div>

  <h2>Fuentes de permisos</h2>
  <ul>
    <li><b>area_admin</b>: El usuario es admin del área (tiene todos los permisos de la app en esa área).</li>
    <li><b>group_derived</b>: El permiso se concede por pertenencia a grupos del área.</li>
    <li><b>default</b>: El permiso está marcado como <code>BYDEFAULT=1</code> y el usuario pertenece al área.</li>
  </ul>

  <h2>Utilidades de instalación</h2>
  <ul>
    <li><code>addApp($name, $appKey = null, $active = 1)</code>: Crea o actualiza una app.</li>
    <li><code>addAppPermission($appKey, $name, $permKey = null, $desc = '', $byDefault = 0, $active = 1)</code>: Crea o actualiza un permiso.</li>
    <li><code>ensureArea($areaKey, $name, $active = 1)</code>: Crea o actualiza un área.</li>
    <li><code>ensureAreaApp($areaKey, $appKey, $active = 1)</code>: Habilita una app en un área.</li>
  </ul>

  <h2>Validaciones</h2>
  <ul>
    <li>Los métodos lanzan <code>InvalidArgumentException</code> si los identificadores no son válidos.</li>
    <li>Las operaciones usan consultas preparadas para seguridad SQL.</li>
  </ul>

  <footer style="margin-top:2em; color:#888; font-size:0.9em;">ExtFW Framework &copy; 2026</footer>

  <h2 style="margin-top:2em;">Caso concreto: cómo migrar de <code>$_ACL</code> a <code>AreasACL</code> (ejemplo: módulo <b>hulamm_ware</b>)</h2>
  <p>Si tu módulo usa la clase <code>$_ACL</code> (ACL clásico por roles), puedes migrar a <code>AreasACL</code> para aprovechar la gestión avanzada de áreas, apps y permisos. Aquí tienes una guía de equivalencias y pasos:</p>

  <h3>Correspondencia de métodos</h3>
  <table>
    <tr><th>ACL clásico (<code>$_ACL</code>)</th><th>Nuevo (<code>AreasACL</code>)</th><th>Notas</th></tr>
    <tr>
      <td><code>$_ACL-&gt;hasPermission('perm')</code></td>
      <td><code>AreasACL::hasPermission('app', 'perm')</code></td>
      <td>Debes indicar la <b>app</b> (ej: 'hulamm_ware') y el <b>permiso</b> (ej: 'doku_view').</td>
    </tr>
    <tr>
      <td><code>$_ACL-&gt;userHasRoleName('Rol')</code></td>
      <td><code>AreasACL::userInAreaGroup('area', 'group')</code> o <br><code>AreasACL::isAreaAdmin('area')</code></td>
      <td>Los roles se gestionan como <b>grupos</b> o <b>admin de área</b>.</td>
    </tr>
    <tr>
      <td><code>$_ACL-&gt;getUsersWithPermissionName('perm')</code></td>
      <td><code>AreasACL::getUsersWithPermission('area', 'app', 'perm')</code></td>
      <td>Debes indicar el área y la app.</td>
    </tr>
    <tr>
      <td><code>$_ACL-&gt;addRole('Rol')</code></td>
      <td><code>AreasACL::getAreaGroups('area')</code> + <br>alta en tabla CFG_AREAS_GROUPS</td>
      <td>Los roles se migran a grupos de área.</td>
    </tr>
    <tr>
      <td><code>$_ACL-&gt;addPermission('perm')</code></td>
      <td><code>AreasACL::addAppPermission('app', 'nombre', 'perm')</code></td>
      <td>Permisos asociados a una app.</td>
    </tr>
    <tr>
      <td><code>$_ACL-&gt;addRolePerm('Rol','perm')</code></td>
      <td>Asignar permiso a grupo vía <br><code>CFG_AREAS_APPS_GROUPS_PERMS</code></td>
      <td>Ver utilidades de setup de AreasACL.</td>
    </tr>
  </table>

  <h3>Ejemplo de migración (fragmento)</h3>
  <div class="block">
    <pre><code>// Antes (ACL clásico)
if ($_ACL->userHasRoleName('Administradores')) {
    $_ACL->addRole('HulammWare_Archivo');
    $_ACL->updateUserRole(1,'HulammWare_Archivo',true);
    $_ACL->addPermission('doku_view');
    $_ACL->addRolePerm('Administradores','doku_view');
}

// Después (AreasACL)
if (AreasACL::isAreaAdmin('area_admin')) { // o userInAreaGroup('area','Administradores')
    AreasACL::addApp('HulammWare Archivo', 'hulamm_ware_archivo');
    AreasACL::addAppPermission('hulamm_ware_archivo', 'Ver documento', 'doku_view');
    // Asignar grupo a usuario y permiso a grupo: ver utilidades de setup
}
    </code></pre>
  </div>

  <h3>Recomendaciones</h3>
  <ul>
    <li>Define <b>áreas</b> y <b>apps</b> para tu módulo antes de migrar permisos.</li>
    <li>Los <b>roles</b> clásicos se migran a <b>grupos de área</b> o a admins de área.</li>
    <li>Los permisos deben estar asociados a una app y área.</li>
    <li>Consulta los métodos de <b>setup</b> de AreasACL para crear apps, áreas, permisos y asociaciones.</li>
    <li>Revisa la lógica de comprobación de permisos en tu módulo y reemplaza llamadas a <code>$_ACL</code> por las equivalentes de <code>AreasACL</code>.</li>
  </ul>

  <p>Para dudas o ejemplos más avanzados, consulta la documentación interna o contacta con el equipo ExtFW.</p>

  <h2 style="margin-top:2em;">Ejemplo real: migración de permisos de <b>hulamm_ware</b> a AreasACL</h2>
  <p>En el sistema, la aplicación <b>HulammWare</b> (<code>hulamm_ware</code>) tiene definidos estos permisos:</p>
  <ul>
    <li><b>Añadir</b> (<code>doku_add</code>): Puede añadir documentos, pasar OCR y mover a procesados.</li>
    <li><b>Administrar</b> (<code>doku_admin</code>): Puede modificar y eliminar archivos procesados.</li>
  </ul>
  <p>Las áreas existentes son: <b>Archivo</b> (<code>archivo</code>), <b>Informática</b> (<code>informatica</code>), <b>Mantenimiento</b> (<code>mante</code>), cada una con sus usuarios y grupos.</p>

  <h3>Pasos de migración recomendados</h3>
  <ol>
    <li><b>Verifica que la app y permisos existen en AreasACL:</b>
      <ul>
        <li>App: <code>hulamm_ware</code></li>
        <li>Permisos: <code>doku_add</code>, <code>doku_admin</code></li>
      </ul>
      <pre><code>AreasACL::addApp('HulammWare', 'hulamm_ware');
AreasACL::addAppPermission('hulamm_ware', 'Añadir', 'doku_add', 'Puede añadir documentos...');
AreasACL::addAppPermission('hulamm_ware', 'Administrar', 'doku_admin', 'Puede modificar/eliminar archivos...');
</code></pre>
    </li>
    <li><b>Asocia la app a cada área relevante:</b>
      <pre><code>AreasACL::ensureAreaApp('archivo', 'hulamm_ware');
AreasACL::ensureAreaApp('informatica', 'hulamm_ware');
AreasACL::ensureAreaApp('mante', 'hulamm_ware');
</code></pre>
    </li>
    <li><b>Asigna permisos a grupos/usuarios según la lógica de tu módulo:</b>
      <ul>
        <li>Para dar <code>doku_add</code> a un grupo de un área:<br>
        (asignar en CFG_AREAS_APPS_GROUPS_PERMS el permiso <code>doku_add</code> al grupo deseado de esa área)</li>
        <li>Para dar <code>doku_admin</code> a los administradores del área, basta con que sean admin en esa área.</li>
      </ul>
    </li>
    <li><b>Revisa el código:</b>
      <ul>
        <li>Reemplaza <code>$_ACL->hasPermission('doku_add')</code> por <br><code>AreasACL::hasPermission('hulamm_ware','doku_add')</code></li>
        <li>Reemplaza <code>$_ACL->hasPermission('doku_admin')</code> por <br><code>AreasACL::hasPermission('hulamm_ware','doku_admin')</code></li>
        <li>El permiso <b>doku_view</b> no existe: elimina o ignora comprobaciones a ese permiso.</li>
      </ul>
    </li>
    <li><b>Comprueba la pertenencia a grupos o admin de área</b> si tu lógica dependía de roles:</li>
      <ul>
        <li>Para saber si un usuario es admin de un área:<br>
        <code>AreasACL::isAreaAdmin('archivo')</code></li>
        <li>Para saber si pertenece a un grupo:<br>
        <code>AreasACL::userInAreaGroup('archivo', 'nombre_grupo')</code></li>
      </ul>
  </ol>

  <h3>Notas específicas</h3>
  <ul>
    <li>Solo migra permisos realmente definidos en AreasACL (<code>doku_add</code>, <code>doku_admin</code>).</li>
    <li>Si encuentras comprobaciones de <code>doku_view</code> u otros permisos inexistentes, revisa la lógica y elimina o adapta según los permisos válidos.</li>
    <li>La gestión de usuarios y grupos por área permite granularidad superior al modelo clásico de roles.</li>
  </ul>

  <h2 style="margin-top:2em;">Listado de ocurrencias de <code>$_ACL</code> en hulamm_ware y propuesta de migración</h2>
  <table>
    <tr><th>Archivo</th><th>Línea</th><th>Fragmento</th><th>Cambio propuesto</th></tr>
    <tr><td>init.php</td><td>7</td><td>// $_ACL->getRoleUsers($role_name);</td><td>Usar <code>AreasACL::getAreaGroupUsers('area','grupo')</code> o <code>AreasACL::getAreaAdmins('area')</code></td></tr>
    <tr><td>init.php</td><td>12</td><td>$_ACL->hasPermission('doku_view')</td><td><b>No migrar</b>: doku_view no existe en AreasACL. Eliminar o adaptar lógica.</td></tr>
    <tr><td>init.php</td><td>13</td><td>$_ACL->hasPermission('doku_add')</td><td>Usar <code>AreasACL::hasPermission('hulamm_ware','doku_add')</code></td></tr>
    <tr><td>init.php</td><td>14</td><td>$_ACL->hasPermission('doku_admin')</td><td>Usar <code>AreasACL::hasPermission('hulamm_ware','doku_admin')</code></td></tr>
    <tr><td>init.php</td><td>24</td><td>$_ACL->userHasRoleName('HulammWare')</td><td>Usar <code>AreasACL::userInAreaGroup('area','grupo')</code> o <code>AreasACL::isAreaAdmin('area')</code></td></tr>
    <tr><td>ajax.php</td><td>525</td><td>$_ACL->getUsersWithPermissionName('doku_view')</td><td><b>No migrar</b>: doku_view no existe en AreasACL. Eliminar o adaptar lógica.</td></tr>
    <tr><td>ajax.php</td><td>526</td><td>$_ACL->getUsersWithPermissionName('doku_add')</td><td>Usar <code>AreasACL::getUsersWithPermission('area','hulamm_ware','doku_add')</code></td></tr>
    <tr><td>ajax.php</td><td>527</td><td>$_ACL->getUsersWithPermissionName('doku_admin')</td><td>Usar <code>AreasACL::getUsersWithPermission('area','hulamm_ware','doku_admin')</code></td></tr>
    <tr><td>ajax.php</td><td>553</td><td>$_ACL->getUserId(...)</td><td>Implementar función equivalente si es necesario</td></tr>
    <tr><td>ajax.php</td><td>555</td><td>$_ACL->addUserRole(...)</td><td>Asignar usuario a grupo de área según AreasACL</td></tr>
    <tr><td>after_init.php</td><td>7</td><td>// $_ACL->getRoleUsers($role_name);</td><td>Usar <code>AreasACL::getAreaGroupUsers('area','grupo')</code> o <code>AreasACL::getAreaAdmins('area')</code></td></tr>
    <tr><td>after_init.php</td><td>13</td><td>$_ACL->hasPermission('doku_view')</td><td><b>No migrar</b>: doku_view no existe en AreasACL. Eliminar o adaptar lógica.</td></tr>
    <tr><td>after_init.php</td><td>14</td><td>$_ACL->hasPermission('doku_add')</td><td>Usar <code>AreasACL::hasPermission('hulamm_ware','doku_add')</code></td></tr>
    <tr><td>after_init.php</td><td>15</td><td>$_ACL->hasPermission('doku_admin')</td><td>Usar <code>AreasACL::hasPermission('hulamm_ware','doku_admin')</code></td></tr>
    <tr><td>after_init.php</td><td>16</td><td>$_ACL->hasPermission('doku_download')</td><td><b>No migrar</b>: doku_download no existe en AreasACL. Eliminar o adaptar lógica.</td></tr>
    <tr><td>after_init.php</td><td>26</td><td>$_ACL->userHasRoleName('HulammWare')</td><td>Usar <code>AreasACL::userInAreaGroup('area','grupo')</code> o <code>AreasACL::isAreaAdmin('area')</code></td></tr>
    <tr><td>INSTALL.php</td><td>3</td><td>$_ACL->userHasRoleName('Administradores')</td><td>Usar <code>AreasACL::isAreaAdmin('area')</code> o <code>AreasACL::userInAreaGroup('area','Administradores')</code></td></tr>
    <tr><td>INSTALL.php</td><td>5-6</td><td>$_ACL->addRole(...)</td><td>Gestionar como grupo de área en AreasACL</td></tr>
    <tr><td>INSTALL.php</td><td>8-9</td><td>$_ACL->updateUserRole(...)</td><td>Asignar usuario a grupo de área en AreasACL</td></tr>
    <tr><td>INSTALL.php</td><td>11</td><td>$_ACL->addPermission('doku_view')</td><td><b>No migrar</b>: doku_view no existe en AreasACL</td></tr>
    <tr><td>INSTALL.php</td><td>12</td><td>$_ACL->addPermission('doku_add')</td><td>Usar <code>AreasACL::addAppPermission('hulamm_ware','Añadir','doku_add')</code></td></tr>
    <tr><td>INSTALL.php</td><td>13</td><td>$_ACL->addPermission('doku_delete')</td><td><b>No migrar</b>: doku_delete no existe en AreasACL</td></tr>
    <tr><td>INSTALL.php</td><td>14</td><td>$_ACL->addPermission('doku_admin')</td><td>Usar <code>AreasACL::addAppPermission('hulamm_ware','Administrar','doku_admin')</code></td></tr>
    <tr><td>INSTALL.php</td><td>16</td><td>$_ACL->addRolePerm('Administradores','doku_view')</td><td><b>No migrar</b>: doku_view no existe en AreasACL</td></tr>
    <tr><td>INSTALL.php</td><td>17</td><td>$_ACL->addRolePerm('Administradores','doku_add')</td><td>Asignar permiso a grupo en área con AreasACL (CFG_AREAS_APPS_GROUPS_PERMS)</td></tr>
    <tr><td>INSTALL.php</td><td>18</td><td>$_ACL->addRolePerm('Administradores','doku_admin')</td><td>Asignar permiso a grupo en área con AreasACL (CFG_AREAS_APPS_GROUPS_PERMS)</td></tr>
    <tr><td>INSTALL.php</td><td>19</td><td>$_ACL->addRolePerm('Administradores','doku_delete')</td><td><b>No migrar</b>: doku_delete no existe en AreasACL</td></tr>
  </table>