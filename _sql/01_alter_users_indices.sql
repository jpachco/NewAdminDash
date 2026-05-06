-- ─────────────────────────────────────────
-- ÍNDICES PARA TABLA users EXISTENTE
-- Solo ejecutar si no existen ya
-- ─────────────────────────────────────────

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_users_email' AND object_id = OBJECT_ID('users'))
    CREATE UNIQUE INDEX idx_users_email  ON users (email);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_users_role' AND object_id = OBJECT_ID('users'))
    CREATE INDEX idx_users_role   ON users (role);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'idx_users_status' AND object_id = OBJECT_ID('users'))
    CREATE INDEX idx_users_status ON users (status);

PRINT 'Índices verificados correctamente.';

-- ─────────────────────────────────────────
-- USUARIO ADMIN INICIAL
-- Contraseña: Admin1234!
-- IMPORTANTE: Cámbiala inmediatamente tras el primer login
-- ─────────────────────────────────────────
IF NOT EXISTS (SELECT id FROM users WHERE email = 'admin@admindash.com')
BEGIN
    INSERT INTO users (name, email, password, role, status, created_at)
    VALUES (
        'Administrador',
        'admin@admindash.com',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        1,
        GETDATE()
    );

    PRINT 'Usuario admin inicial creado: admin@admindash.com / Admin1234!';
END
ELSE
BEGIN
    PRINT 'El usuario admin ya existe, no se insertó.';
END
