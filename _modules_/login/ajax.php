<?php
// CFG::$vars['site']['lpd_accept']['required']=true;

// Parsear JSON body si viene como application/json
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $jsonBody = file_get_contents('php://input');
    $jsonData = json_decode($jsonBody, true);
    if ($jsonData) {
        $_ARGS = array_merge($_ARGS ?? [], $jsonData);
    }
}

$op = $_ARGS['op'] ?? '';

if       ($op == 'randompassword') {
    
    sleep(1);  // pausa de 1 sg. pa que se vea el gif animao!
    $result = array();
    $length   = $_ARGS['length'] ? $_ARGS['length'] : 8;
    $strength = $_ARGS['strength'] ? $_ARGS['strength'] : 0;
    $result['randompassword'] =  Str::password($length, $strength);
    echo json_encode($result);

}else if ($op == 'sync-ldap') {

   // sleep(2);  // pausa de 1 sg. pa que se vea el gif animao!
    $result = array();
    if (CFG::$vars['auth']=='ldap') {     
    
        $ajax_ldap = new AuthLDAP(); 
        if($ajax_ldap->connected()) {
    

            $rPerms = $_ACL->perms;
            $aPerms = $_ACL->getAllPerms('full');
            $aRoles = $_ACL->getAllRoles('full');

            /*
            $html_roles  = '<thead>';
            $html_roles .= '<tr><th colspan="2">'.t('Roles').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
            $html_roles .= '</thead>';
            $html_roles .= '<tbody>';
            foreach ($aRoles as $k => $v){
              //if ( $_ACL->userHasRole($v['ID'])) {  
                  $html_roles .= '<tr><td>'.$v['Name'].'</td><td>'.($_ACL->userHasRole($v['ID'])?'<span style="color:green;">&#9745;</span>':'<span style="color:red;">&#9746;</span>').'</td></tr>'; 
              //}
            }
            $html_roles .= '</tbody>';

            $html_perms .= '<thead>';
            $html_perms .= '<tr><th colspan="2">'.t('Permisos').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
            $html_perms .= '<tr><th>'.t('Permiso').'</th><th>'.t('Valor').'</th></tr>';
            $html_perms .= '</thead>';
            $html_perms .= '<tbody>';
            foreach ($aPerms as $k => $v){
              $allow = false; 
              $allowhtml = '';
              if ($_ACL->hasPermission($v['Key']) && $rPerms[$v['Key']]['inherited'] != true){
                $allow = true;
                $allowhtml .= '<span style="color:green;">Allow</span>';
              }
              if ($rPerms[$v['Key']]['value'] === false && $rPerms[$v['Key']]['inherited'] != true) {
                $allowhtml .= '<span style="color:red;">Deny</span>';
              }
              if ($rPerms[$v['Key']]['inherited'] == true || !array_key_exists($v['Key'],$rPerms)){
                if  ($rPerms[$v['Key']]['value'] === true ) $allow = true;
                $iVal = ($rPerms[$v['Key']]['value'] === true ) ? '<span style="color:green;">(Allow)</span>'  : '<span style="color:red;">(Deny)</span>';
                $allowhtml .= '<span style="color:#777;">Inherit</span> '.$iVal;
              }
              if($allow){
                $html_perms .= '<tr><td>' . $v['ID'] .' - '. $v['Name'] . '</td><td>'.$allowhtml.'</td></tr>';
              }
            }
            $html_perms .= '</tbody>';
            */ 



            $html_roles  = '<thead>';
            $html_roles .= '<tr><th>'.t('Roles').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
            $html_roles .= '</thead>';
            $html_roles .= '<tbody>';
            foreach ($aRoles as $k => $v){
              if ( $_ACL->userHasRole($v['ID'])) {  
                  $html_roles .= '<tr><td>'.$v['Name'].'</td></tr>'; 
              }
            }
            $html_roles .= '</tbody>';

            $html_perms .= '<thead>';
            $html_perms .= '<tr><th colspan="2">'.t('Permisos').'</th></tr>';  // <th class="thc">'.t('Miembro').'</th>
            $html_perms .= '</thead>';
            $html_perms .= '<tbody>';
            foreach ($aPerms as $k => $v){
              $allow = false; 
              $allowhtml = '';
              if ($_ACL->hasPermission($v['Key']) && $rPerms[$v['Key']]['inherited'] != true){
                $allow = true;
                $allowhtml .= '<span style="color:green;">Allow</span>';
              }
              if ($rPerms[$v['Key']]['value'] === false && $rPerms[$v['Key']]['inherited'] != true) {
                $allowhtml .= '<span style="color:red;">Deny</span>';
              }
              if ($rPerms[$v['Key']]['inherited'] == true || !array_key_exists($v['Key'],$rPerms)){
                if  ($rPerms[$v['Key']]['value'] === true ) $allow = true;
                $iVal = ($rPerms[$v['Key']]['value'] === true ) ? '<span style="color:green;">(Allow)</span>'  : '<span style="color:red;">(Deny)</span>';
                $allowhtml .= '<span style="color:#777;">Inherit</span> '.$iVal;
              }
              if($allow){
                $html_perms .= '<tr><td>' ./* $v['ID'] .' - '.*/ $v['Name'] . '</td><td>'.$allowhtml.'</td></tr>';
              }
            }
            $html_perms .= '</tbody>';





            $result['roles'] = $html_roles;
            $result['perms'] = $html_perms;

            $result['email']           = $ajax_ldap->get_value($_SESSION['username'],'mail');
            $result['sAMAccountName']  = $ajax_ldap->get_value($_SESSION['username'],'sAMAccountName');
            $result['givenname']       = $ajax_ldap->get_value($_SESSION['username'],'givenname');
            $result['lastlogon']       = AuthLDAP::ldapTimeToDate($ajax_ldap->get_value($_SESSION['username'],'lastLogonTimestamp'));
            $result['department']      = $ajax_ldap->get_value($_SESSION['username'],'department');
            $result['telephonenumber'] = $ajax_ldap->get_value($_SESSION['username'],'telephonenumber');
            $groups = $ajax_ldap->get_user_groups($_SESSION['username']);
            /*
            echo '<pre>';
            echo '        username: ' . $result['sAMAccountName'] . "\n";
            echo '           email: ' . $result['email'] . "\n";
            echo '      Given Name: ' . $result['givenname'] . "\n";
            echo '       LASTlogon: ' . $result['lastlogon'] . "\n";
            echo '      Department: ' . $result['department'] . "\n";
            echo 'Telephone Number: ' . $result['telephonenumber'] . "\n";
            echo 'Groups'."\n";
            print_r($groups);
            echo '</pre>';
            */
            Login::LDAP_sincronizeRoles($groups);
            Login::LDAP_sincronizeMyRoles($groups);
            unset($_SESSION['ACL']) ;
            $_ACL = new ACL();
        }else{
            $result['msg'] = 'LDAP not connected';
        }
    }else{
        $result['msg'] = 'No LADAP Auth';
    }

    echo json_encode($result);


// ============================================================================
// PASSWORDLESS AUTHENTICATION
// ============================================================================

}else if ($op == 'passwordless_challenge') {
    // Solicitar challenge para login passwordless
    header('Content-Type: application/json');
    
    $identifier = $_ARGS['identifier'] ?? $_ARGS['email'] ?? '';
    
    if (empty($identifier)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_identifier', 'msg' => 'Falta email o username']);
        exit;
    }

    //No hace falta esto. tenmos autolaoder
    //include_once(SCRIPT_DIR_CLASSES . '/passwordless.class.php');
    $auth = new PasswordlessAuth();
    $user = $auth->findUser($identifier);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'user_not_found', 'msg' => __LINE__ . ' Usuario ['.$identifier.'] no encontrado']);
        exit;
    }

    // Verificar si el usuario tiene dispositivos autorizados
    if (!$auth->userHasKeys($user['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'no_keys',
            'msg' => 'Este usuario no tiene login sin contraseña configurado. Debe hacer login con contraseña primero, y después configurar sus claves públicas en la sección de configuración de su cuenta.',
            'requires_password' => true
        ]);
        exit;
    }

    $challenge = $auth->generateChallenge($user['user_id'], get_ip());
    
    echo json_encode([
        'success' => true,
        'challenge' => $challenge['challenge'],
        'expires_in' => $challenge['expires_in'],
        'user_id' => $user['user_id']
    ]);

}else if ($op == 'passwordless_verify') {
    // Verificar firma y hacer login
    header('Content-Type: application/json');

    try {
        $challenge = $_ARGS['challenge'] ?? '';
        $signature = $_ARGS['signature'] ?? '';
        $identifier = $_ARGS['identifier'] ?? '';
        $deviceId = $_ARGS['deviceId'] ?? '';

        if (empty($challenge) || empty($signature) || empty($identifier) || empty($deviceId)) {
            http_response_code(400);
            echo json_encode(['error' => 'missing_fields', 'msg' => 'Faltan campos requeridos']);
            exit;
        }

        //include_once(SCRIPT_DIR_CLASSES . '/passwordless.class.php');
        $auth = new PasswordlessAuth();
        $user = $auth->findUser($identifier);

        if (!$user) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_user', 'msg' => 'Usuario no encontrado']);
            exit;
        }

        // Obtener la clave pública del dispositivo específico
        $deviceKey = $auth->getDeviceKey($user['user_id'], $deviceId);

        // DEBUG: Log para diagnosticar problema de claves
        error_log("=== PASSWORDLESS_VERIFY DEBUG ===");
        error_log("user_id: " . $user['user_id'] . " (type: " . gettype($user['user_id']) . ")");
        error_log("deviceId from request: " . $deviceId);
        error_log("deviceKey found: " . ($deviceKey ? 'YES' : 'NO'));
        if ($deviceKey) {
            error_log("deviceKey device_id: " . ($deviceKey['device_id'] ?? 'N/A'));
            error_log("deviceKey sign_public_key (first 50 chars): " . substr($deviceKey['sign_public_key'] ?? '', 0, 50));
        }

        if (!$deviceKey) {
            http_response_code(400);
            echo json_encode([
                'error' => 'device_not_found',
                'msg' => 'Este dispositivo no está autorizado. Configura login sin contraseña desde tu cuenta.',
                'debug' => [
                    'user_id' => $user['user_id'],
                    'deviceId_requested' => $deviceId
                ]
            ]);
            exit;
        }

        // DEBUG: Log antes de verificar
        error_log("challenge: " . substr($challenge, 0, 50) . "...");
        error_log("signature (first 50 chars): " . substr($signature, 0, 50));

        // Verificar la firma con la clave pública del dispositivo
        $result = $auth->verifyChallenge($challenge, $signature, $deviceKey['sign_public_key'], get_ip());

        // DEBUG: Log del resultado
        error_log("verifyChallenge result: " . print_r($result, true));
        error_log("=== END DEBUG ===");

        if (!$result || !$result['valid']) {
            http_response_code(401);
            echo json_encode([
                'error' => $result['error'] ?? 'signature_invalid',
                'msg' => $result['msg'] ?? 'Firma inválida o challenge expirado',
                'debug' => [
                    'challenge_valid' => isset($result['error']) && $result['error'] !== 'challenge_invalid',
                    'user_id' => $user['user_id'],
                    'device_id' => $deviceId
                ]
            ]);
            exit;
        }

        // Actualizar last_used_at del dispositivo
        $keyId = $deviceKey['ID'] ?? $deviceKey['id'] ?? null;
        if ($deviceKey && $keyId) {
            $auth->updateLastUsed($keyId);
        }

        // ¡Login exitoso! Configurar sesión
        $_SESSION['valid_user']     = true;
        $_SESSION['userid']         = $user['user_id'];
        $_SESSION['userlevel']      = $user['user_level'];
        $_SESSION['username']       = $user['username'];
        $_SESSION['user_fullname']  = $user['user_fullname'];
        $_SESSION['user_email']     = $user['user_email'];
        $_SESSION['user_score']     = $user['user_score'];
        $_SESSION['user_url_avatar']= $user['user_url_avatar'];
        $_SESSION['auth_provider']  = 'passwordless';
    
        echo json_encode([
            'success' => true,
            'msg' => sprintf('¡Bienvenido %s!', $user['user_fullname'] ?: $user['username']),
            'redirect' => $_SESSION['backurl'] ?: '/'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'exception',
            'msg' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

}else if ($op == 'update_user_keys') {
    // Registrar nuevo dispositivo con sus claves públicas (requiere estar logueado)
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        http_response_code(401);
        echo json_encode(['error' => 'not_logged_in', 'msg' => 'Debes iniciar sesión primero']);
        exit;
    }

    $deviceId = $_ARGS['deviceId'] ?? '';
    $signPubKey = $_ARGS['signPub'] ?? '';
    $encPubKey = $_ARGS['encPub'] ?? '';
    $deviceName = $_ARGS['deviceName'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (empty($deviceId) || empty($signPubKey) || empty($encPubKey)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_keys', 'msg' => 'Faltan deviceId o claves públicas']);
        exit;
    }

    //include_once(SCRIPT_DIR_CLASSES . '/passwordless.class.php');
    try {
        $auth = new PasswordlessAuth();
        $keyId = $auth->addUserKeys($_SESSION['userid'], $deviceId, $signPubKey, $encPubKey, $deviceName, $userAgent);

        if ($keyId) {
            echo json_encode(['success' => true, 'msg' => 'Dispositivo registrado. Ya puedes usar login sin contraseña.', 'key_id' => $keyId]);
        } else {
            $error = $auth->getError();
            http_response_code(500);
            echo json_encode(['error' => 'update_failed', 'msg' => 'Error al guardar las claves', 'debug' => $error]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'exception', 'msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }

}else if ($op == 'get_current_user_id') {
    // Devolver el userId del usuario actualmente logueado
    // Necesario para soporte multi-usuario en IndexedDB
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        echo json_encode(['userId' => null, 'logged_in' => false]);
        exit;
    }

    echo json_encode(['userId' => (int)$_SESSION['userid'], 'logged_in' => true]);

}else if ($op == 'get_user_has_keys') {
    // Verificar si el usuario actual tiene claves configuradas
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        echo json_encode(['has_keys' => false, 'logged_in' => false]);
        exit;
    }

    // include_once(SCRIPT_DIR_CLASSES . '/passwordless.class.php');
    $auth = new PasswordlessAuth();
    $hasKeys = $auth->userHasKeys($_SESSION['userid']);

    echo json_encode(['has_keys' => $hasKeys, 'logged_in' => true]);

}else if ($op == 'revoke_device') {
    // Revocar (desactivar) un dispositivo específico
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        http_response_code(401);
        echo json_encode(['error' => 'not_logged_in', 'msg' => 'No estás autenticado']);
        exit;
    }

    $deviceId = $_ARGS['deviceId'] ?? '';

    if (empty($deviceId)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_device_id', 'msg' => 'Falta el identificador del dispositivo']);
        exit;
    }

    $auth = new PasswordlessAuth();
    $success = $auth->revokeDeviceByUUID($deviceId, $_SESSION['userid']);

    if ($success) {
        echo json_encode(['success' => true, 'msg' => 'Dispositivo revocado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'revoke_failed', 'msg' => 'Error al revocar el dispositivo']);
    }

// ============================================================================
// PASSWORDLESS REGISTRATION - Registro directo sin contraseña
// ============================================================================

}else if ($op == 'register_passwordless') {    // Registrar nuevo usuario con passwordless (sin contraseña, sin N0str)

    // Parsear JSON body si viene como application/json
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $jsonBody = file_get_contents('php://input');
        $jsonData = json_decode($jsonBody, true);
        if ($jsonData) {
            $_ARGS = array_merge($_ARGS ?? [], $jsonData);
        }
    }

    header('Content-Type: application/json');
                                          
    $data['user_email'] = trim($_ARGS['email']) ?? '';
    $data['deviceId']   = $_ARGS['deviceId']    ?? '';
    $data['signPub']    = $_ARGS['signPub']     ?? '';
    $data['encPub']     = $_ARGS['encPub']      ?? '';
    $data['deviceName'] = $_ARGS['deviceName']  ?? 'Unknown';
    $data['userAgent']  = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $data['invitation_code'] = $_ARGS['invitation_code'] ?? '';
    // Validar campos
    if (empty($data['user_email']) || empty($data['deviceId']) || empty($data['signPub']) || empty($data['encPub'])) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_fields', 'msg' => 'Missing required fields']);
        exit;
    }
    $login->register_passwordless($data);

// ============================================================================
// DEVICE LINKING - Vincular nuevo dispositivo con token
// ============================================================================

}else if ($op == 'generate_device_link_token') {
    // Generar token para vincular un nuevo dispositivo
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        http_response_code(401);
        echo json_encode(['error' => 'not_logged_in', 'msg' => 'Debes iniciar sesión']);
        exit;
    }

    // Verificar que el usuario tiene passwordless configurado
    $auth = new PasswordlessAuth();
    if (!$auth->userHasKeys($_SESSION['userid'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'no_passwordless',
            'msg' => 'Debes configurar login sin contraseña primero antes de vincular dispositivos'
        ]);
        exit;
    }

    // Generar token único (16 bytes = 32 chars hex)
    $token = bin2hex(random_bytes(16));
    $userId = (int)$_SESSION['userid'];
    $now = time();
    $expiresAt = $now + 900; // 15 minutos
    $ip = get_ip();

    // Guardar token en CLI_USER (columna device_link_token y device_link_expires)
    // KISS: guardamos directamente en CLI_USER, no creamos tabla nueva
    $sql = "UPDATE " . TB_USER . "
            SET device_link_token = ?,
                device_link_expires = ?
            WHERE user_id = ?";

    $result = Table::sqlQueryPrepared($sql, [$token, $expiresAt, $userId]);

    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'token' => $token,
            'expires_in' => 900,
            'expires_at' => $expiresAt,
            'msg' => 'Token generado. Válido por 15 minutos.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'generation_failed', 'msg' => 'Error al generar token']);
    }

}else if ($op == 'verify_device_link_token') {
    // Verificar token y vincular nuevo dispositivo
    header('Content-Type: application/json');

    // Parsear JSON body si viene como application/json
    if (empty($_ARGS['token']) && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $jsonBody = file_get_contents('php://input');
        $jsonData = json_decode($jsonBody, true);
        if ($jsonData) {
            $_ARGS = array_merge($_ARGS, $jsonData);
        }
    }

    $token      = $_ARGS['token'] ?? '';
    $identifier = $_ARGS['identifier'] ?? ''; // email o username
    $deviceId   = $_ARGS['deviceId'] ?? '';
    $signPub    = $_ARGS['signPub'] ?? '';
    $encPub     = $_ARGS['encPub'] ?? '';
    $deviceName = $_ARGS['deviceName'] ?? '';
    $userAgent  = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (empty($token) || empty($identifier) || empty($deviceId) || empty($signPub) || empty($encPub)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_fields', 'msg' => 'Faltan campos requeridos']);
        exit;
    }

    // Buscar usuario por token Y por identifier (doble verificación)
    $sql = "SELECT user_id, username, user_email, user_fullname, user_level, device_link_token, device_link_expires, user_url_avatar, user_score
            FROM " . TB_USER . "
            WHERE device_link_token = ?
            AND (user_email = ? OR username = ?)
            AND device_link_expires > ?
            LIMIT 1";

    $result = Table::sqlQueryPrepared($sql, [$token, $identifier, $identifier, time()]);

    if (empty($result)) {
        http_response_code(401);
        echo json_encode([
            'error' => 'invalid_token',
            'msg' => 'Token inválido, expirado o no corresponde a este usuario'
        ]);
        exit;
    }

    $user = $result[0];

    // Token válido! Registrar las claves del nuevo dispositivo
    $auth = new PasswordlessAuth();
    $keyId = $auth->addUserKeys($user['user_id'], $deviceId, $signPub, $encPub, $deviceName, $userAgent);

    if (!$keyId) {
        http_response_code(500);
        echo json_encode(['error' => 'key_registration_failed', 'msg' => 'Error al registrar claves']);
        exit;
    }

    // Invalidar el token (ya fue usado)
    $sql = "UPDATE " . TB_USER . "
            SET device_link_token = NULL,
                device_link_expires = NULL
            WHERE user_id = ?";
    Table::sqlQueryPrepared($sql, [$user['user_id']]);

    // Crear sesión para el usuario
    $_SESSION['valid_user']      = true;
    $_SESSION['userid']          = $user['user_id'];
    $_SESSION['userlevel']       = $user['user_level'];
    $_SESSION['username']        = $user['username'];
    $_SESSION['user_fullname']   = $user['user_fullname'];
    $_SESSION['user_email']      = $user['user_email'];
    $_SESSION['user_score']      = $user['user_score'];
    $_SESSION['user_url_avatar'] = $user['user_url_avatar'];    
    $_SESSION['auth_provider']   = 'device_link';

    Login::updateLastLogin($user['user_id']);

    echo json_encode([
        'success' => true,
        'user_id' => (int)$user['user_id'],
        'msg' => '¡Dispositivo vinculado correctamente! Ya puedes usar login sin contraseña en este dispositivo.',
        'redirect' => $_SESSION['backurl'] ?: '/'
    ]);

// ============================================================================
// MAGIC LINK - Recuperación de acceso
// ============================================================================

}else if ($op == 'request_magic_link') {
    // Solicitar magic link para recuperación de acceso
    header('Content-Type: application/json');

    $email = trim($_ARGS['email'] ?? '');

    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_email', 'msg' => 'Email no válido']);
        exit;
    }

    // Buscar usuario por email
    $sql = "SELECT user_id, username, user_email, user_fullname FROM " . TB_USER . " WHERE user_email = ? LIMIT 1";
    $result = Table::sqlQueryPrepared($sql, [$email]);

    if (empty($result)) {
        // Por seguridad, no revelamos si el email existe o no
        // Respondemos siempre igual para evitar enumerar usuarios
        echo json_encode([
            'success' => true,
            'msg' => 'Si el email existe en nuestro sistema, recibirás un enlace de acceso temporal.'
        ]);
        exit;
    }

    $user = $result[0];
    $userId = (int)$user['user_id'];

    // Generar token único (32 caracteres hexadecimales)
    $token = bin2hex(random_bytes(16));
    $expiresAt = time() + (30 * 60); // 30 minutos

    // Guardar token en BD (reutilizamos las columnas de device_link)
    $sql = "UPDATE " . TB_USER . "
            SET device_link_token = ?,
                device_link_expires = ?
            WHERE user_id = ?";

    $updateResult = Table::sqlQueryPrepared($sql, [$token, $expiresAt, $userId]);

    if ($updateResult === false) {
        http_response_code(500);
        echo json_encode(['error' => 'database_error', 'msg' => 'Error al generar enlace']);
        exit;
    }

    // Generar URL del magic link
    $scriptDir = rtrim(SCRIPT_DIR, '/');
    $magicLinkUrl = 'https://' . $_SERVER['HTTP_HOST'] . $scriptDir . '/login/magic/' . $token;

    // Enviar email con magic link
    $msg = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #8B5CF6;">🔑 Acceso temporal a tu cuenta</h2>
                <p>Has solicitado un enlace de acceso temporal porque perdiste acceso a tus dispositivos.</p>
                <p>Haz click en el siguiente enlace para acceder (válido por 30 minutos):</p>
                <p style="text-align: center; margin: 30px 0;">
                    <a href="' . $magicLinkUrl . '"
                       style="background: #8B5CF6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                        Acceder a mi cuenta
                    </a>
                </p>
                <p style="color: #666; font-size: 0.9em;">O copia y pega este enlace en tu navegador:</p>
                <p style="color: #666; font-size: 0.9em; word-break: break-all;">' . $magicLinkUrl . '</p>
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                <p style="color: #999; font-size: 0.85em;">
                    <strong>Importante:</strong> Una vez dentro, genera un código de vinculación para tu nuevo dispositivo desde "Mi cuenta" → "Vincular dispositivo".
                </p>
                <p style="color: #999; font-size: 0.85em;">
                    Si no has solicitado este acceso, ignora este email.
                </p>
            </div>';

    $emailSent = message_mail(
        '🔑 Acceso temporal a tu cuenta',
        $msg,
        CFG::$vars['smtp']['from_email'],
        $email
    );

    if (!$emailSent) {
        http_response_code(500);
        echo json_encode(['error' => 'email_failed', 'msg' => 'Error al enviar email']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'msg' => 'Si el email existe en nuestro sistema, recibirás un enlace de acceso temporal.'
    ]);

// ============================================================================
// PIN MANAGEMENT
// ============================================================================

}else if ($op == 'save_pin') {
    // Guardar PIN del usuario logueado
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        http_response_code(401);
        echo json_encode(['error' => 'not_logged_in', 'msg' => 'No estás autenticado']);
        exit;
    }

    $pin = $_ARGS['pin'] ?? '';

    // Validar PIN: debe ser exactamente 4 dígitos
    if (!preg_match('/^[0-9]{4}$/', $pin)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_pin', 'msg' => 'El PIN debe tener exactamente 4 dígitos']);
        exit;
    }

    // Guardar PIN hasheado en la base de datos
    $hashedPIN = password_hash($pin, PASSWORD_DEFAULT);
    $userId = (int)$_SESSION['userid'];

    $sql = "UPDATE CLI_USER SET PIN = ? WHERE user_id = ?";
    $result = Table::sqlQueryPrepared($sql, [$hashedPIN, $userId]);

    if ($result !== false) {
        echo json_encode(['success' => true, 'msg' => 'PIN guardado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'save_failed', 'msg' => 'Error al guardar el PIN']);
    }

}else if ($op == 'verify_pin') {
    // Verificar PIN de un usuario (usado durante login)
    header('Content-Type: application/json');

    $identifier = $_ARGS['identifier'] ?? '';
    $pin = $_ARGS['pin'] ?? '';

    if (empty($identifier)) {
        http_response_code(400);
        echo json_encode(['error' => 'missing_identifier', 'msg' => 'Falta el identificador de usuario']);
        exit;
    }

    // Buscar usuario por email o username
    $sql = "SELECT PIN FROM CLI_USER WHERE user_email = ? OR username = ? LIMIT 1";
    $user = Table::sqlQueryPrepared($sql, [$identifier, $identifier]);

    // DEBUG
    error_log("verify_pin - identifier: $identifier");
    error_log("verify_pin - user result: " . print_r($user, true));

    if (empty($user)) {
        http_response_code(404);
        echo json_encode(['error' => 'user_not_found', 'msg' => 'Usuario no encontrado']);
        exit;
    }

    $userPIN = $user[0]['PIN'] ?? '';
    error_log("verify_pin - userPIN: " . ($userPIN ? 'SET (' . strlen($userPIN) . ' chars)' : 'EMPTY'));

    // Si el usuario no tiene PIN configurado
    if (empty($userPIN)) {
        error_log("verify_pin - No PIN configured, returning requires_pin=false");
        echo json_encode(['success' => true, 'requires_pin' => false]);
        exit;
    }

    // Si el usuario tiene PIN pero no se proporcionó PIN en la request
    if (empty($pin)) {
        error_log("verify_pin - PIN configured but not provided, returning requires_pin=true");
        echo json_encode(['success' => false, 'requires_pin' => true, 'msg' => 'Este usuario requiere PIN']);
        exit;
    }

    // Verificar PIN
    error_log("verify_pin - Attempting to verify PIN. Provided PIN: '$pin' (length: " . strlen($pin) . ")");
    error_log("verify_pin - Hash in DB: '$userPIN' (length: " . strlen($userPIN) . ")");

    $verified = password_verify($pin, $userPIN);
    error_log("verify_pin - password_verify result: " . ($verified ? 'TRUE' : 'FALSE'));

    if ($verified) {
        error_log("verify_pin - PIN correct");
        echo json_encode(['success' => true, 'requires_pin' => true]);
    } else {
        error_log("verify_pin - PIN incorrect");
        echo json_encode(['success' => false, 'requires_pin' => true, 'msg' => 'PIN incorrecto']);
    }

}else if ($op == 'check_user_pin') {
    // Verificar si el usuario logueado tiene PIN configurado
    header('Content-Type: application/json');

    if (empty($_SESSION['userid'])) {
        echo json_encode(['has_pin' => false, 'logged_in' => false]);
        exit;
    }

    $userId = (int)$_SESSION['userid'];
    $sql = "SELECT PIN FROM CLI_USER WHERE user_id = ? LIMIT 1";
    $user = Table::sqlQueryPrepared($sql, [$userId]);

    $hasPIN = !empty($user[0]['PIN'] ?? '');

    echo json_encode(['has_pin' => $hasPIN, 'logged_in' => true]);

// ============================================================================
// NOSTR AUTHENTICATION (NIP-07)
// ============================================================================

}else if ( str_contains($op,'nostr')  && CFG::$vars['login']['nostr']['enabled']) {


    if ($op == 'nostr_challenge') {
        // Generar challenge para login con Nostr
        header('Content-Type: application/json');
        
        // include_once(SCRIPT_DIR_CLASSES . '/nostrauth.class.php');
        
        $challenge = NostrAuth::generateChallenge();
        $_SESSION['nostr_challenge'] = $challenge;
        $_SESSION['nostr_challenge_time'] = time();
        
        echo json_encode([
            'success' => true,
            'challenge' => $challenge,
            'domain' => $_SERVER['HTTP_HOST']
        ]);

    }else if ($op == 'nostr_challenge_for_pubkey') {
        // Generar challenge para login manual con npub (sin extensión)
        header('Content-Type: application/json');
        
        // include_once(SCRIPT_DIR_CLASSES . '/nostrauth.class.php');
        
        $pubkeyInput = trim($_ARGS['pubkey'] ?? $_POST['pubkey'] ?? '');
        
        if (empty($pubkeyInput)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => 'Falta la clave pública']);
            exit;
        }
        
        // Convertir npub a hex si es necesario
        $pubkeyHex = $pubkeyInput;
        $npub = '';
        
        if (strpos($pubkeyInput, 'npub1') === 0) {
            // Es un npub, convertir a hex
            $pubkeyHex = NostrAuth::npubToHex($pubkeyInput);
            $npub = $pubkeyInput;
            if (!$pubkeyHex) {
                http_response_code(400);
                echo json_encode(['success' => false, 'msg' => 'npub inválido']);
                exit;
            }
        } else {
            // Validar que sea hex válido (64 caracteres)
            if (!preg_match('/^[a-fA-F0-9]{64}$/', $pubkeyInput)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'msg' => 'Clave pública inválida. Debe ser npub1... o 64 caracteres hex']);
                exit;
            }
            $pubkeyHex = strtolower($pubkeyInput);
            $npub = NostrAuth::hexToNpub($pubkeyHex);
        }
        
        $challenge = NostrAuth::generateChallenge();
        $_SESSION['nostr_challenge'] = $challenge;
        $_SESSION['nostr_challenge_time'] = time();
        $_SESSION['nostr_expected_pubkey'] = $pubkeyHex; // Guardar pubkey esperada
        
        echo json_encode([
            'success' => true,
            'challenge' => $challenge,
            'domain' => $_SERVER['HTTP_HOST'],
            'pubkey_hex' => $pubkeyHex,
            'npub' => $npub
        ]);

    }else if ($op == 'nostr_challenge_for_user') {
        // Generar challenge buscando el usuario por email/username
        header('Content-Type: application/json');

        $usernameInput = trim($_ARGS['username'] ?? $_POST['username'] ?? '');

        if (empty($usernameInput)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => 'Falta el email o username']);
            exit;
        }

        // Buscar usuario por email o username
        $sql = "SELECT user_id, username, user_email, nostr_pubkey FROM " . TB_USER . " WHERE (user_email = ? OR username = ?) LIMIT 1";
        $rows = NostrAuth::sqlQueryPrepared($sql, [$usernameInput, $usernameInput]);

        if (empty($rows)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'msg' => 'Usuario no encontrado']);
            exit;
        }

        $user = $rows[0];

        if (empty($user['nostr_pubkey'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => 'Este usuario no tiene identidad Nostr vinculada']);
            exit;
        }

        $challenge = NostrAuth::generateChallenge();
        $_SESSION['nostr_challenge'] = $challenge;
        $_SESSION['nostr_challenge_time'] = time();
        $_SESSION['nostr_expected_pubkey'] = $user['nostr_pubkey'];

        echo json_encode([
            'success' => true,
            'challenge' => $challenge,
            'domain' => $_SERVER['HTTP_HOST'],
            'pubkey_hex' => $user['nostr_pubkey'],
            'npub' => NostrAuth::hexToNpub($user['nostr_pubkey'])
        ]);

    }else if ($op == 'nostr_verify') {
        // Verificar evento firmado y hacer login
        header('Content-Type: application/json');
        
        $eventJson = $_ARGS['event'] ?? $_POST['event'] ?? '';
        
        if (empty($eventJson)) {
            http_response_code(400);
            echo json_encode(['error' => 'missing_event', 'msg' => 'Falta el evento firmado']);
            exit;
        }
        
        $event = json_decode($eventJson);
        if (!$event) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_json', 'msg' => 'JSON inválido']);
            exit;
        }
        
        // Verificar que tenemos un challenge pendiente
        $expectedChallenge = $_SESSION['nostr_challenge'] ?? '';
        if (empty($expectedChallenge)) {
            http_response_code(400);
            echo json_encode(['error' => 'no_challenge', 'msg' => 'No hay challenge pendiente']);
            exit;
        }
        
        // Verificar timeout del challenge (5 min)
        if (time() - ($_SESSION['nostr_challenge_time'] ?? 0) > 300) {
            unset($_SESSION['nostr_challenge'], $_SESSION['nostr_challenge_time']);
            http_response_code(400);
            echo json_encode(['error' => 'challenge_expired', 'msg' => 'Challenge expirado']);
            exit;
        }
        
        //include_once(SCRIPT_DIR_CLASSES . '/nostrauth.class.php');
        
        $result = NostrAuth::verifyEvent($event, $expectedChallenge);
        
        // Limpiar challenge usado
        unset($_SESSION['nostr_challenge'], $_SESSION['nostr_challenge_time']);
        
        if (!$result['valid']) {
            http_response_code(401);
            echo json_encode([
                'error' => $result['error'],
                'msg' => 'Verificación fallida: ' . $result['error']
            ]);
            exit;
        }
        
        // Si es usuario nuevo y se requiere invitación, validar antes de crear
        $existingNostrUser = NostrAuth::findUserByPubkey($result['pubkey']);
        if(!$existingNostrUser && Invitation::isRequired()){
            $invCode = trim($_ARGS['invitation_code'] ?? $_POST['invitation_code'] ?? '');
            if(!Invitation::validate($invCode)){
                http_response_code(400);
                echo json_encode(['error' => 'invalid_invitation', 'msg' => 'Código de invitación no válido o ya utilizado']);
                exit;
            }
        }

        // Login exitoso - crear/actualizar usuario
        $customUsername = trim($_ARGS['username'] ?? $_POST['username'] ?? '');
        $userResult = NostrAuth::createOrUpdateUser($result['pubkey'], $customUsername);
        
        if (!$userResult || !$userResult['user_id']) {
            http_response_code(500);
            echo json_encode(['error' => 'user_creation_failed', 'msg' => 'Error al crear usuario']);
            exit;
        }
        
        $user = $userResult['user'];
        $isNew = $userResult['is_new'];

        // Marcar invitación como usada si es usuario nuevo
        if($isNew && Invitation::isRequired()){
            $invCode = trim($_ARGS['invitation_code'] ?? $_POST['invitation_code'] ?? '');
            if($invCode) Invitation::markUsed($invCode, $user['user_id']);
        }

        // Configurar sesión
        $_SESSION['valid_user']      = true;
        $_SESSION['userid']          = $user['user_id'];
        $_SESSION['userlevel']       = $user['user_level'];
        $_SESSION['username']        = $user['username'];
        $_SESSION['user_fullname']   = $user['user_fullname'];
        $_SESSION['user_email']      = $user['user_email'] ?? '';
        $_SESSION['auth_provider']   = 'nostr';
        $_SESSION['auth_id']         = $result['pubkey'];
        $_SESSION['user_score']      = $user['user_score'];
        $_SESSION['user_url_avatar'] = $user['user_url_avatar'];

        $npub = NostrAuth::hexToNpub($result['pubkey']);
        
        // Mensaje diferente para usuarios nuevos vs existentes
        if ($isNew) {
            $msg = '¡Bienvenido a ' . (CFG::$vars['site']['title'] ?? 'la aplicación') . '! Tu cuenta ha sido creada.';
            $redirect = 'login/profile'; // Redirigir al perfil para que complete sus datos
        } else {
            $msg = '¡Hola de nuevo, ' . $user['user_fullname'] . '!';
            $redirect = $_SESSION['backurl'] ?: '/';
        }
        $_SESSION['backurl'] = false;
        
        echo json_encode([
            'success' => true,
            'msg' => $msg,
            'npub' => $npub,
            'is_new' => $isNew,
            'redirect' => $redirect
        ]);

    }else if ($op == 'nostr_link') {
        // Vincular cuenta Nostr a usuario existente (desde perfil)
        header('Content-Type: application/json');
        
        if (empty($_SESSION['userid'])) {
            http_response_code(401);
            echo json_encode(['error' => 'not_logged_in', 'msg' => 'Debes iniciar sesión']);
            exit;
        }
        
        $eventJson = $_ARGS['event'] ?? $_POST['event'] ?? '';
        
        if (empty($eventJson)) {
            http_response_code(400);
            echo json_encode(['error' => 'missing_event', 'msg' => 'Falta el evento firmado']);
            exit;
        }
        
        $event = json_decode($eventJson);
        if (!$event) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_json', 'msg' => 'JSON inválido']);
            exit;
        }
        
        // Verificar que tenemos un challenge pendiente
        $expectedChallenge = $_SESSION['nostr_link_challenge'] ?? '';
        if (empty($expectedChallenge)) {
            http_response_code(400);
            echo json_encode(['error' => 'no_challenge', 'msg' => 'No hay challenge pendiente']);
            exit;
        }
        
        // Verificar timeout del challenge (5 min)
        if (time() - ($_SESSION['nostr_link_challenge_time'] ?? 0) > 300) {
            unset($_SESSION['nostr_link_challenge'], $_SESSION['nostr_link_challenge_time']);
            http_response_code(400);
            echo json_encode(['error' => 'challenge_expired', 'msg' => 'Challenge expirado']);
            exit;
        }
        
        // include_once(SCRIPT_DIR_CLASSES . '/nostrauth.class.php');
        
        $result = NostrAuth::verifyEvent($event, $expectedChallenge);
        
        // Limpiar challenge usado
        unset($_SESSION['nostr_link_challenge'], $_SESSION['nostr_link_challenge_time']);
        
        if (!$result['valid']) {
            http_response_code(401);
            echo json_encode([
                'error' => $result['error'],
                'msg' => 'Verificación fallida: ' . $result['error']
            ]);
            exit;
        }
        
        // Verificar que este npub no está ya vinculado a otro usuario
        $existingUser = NostrAuth::findUserByPubkey($result['pubkey']);
        if ($existingUser && $existingUser['user_id'] != $_SESSION['userid']) {
            http_response_code(400);
            echo json_encode([
                'error' => 'pubkey_taken',
                'msg' => 'Esta cuenta Nostr ya está vinculada a otro usuario'
            ]);
            exit;
        }
        
        // Vincular npub al usuario actual
        $success = NostrAuth::linkPubkeyToUser($_SESSION['userid'], $result['pubkey']);
        
        if (!$success) {
            http_response_code(500);
            echo json_encode(['error' => 'link_failed', 'msg' => 'Error al vincular cuenta']);
            exit;
        }
        
        $npub = NostrAuth::hexToNpub($result['pubkey']);
        
        echo json_encode([
            'success' => true,
            'msg' => '¡Cuenta Nostr vinculada correctamente!',
            'npub' => $npub
        ]);

    }else if ($op == 'nostr_link_challenge') {
        // Generar challenge para vincular cuenta Nostr (usuario ya logueado)
        header('Content-Type: application/json');

        if (empty($_SESSION['userid'])) {
            http_response_code(401);
            echo json_encode(['error' => 'not_logged_in', 'msg' => 'Debes iniciar sesión']);
            exit;
        }

        // include_once(SCRIPT_DIR_CLASSES . '/nostrauth.class.php');

        $challenge = NostrAuth::generateChallenge();
        $_SESSION['nostr_link_challenge'] = $challenge;
        $_SESSION['nostr_link_challenge_time'] = time();

        echo json_encode([
            'success' => true,
            'challenge' => $challenge,
            'domain' => $_SERVER['HTTP_HOST']
        ]);

    // NOTA: nostr_link_manual fue eliminado por seguridad
    // Ahora solo se permite vincular cuentas Nostr mediante firma criptográfica
    // usando la operación 'nostr_link' que requiere verificación de firma

    }else if ($op == 'nostr_unlink') {
        // Desvincular cuenta Nostr del usuario (eliminar npub del servidor)
        header('Content-Type: application/json');

        if (empty($_SESSION['userid'])) {
            http_response_code(401);
            echo json_encode(['error' => 'not_logged_in', 'msg' => 'Debes iniciar sesión']);
            exit;
        }

        // Verificar que el usuario tiene una npub vinculada
        $sql = "SELECT nostr_pubkey FROM " . TB_USER . " WHERE user_id = ?";
        $result = Login::sqlQueryPrepared($sql, [$_SESSION['userid']]);

        if (empty($result[0]['nostr_pubkey'])) {
            http_response_code(400);
            echo json_encode(['error' => 'not_linked', 'msg' => 'No tienes ninguna cuenta Nostr vinculada']);
            exit;
        }

        // Desvincular (poner nostr_pubkey a NULL)
        $sql = "UPDATE " . TB_USER . " SET nostr_pubkey = NULL WHERE user_id = ?";
        $success = Login::sqlQueryPrepared($sql, [$_SESSION['userid']]);

        if ($success === false) {
            http_response_code(500);
            echo json_encode(['error' => 'unlink_failed', 'msg' => 'Error al desvincular cuenta']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'msg' => 'Cuenta Nostr desvinculada correctamente'
        ]);

    }

// ============================================================================
// INVITATIONS
// ============================================================================

}else if ($op == 'validate_invitation') {

    header('Content-Type: application/json');
    $code = trim($_POST['code'] ?? $_REQUEST['code'] ?? '');
    if(Invitation::validate($code)){
        echo json_encode(['error' => 0, 'msg' => 'Código válido']);
    }else{
        echo json_encode(['error' => 1, 'msg' => 'Código no válido o ya utilizado']);
    }

}else if ($op == 'generate_invitation' && $_SESSION['valid_user']) {

    header('Content-Type: application/json');
    $userId = (int)$_SESSION['userid'];
    echo json_encode(Invitation::create($userId));

}else if ($op == 'get_my_invitations' && $_SESSION['valid_user']) {

    header('Content-Type: application/json');
    $userId = (int)$_SESSION['userid'];
    echo json_encode([
        'error'     => 0,
        'items'     => Invitation::getByUser($userId),
        'available' => Invitation::countAvailable($userId),
        'used'      => Invitation::countUsed($userId),
        'score'     => Karma::getUserScore($userId),
        'cost'      => Invitation::KARMA_COST
    ]);

}else{

    include(SCRIPT_DIR_CLASSES.'/scaffold/ajax.php');

}

