<?php
/**
 * Archivo de Configuración de Ejemplo
 * Copia este archivo a config.php y actualiza los valores
 */

// Configuración del sistema
define('APP_NAME', 'Dashboard PHP');
define('APP_VERSION', '1.0.0');

// IMPORTANTE: Actualiza esta URL según tu instalación
// Ejemplos:
// - Local: http://localhost/dashboard
// - Desarrollo: http://dev.ejemplo.com/dashboard
// - Producción: https://www.ejemplo.com/dashboard
define('APP_URL', 'http://localhost/dashboard');

// Zona horaria
// Lista completa: https://www.php.net/manual/es/timezones.php
define('TIMEZONE', 'America/Mexico_City');
date_default_timezone_set(TIMEZONE);

// Configuración de sesión
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
define('SESSION_COOKIE_NAME', 'dashboard_session');

// Configuración de errores
// En producción, desactiva el display de errores
define('DEBUG_MODE', true);

if (!DEBUG_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Configuración de autenticación
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de archivos
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Configuración de base de datos SQL Server
// IMPORTANTE: Actualiza estos valores según tu configuración

// Host de SQL Server
define('DB_HOST', 'localhost');

// Instancia de SQL Server (común: SQLEXPRESS, MSSQLSERVER, o nombre personalizado)
define('DB_INSTANCE', 'SQLEXPRESS');

// Nombre de la base de datos
define('DB_NAME', 'dashboard_db');

// Usuario de SQL Server
define('DB_USER', 'sa');

// Contraseña de SQL Server
define('DB_PASSWORD', '');

// Codificación de caracteres
define('DB_CHARSET', 'UTF-8');

// Configuración de correo (para notificaciones futuras)
define('MAIL_HOST', 'smtp.ejemplo.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'noreply@ejemplo.com');
define('MAIL_PASSWORD', '');
define('MAIL_FROM_NAME', APP_NAME);
define('MAIL_FROM_EMAIL', 'noreply@ejemplo.com');

// Configuración de seguridad
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL', false);

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_COOKIE_NAME);
    session_start();
}
?>