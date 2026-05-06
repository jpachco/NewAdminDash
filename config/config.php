<?php
/**
 * Configuración General del Sistema
 */

// ─────────────────────────────────────────
// ENTORNO
// ─────────────────────────────────────────
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('IS_PRODUCTION', APP_ENV === 'production');

// ─────────────────────────────────────────
// CONFIGURACIÓN DEL SISTEMA
// ─────────────────────────────────────────
define('APP_NAME',    'Admindash');
define('APP_VERSION', '1.0.0');
define('APP_URL',     '/Admindash');

set_time_limit(120);
date_default_timezone_set('America/Mexico_City');

// ─────────────────────────────────────────
// MANEJO DE ERRORES
// ─────────────────────────────────────────
if (IS_PRODUCTION) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/logs/app_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// ─────────────────────────────────────────
// RUTAS DEL SISTEMA
// ─────────────────────────────────────────
define('ROOT_PATH',     dirname(__DIR__));
define('CONFIG_PATH',   ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH',  ROOT_PATH . '/classes');
define('MODULES_PATH',  ROOT_PATH . '/modules');
define('ASSETS_PATH',   ROOT_PATH . '/assets');
define('LOGS_PATH',     ROOT_PATH . '/logs');

// ─────────────────────────────────────────
// CONFIGURACIÓN DE AUTENTICACIÓN
// (debe ir ANTES de la configuración de sesión)
// ─────────────────────────────────────────
define('SESSION_TIMEOUT',    3600); // 1 hora
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);  // 15 minutos

// ─────────────────────────────────────────
// CONFIGURACIÓN DE SESIÓN
// ─────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure',   IS_PRODUCTION ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime',  SESSION_TIMEOUT);

// ─────────────────────────────────────────
// CONFIGURACIÓN DE PAGINACIÓN
// ─────────────────────────────────────────
define('ITEMS_PER_PAGE', 10);

// ─────────────────────────────────────────
// CONFIGURACIÓN DE ARCHIVOS
// ─────────────────────────────────────────
define('MAX_FILE_SIZE',      5242880); // 5MB en bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// ─────────────────────────────────────────
// INICIAR SESIÓN
// ─────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    if (empty($_SESSION)) {
        session_regenerate_id(true);
    }
}

// ─────────────────────────────────────────
// CREAR DIRECTORIO DE LOGS SI NO EXISTE
// ─────────────────────────────────────────
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

// ─────────────────────────────────────────
// CARGA DE ARCHIVOS REQUERIDOS
// ─────────────────────────────────────────
$required_files = [
    CONFIG_PATH  . '/database.php',
    CONFIG_PATH  . '/phpmailer/src/PHPMailer.php',
    CONFIG_PATH  . '/phpmailer/src/Exception.php',
    CONFIG_PATH  . '/phpmailer/src/SMTP.php',
    CLASSES_PATH . '/Auth.php',
    CLASSES_PATH . '/User.php',
    CLASSES_PATH . '/Dashboard.php',
    CLASSES_PATH . '/Messenger.php',

];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $msg = "Error crítico: archivo requerido no encontrado → $file";
        error_log($msg);
        if (!IS_PRODUCTION) {
            die($msg);
        } else {
            die('Error interno del sistema. Por favor contacta al administrador.');
        }
    }
    require_once $file;
}
