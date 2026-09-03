<?php


//. if(isset($cfg['ratelimiter']['enabled']) && $cfg['ratelimiter']['enabled']===true){


    /*
        include(SCRIPT_DIR_CLASSES.'/storage/storage.interface.php');
        //include(SCRIPT_DIR_CLASSES.'/storage/sqlite.storage.class.php');
        include(SCRIPT_DIR_CLASSES.'/storage/mysql.storage.class.php');
        include(SCRIPT_DIR_CLASSES.'/ratelimiter.class.php');
    */

    //$redis = new Redis();
    //$redis->connect('127.0.0.1', 6379);
    //storage = new RedisStorage($redis);

    //$storage = new SQLiteStorage('cache.sqlite');


// Rate limit global de ExtFW.
// El endpoint público btcpay_pay mantiene su límite específico en noxtr/raw.php.

if (defined('EXTFW_GLOBAL_RATE_LIMIT_APPLIED')) {
    return;
}
define('EXTFW_GLOBAL_RATE_LIMIT_APPLIED', true);

// No limitar procesos CLI, preflight CORS ni streams SSE de larga duración.
// Tampoco los polls del visor Live del panel (log y rate log): son observación cada 2s
// del propio admin — si contaran, el visor del rate log se llenaría de sus propios
// bloqueos con límites bajos, y en modo real el panel se auto-bloquearía. Sin riesgo
// de abuso: esos endpoints exigen Administrador() en control_panel/ajax.php.
if (
    PHP_SAPI === 'cli'
    || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS'
    || MODULE === 'sse'
    || OUTPUT === 'sse'
    || (($_ARGS['action'] ?? '') === 'btcpay_pay')
 // || (MODULE === 'comments' && ($_ARGS['op'] ?? '') === 'vote_comment')
    || (MODULE === 'control_panel' && in_array($_ARGS[2] ?? '', ['log', 'ratelog', 'ratelog_test'], true))
) {
    return;
}

// SQLiteStorage crea la tabla automáticamente. Se usa un fichero independiente
// del cache de BTCPay y de la base de datos principal de la aplicación.
$rateStorage = new SQLiteStorage(CFG::$vars['db']['name'] . '-rate.sqlite');

// Usuarios autenticados: cupo mayor y keyeado por usuario (no por IP compartida).
// El cliente Mostro de noxtr dispara ráfagas legítimas de AJAX (log de eventos kind 14,
// mostro_trade_update por cada mensaje del robot, replay del historial al recargar):
// con 120/min por IP un usuario real operando se auto-bloqueaba, y el fallo era
// silencioso (el JS traga el {error:1} de mostro_trade_list y deja la UI sin trades
// ni QR de fianza — bug real, 2026-08-23). La sesión ya está iniciada aquí
// (session_start() corre antes en run.php).
// ── Ajustes del limiter en CFG_CFG (editables en Control Panel → Ajustes) ──
//   ratelimit.dry_run   1 = simulación (solo log, no bloquea) / 0 = real (429)
//   ratelimit.max_user  cupo por minuto por usuario autenticado (default 600)
//   ratelimit.max_anon  cupo por minuto por IP anónima (default 120)
// CFG_CFG ya está cargado en CFG::$vars por _classes_/<db_engine>/init.php — el
// include de este archivo en run.php va después de esa carga precisamente por esto.
// Sin filas en CFG_CFG: defaults (y dry-run, que nunca bloquea por sorpresa).
$rateCfg = CFG::$vars['ratelimit'] ?? [];
// _v() del init convierte 'true'/'false' a bool; '0'/'1' llegan como string.
$rateDryRunRaw = $rateCfg['dry_run'] ?? '1';
$rateDryRun = !($rateDryRunRaw === '0' || $rateDryRunRaw === 0 || $rateDryRunRaw === false);

$rateUserId = (int)($_SESSION['userid'] ?? 0);
$rateCapacity = $rateUserId > 0
    ? max(1, (int)($rateCfg['max_user'] ?? 600))
    : max(1, (int)($rateCfg['max_anon'] ?? 120));
if ($rateUserId > 0) {
    $rateLimiter = new RateLimiter([
        'prefix'       => 'extfw_global_u_',
        'maxCapacity'  => $rateCapacity,
        'refillPeriod' => 60,
    ], $rateStorage);
    $rateKey = 'u' . $rateUserId;
} else {
    $rateLimiter = new RateLimiter([
        'prefix'       => 'extfw_global_',
        'maxCapacity'  => $rateCapacity,
        'refillPeriod' => 60,
    ], $rateStorage);
    $rateKey = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

$rateAllowed = $rateLimiter->check($rateKey);
$rateHeaders = $rateLimiter->headers($rateKey);
foreach ($rateHeaders as $headerName => $headerValue) {
    header($headerName . ': ' . $headerValue);
}

// Log de dimensionamiento vía LOG::rate() (archivo diario rate_YYYYMMDD.log en
// SCRIPT_DIR_LOG, una línea JSON por evento): 'dryrun-block'/'block' (bloqueado, o
// habría sido bloqueado en dry-run) y 'low' (quedan <20% de tokens — "casi-bloqueo").
// Contando los block de un mismo minuto se obtiene el exceso sobre la capacidad
// actual: capacidad necesaria ≈ capacidad actual + máximo de blocks/minuto (+ margen).
$rateRemaining = (int)($rateHeaders['X-RateLimit-Remaining'] ?? 0);
if (!$rateAllowed || $rateRemaining < $rateCapacity * 0.2) {
    LOG::rate(
        !$rateAllowed ? ($rateDryRun ? 'dryrun-block' : 'block') : 'low',
        [
            'key'       => $rateKey,
            'module'    => MODULE,
            'action'    => ($_ARGS['action'] ?? '') !== '' ? $_ARGS['action'] : '-',
            'remaining' => $rateRemaining,
            'limit'     => $rateCapacity,
        ]
    );
}

if ($rateAllowed || $rateDryRun) {
    return;
}

http_response_code(429);
header('Retry-After: 60');

if (in_array(OUTPUT, ['ajax', 'api', 'json'], true)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 1,
        'msg'   => 'Too many requests. Try again later.',
    ], JSON_UNESCAPED_UNICODE);
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Too many requests. Try again later.';
}

exit;
