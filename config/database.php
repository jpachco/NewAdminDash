<?php
/**
 * Gestión Multiconexión SQL Server
 * Sistema de Dashboard PHP - Edición Ejecutiva
 */

// ─────────────────────────────────────────
// LECTOR DE .env NATIVO (sin dependencias)
// ─────────────────────────────────────────
if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) {
            // Crear .env vacío desde .env.example si existe
            $example = dirname($path) . '/.env.example';
            if (file_exists($example)) {
                copy($example, $path);
                error_log("Advertencia: .env no encontrado. Se creó uno vacío desde .env.example en: $path");
            } else {
                error_log("Advertencia: archivo .env no encontrado en: $path");
            }
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (str_starts_with(trim($line), '#')) continue;

            // Separar clave=valor
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Eliminar comillas opcionales del valor
            $value = trim($value, '"\'');

            // Solo registrar si no existe ya
            if (!isset($_ENV[$key]) && !getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Cargar .env desde la raíz del proyecto
loadEnv(dirname(__DIR__) . '/.env');

// ─────────────────────────────────────────
// CLASE DATABASE
// ─────────────────────────────────────────
class Database {

    // Registro de conexiones activas (Singleton por nombre)
    private static array $instances = [];

    // Configuraciones construidas desde variables de entorno
    private static function getConfigs(): array {
        return [
            'default' => [
                'host'     => $_ENV['DB_DEFAULT_HOST']     ?? '',
                'instance' => $_ENV['DB_DEFAULT_INSTANCE'] ?? '',
                'db'       => $_ENV['DB_DEFAULT_NAME']     ?? '',
                'user'     => $_ENV['DB_DEFAULT_USER']     ?? '',
                'pass'     => $_ENV['DB_DEFAULT_PASS']     ?? '',
            ],
            'nav' => [
                'host'     => $_ENV['DB_NAV_HOST']     ?? '',
                'instance' => $_ENV['DB_NAV_INSTANCE'] ?? '',
                'db'       => $_ENV['DB_NAV_NAME']     ?? '',
                'user'     => $_ENV['DB_NAV_USER']     ?? '',
                'pass'     => $_ENV['DB_NAV_PASS']     ?? '',
            ],
            'MF' => [
                'host'     => $_ENV['DB_MF_HOST']     ?? '',
                'instance' => $_ENV['DB_MF_INSTANCE'] ?? '',
                'db'       => $_ENV['DB_MF_NAME']     ?? '',
                'user'     => $_ENV['DB_MF_USER']     ?? '',
                'pass'     => $_ENV['DB_MF_PASS']     ?? '',
            ],
            'RB' => [
                'host'     => $_ENV['DB_RB_HOST']     ?? '',
                'instance' => $_ENV['DB_RB_INSTANCE'] ?? '',
                'db'       => $_ENV['DB_RB_NAME']     ?? '',
                'user'     => $_ENV['DB_RB_USER']     ?? '',
                'pass'     => $_ENV['DB_RB_PASS']     ?? '',
            ],
            'HL' => [
                'host'     => $_ENV['DB_HL_HOST']     ?? '',
                'instance' => $_ENV['DB_HL_INSTANCE'] ?? '',
                'db'       => $_ENV['DB_HL_NAME']     ?? '',
                'user'     => $_ENV['DB_HL_USER']     ?? '',
                'pass'     => $_ENV['DB_HL_PASS']     ?? '',
            ],
            'BG' => [
                'host'     => $_ENV['DB_BG_HOST']     ?? '',
                'instance' => $_ENV['DB_BG_INSTANCE'] ?? '',
                'db'       => $_ENV['DB_BG_NAME']     ?? '',
                'user'     => $_ENV['DB_BG_USER']     ?? '',
                'pass'     => $_ENV['DB_BG_PASS']     ?? '',
            ],
        ];
    }

    /**
     * Obtiene la conexión solicitada (Singleton por nombre)
     *
     * @param  string $name Nombre de la configuración
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(string $name = 'default'): PDO {
        $configs = self::getConfigs();

        if (!isset($configs[$name])) {
            throw new Exception("La configuración de base de datos '{$name}' no existe.");
        }

        // Verificar si la conexión sigue activa (reconexión automática)
        if (!isset(self::$instances[$name]) || !self::isAlive(self::$instances[$name])) {
            self::$instances[$name] = self::connect($name, $configs[$name]);
        }

        return self::$instances[$name];
    }

    /**
     * Verifica si una conexión PDO sigue activa
     */
    private static function isAlive(PDO $pdo): bool {
        try {
            $pdo->query('SELECT 1');
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * Proceso interno de conexión
     *
     * @throws Exception
     */
    private static function connect(string $name, array $conf): PDO {
        // Validar que las credenciales no estén vacías
        foreach (['host', 'instance', 'db', 'user', 'pass'] as $key) {
            if (empty($conf[$key])) {
                throw new Exception("Configuración incompleta para '{$name}': falta '{$key}'. Verifica tu archivo .env");
            }
        }

        try {
            $isProduction = (($_ENV['APP_ENV'] ?? 'development') === 'production');

            $dsn = sprintf(
                'sqlsrv:Server=%s\\%s;Database=%s;TrustServerCertificate=%s;Encrypt=%s;',
                $conf['host'],
                $conf['instance'],
                $conf['db'],
                $isProduction ? 'false' : 'false', // Ajustar según certificado en producción
                $isProduction ? 'true'  : 'false'  // Cifrado activado en producción
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::SQLSRV_ATTR_ENCODING    => PDO::SQLSRV_ENCODING_UTF8,
            ];

            return new PDO($dsn, $conf['user'], $conf['pass'], $options);

        } catch (PDOException $e) {
            $msg = "Error de conexión DB ({$name}): " . $e->getMessage();
            error_log($msg);

            // En producción: mensaje genérico. En desarrollo: detalle completo
            if (($_ENV['APP_ENV'] ?? 'development') === 'production') {
                throw new Exception("No se pudo conectar al origen de datos '{$name}'. Contacta al administrador.");
            } else {
                throw new Exception($msg);
            }
        }
    }

    /**
     * Cierra una conexión específica o todas
     *
     * @param string|null $name Nombre de la conexión o null para cerrar todas
     */
    public static function closeConnection(?string $name = null): void {
        if ($name !== null) {
            self::$instances[$name] = null;
            unset(self::$instances[$name]);
        } else {
            self::$instances = [];
        }
    }
}