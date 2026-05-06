-- ─────────────────────────────────────────
-- Agregar columna 'tipo' a tabla users
-- Compatible con SQL Server
-- ─────────────────────────────────────────

IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'tipo'
)
BEGIN
    ALTER TABLE users
    ADD tipo NVARCHAR(50) NULL;

    PRINT 'Columna tipo agregada correctamente.';
END
ELSE
BEGIN
    PRINT 'La columna tipo ya existe.';
END

-- ─────────────────────────────────────────
-- Tabla log_mensajeria (si no existe)
-- ─────────────────────────────────────────
IF NOT EXISTS (
    SELECT * FROM sysobjects
    WHERE name = 'log_mensajeria' AND xtype = 'U'
)
BEGIN
    CREATE TABLE log_mensajeria (
        id               INT IDENTITY(1,1) PRIMARY KEY,
        tipo             NVARCHAR(10)  NOT NULL,   -- EMAIL | SMS
        destinatario     NVARCHAR(255) NOT NULL,
        asunto_o_mensaje NVARCHAR(500) NULL,
        template         NVARCHAR(100) NULL,
        estatus          NVARCHAR(10)  NOT NULL,   -- EXITO | ERROR
        error_detalle    NVARCHAR(MAX) NULL,
        created_at       DATETIME      NOT NULL DEFAULT GETDATE()
    );

    CREATE INDEX idx_log_tipo      ON log_mensajeria (tipo);
    CREATE INDEX idx_log_estatus   ON log_mensajeria (estatus);
    CREATE INDEX idx_log_created   ON log_mensajeria (created_at);

    PRINT 'Tabla log_mensajeria creada correctamente.';
END
ELSE
BEGIN
    PRINT 'La tabla log_mensajeria ya existe.';
END
