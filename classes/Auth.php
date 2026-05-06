<?php
/**
 * Clase de Autenticación
 * Incluye protección contra fuerza bruta, CSRF, session fixation y control de roles
 */

class Auth {

    // ─────────────────────────────────────────
    // ROLES DEL SISTEMA
    // ─────────────────────────────────────────
    const ROLE_ADMIN  = 'admin';
    const ROLE_EDITOR = 'editor';
    const ROLE_VIEWER = 'viewer';

    const ROLE_USER   = 'user'; // Default de la tabla users

    const VALID_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_EDITOR,
        self::ROLE_VIEWER,
        self::ROLE_USER,
    ];

    // ─────────────────────────────────────────
    // VERIFICACIÓN DE AUTENTICACIÓN
    // ─────────────────────────────────────────

    /**
     * Verifica si el usuario está autenticado
     */
    public static function check(): bool {
        return isset($_SESSION['user_id'], $_SESSION['logged_in'])
            && $_SESSION['logged_in'] === true;
    }

    /**
     * Obtiene el usuario actual desde sesión
     */
    public static function user(): ?array {
        if (self::check()) {
            return User::find($_SESSION['user_id']);
        }
        return null;
    }

    // ─────────────────────────────────────────
    // LOGIN
    // ─────────────────────────────────────────

    /**
     * Intenta iniciar sesión con email y contraseña
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public static function login(string $email, string $password): array {
        // Validar CSRF token
        if (!self::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Token de seguridad inválido. Recarga la página.'];
        }

        // Verificar bloqueo por intentos fallidos
        $lockout = self::checkLockout($email);
        if ($lockout['locked']) {
            $minutes = ceil($lockout['remaining'] / 60);
            return ['success' => false, 'message' => "Cuenta bloqueada. Intenta de nuevo en {$minutes} minuto(s)."];
        }

        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("
                SELECT id, name, email, password, role, status
                FROM users
                WHERE email = ?
            ");
            $stmt->execute([trim($email)]);
            $user = $stmt->fetch();

            if ($user && (int)$user['status'] === 1 && password_verify($password, $user['password'])) {
                // Login exitoso: limpiar intentos fallidos
                self::clearLoginAttempts($email);

                // Prevenir session fixation
                session_regenerate_id(true);

                // Registrar datos en sesión
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['user_name']     = $user['name'];
                $_SESSION['user_email']    = $user['email'];
                $_SESSION['user_role']     = $user['role'];
                $_SESSION['logged_in']     = true;
                $_SESSION['last_activity'] = time();

                // Regenerar CSRF token tras login exitoso
                self::regenerateCsrfToken();

                // Actualizar último login
                User::updateLastLogin($user['id']);

                return ['success' => true, 'message' => 'Bienvenido, ' . htmlspecialchars($user['name'])];
            }

            // Login fallido: registrar intento
            self::recordFailedAttempt($email);

            // Mensaje genérico para no revelar si el email existe
            return ['success' => false, 'message' => 'Credenciales incorrectas.'];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno. Intenta de nuevo más tarde.'];
        }
    }

    // ─────────────────────────────────────────
    // LOGOUT
    // ─────────────────────────────────────────

    /**
     * Cierra la sesión del usuario y redirige al login
     */
    public static function logout(): void {
        $userId = $_SESSION['user_id'] ?? null;

        // Registrar logout en log
        if ($userId) {
            error_log("Logout: user_id={$userId} ip=" . self::getClientIp());
        }

        // Limpiar sesión
        $_SESSION = [];

        // Eliminar cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . APP_URL . '/modules/home/login.php');
        exit();
    }

    // ─────────────────────────────────────────
    // CONTROL DE SESIÓN
    // ─────────────────────────────────────────

    /**
     * Verifica que la sesión no haya expirado por inactividad
     */
    public static function checkSessionTimeout(): bool {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }

        $inactive = time() - $_SESSION['last_activity'];

        if ($inactive > SESSION_TIMEOUT) {
            error_log("Sesión expirada: user_id=" . ($_SESSION['user_id'] ?? 'desconocido') . " inactivo={$inactive}s");
            self::logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Requiere autenticación para acceder — redirige si no está logueado
     */
    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/modules/home/login.php');
            exit();
        }

        if (!self::checkSessionTimeout()) {
            header('Location: ' . APP_URL . '/modules/home/login.php?timeout=1');
            exit();
        }
    }

    // ─────────────────────────────────────────
    // CONTROL DE ROLES
    // ─────────────────────────────────────────

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public static function hasRole(string $role): bool {
        if (!self::check()) return false;
        return $_SESSION['user_role'] === $role;
    }

    /**
     * Verifica si el usuario tiene alguno de los roles indicados
     */
    public static function hasAnyRole(array $roles): bool {
        if (!self::check()) return false;
        return in_array($_SESSION['user_role'], $roles, true);
    }

    /**
     * Requiere un rol específico — redirige si no tiene permiso
     */
    public static function requireRole(string $role): void {
        self::requireLogin();
        if (!self::hasRole($role)) {
            header('Location: ' . APP_URL . '/index.php?error=unauthorized');
            exit();
        }
    }

    /**
     * Requiere alguno de los roles indicados
     */
    public static function requireAnyRole(array $roles): void {
        self::requireLogin();
        if (!self::hasAnyRole($roles)) {
            header('Location: ' . APP_URL . '/index.php?error=unauthorized');
            exit();
        }
    }

    // ─────────────────────────────────────────
    // PROTECCIÓN CONTRA FUERZA BRUTA
    // ─────────────────────────────────────────

    /**
     * Registra un intento de login fallido en BD
     */
    private static function recordFailedAttempt(string $email): void {
        try {
            $db  = Database::getConnection();
            $ip  = self::getClientIp();
            $now = date('Y-m-d H:i:s');

            $stmt = $db->prepare("
                INSERT INTO login_attempts (email, ip_address, attempted_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$email, $ip, $now]);

        } catch (Exception $e) {
            error_log("Error registrando intento fallido: " . $e->getMessage());
        }
    }

    /**
     * Verifica si el email está bloqueado por exceso de intentos
     *
     * @return array ['locked' => bool, 'remaining' => int segundos]
     */
    private static function checkLockout(string $email): array {
        try {
            $db      = Database::getConnection();
            $ip      = self::getClientIp();
            $since   = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);

            $stmt = $db->prepare("
                SELECT COUNT(*) as attempts,
                       MAX(attempted_at) as last_attempt
                FROM login_attempts
                WHERE (email = ? OR ip_address = ?)
                  AND attempted_at >= ?
            ");
            $stmt->execute([$email, $ip, $since]);
            $result = $stmt->fetch();

            if ((int)$result['attempts'] >= MAX_LOGIN_ATTEMPTS) {
                $lastAttempt = strtotime($result['last_attempt']);
                $remaining   = LOGIN_LOCKOUT_TIME - (time() - $lastAttempt);
                return ['locked' => true, 'remaining' => max(0, $remaining)];
            }

        } catch (Exception $e) {
            error_log("Error verificando bloqueo: " . $e->getMessage());
        }

        return ['locked' => false, 'remaining' => 0];
    }

    /**
     * Limpia los intentos fallidos tras un login exitoso
     */
    private static function clearLoginAttempts(string $email): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM login_attempts WHERE email = ?");
            $stmt->execute([$email]);
        } catch (Exception $e) {
            error_log("Error limpiando intentos: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // CSRF
    // ─────────────────────────────────────────

    /**
     * Genera y almacena un CSRF token en sesión
     */
    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            self::regenerateCsrfToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Regenera el CSRF token
     */
    public static function regenerateCsrfToken(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    /**
     * Verifica que el CSRF token sea válido
     */
    public static function verifyCsrfToken(string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // ─────────────────────────────────────────
    // UTILIDADES
    // ─────────────────────────────────────────

    /**
     * Obtiene la IP real del cliente (considera proxies)
     */
    private static function getClientIp(): string {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // Tomar la primera IP si viene separada por comas (proxies)
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
