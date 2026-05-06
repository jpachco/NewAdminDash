-- ─────────────────────────────────────────
-- TABLA: login_attempts
-- Registra intentos fallidos de login para
-- protección contra fuerza bruta
-- Compatible con SQL Server
-- ─────────────────────────────────────────

IF NOT EXISTS (
    SELECT * FROM sysobjects
    WHERE name = 'login_attempts' AND xtype = 'U'
)
BEGIN
    CREATE TABLE login_attempts (
        id           INT IDENTITY(1,1) PRIMARY KEY,
        email        NVARCHAR(255)    NOT NULL,
        ip_address   NVARCHAR(45)     NOT NULL,  -- Soporta IPv4 e IPv6
        attempted_at DATETIME         NOT NULL DEFAULT GETDATE(),

    );

    -- Índices separados (sintaxis correcta para SQL Server)
    CREATE INDEX idx_login_email        ON login_attempts (email);
    CREATE INDEX idx_login_ip           ON login_attempts (ip_address);
    CREATE INDEX idx_login_attempted_at ON login_attempts (attempted_at);

    PRINT 'Tabla login_attempts creada correctamente.';
END
ELSE
BEGIN
    PRINT 'La tabla login_attempts ya existe.';
END

-- ─────────────────────────────────────────
-- JOB DE LIMPIEZA (opcional)
-- Elimina registros con más de 24 horas
-- Ejecutar periódicamente o como SQL Agent Job
-- ─────────────────────────────────────────
-- DELETE FROM login_attempts
-- WHERE attempted_at < DATEADD(HOUR, -24, GETDATE());
