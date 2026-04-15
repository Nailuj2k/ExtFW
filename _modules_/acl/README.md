    
    Sistema de permisos
    
    Los nombres de las tablas comienzan por CFG_
    
    Tenemos Aplicaciones y Áreas con la siguiente lógica:

    Aplicaciones
    Cada aplicación tiene permisos. Un permiso designa alñgo que puede hacerse en esa aplicación. Por ejemploi, en una aplicación llamada tareas podrían haber permisos para crear tareas, ver tareas, eliminar tareas, etc. CAda Aplicación defione sus propios permisos, que se definen en la tabla CFG_APPS_PERMS. En cada permiso se define el nombre del permiso, la clave del permiso, una descripción del permiso y si es por defecto o no.
    
    Áreas: 

    Cada área de trabajo tendrá permiso para usar, o no, determinadas aplicaciones.

    Cada área tendrá usuarios, algunos de los cuales podrán tener nivel 'admin'. Estos usuarios podrán formar grupos dentro de cada área, por ejemplo, en un área llamada 'Desarrollo' podría haber un grupo 'backend', otro grupo 'frontend', otro 'design', etc.  Cada grupo dentro de cada ñarea podrá tener permisos distintos pars cada aplicación, por ejemplo, los miembros del grupo 'backend' podrían tener permisos para crear tareas y cerrar tareas y los del grupo fronttend sí para crear y no para asignar ni cerrar. 
    
    Dentro de cada área, los usuarios 'admin' tendrán permisos para crear grupos, añadir o quitar usuarios de esos grupos, y editar permisos para cada aplicación dentro de ese grupo. De esta manera, el trabajo de asisgnar permisos, grupos, etc se delega en el responsable de cada área, liberando de este 'trabajo administrativo' al servicio de soporte.

    No se definen permisos directos a nivel de usuario; el modelo se simplifica usando grupos por área y permisos por defecto (BYDEFAULT) en el contexto Área→App.  

    Notas: 
    - Los usuarios se obtienen de una tabla de usuarios, que en este caso se llama CLI_USER y su PK es user_id
    - Además de CLI_USER hay una ACL_ROLES y ACL_USER_ROLES que permiten asignar roles a usuarios y asignar roles a grupos. Esto permite que, al editar un aéra podamos importar todos los usuarios que pertenezcan un determinado rol.
    - En éste módulo sólo se gestionan las tablas CFG_APPS_* y CFG_AREAS_*, los usuarios , grupos, etc no se tocan.
    
    Actualmente el módulo ya permite editar aplicaciones, permisos, usuarios y grupos. 
    Hay dos niveles de acceso definidos en los roles Area_Admin y Area_User (parametrizables vía constantes ACL_ACCESS_ROLE_ADMIN y ACL_ACCESS_ROLE_USER en init.php). Los usuarios con rol Area_Admin pueden editar Aplicaciones y sus permisos. Los usuarios con rol Area_User sólo pueden editar usuarios, grupos, permisos, etc. en el área o áreas en las que sean 'admin'.
    
    Para la edición y gestión de las tablas en el módulo se usa la clase Table, que no necesita mucha explicación.
    
    BYDEFAULT:
    - Cada Aplicación define sus permisos en CFG_APPS_PERMS.
    - En el vínculo Área→App, la tabla CFG_AREAS_APPS_PERMS referencia esos permisos y permite marcar BYDEFAULT=1.
    - Un usuario que pertenezca al Área obtiene automáticamente todos los permisos marcados BYDEFAULT=1 para esa App/Área.
    - Los administradores del Área (AU.ADMIN=1) tienen todos los permisos de la App en su Área, si la App está habilitada en esa Área (CFG_AREAS_APPS.ACTIVE=1).
    - El resto de permisos se conceden a través de grupos del Área.

    Uso del API (resumen):
    - hasPermissionByArea(areaKey, appKey, permKey, userId=null) → bool
    - hasPermission(appKey, permKey, userId=null) → bool (valida en cualquiera de las áreas del usuario)
    - getUserPermissions(areaKey, appKey, userId=null) → array [{ PERMKEY, PERMNAME, DESCRIPTION, SOURCE }]
    - getUsersWithPermission(areaKey, appKey, permKey) → array deduplicada por prioridad (area_admin > group_derived > default)
    - hasPermissionAnyArea(appKey, permKey, userId=null) → bool
    - getAreasWithPermission(appKey, permKey, userId=null) → array de AREAKEY
    - getAppPermissionsCatalog(appKey) → array catálogo de permisos de la app
    - isAppEnabledInArea(areaKey, appKey) → bool
    - addApp(name, appKey=null, active=1) → int ID (crea/actualiza app)
    - addAppPermission(appKey, name, permKey=null, description='', byDefault=0, active=1) → int ID (crea/actualiza permiso)
    - ensureAreaApp(areaKey, appKey, active=1) → int ID (habilita App en Área)
    - ensureArea(areaKey, name, active=1) → int ID (crea/actualiza Área)
   
    El archivo areas_acl.class.php define una clase para obtener permisos, áreas, apps, etc. 

    Páginas útiles:
    - acl/doc  → Documentación del módulo
    - acl/test → Test interactivo de la clase AreasACL

    
    - Aplicaciones y permisos.
    CFG_APPS    CFG_APPS_PERMS
    ID          ID
    NAME        ID_APP
    APPKEY      NAME
                PERMKEY
                DESCRIPTION
                BYDEFAULT
   
    -Áreas      -Usuarios (por área)
    CFG_AREAS   CFG_AREAS_USERS
    ID          ID
    NAME        ID_AREA
    AREAKEY     ID_USER   >--   CLI_USER.user_id
    ID_ROLE     ADMIN
                
                -Grupos (por área)   -Usuarios (por grupo y área)
                CFG_AREAS_GROUPS     CFG_AREAS_GROUPS_USERS
                ID                   ID
                ID_AREA              ID_AREA_GROUP
                NAME                 ID_USER
                GROUPKEY

                -Apps (por área)     -Permisos (por app y área)     
                CFG_AREAS_APPS       CFG_AREAS_APPS_PERMS 
                ID                   ID  
                ID_AREA              ID_AREA_APP   
                ID_APP               ID_APP_PERM   
                                     BYDEFAULT   
                                     
                                     -Grupos(por área y app)  -Permisos (por grupo, app y área)
                                     CFG_AREAS_APPS_GROUPS      CFG_AREAS_APPS_GROUPS_PERMS
                                     ID                         ID
                                     ID_AREA_APP                ID_AREA_APP_GROUP
                                     ID_GROUP                   ID_AREA_APP_PERM
    Ventajas del diseño (autogestión por área):
    - Delegación: cada área administra sus grupos y permisos en sus aplicaciones.
    - Menor carga central: informática/administración no gestiona el detalle del día a día.
    - Sencillez: sin permisos directos a usuario; grupos + BYDEFAULT.
    - Escalabilidad: cada área evoluciona de forma independiente sin interferir con otras.
