-- =============================================
-- Script SQL para crear la base de datos del Dashboard
-- Compatible con SQL Server
-- =============================================

-- Crear base de datos
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'dashboard_db')
BEGIN
    CREATE DATABASE dashboard_db;
END
GO

USE dashboard_db;
GO

-- Crear tabla de usuarios
IF OBJECT_ID('dbo.users', 'U') IS NOT NULL
    DROP TABLE dbo.users;
GO

-- 1. Creamos la tabla primero
CREATE TABLE dbo.users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL,
    email NVARCHAR(255) NOT NULL UNIQUE,
    password NVARCHAR(255) NOT NULL,
    role NVARCHAR(20) NOT NULL DEFAULT 'user',
    status BIT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    last_login DATETIME NULL,
    updated_at DATETIME NULL
);
GO

-- 2. Creamos los índices por separado
CREATE INDEX idx_users_email ON dbo.users (email);
CREATE INDEX idx_users_status ON dbo.users (status);
CREATE INDEX idx_users_role ON dbo.users (role);
GO

-- Crear tabla de sesiones (opcional, para manejo de sesiones en BD)
IF OBJECT_ID('dbo.sessions', 'U') IS NOT NULL
    DROP TABLE dbo.sessions;
GO

CREATE TABLE dbo.sessions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    session_id NVARCHAR(255) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    ip_address NVARCHAR(45) NULL,
    user_agent NVARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE CASCADE
);
GO

-- Crear tabla de logs de actividad
IF OBJECT_ID('dbo.activity_logs', 'U') IS NOT NULL
    DROP TABLE dbo.activity_logs;
GO

CREATE TABLE dbo.activity_logs (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NULL,
    action NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX) NULL,
    ip_address NVARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE SET NULL
);
GO

-- Crear tabla de configuración
IF OBJECT_ID('dbo.settings', 'U') IS NOT NULL
    DROP TABLE dbo.settings;
GO

CREATE TABLE dbo.settings (
    id INT IDENTITY(1,1) PRIMARY KEY,
    setting_key NVARCHAR(100) NOT NULL UNIQUE,
    setting_value NVARCHAR(MAX) NULL,
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NULL
);
GO

-- Insertar configuraciones iniciales
INSERT INTO dbo.settings (setting_key, setting_value) VALUES
('site_name', 'Dashboard PHP'),
('site_description', 'Sistema de gestión de usuarios'),
('admin_email', 'admin@dashboard.com'),
('timezone', 'America/Mexico_City'),
('language', 'es');
GO

-- Crear usuario administrador por defecto
-- Contraseña: admin123 (hash generado con password_hash en PHP)
IF NOT EXISTS (SELECT id FROM dbo.users WHERE email = 'admin@dashboard.com')
BEGIN
    INSERT INTO dbo.users (name, email, password, role, status) 
    VALUES ('Administrador', 'admin@dashboard.com', '$2y$10$YourHashedPasswordHere', 'admin', 1);
END
GO

-- Procedimiento almacenado para registrar logs
IF OBJECT_ID('dbo.sp_log_activity', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_log_activity;
GO

CREATE PROCEDURE dbo.sp_log_activity
    @user_id INT = NULL,
    @action NVARCHAR(255),
    @description NVARCHAR(MAX) = NULL,
    @ip_address NVARCHAR(45) = NULL
AS
BEGIN
    INSERT INTO dbo.activity_logs (user_id, action, description, ip_address)
    VALUES (@user_id, @action, @description, @ip_address);
END
GO

-- Procedimiento almacenado para limpiar sesiones expiradas
IF OBJECT_ID('dbo.sp_clean_expired_sessions', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_clean_expired_sessions;
GO

CREATE PROCEDURE dbo.sp_clean_expired_sessions
AS
BEGIN
    DELETE FROM dbo.sessions WHERE expires_at < GETDATE();
END
GO

-- Vista de estadísticas de usuarios
IF OBJECT_ID('dbo.v_user_statistics', 'V') IS NOT NULL
    DROP VIEW dbo.v_user_statistics;
GO

CREATE VIEW dbo.v_user_statistics AS
SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins,
    SUM(CASE WHEN created_at >= DATEADD(month, -1, GETDATE()) THEN 1 ELSE 0 END) as new_users_this_month
FROM dbo.users;
GO

-- Vista de usuarios activos recientemente
IF OBJECT_ID('dbo.v_recent_active_users', 'V') IS NOT NULL
    DROP VIEW dbo.v_recent_active_users;
GO

CREATE VIEW dbo.v_recent_active_users AS
SELECT TOP 10
    u.id,
    u.name,
    u.email,
    u.role,
    u.last_login,
    u.created_at
FROM dbo.users u
WHERE u.status = 1 AND u.last_login IS NOT NULL
ORDER BY u.last_login DESC;
GO


-- Registro de mensajeria
IF OBJECT_ID('dbo.log_mensajeria', 'U') IS NOT NULL
    DROP TABLE dbo.log_mensajeria;
GO
CREATE TABLE dbo.log_mensajeria (
    id INT IDENTITY(1,1) PRIMARY KEY,
    tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('EMAIL', 'SMS')),
    destinatario VARCHAR(100) NOT NULL,
    asunto_o_mensaje NVARCHAR(MAX), -- Usamos NVARCHAR para soportar cualquier caracter
    template VARCHAR(50),
    estatus VARCHAR(10) NOT NULL CHECK (estatus IN ('EXITO', 'ERROR')),
    error_detalle NVARCHAR(MAX),
    fecha_envio DATETIME DEFAULT GETDATE()
);

-- Índices adicionales para mejor rendimiento
CREATE INDEX idx_activity_logs_user_id ON dbo.activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON dbo.activity_logs(created_at);
CREATE INDEX idx_sessions_user_id ON dbo.sessions(user_id);
CREATE INDEX idx_sessions_expires_at ON dbo.sessions(expires_at);
GO



-- Fin del script
-- =============================================
-- IMPORTANTE: 
-- 1. Ejecutar este script en SQL Server Management Studio o desde el comando sqlcmd
-- 2. La contraseña por defecto del admin es admin123 (cambiarla después del primer login)
-- 3. Actualizar las credenciales en config/database.php
-- =============================================

