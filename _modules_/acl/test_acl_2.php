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
 * @author ExtFW Framework
 * @version 2.0
 */
class AreasACL {
    
    /**
     * Verifica si un usuario tiene permiso específico en una app/área
     * 
     * @param string $area Clave del área
     * @param string $app Clave de la aplicación  
     * @param string $permission Clave del permiso
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return bool True si tiene el permiso
     */
    public static function hasPermission($area, $app, $permission, $userId = null) {
        if (!self::validateSession($userId)) return false;
        
        $userId = $userId ?? $_SESSION['userid'];
        
        // Verificar permisos directos del usuario
        $hasDirectPermission = self::hasDirectUserPermission($area, $app, $permission, $userId);
        
        // Verificar permisos a través de grupos
        $hasGroupPermission = self::hasGroupPermission($area, $app, $permission, $userId);
        
        return $hasDirectPermission || $hasGroupPermission;
    }
    
    /**
     * Verifica permisos directos del usuario en app/área
     */
    private static function hasDirectUserPermission($area, $app, $permission, $userId) {
        $sql = "SELECT COUNT(*) as count
                FROM CFG_AREAS_APPS_USERS_PERMS AAUP
                INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AAUP.ID_AREA_APP_PERM = AAP.ID
                INNER JOIN CFG_APPS_PERMS AP ON AAP.ID_APP_PERM = AP.ID
                INNER JOIN CFG_AREAS_APPS AA ON AAP.ID_AREA_APP = AA.ID
                INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
                INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
                INNER JOIN CFG_AREAS_APPS_USERS AAU ON AA.ID = AAU.ID_AREA_APP
                WHERE A.AREAKEY = '" . self::escapeString($area) . "' 
                  AND APP.APPKEY = '" . self::escapeString($app) . "' 
                  AND AP.PERMKEY = '" . self::escapeString($permission) . "'
                  AND AAU.ID_USER = " . intval($userId) . "
                  AND AAUP.ACTIVE = 1
                  AND AAP.ACTIVE = 1";
        
        $result = Table::sqlQuery($sql);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }
    
    /**
     * Verifica permisos a través de grupos del usuario
     */
    private static function hasGroupPermission($area, $app, $permission, $userId) {
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
                WHERE A.AREAKEY = '" . self::escapeString($area) . "' 
                  AND APP.APPKEY = '" . self::escapeString($app) . "' 
                  AND AP.PERMKEY = '" . self::escapeString($permission) . "'
                  AND AGU.ID_USER = " . intval($userId) . "
                  AND AAGP.ACTIVE = 1
                  AND AAP.ACTIVE = 1
                  AND AGU.ACTIVE = 1
                  AND AG.ACTIVE = 1";
        
        $result = Table::sqlQuery($sql);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
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
                WHERE A.AREAKEY = '" . self::escapeString($area) . "' 
                  AND AU.ID_USER = " . intval($userId) . " 
                  AND AU.ACTIVE = 1";
        
        $result = Table::sqlQuery($sql);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
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
                WHERE A.AREAKEY = '" . self::escapeString($area) . "' 
                  AND AU.ID_USER = " . intval($userId) . " 
                  AND AU.ADMIN = 1 
                  AND AU.ACTIVE = 1";
        
        $result = Table::sqlQuery($sql);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
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
                WHERE A.AREAKEY = '" . self::escapeString($area) . "' 
                  AND AG.GROUPKEY = '" . self::escapeString($group) . "' 
                  AND AGU.ID_USER = " . intval($userId) . " 
                  AND AGU.ACTIVE = 1 
                  AND AG.ACTIVE = 1";
        
        $result = Table::sqlQuery($sql);
        return isset($result[0]['count']) && $result[0]['count'] > 0;
    }

        
    /**
     * Obtiene todas las áreas del usuario actual
     * 
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return array Array de áreas con ID, NAME, AREAKEY
     */
    public static function getUserAreas($userId = null) {
        if (!self::validateSession($userId)) return [];
        
        $userId = $userId ?? $_SESSION['userid'];
        $cacheKey = "user_areas_{$userId}";
        
        if (isset(self::$cache['areas'][$cacheKey])) {
            return self::$cache['areas'][$cacheKey];
        }
        
        $sql = "SELECT A.ID, A.NAME, A.AREAKEY
                FROM CFG_AREAS A
                INNER JOIN CFG_AREAS_USERS AU ON A.ID = AU.ID_AREA
                WHERE AU.ID_USER = ? AND AU.ACTIVE = 1
                ORDER BY A.NAME";
        
        $areas = self::sqlQueryPrepared($sql, [$userId]);
        self::$cache['areas'][$cacheKey] = $areas;
        
        return $areas;
    }
    
    /**
     * Obtiene todas las aplicaciones disponibles en un área
     * 
     * @param string $area Clave del área
     * @param bool $onlyUserApps Si true, solo apps donde el usuario tiene acceso
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return array Array de aplicaciones
     */
    public static function getAreaApps($area, $onlyUserApps = false, $userId = null) {
        $cacheKey = "area_apps_{$area}_" . ($onlyUserApps ? 'user' : 'all') . "_{$userId}";
        
        if (isset(self::$cache['apps'][$cacheKey])) {
            return self::$cache['apps'][$cacheKey];
        }
        
        $sql = "SELECT AA.ID, AA.ID_AREA, AA.ID_APP, A.APPKEY, A.NAME as APP_NAME
                FROM CFG_AREAS_APPS AA
                INNER JOIN CFG_APPS A ON AA.ID_APP = A.ID
                INNER JOIN CFG_AREAS AR ON AA.ID_AREA = AR.ID
                WHERE AR.AREAKEY = ? AND AA.ACTIVE = 1";
        
        $params = [$area];
        
        if ($onlyUserApps && $userId) {
            $sql .= " AND AA.ID_AREA IN (
                        SELECT AU.ID_AREA FROM CFG_AREAS_USERS AU 
                        WHERE AU.ID_USER = ? AND AU.ACTIVE = 1
                      )";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY A.NAME";
        
        $apps = self::sqlQueryPrepared($sql, $params);
        self::$cache['apps'][$cacheKey] = $apps;
        
        return $apps;
    }
    
    /**
     * Obtiene usuarios de un área específica
     * 
     * @param string $area Clave del área
     * @param bool $onlyAdmins Si true, solo administradores
     * @return array Array de usuarios con ID, USER, NAME
     */
    public static function getAreaUsers($area, $onlyAdmins = false) {
        $cacheKey = "area_users_{$area}_" . ($onlyAdmins ? 'admins' : 'all');
        
        if (isset(self::$cache['users'][$cacheKey])) {
            return self::$cache['users'][$cacheKey];
        }
        
        $sql = "SELECT CU.user_id AS ID, CU.username AS USER, CU.user_fullname AS NAME,
                       AU.ADMIN, AU.ACTIVE
                FROM CLI_USER CU
                INNER JOIN CFG_AREAS_USERS AU ON CU.user_id = AU.ID_USER
                INNER JOIN CFG_AREAS A ON AU.ID_AREA = A.ID
                WHERE A.AREAKEY = ? AND AU.ACTIVE = 1";
        
        if ($onlyAdmins) {
            $sql .= " AND AU.ADMIN = 1";
        }
        
        $sql .= " ORDER BY CU.user_fullname";
        
        $users = self::sqlQueryPrepared($sql, [$area]);
        self::$cache['users'][$cacheKey] = $users;
        
        return $users;
    }
    
    /**
     * Obtiene usuarios de un grupo específico en un área
     * 
     * @param string $area Clave del área
     * @param string $group Clave del grupo
     * @return array Array de usuarios
     */
    public static function getAreaGroupUsers($area, $group) {
        $cacheKey = "group_users_{$area}_{$group}";
        
        if (isset(self::$cache['groups'][$cacheKey])) {
            return self::$cache['groups'][$cacheKey];
        }
        
        $sql = "SELECT CU.user_id AS ID, CU.username AS USER, CU.user_fullname AS NAME
                FROM CLI_USER CU
                INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON CU.user_id = AGU.ID_USER
                INNER JOIN CFG_AREAS_GROUPS AG ON AGU.ID_AREA_GROUP = AG.ID
                INNER JOIN CFG_AREAS A ON AG.ID_AREA = A.ID
                WHERE A.AREAKEY = ? 
                  AND AG.GROUPKEY = ? 
                  AND AGU.ACTIVE = 1 
                  AND AG.ACTIVE = 1
                ORDER BY CU.user_fullname";
        
        $users = self::sqlQueryPrepared($sql, [$area, $group]);
        self::$cache['groups'][$cacheKey] = $users;
        
        return $users;
    }
    
    /**
     * Obtiene los permisos de un usuario para una app/área específica
     * 
     * @param string $area Clave del área
     * @param string $app Clave de la aplicación
     * @param int|null $userId ID del usuario (null = usuario actual)
     * @return array Array de permisos con PERMKEY, NAME, SOURCE (direct/group)
     */
    public static function getUserPermissions($area, $app, $userId = null) {
        if (!self::validateSession($userId)) return [];
        
        $userId = $userId ?? $_SESSION['userid'];
        $cacheKey = "user_perms_{$userId}_{$area}_{$app}";
        
        if (isset(self::$cache['permissions'][$cacheKey])) {
            return self::$cache['permissions'][$cacheKey];
        }
        
        // Permisos directos
        $directPerms = self::sqlQueryPrepared("
            SELECT DISTINCT AP.PERMKEY, AP.NAME, 'direct' as SOURCE
            FROM CFG_AREAS_APPS_USERS_PERMS AAUP
            INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AAUP.ID_AREA_APP_PERM = AAP.ID
            INNER JOIN CFG_APPS_PERMS AP ON AAP.ID_APP_PERM = AP.ID
            INNER JOIN CFG_AREAS_APPS AA ON AAP.ID_AREA_APP = AA.ID
            INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
            INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
            INNER JOIN CFG_AREAS_APPS_USERS AAU ON AA.ID = AAU.ID_AREA_APP
            WHERE A.AREAKEY = ? AND APP.APPKEY = ? AND AAU.ID_USER = ?
              AND AAUP.ACTIVE = 1 AND AAP.ACTIVE = 1
            ORDER BY AP.NAME
        ", [$area, $app, $userId]);
        
        // Permisos por grupos
        $groupPerms = self::sqlQueryPrepared("
            SELECT DISTINCT AP.PERMKEY, AP.NAME, AG.GROUPKEY as SOURCE
            FROM CFG_AREAS_APPS_GROUPS_PERMS AAGP
            INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AAGP.ID_AREA_APP_PERM = AAP.ID
            INNER JOIN CFG_APPS_PERMS AP ON AAP.ID_APP_PERM = AP.ID
            INNER JOIN CFG_AREAS_APPS_GROUPS AAG ON AAGP.ID_AREA_APP_GROUP = AAG.ID
            INNER JOIN CFG_AREAS_APPS AA ON AAG.ID_AREA_APP = AA.ID
            INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
            INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
            INNER JOIN CFG_AREAS_GROUPS AG ON AAG.ID_GROUP = AG.ID
            INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON AG.ID = AGU.ID_AREA_GROUP
            WHERE A.AREAKEY = ? AND APP.APPKEY = ? AND AGU.ID_USER = ?
              AND AAGP.ACTIVE = 1 AND AAP.ACTIVE = 1 AND AGU.ACTIVE = 1 AND AG.ACTIVE = 1
            ORDER BY AP.NAME
        ", [$area, $app, $userId]);
        
        $allPerms = array_merge($directPerms, $groupPerms);
        self::$cache['permissions'][$cacheKey] = $allPerms;
        
        return $allPerms;
    }
    
    /**
     * Busca usuarios con un permiso específico en un área/app
     * 
     * @param string $area Clave del área
     * @param string $app Clave de la aplicación
     * @param string $permission Clave del permiso
     * @return array Array de usuarios que tienen el permiso
     */
    public static function getUsersWithPermission($area, $app, $permission) {
        $cacheKey = "users_with_perm_{$area}_{$app}_{$permission}";
        
        if (isset(self::$cache['permissions'][$cacheKey])) {
            return self::$cache['permissions'][$cacheKey];
        }
        
        // Usuarios con permiso directo
        $directUsers = self::sqlQueryPrepared("
            SELECT DISTINCT CU.user_id AS ID, CU.username AS USER, CU.user_fullname AS NAME, 'direct' as SOURCE
            FROM CLI_USER CU
            INNER JOIN CFG_AREAS_APPS_USERS AAU ON CU.user_id = AAU.ID_USER
            INNER JOIN CFG_AREAS_APPS_USERS_PERMS AAUP ON AAU.ID = AAUP.ID_AREA_APP_USER
            INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AAUP.ID_AREA_APP_PERM = AAP.ID
            INNER JOIN CFG_APPS_PERMS AP ON AAP.ID_APP_PERM = AP.ID
            INNER JOIN CFG_AREAS_APPS AA ON AAP.ID_AREA_APP = AA.ID
            INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
            INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
            WHERE A.AREAKEY = ? AND APP.APPKEY = ? AND AP.PERMKEY = ?
              AND AAUP.ACTIVE = 1 AND AAP.ACTIVE = 1
            ORDER BY CU.user_fullname
        ", [$area, $app, $permission]);
        
        // Usuarios con permiso por grupo
        $groupUsers = self::sqlQueryPrepared("
            SELECT DISTINCT CU.user_id AS ID, CU.username AS USER, CU.user_fullname AS NAME, AG.GROUPKEY as SOURCE
            FROM CLI_USER CU
            INNER JOIN CFG_AREAS_GROUPS_USERS AGU ON CU.user_id = AGU.ID_USER
            INNER JOIN CFG_AREAS_GROUPS AG ON AGU.ID_AREA_GROUP = AG.ID
            INNER JOIN CFG_AREAS_APPS_GROUPS AAG ON AG.ID = AAG.ID_GROUP
            INNER JOIN CFG_AREAS_APPS_GROUPS_PERMS AAGP ON AAG.ID = AAGP.ID_AREA_APP_GROUP
            INNER JOIN CFG_AREAS_APPS_PERMS AAP ON AAGP.ID_AREA_APP_PERM = AAP.ID
            INNER JOIN CFG_APPS_PERMS AP ON AAP.ID_APP_PERM = AP.ID
            INNER JOIN CFG_AREAS_APPS AA ON AAP.ID_AREA_APP = AA.ID
            INNER JOIN CFG_AREAS A ON AA.ID_AREA = A.ID
            INNER JOIN CFG_APPS APP ON AA.ID_APP = APP.ID
            WHERE A.AREAKEY = ? AND APP.APPKEY = ? AND AP.PERMKEY = ?
              AND AAGP.ACTIVE = 1 AND AAP.ACTIVE = 1 AND AGU.ACTIVE = 1 AND AG.ACTIVE = 1
            ORDER BY CU.user_fullname
        ", [$area, $app, $permission]);
        
        $allUsers = array_merge($directUsers, $groupUsers);
        
        // Eliminar duplicados manteniendo la fuente más específica (direct > group)
        $uniqueUsers = [];
        foreach ($allUsers as $user) {
            $key = $user['ID'];
            if (!isset($uniqueUsers[$key]) || $user['SOURCE'] === 'direct') {
                $uniqueUsers[$key] = $user;
            }
        }
        
        $result = array_values($uniqueUsers);
        self::$cache['permissions'][$cacheKey] = $result;
        
        return $result;
    }
    
    /**
     * Obtiene administradores de un área específica
     * 
     * @param string $area Clave del área
     * @return array Array de administradores
     */
    public static function getAreaAdmins($area) {
        return self::getAreaUsers($area, true);
    }
    
    /**
     * Verifica si la sesión es válida para operaciones ACL
     * 
     * @param int|null $userId ID del usuario a validar
     * @return bool True si la sesión es válida
     */
    private static function validateSession($userId = null) {
        if ($userId !== null) {
            return $userId > 0;
        }
        
        return isset($_SESSION['valid_user']) && 
               $_SESSION['valid_user'] && 
               isset($_SESSION['userid']) && 
               $_SESSION['userid'] > 0;
    }
    
    /**
     * Ejecuta una consulta SQL preparada de forma segura
     * 
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para la consulta
     * @return array Resultado de la consulta
     */
    private static function sqlQueryPrepared($sql, $params = []) {
        // Esta función debería usar la funcionalidad de prepared statements
        // Por ahora, usamos el método existente con escape manual
        foreach ($params as $i => $param) {
            if (is_string($param)) {
                $params[$i] = "'" . self::escapeString($param) . "'";
            } elseif (is_null($param)) {
                $params[$i] = 'NULL';
            }
        }
        
        // Reemplazar ? con los parámetros escapados
        $finalSql = $sql;
        foreach ($params as $param) {
            $finalSql = preg_replace('/\?/', $param, $finalSql, 1);
        }
        
        return self::sqlQuery($finalSql);
    }
    
    /**
     * Escapa una cadena para usar en SQL (implementación básica)
     * 
     * @param string $string Cadena a escapar
     * @return string Cadena escapada
     */
    private static function escapeString($string) {
        // Implementación básica - en producción usar funciones específicas del SGBD
        return str_replace(["'", '"', '\\'], ["''", '""', '\\\\'], $string);
    }
    
    /**
     * Limpia el cache de ACL
     * 
     * @param string|null $type Tipo de cache a limpiar (null = todo)
     */
    public static function clearCache($type = null) {
        if ($type === null) {
            self::$cache = [
                'areas' => [],
                'users' => [],
                'groups' => [],
                'permissions' => [],
                'apps' => []
            ];
        } elseif (isset(self::$cache[$type])) {
            self::$cache[$type] = [];
        }
    }
    
    // Métodos de compatibilidad con la API anterior (DEPRECATED)
    
    /**
     * @deprecated Usar getUserAreas() en su lugar
     */
    public static function getAreasUser() {
        return self::getUserAreas();
    }
    
    /**
     * @deprecated Usar userInArea() en su lugar  
     */
    public static function userInArea($area) {
        return self::userInArea($area);
    }
    
    /**
     * @deprecated Usar isAreaAdmin() en su lugar
     */
    public static function adminInArea($area) {
        return self::isAreaAdmin($area);
    }
    
    /**
     * @deprecated Usar userInAreaGroup() en su lugar
     */
    public static function userInAreaAndGroup($area, $group) {
        return self::userInAreaGroup($area, $group);
    }
    
    /**
     * @deprecated Usar getAreaUsers() en su lugar
     */
    public static function getUsersInArea($area) {
        return self::getAreaUsers($area);
    }
    
    /**
     * @deprecated Usar getAreaAdmins() en su lugar
     */
    public static function getAdminsInArea($area) {
        return self::getAreaAdmins($area);
    }
    
    /**
     * @deprecated Usar getAreaGroupUsers() en su lugar
     */
    public static function getUsersInAreaAndGroup($area, $group) {
        return self::getAreaGroupUsers($area, $group);
    }
    
    /**
     * @deprecated Usar getAreaApps() en su lugar
     */
    public static function getAppsInArea($area, $user = false) {
        return self::getAreaApps($area, $user, $user ? $_SESSION['userid'] : null);
    }

}












/*
height:100dvh  /altura dinamic
aspect-ratio:16/9
object-fit: cover;
object-position:0  20%


mix-blend-mode: difference

*/