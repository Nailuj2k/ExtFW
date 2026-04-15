<?php


/**
 * AreasACL - Sistema de Control de Acceso por Áreas
 * 
 * Esta clase gestiona permisos y accesos de usuarios a través de un sistema de:
 * - Áreas: Contenedores organizacionales
 * - Aplicaciones: Sistemas con permisos específicos  
 * - Usuarios: Asignados directamente a áreas o a través de grupos
 * - Grupos: Agrupaciones de usuarios dentro de áreas
 * - Permisos: Acciones específicas que usuarios/grupos pueden realizar en apps/áreas
 * 
 * Precedencia de permisos (de mayor a menor):
 * - area_admin → el usuario es admin del área: tiene todos los permisos de la app en esa área
 * - group_derived → el permiso se concede vía pertenencia a grupos del área en el contexto Área→App
 * - default → el permiso está marcado como BYDEFAULT=1 en CFG_AREAS_APPS_PERMS y el usuario pertenece al área
 * 
 * API principal (resumen):
 * - hasPermission(appKey, permKey, userId=null): bool
 * - hasPermissionByArea(areaKey, appKey, permKey, userId=null): bool
 * - hasPermissionAnyArea(appKey, permKey, userId=null): bool
 * - getAreasWithPermission(appKey, permKey, userId=null): array
 * - getUserAreas(userId=null): array, getAreaApps(areaKey): array, ...
 * 
 * Seguridad: los identificadores (AREAKEY/APPKEY/PERMKEY) se validan con validateSqlIdentifier().
 * Los accesos a BD usan Table::sqlQueryPrepared() con parámetros preparados.
 * 
 * Verás veas consultas SQL así de grandes peore stá pensado para tener la cahe activada
 * y asi va a to leche. Ms rápido que ...
 * 
 * @author ExtFW Framework
 * @version 2.1
 */
class AreasACL {
    
    /**
     * Verifica si un usuario tiene un permiso de una app en cualquiera de sus áreas.
     * @param string $app APPKEY
     * @param string $permission PERMKEY
     * @param int|null $userId Usuario (null = sesión actual)
     */
    public static function hasPermission($app, $permission, $userId = null) {
        if (!self::validateSession($userId)) return false;
        $userId = $userId ?? $_SESSION['userid'];
        $areas = self::getUserAreas($userId);
        foreach ($areas as $areaRow) {
            $areaKey = $areaRow['AREAKEY'] ?? null;
            if ($areaKey && self::hasPermissionByArea($areaKey, $app, $permission, $userId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si un usuario tiene un permiso de una app en un área concreta.
     * @param string $area AREAKEY
     * @param string $app APPKEY
     * @param string $permission PERMKEY
     * @param int|null $userId Usuario (null = sesión actual)
     */
    public static function hasPermissionByArea($area, $app, $permission, $userId = null) {
        if (!self::validateSession($userId)) return false;
        $userId = $userId ?? $_SESSION['userid'];

        // 0) Los administradores del área tienen todos los permisos de la app (si la app está habilitada en el área)
        if (self::isAreaAdmin($area, $userId)) {
            if (self::isAppEnabledInArea($area, $app)) {
                // Verificar que el permiso existe en la app antes de concederlo
                if (self::permissionExistsInApp($app, $permission)) {
                    return true;
                }
            }
            // Si la app no está habilitada en el área, continuar con el resto de chequeos (acabará en false)
        }

        // 1) Permisos derivados de grupos
        $sql = "SELECT COUNT(*) as count
                FROM CFG_AREAS_APPS_GROUPS_PERMS AAGP
                INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AAGP.ID_AREA_APP_PERM = AAP.ID
                INNER JOIN CFG_APPS_PERMS AP ON AAP.ID_APP_PERM = AP.ID
                INNER JOIN CFG_AREAS_APPS_GROUPS AAG ON AAGP.ID_AREA_APP_GROUP = AAG.ID
                INNER JOIN CFG_AREAS_APPS AA ON AAG.ID_AREA_APP = AA.ID
                INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
                INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
                INNER JOIN CFG_AREAS_GROUPS AG ON AAG.ID_GROUP = AG.ID
                INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON AG.ID = AGU.ID_AREA_GROUP
                WHERE A.AREAKEY = ?
                  AND APP.APPKEY = ?
                  AND AP.PERMKEY = ?
                  AND AGU.ID_USER = ?
                  AND AAGP.ACTIVE = 1
                  AND AAP.ACTIVE = 1
                  AND AGU.ACTIVE = 1
                  AND AG.ACTIVE = 1";
        $params = [
            self::validateSqlIdentifier($area),
            self::validateSqlIdentifier($app),
            self::validateSqlIdentifier($permission),
            intval($userId)
        ];
        $result = Table::sqlQueryPrepared($sql, $params);
        if (isset($result[0]['count']) && $result[0]['count'] > 0) return true;

        // 2) Permisos por defecto (BYDEFAULT=1) para todos los usuarios del área
        if (!self::userInArea($area, $userId)) return false;
        $sqlDefault = "SELECT COUNT(*) as count
                       FROM CFG_AREAS_APPS_PERMS AAP
                       INNER JOIN CFG_AREAS_APPS AA ON AAP.ID_AREA_APP = AA.ID AND AAP.ACTIVE = 1
                       INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
                       INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
                       INNER JOIN CFG_APPS_PERMS AP ON AAP.ID_APP_PERM = AP.ID
                       WHERE A.AREAKEY = ?
                         AND APP.APPKEY = ?
                         AND AP.PERMKEY = ?
                         AND AAP.BYDEFAULT = 1";
        $paramsDefault = [
            self::validateSqlIdentifier($area),
            self::validateSqlIdentifier($app),
            self::validateSqlIdentifier($permission)
        ];
        $rDefault = Table::sqlQueryPrepared($sqlDefault, $paramsDefault);
        return (isset($rDefault[0]['count']) && $rDefault[0]['count'] > 0);
    }

    /**
     * Verifica si el usuario está en un área específica
     * 
     * @param string $area Clave del área
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return bool True si el usuario está en el área
     */
    public static function userInArea($area, $userId = null) {
        if (!self::validateSession($userId)) return false;
        
        $userId = $userId ?? $_SESSION['userid'];
        
        $sql = "SELECT COUNT(*) as count
                FROM CFG_AREAS_USERS AU
                INNER JOIN CFG_AREAS A ON AU.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AU.ID_USER = ?
                  AND AU.ACTIVE = 1";
        
        $params = [
            self::validateSqlIdentifier($area),
            intval($userId)
        ];
        
        $result = Table::sqlQueryPrepared($sql, $params);
        return (isset($result[0]['count']) && $result[0]['count'] > 0);
    }
    
    /**
     * Verifica si el usuario es administrador de un área
     * 
     * @param string $area Clave del área
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return bool True si es administrador del área
     */
    public static function isAreaAdmin($area, $userId = null) {
        if (!self::validateSession($userId)) return false;
        
        $userId = $userId ?? $_SESSION['userid'];
        
        $sql = "SELECT COUNT(*) as count
                FROM CFG_AREAS_USERS AU
                INNER JOIN CFG_AREAS A ON AU.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AU.ID_USER = ?
                  AND AU.ADMIN = 1 
                  AND AU.ACTIVE = 1";
        
        $params = [
            self::validateSqlIdentifier($area),
            intval($userId)
        ];
        
        $result = Table::sqlQueryPrepared($sql, $params);
        return (isset($result[0]['count']) && $result[0]['count'] > 0);
    }
    
    /**
     * Verifica si el usuario pertenece a un grupo específico en un área
     * 
     * @param string $area Clave del área
     * @param string $group Clave del grupo
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return bool True si pertenece al grupo
     */
    public static function userInAreaGroup($area, $group, $userId = null) {
        if (!self::validateSession($userId)) return false;
        
        $userId = $userId ?? $_SESSION['userid'];
        
        $sql = "SELECT COUNT(*) as count
                FROM CFG_AREAS_GROUPS_USERS AGU
                INNER JOIN CFG_AREAS_GROUPS AG ON AGU.ID_AREA_GROUP = AG.ID
                INNER JOIN CFG_AREAS A ON AG.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AG.GROUPKEY = ?
                  AND AGU.ID_USER = ?
                  AND AGU.ACTIVE = 1 
                  AND AG.ACTIVE = 1";
        
        $params = [
            self::validateSqlIdentifier($area),
            self::validateSqlIdentifier($group),
            intval($userId)
        ];
        
        $result = Table::sqlQueryPrepared($sql, $params);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }
    
    // ===== MÉTODOS DE OBTENCIÓN DE DATOS =====
    
    /**
     * Obtiene todas las áreas a las que pertenece un usuario
     * 
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return array Lista de áreas con información completa
     */
    public static function getUserAreas($userId = null) {
        if (!self::validateSession($userId)) return [];
        
        $userId = $userId ?? $_SESSION['userid'];
        $uid = intval($userId);
        
        $sql = "SELECT A.ID, A.AREAKEY, A.NAME as AREANAME, AU.ADMIN
                FROM CFG_AREAS A
                INNER JOIN CFG_AREAS_USERS AU ON A.ID = AU.ID_AREA
                WHERE AU.ID_USER = ?
                  AND AU.ACTIVE = 1
                ORDER BY A.NAME";
        
        $params = [$uid];
        
        return Table::sqlQueryPrepared($sql, $params);
    }

    public static function getAreas($userId = null , $assoc = null) {

        if(!$userId) {
            $sql = 'SELECT ID, AREAKEY AS NAME FROM CFG_AREAS WHERE ACTIVE=1 ORDER BY NAME';
            $rows = Table::sqlQuery($sql);

        }else{
            $sql = "SELECT A.ID, A.AREAKEY AS NAME
                    FROM CFG_AREAS A
                    INNER JOIN CFG_AREAS_USERS AU ON A.ID = AU.ID_AREA
                    WHERE AU.ID_USER = ?
                    AND AU.ACTIVE = 1
                    ORDER BY A.AREAKEY";

            $params = [$userId];
            $rows   =  Table::sqlQueryPrepared($sql, $params);                    
        }

        $result = [];
        
        if($assoc) {
            foreach($rows as $row) $result[] = ['ID'=>$row['ID'], 'NAME' => $row['NAME']];
        }else{
            foreach($rows as $row) $result[$row['ID']] = $row['NAME'];
        }
        
        return $result;
        

    }


    /**
     * Obtiene todas las aplicaciones disponibles en un área
     * 
     * @param string $area Clave del área
     * @return array Lista de aplicaciones del área
     */
    public static function getAreaApps($area) {
        $sql = "SELECT APP.ID, APP.APPKEY, APP.NAME as APPNAME
                FROM CFG_APPS APP
                INNER JOIN CFG_AREAS_APPS AA ON APP.ID = AA.ID_APP
                INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AA.ACTIVE = 1
                ORDER BY APP.NAME";
        
        $params = [self::validateSqlIdentifier($area)];
        
        return Table::sqlQueryPrepared($sql, $params);
    }
    

    /**
     * Obtiene todos los usuarios de un área específica
     * 
     * @param string $area Clave del área
     * @param bool $includeGroups Si incluir usuarios de grupos
     * @return array Lista de usuarios del área
     */
    public static function getAreaUsers($area) {
        $validatedArea = self::validateSqlIdentifier($area);
        $sql = "SELECT U.user_id as ID, U.username as USERNAME, AU.ADMIN
                FROM CLI_USER U
                INNER JOIN CFG_AREAS_USERS AU ON U.user_id = AU.ID_USER
                INNER JOIN CFG_AREAS A ON AU.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AU.ACTIVE = 1
                ORDER BY U.username";
        $params = [$validatedArea];
        return Table::sqlQueryPrepared($sql, $params);
    }

    /**
     * Catálogo de permisos de una aplicación (definición en CFG_APPS_PERMS)
     * @param string $app APPKEY
     * @return array [{ PERMKEY, PERMNAME, DESCRIPTION }]
     */
    public static function getAppPermissionsCatalog($app) {
        $validatedApp = self::validateSqlIdentifier($app);
        $sql = "SELECT AP.PERMKEY, AP.NAME as PERMNAME, AP.DESCRIPTION
                FROM CFG_APPS_PERMS AP
                INNER JOIN CFG_APPS APP ON AP.ID_APP = APP.ID
                WHERE APP.APPKEY = ?
                ORDER BY AP.PERMKEY";
        return Table::sqlQueryPrepared($sql, [$validatedApp]);
    }
    
    /**
     * Obtiene todos los permisos de un usuario en un área/app específica
     * 
     * @param string $area Clave del área
     * @param string $app Clave de la aplicación
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return array Lista de permisos del usuario
     */
    public static function getUserPermissions($area, $app, $userId = null) {
        if (!self::validateSession($userId)) return [];
        
        $userId = $userId ?? $_SESSION['userid'];
        
        try {
            $rawPermissions = [];

            // 1. Si es administrador del área, obtener todos los permisos de la aplicación
            if (self::isAreaAdmin($area, $userId)) {
                // Administrador del área: obtiene todos los permisos de la app SOLO si la app está habilitada en el área
                if (self::isAppEnabledInArea($area, $app)) {
                    $sqlAdminPerms = "
                        SELECT DISTINCT AP.PERMKEY, AP.NAME as PERMNAME, AP.DESCRIPTION
                        FROM CFG_APPS_PERMS AP
                        INNER JOIN CFG_APPS APP ON AP.ID_APP = APP.ID
                        WHERE APP.APPKEY = ?
                        ORDER BY AP.PERMKEY
                    ";
                    $paramsAdminPerms = [self::validateSqlIdentifier($app)];
                    $adminPermsResult = Table::sqlQueryPrepared($sqlAdminPerms, $paramsAdminPerms);
                    foreach ($adminPermsResult as $perm) {
                        $rawPermissions[] = [
                            'PERMKEY' => $perm['PERMKEY'],
                            'PERMNAME' => $perm['PERMNAME'],
                            'DESCRIPTION' => $perm['DESCRIPTION'],
                            'SOURCE' => 'area_admin'
                        ];
                    }
                }
            } else {
    
                // 3. Obtener permisos derivados de grupos para usuarios no administradores
                $sqlGroupPerms = "
                    SELECT DISTINCT AP.PERMKEY, AP.NAME as PERMNAME, AP.DESCRIPTION
                    FROM CFG_APPS_PERMS AP
                    INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AP.ID = AAP.ID_APP_PERM
                    INNER JOIN CFG_AREAS_APPS AA_Context ON AAP.ID_AREA_APP = AA_Context.ID
                    INNER JOIN CFG_AREAS A_Context ON AA_Context.ID_AREA = A_Context.ID
                    INNER JOIN CFG_APPS APP_Context ON AA_Context.ID_APP = APP_Context.ID
                    INNER JOIN CFG_AREAS_APPS_GROUPS_PERMS AAGP ON AAP.ID = AAGP.ID_AREA_APP_PERM
                    INNER JOIN CFG_AREAS_APPS_GROUPS AAG ON AAGP.ID_AREA_APP_GROUP = AAG.ID AND AAG.ID_AREA_APP = AA_Context.ID
                    INNER JOIN CFG_AREAS_GROUPS AG ON AAG.ID_GROUP = AG.ID
                    INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON AG.ID = AGU.ID_AREA_GROUP
                    WHERE A_Context.AREAKEY = ?
                      AND APP_Context.APPKEY = ?
                      AND AGU.ID_USER = ?
                      AND AAGP.ACTIVE = 1
                      AND AAP.ACTIVE = 1
                      AND AAG.ACTIVE = 1
                      AND AG.ACTIVE = 1
                      AND AGU.ACTIVE = 1
                    ORDER BY AP.PERMKEY";

                $paramsGroupPerms = [
                    self::validateSqlIdentifier($area),
                    self::validateSqlIdentifier($app),
                    intval($userId)
                ];
                $groupPermsResult = Table::sqlQueryPrepared($sqlGroupPerms, $paramsGroupPerms);
                
                foreach ($groupPermsResult as $perm) {
                    $rawPermissions[] = [
                        'PERMKEY' => $perm['PERMKEY'],
                        'PERMNAME' => $perm['PERMNAME'],
                        'DESCRIPTION' => $perm['DESCRIPTION'],
                        'SOURCE' => 'group_derived'
                    ];
                }

                // 4. Permisos por defecto (BYDEFAULT=1) para todos los usuarios del área
                if (self::userInArea($area, $userId)) {
                    $sqlDefaultPerms = "
                        SELECT DISTINCT AP.PERMKEY, AP.NAME as PERMNAME, AP.DESCRIPTION
                        FROM CFG_APPS_PERMS AP
                        INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AP.ID = AAP.ID_APP_PERM AND AAP.ACTIVE = 1 AND AAP.BYDEFAULT = 1
                        INNER JOIN CFG_AREAS_APPS AA ON AAP.ID_AREA_APP = AA.ID
                        INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
                        INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
                        WHERE A.AREAKEY = ? AND APP.APPKEY = ?
                        ORDER BY AP.PERMKEY";
                    $paramsDefaultPerms = [
                        self::validateSqlIdentifier($area),
                        self::validateSqlIdentifier($app)
                    ];
                    $defaultPermsResult = Table::sqlQueryPrepared($sqlDefaultPerms, $paramsDefaultPerms);
                    foreach ($defaultPermsResult as $perm) {
                        $rawPermissions[] = [
                            'PERMKEY' => $perm['PERMKEY'],
                            'PERMNAME' => $perm['PERMNAME'],
                            'DESCRIPTION' => $perm['DESCRIPTION'],
                            'SOURCE' => 'default'
                        ];
                    }
                }
            }
            
            // Unificar permisos y eliminar duplicados por PERMKEY.
            // Si un permiso viene de múltiples fuentes (ej. directo y grupo), esta lógica simple tomará el primero que encuentre.
            // Dado que los administradores ya tienen un bloque exclusivo, esto afecta principalmente la mezcla de permisos directos y de grupo para no administradores.
            $finalPermissions = [];
            $seenPermKeys = [];
            foreach ($rawPermissions as $perm) {
                if (!isset($seenPermKeys[$perm['PERMKEY']])) {
                    $finalPermissions[] = $perm;
                    $seenPermKeys[$perm['PERMKEY']] = true;
                }
            }
            return $finalPermissions;

        } catch (Exception $e) {
            // En caso de error, registrar pero no retornar permisos por defecto
            error_log("AreasACL::getUserPermissions - Error: " . $e->getMessage());
            return []; // Retornar array vacío en caso de error
        }
    }
    
    /**
     * Busca usuarios con un permiso específico en un área/app.
     * Si $area es null, busca en todas las áreas.
     *
     * @param string|null $area Clave del área, o null para cualquier área
     * @param string $app Clave de la aplicación
     * @param string $permission Clave del permiso
     * @return array Array de usuarios [ID, USERNAME, SOURCE]
     */
    public static function getUsersWithPermission($area, $app, $permission) {
        $anyArea = ($area === null);
        $validatedArea = $anyArea ? null : self::validateSqlIdentifier($area);
        $validatedApp = self::validateSqlIdentifier($app);
        $validatedPermission = self::validateSqlIdentifier($permission);

        // 1. Verificar si el permiso existe para esta aplicación
        $sqlCheckPerm = "
            SELECT COUNT(*) as count
            FROM CFG_APPS_PERMS AP_Check
            INNER JOIN CFG_APPS APP_Check ON AP_Check.ID_APP = APP_Check.ID
            WHERE APP_Check.APPKEY = ?
              AND AP_Check.PERMKEY = ?";
        $permissionExistsResult = Table::sqlQueryPrepared($sqlCheckPerm, [$validatedApp, $validatedPermission]);

        if (!isset($permissionExistsResult[0]['count']) || $permissionExistsResult[0]['count'] == 0) {
            return [];
        }

        // 2. Unir las fuentes con prioridad y deduplicar (admin > group_derived > default)
        if ($anyArea) {
            $sqlUsers = "
                SELECT ID, USERNAME, SOURCE, PRI FROM (
                    -- Admin de área: tiene todos los permisos de la app en cualquier área
                    SELECT DISTINCT U.user_id as ID, U.username as USERNAME, 'area_admin' as SOURCE, 1 as PRI
                    FROM CLI_USER U
                    INNER JOIN CFG_AREAS_USERS AU ON U.user_id = AU.ID_USER AND AU.ACTIVE = 1 AND AU.ADMIN = 1
                    INNER JOIN CFG_AREAS A ON AU.ID_AREA = A.ID
                    INNER JOIN CFG_AREAS_APPS AA ON A.ID = AA.ID_AREA AND AA.ACTIVE = 1
                    INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID AND APP.APPKEY = ?

                    UNION ALL

                    -- Permiso derivado de grupos en cualquier área
                    SELECT DISTINCT U.user_id as ID, U.username as USERNAME, 'group_derived' as SOURCE, 2 as PRI
                    FROM CLI_USER U
                    INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON U.user_id = AGU.ID_USER AND AGU.ACTIVE = 1
                    INNER JOIN CFG_AREAS_GROUPS AG ON AGU.ID_AREA_GROUP = AG.ID AND AG.ACTIVE = 1
                    INNER JOIN CFG_AREAS A_Grp ON AG.ID_AREA = A_Grp.ID
                    INNER JOIN CFG_AREAS_APPS AA_Grp ON A_Grp.ID = AA_Grp.ID_AREA AND AA_Grp.ACTIVE = 1
                    INNER JOIN CFG_APPS APP_Grp ON AA_Grp.ID_APP = APP_Grp.ID AND APP_Grp.APPKEY = ?
                    INNER JOIN CFG_AREAS_APPS_GROUPS AAG ON AG.ID = AAG.ID_GROUP AND AA_Grp.ID = AAG.ID_AREA_APP AND AAG.ACTIVE = 1
                    INNER JOIN CFG_AREAS_APPS_GROUPS_PERMS AAGP ON AAG.ID = AAGP.ID_AREA_APP_GROUP AND AAGP.ACTIVE = 1
                    INNER JOIN CFG_AREAS_APPS_PERMS AAP_Grp ON AAGP.ID_AREA_APP_PERM = AAP_Grp.ID AND AAP_Grp.ACTIVE = 1
                    INNER JOIN CFG_APPS_PERMS AP_Grp ON AAP_Grp.ID_APP_PERM = AP_Grp.ID AND AP_Grp.PERMKEY = ?

                    UNION ALL

                    -- Permiso por defecto (BYDEFAULT=1) en cualquier área
                    SELECT DISTINCT U2.user_id as ID, U2.username as USERNAME, 'default' as SOURCE, 3 as PRI
                    FROM CLI_USER U2
                    INNER JOIN CFG_AREAS_USERS AU2 ON U2.user_id = AU2.ID_USER AND AU2.ACTIVE = 1
                    INNER JOIN CFG_AREAS A2 ON AU2.ID_AREA = A2.ID
                    INNER JOIN CFG_AREAS_APPS AA2 ON A2.ID = AA2.ID_AREA AND AA2.ACTIVE = 1
                    INNER JOIN CFG_APPS APP2 ON AA2.ID_APP = APP2.ID AND APP2.APPKEY = ?
                    INNER JOIN CFG_AREAS_APPS_PERMS AAP2 ON AA2.ID = AAP2.ID_AREA_APP AND AAP2.ACTIVE = 1 AND AAP2.BYDEFAULT = 1
                    INNER JOIN CFG_APPS_PERMS AP2 ON AAP2.ID_APP_PERM = AP2.ID AND AP2.PERMKEY = ?
                ) X
                ORDER BY ID, PRI, USERNAME";
            $paramsUsers = [
                $validatedApp,
                $validatedApp, $validatedPermission,
                $validatedApp, $validatedPermission
            ];
        } else {
            $sqlUsers = "
                SELECT ID, USERNAME, SOURCE, PRI FROM (
                    -- Admin de área: tiene todos los permisos de la app en esa área
                    SELECT DISTINCT U.user_id as ID, U.username as USERNAME, 'area_admin' as SOURCE, 1 as PRI
                    FROM CLI_USER U
                    INNER JOIN CFG_AREAS_USERS AU ON U.user_id = AU.ID_USER AND AU.ACTIVE = 1 AND AU.ADMIN = 1
                    INNER JOIN CFG_AREAS A ON AU.ID_AREA = A.ID AND A.AREAKEY = ?
                    INNER JOIN CFG_AREAS_APPS AA ON A.ID = AA.ID_AREA
                    INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID AND APP.APPKEY = ?

                    UNION ALL

                    -- Permiso derivado de grupos
                    SELECT DISTINCT U.user_id as ID, U.username as USERNAME, 'group_derived' as SOURCE, 2 as PRI
                    FROM CLI_USER U
                    INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON U.user_id = AGU.ID_USER AND AGU.ACTIVE = 1
                    INNER JOIN CFG_AREAS_GROUPS AG ON AGU.ID_AREA_GROUP = AG.ID AND AG.ACTIVE = 1
                    INNER JOIN CFG_AREAS A_Grp ON AG.ID_AREA = A_Grp.ID AND A_Grp.AREAKEY = ?
                    INNER JOIN CFG_AREAS_APPS AA_Grp ON A_Grp.ID = AA_Grp.ID_AREA
                    INNER JOIN CFG_APPS APP_Grp ON AA_Grp.ID_APP = APP_Grp.ID AND APP_Grp.APPKEY = ?
                    INNER JOIN CFG_AREAS_APPS_GROUPS AAG ON AG.ID = AAG.ID_GROUP AND AA_Grp.ID = AAG.ID_AREA_APP AND AAG.ACTIVE = 1
                    INNER JOIN CFG_AREAS_APPS_GROUPS_PERMS AAGP ON AAG.ID = AAGP.ID_AREA_APP_GROUP AND AAGP.ACTIVE = 1
                    INNER JOIN CFG_AREAS_APPS_PERMS AAP_Grp ON AAGP.ID_AREA_APP_PERM = AAP_Grp.ID AND AAP_Grp.ACTIVE = 1
                    INNER JOIN CFG_APPS_PERMS AP_Grp ON AAP_Grp.ID_APP_PERM = AP_Grp.ID AND AP_Grp.PERMKEY = ?

                    UNION ALL

                    -- Permiso por defecto (BYDEFAULT=1) para los usuarios del área
                    SELECT DISTINCT U2.user_id as ID, U2.username as USERNAME, 'default' as SOURCE, 3 as PRI
                    FROM CLI_USER U2
                    INNER JOIN CFG_AREAS_USERS AU2 ON U2.user_id = AU2.ID_USER AND AU2.ACTIVE = 1
                    INNER JOIN CFG_AREAS A2 ON AU2.ID_AREA = A2.ID AND A2.AREAKEY = ?
                    INNER JOIN CFG_AREAS_APPS AA2 ON A2.ID = AA2.ID_AREA
                    INNER JOIN CFG_APPS APP2 ON AA2.ID_APP = APP2.ID AND APP2.APPKEY = ?
                    INNER JOIN CFG_AREAS_APPS_PERMS AAP2 ON AA2.ID = AAP2.ID_AREA_APP AND AAP2.ACTIVE = 1 AND AAP2.BYDEFAULT = 1
                    INNER JOIN CFG_APPS_PERMS AP2 ON AAP2.ID_APP_PERM = AP2.ID AND AP2.PERMKEY = ?
                ) X
                ORDER BY ID, PRI, USERNAME";
            $paramsUsers = [
                $validatedArea, $validatedApp,
                $validatedArea, $validatedApp, $validatedPermission,
                $validatedArea, $validatedApp, $validatedPermission
            ];
        }

        $rows = Table::sqlQueryPrepared($sqlUsers, $paramsUsers);
        $unique = [];
        $seen = [];
        foreach ($rows as $row) {
            $id = $row['ID'];
            if (!isset($seen[$id])) {
                $unique[] = ['ID' => $row['ID'], 'NAME' => $row['USERNAME']/*, 'SOURCE' => $row['SOURCE']*/];
                $seen[$id] = true;
            }
        }

        return $unique;
    }

    /**
     * Busca usuarios que tengan un permiso en cualquier área de la app.
     * Equivalente a getUsersWithPermission(null, $app, $permission).
     *
     * @param string $app Clave de la aplicación
     * @param string $permission Clave del permiso
     * @return array Array de usuarios [ID, USERNAME, SOURCE]
     */
    public static function getUsersWithPermissionInAnyArea($app, $permission) {
        return self::getUsersWithPermission(null, $app, $permission);
    }
    
    /**
     * Obtiene todos los grupos de un área específica
     * 
     * @param string $area Clave del área
     * @return array Array de grupos del área
     */
    public static function getAreaGroups($area) {
        $sql = "SELECT AG.ID, AG.GROUPKEY, AG.NAME as GROUPNAME, AG.DESCRIPTION
                FROM CFG_AREAS_GROUPS AG
                INNER JOIN CFG_AREAS A ON AG.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AG.ACTIVE = 1
                ORDER BY AG.NAME";
        
        $params = [self::validateSqlIdentifier($area)];
        
        return Table::sqlQueryPrepared($sql, $params);
    }
    
    /**
     * Obtiene administradores de un área específica
     * 
     * @param string $area Clave del área
     * @return array Array de administradores
     */
    public static function getAreaAdmins($area) {
        $sql = "SELECT U.user_id as ID, U.username as USERNAME
                FROM CLI_USER U
                INNER JOIN CFG_AREAS_USERS AU ON U.user_id = AU.ID_USER
                INNER JOIN CFG_AREAS A ON AU.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AU.ADMIN = 1 
                  AND AU.ACTIVE = 1
                ORDER BY U.username";
        
        $params = [self::validateSqlIdentifier($area)];
        
        return Table::sqlQueryPrepared($sql, $params);
    }
    
    /**
     * Obtiene usuarios de un grupo específico en un área
     * 
     * @param string $area Clave del área
     * @param string $group Clave del grupo
     * @return array Array de usuarios
     */
    public static function getAreaGroupUsers($area, $group) {
        $sql = "SELECT U.user_id as ID, U.username as USERNAME
                FROM CLI_USER U
                INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON U.user_id = AGU.ID_USER
                INNER JOIN CFG_AREAS_GROUPS AG ON AGU.ID_AREA_GROUP = AG.ID
                INNER JOIN CFG_AREAS A ON AG.ID_AREA = A.ID
                WHERE A.AREAKEY = ?
                  AND AG.GROUPKEY = ?
                  AND AGU.ACTIVE = 1 
                  AND AG.ACTIVE = 1
                ORDER BY U.username";
        
        $params = [
            self::validateSqlIdentifier($area),
            self::validateSqlIdentifier($group)
        ];
        
        return Table::sqlQueryPrepared($sql, $params);
    }

    /**
     * Conveniencia: verifica un permiso en cualquier área del usuario
     * Retorna true si el usuario tiene el permiso para la app en al menos un área
     * (según la misma lógica que hasPermission: admin, grupos, BYDEFAULT)
     * 
     * @param string $app Clave de la aplicación (APPKEY)
     * @param string $permission Clave del permiso (PERMKEY)
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return bool
     */
    public static function hasPermissionAnyArea($app, $permission, $userId = null) {
        if (!self::validateSession($userId)) return false;
        $userId = $userId ?? $_SESSION['userid'];

        // Reutiliza la lógica de hasPermission por cada área del usuario (KISS)
        $areas = self::getUserAreas($userId);
        foreach ($areas as $areaRow) {
            $areaKey = $areaRow['AREAKEY'] ?? null;
            if ($areaKey && self::hasPermissionByArea($areaKey, $app, $permission, $userId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Conveniencia: devuelve las AREAKEYs donde el usuario tiene un permiso dado para una app
     * Útil para módulos no conscientes del área que necesiten saber el contexto válido
     * 
     * @param string $app Clave de la aplicación (APPKEY)
     * @param string $permission Clave del permiso (PERMKEY)
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return array Lista de AREAKEYs
     */
    public static function getAreasWithPermission($app, $permission, $userId = null) {
        if (!self::validateSession($userId)) return [];
        $userId = $userId ?? $_SESSION['userid'];

        $areas = self::getUserAreas($userId);
        $allowed = [];
        foreach ($areas as $areaRow) {
            $areaKey = $areaRow['AREAKEY'] ?? null;
            if ($areaKey && self::hasPermissionByArea($areaKey, $app, $permission, $userId)) {
                $allowed[] = $areaKey;
            }
        }
        return $allowed;
    }

    /**
     * Indica si una aplicación está habilitada en un área (CFG_AREAS_APPS.ACTIVE=1)
     * @param string $area AREAKEY
     * @param string $app APPKEY
     * @return bool
     */
    public static function isAppEnabledInArea($area, $app) {
        $sql = "SELECT COUNT(*) as count
                FROM CFG_AREAS_APPS AA
                INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
                INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
                WHERE A.AREAKEY = ? AND APP.APPKEY = ? AND AA.ACTIVE = 1";
        $r = Table::sqlQueryPrepared($sql, [
            self::validateSqlIdentifier($area),
            self::validateSqlIdentifier($app)
        ]);
        return isset($r[0]['count']) && $r[0]['count'] > 0;
    }

    /**
     * Verifica si un permiso existe para una aplicación (CFG_APPS_PERMS)
     * @param string $app APPKEY
     * @param string $permission PERMKEY
     * @return bool
     */
    public static function permissionExistsInApp($app, $permission) {
        $sql = "SELECT COUNT(*) as count
                FROM CFG_APPS_PERMS AP
                INNER JOIN CFG_APPS APP ON AP.ID_APP = APP.ID
                WHERE APP.APPKEY = ? AND AP.PERMKEY = ?";
        $r = Table::sqlQueryPrepared($sql, [
            self::validateSqlIdentifier($app),
            self::validateSqlIdentifier($permission)
        ]);
        return isset($r[0]['count']) && $r[0]['count'] > 0;
    }

    // ===== MÉTODOS DE INSTALACIÓN / SETUP =====

    /**
     * Genera una clave tipo identificador (APPKEY/PERMKEY) a partir de un nombre libre
     * Reglas: minúsculas, alfanumérico y guion bajo, 1..50 chars
     */
    private static function makeKey($name) {
        $key = strtolower($name);
        $key = preg_replace('/[^a-z0-9_]+/i', '_', $key);
        $key = trim($key, '_');
        if ($key === '') $key = 'key_'.substr(md5($name), 0, 6);
        if (strlen($key) > 50) $key = substr($key, 0, 50);
        return self::validateSqlIdentifier($key);
    }

    /**
     * Crea (o actualiza) una aplicación en CFG_APPS. Devuelve ID de la app.
     * Si la app existe por APPKEY, actualiza NAME/ACTIVE; si no, inserta.
     */
    public static function addApp($name, $appKey = null, $active = 1) {
        if (!is_string($name) || $name === '') throw new InvalidArgumentException('Nombre de app inválido');
        $active = intval($active) ? 1 : 0;
        $appKey = $appKey ? self::validateSqlIdentifier($appKey) : self::makeKey($name);

        // Buscar app existente
        $row = Table::sqlQueryPrepared(
            "SELECT ID FROM CFG_APPS WHERE APPKEY = ?",
            [$appKey]
        );
        if (!empty($row)) {
            // Update NAME/ACTIVE por idempotencia (invalidar caché a nivel de tabla)
            Table::sqlQueryPrepared(
                "UPDATE CFG_APPS SET NAME = ?, ACTIVE = ? WHERE ID = ?",
                [$name, $active, intval($row[0]['ID'])]
            );
            return intval($row[0]['ID']);
        }

        // Insertar nueva app
        Table::sqlQueryPrepared(
            "INSERT INTO CFG_APPS (NAME, APPKEY, ACTIVE) VALUES (?,?,?)",
            [$name, $appKey, $active]
        );
        $row2 = Table::sqlQueryPrepared(
            "SELECT ID FROM CFG_APPS WHERE APPKEY = ?",
            [$appKey]
        );
        return !empty($row2) ? intval($row2[0]['ID']) : 0;
    }

    /**
     * Crea (o actualiza) un permiso de app en CFG_APPS_PERMS. Devuelve ID del permiso.
     * Si existe por (APPKEY, PERMKEY), actualiza NAME/DESCRIPTION/BYDEFAULT/ACTIVE; si no, inserta.
     */
    public static function addAppPermission($appKey, $name, $permKey = null, $description = '', $byDefault = 0, $active = 1) {
        if (!is_string($name) || $name === '') throw new InvalidArgumentException('Nombre de permiso inválido');
        $byDefault = intval($byDefault) ? 1 : 0;
        $active = intval($active) ? 1 : 0;
        $appKey = self::validateSqlIdentifier($appKey);
        $permKey = $permKey ? self::validateSqlIdentifier($permKey) : self::makeKey($name);

        // Obtener ID de la app
        $app = Table::sqlQueryPrepared("SELECT ID FROM CFG_APPS WHERE APPKEY = ?", [$appKey]);
        if (empty($app)) throw new InvalidArgumentException("Aplicación no encontrada: $appKey");
        $appId = intval($app[0]['ID']);

        // Existe permiso?
        $perm = Table::sqlQueryPrepared(
            "SELECT P.ID\n             FROM CFG_APPS_PERMS P\n             INNER JOIN CFG_APPS A ON P.ID_APP = A.ID\n             WHERE A.APPKEY = ? AND P.PERMKEY = ?",
            [$appKey, $permKey]
        );
        if (!empty($perm)) {
            Table::sqlQueryPrepared(
                "UPDATE CFG_APPS_PERMS\n                 SET NAME = ?, DESCRIPTION = ?, BYDEFAULT = ?, ACTIVE = ?\n                 WHERE ID = ?",
                [$name, $description, $byDefault, $active, intval($perm[0]['ID'])]
            );
            return intval($perm[0]['ID']);
        }

        // Insertar nuevo permiso
        Table::sqlQueryPrepared(
            "INSERT INTO CFG_APPS_PERMS (ID_APP, NAME, PERMKEY, DESCRIPTION, BYDEFAULT, ACTIVE)\n             VALUES (?,?,?,?,?,?)",
            [$appId, $name, $permKey, $description, $byDefault, $active]
        );
        $row2 = Table::sqlQueryPrepared(
            "SELECT ID FROM CFG_APPS_PERMS WHERE ID_APP = ? AND PERMKEY = ?",
            [$appId, $permKey]
        );
        return !empty($row2) ? intval($row2[0]['ID']) : 0;
    }

    /**
     * Habilita una aplicación en un área (CFG_AREAS_APPS). Devuelve ID del vínculo Área→App.
     * Si existe, actualiza ACTIVE; si no, inserta.
     */
    public static function ensureAreaApp($areaKey, $appKey, $active = 1) {
        $areaKey = self::validateSqlIdentifier($areaKey);
        $appKey  = self::validateSqlIdentifier($appKey);
        $active  = intval($active) ? 1 : 0;

        // IDs
        $area = Table::sqlQueryPrepared("SELECT ID FROM CFG_AREAS WHERE AREAKEY = ?", [$areaKey]);
        if (empty($area)) throw new InvalidArgumentException("Área no encontrada: $areaKey");
        $areaId = intval($area[0]['ID']);

        $app = Table::sqlQueryPrepared("SELECT ID FROM CFG_APPS WHERE APPKEY = ?", [$appKey]);
        if (empty($app)) throw new InvalidArgumentException("Aplicación no encontrada: $appKey");
        $appId = intval($app[0]['ID']);

        // Existe vínculo?
        $row = Table::sqlQueryPrepared(
            "SELECT ID FROM CFG_AREAS_APPS WHERE ID_AREA = ? AND ID_APP = ?",
            [$areaId, $appId]
        );
        if (!empty($row)) {
            Table::sqlQueryPrepared(
                "UPDATE CFG_AREAS_APPS SET ACTIVE = ? WHERE ID = ?",
                [$active, intval($row[0]['ID'])]
            );
            return intval($row[0]['ID']);
        }

        Table::sqlQueryPrepared(
            "INSERT INTO CFG_AREAS_APPS (ID_AREA, ID_APP, ACTIVE) VALUES (?,?,?)",
            [$areaId, $appId, $active]
        );

        $row2 = Table::sqlQueryPrepared(
            "SELECT ID FROM CFG_AREAS_APPS WHERE ID_AREA = ? AND ID_APP = ?",
            [$areaId, $appId]
        );
        return !empty($row2) ? intval($row2[0]['ID']) : 0;
    }

    /**
     * Crea (o actualiza) un Área en CFG_AREAS. Devuelve ID del Área.
     * Si existe por AREAKEY, actualiza NAME/ACTIVE; si no, inserta.
     */
    public static function ensureArea($areaKey, $name, $active = 1) {
        $areaKey = self::validateSqlIdentifier($areaKey);
        if (!is_string($name) || $name === '') throw new InvalidArgumentException('Nombre de área inválido');
        $active  = intval($active) ? 1 : 0;

        $row = Table::sqlQueryPrepared(
            "SELECT ID FROM CFG_AREAS WHERE AREAKEY = ?",
            [$areaKey]
        );
        if (!empty($row)) {
            Table::sqlQueryPrepared(
                "UPDATE CFG_AREAS SET NAME = ?, ACTIVE = ? WHERE ID = ?",
                [$name, $active, intval($row[0]['ID'])]
            );
            return intval($row[0]['ID']);
        }

        Table::sqlQueryPrepared(
            "INSERT INTO CFG_AREAS (NAME, AREAKEY, ACTIVE) VALUES (?,?,?)",
            [$name, $areaKey, $active]
        );
        $row2 = Table::sqlQueryPrepared(
            "SELECT ID FROM CFG_AREAS WHERE AREAKEY = ?",
            [$areaKey]
        );
        return !empty($row2) ? intval($row2[0]['ID']) : 0;
    }

    // ===== MÉTODOS UTILITARIOS =====
    
    /**
     * Verifica si la sesión es válida para operaciones ACL
     * 
     * Esta función valida dos escenarios:
     * 1. Si se proporciona un $userId específico, verifica que sea válido
     * 2. Si no se proporciona $userId, usa el usuario de la sesión actual
     * 
     * @param int|null $userId ID del usuario a validar (null = usar sesión actual)
     * @return bool True si la sesión/usuario es válido
     */
    private static function validateSession($userId = null) {
        // CASO 1: Si $userId tiene un valor (no es null)
        // Ejemplo: validateSession(5) -> $userId = 5
        if ($userId !== null) {  // 5 !== null = TRUE, entra al if
            return is_numeric($userId) && $userId > 0;  // Verifica que 5 sea número y > 0
        }
        
        // CASO 2: Si $userId es null (no se proporcionó)
        // Ejemplo: validateSession() -> $userId = null por defecto
        // null !== null = FALSE, NO entra al if anterior, llega aquí
        
        // Verificar la sesión actual del framework
        // El framework ExtFW requiere estas variables de sesión para estar autenticado
        return isset($_SESSION['valid_user']) && 
               $_SESSION['valid_user'] === true && 
               isset($_SESSION['userid']) && 
               is_numeric($_SESSION['userid']) && 
               $_SESSION['userid'] > 0;
    }
    
    /**
     * Valida que una cadena contenga solo caracteres seguros para SQL
     * (letras, números y guiones bajos)
     * 
     * @param string $string Cadena a validar
     * @return string Cadena validada (lanza excepción si es inválida)
     * @throws InvalidArgumentException Si contiene caracteres no permitidos
     */
    public static function validateSqlIdentifier($string) {
        // Verificar que sea una cadena
        if (!is_string($string)) {
            $string = (string) $string;
        }
        
        // Verificar que solo contenga caracteres alfanuméricos y guiones bajos
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $string)) {
            throw new InvalidArgumentException("Identificador SQL inválido: '$string'. Solo se permiten letras, números y guiones bajos.");
        }
        
        // Verificar que no esté vacío y que no sea demasiado largo
        if (empty($string) || strlen($string) > 50) {
            throw new InvalidArgumentException("Identificador SQL debe tener entre 1 y 50 caracteres: '$string'");
        }
        
        return $string;
    }
   
   
}
