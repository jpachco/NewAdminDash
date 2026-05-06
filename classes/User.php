<?php
/**
 * Clase de Usuarios
 * Alineado con estructura real de tabla users
 * status: BIT → 1 = activo | 0 = inactivo
 */

class User {

    // Campos seguros a retornar (sin password)
    private const SAFE_FIELDS = 'id, name, email, role, status, created_at, last_login, updated_at';

    // Tipos de usuario
    const TIPOS = ['Corporativo', 'Tienda', 'AreaManager', 'Almacen'];

    // Costo de bcrypt (mínimo recomendado: 12)
    private const BCRYPT_COST = 12;

    // ─────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────

    /**
     * Obtiene usuarios con paginación
     *
     * @return array ['data' => [], 'total' => int, 'pages' => int]
     */
    public static function all(int $page = 1, int $limit = ITEMS_PER_PAGE, bool $onlyActive = false): array {
        $db     = Database::getConnection();
        $offset = ($page - 1) * $limit;
        $where  = $onlyActive ? 'WHERE status = 1' : '';

        try {
            $stmtCount = $db->query("SELECT COUNT(*) as total FROM users {$where}");
            $total     = (int)$stmtCount->fetch()['total'];

            $stmt = $db->prepare("
                SELECT " . self::SAFE_FIELDS . "
                FROM users
                {$where}
                ORDER BY created_at DESC
                OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
            ");
            $stmt->execute([$offset, $limit]);

            return [
                'data'  => $stmt->fetchAll(),
                'total' => $total,
                'pages' => (int)ceil($total / $limit),
            ];

        } catch (Exception $e) {
            error_log("User::all error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'pages' => 0];
        }
    }

    /**
     * Obtiene un usuario por ID (sin password)
     */
    public static function find(int $id): ?array {
        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("SELECT " . self::SAFE_FIELDS . " FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("User::find error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un usuario por email (sin password)
     */
    public static function findByEmail(string $email): ?array {
        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("SELECT " . self::SAFE_FIELDS . " FROM users WHERE email = ?");
            $stmt->execute([trim(strtolower($email))]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("User::findByEmail error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cuenta usuarios
     *
     * @param string $filter 'all' | 'active' | 'inactive'
     */
    public static function count(string $filter = 'all'): int {
        $db = Database::getConnection();

        $where = match($filter) {
            'active'   => 'WHERE status = 1',
            'inactive' => 'WHERE status = 0',
            default    => '',
        };

        try {
            $stmt = $db->query("SELECT COUNT(*) as total FROM users {$where}");
            return (int)$stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("User::count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verifica si el email ya existe (excluyendo opcionalmente un ID)
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool {
        $db = Database::getConnection();

        try {
            if ($excludeId !== null) {
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([trim(strtolower($email)), $excludeId]);
            } else {
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([trim(strtolower($email))]);
            }

            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("User::emailExists error: " . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────

    /**
     * Crea un nuevo usuario
     *
     * @return int|false ID del nuevo usuario o false en error
     */
    public static function create(array $data): int|false {
        $validation = self::validate($data);
        if (!$validation['valid']) {
            error_log("User::create validación fallida: " . implode(', ', $validation['errors']));
            return false;
        }

        if (self::emailExists($data['email'])) {
            error_log("User::create email duplicado: " . $data['email']);
            return false;
        }

        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO users (name, email, password, role, status, created_at)
                VALUES (?, ?, ?, ?, ?, GETDATE())
            ");
            $stmt->execute([
                trim($data['name']),
                trim(strtolower($data['email'])),
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]),
                $data['role'],
                (int)$data['status'],
            ]);

            return (int)$db->lastInsertId();

        } catch (Exception $e) {
            error_log("User::create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un usuario existente
     */
    public static function update(int $id, array $data): bool {
        $validation = self::validate($data, isUpdate: true);
        if (!$validation['valid']) {
            error_log("User::update validación fallida: " . implode(', ', $validation['errors']));
            return false;
        }

        if (self::emailExists($data['email'], $id)) {
            error_log("User::update email duplicado: " . $data['email']);
            return false;
        }

        $db = Database::getConnection();

        try {
            if (!empty($data['password'])) {
                $stmt = $db->prepare("
                    UPDATE users
                    SET name = ?, email = ?, password = ?, role = ?, status = ?, updated_at = GETDATE()
                    WHERE id = ?
                ");
                $stmt->execute([
                    trim($data['name']),
                    trim(strtolower($data['email'])),
                    password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]),
                    $data['role'],
                    (int)$data['status'],
                    $id,
                ]);
            } else {
                $stmt = $db->prepare("
                    UPDATE users
                    SET name = ?, email = ?, role = ?, status = ?, updated_at = GETDATE()
                    WHERE id = ?
                ");
                $stmt->execute([
                    trim($data['name']),
                    trim(strtolower($data['email'])),
                    $data['role'],
                    (int)$data['status'],
                    $id,
                ]);
            }

            return true;

        } catch (Exception $e) {
            error_log("User::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete: desactiva el usuario (status = 0)
     * No borra el registro de la BD
     */
    public static function delete(int $id): bool {
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
            error_log("User::delete intento de auto-eliminación user_id={$id}");
            return false;
        }

        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("UPDATE users SET status = 0, updated_at = GETDATE() WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (Exception $e) {
            error_log("User::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el último login del usuario
     */
    public static function updateLastLogin(int $id): void {
        $db = Database::getConnection();

        try {
            $stmt = $db->prepare("UPDATE users SET last_login = GETDATE() WHERE id = ?");
            $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("User::updateLastLogin error: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // VALIDACIÓN INTERNA
    // ─────────────────────────────────────────

    /**
     * Valida los datos de un usuario
     *
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    private static function validate(array $data, bool $isUpdate = false): array {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'El nombre es requerido';
        } elseif (strlen(trim($data['name'])) > 100) {
            $errors[] = 'El nombre no puede exceder 100 caracteres';
        }

        if (empty(trim($data['email'] ?? ''))) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no tiene un formato válido';
        }

        if (!$isUpdate && empty($data['password'] ?? '')) {
            $errors[] = 'La contraseña es requerida';
        } elseif (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }

        if (empty($data['role'] ?? '')) {
            $errors[] = 'El rol es requerido';
        } elseif (!in_array($data['role'], Auth::VALID_ROLES, true)) {
            $errors[] = 'El rol especificado no es válido: ' . $data['role'];
        }

        if (!isset($data['status']) || !in_array((int)$data['status'], [0, 1], true)) {
            $errors[] = 'El estado debe ser 0 (inactivo) o 1 (activo)';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }
}
