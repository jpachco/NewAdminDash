# Dashboard PHP con SQL Server

Un sistema de dashboard completo desarrollado en PHP puro, con diseño moderno usando Bootstrap 5 y conexión a base de datos SQL Server.

## 🚀 Características

- **Autenticación completa**: Sistema de login, logout y gestión de sesiones
- **Gestión de usuarios**: CRUD completo (Crear, Leer, Actualizar, Eliminar)
- **Panel de control**: Dashboard con estadísticas y gráficos
- **Diseño responsivo**: Interfaz moderna con Bootstrap 5
- **Arquitectura limpia**: Código bien estructurado y organizado
- **SQL Server**: Base de datos robusta y escalable
- **Seguridad**: Validaciones, hashing de contraseñas y protecciones CSRF

## 📋 Requisitos Previos

### Servidor
- PHP 7.4 o superior (recomendado PHP 8.x)
- SQL Server 2012 o superior
- Servidor web (Apache, Nginx, o IIS)

### Extensiones de PHP requeridas
- pdo_sqlsrv
- sqlsrv
- mbstring
- json
- session

### Extensiones recomendadas
- openssl (para seguridad mejorada)
- gd (para manipulación de imágenes)

## 🔧 Instalación

### 1. Instalar drivers de SQL Server para PHP

#### Windows:
Descargar desde: https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server

Copiar los archivos DLL correspondientes a la carpeta `ext` de tu instalación de PHP y habilitarlos en `php.ini`:
```ini
extension=pdo_sqlsrv
extension=sqlsrv
```

#### Linux (Debian/Ubuntu):
```bash
# Instalar drivers
sudo apt-get update
sudo apt-get install php-sqlsrv

# O compilar desde fuente
# Ver documentación oficial para más detalles
```

### 2. Configurar la Base de Datos

#### Opción A: Usar SQL Server Management Studio (SSMS)
1. Abrir SSMS
2. Conectarse a tu instancia de SQL Server
3. Abrir el archivo `database.sql`
4. Ejecutar el script completo

#### Opción B: Usar sqlcmd desde línea de comandos
```bash
sqlcmd -S localhost\SQLEXPRESS -U sa -P tu_contraseña -i database.sql
```

#### Opción C: Crear manualmente
Si prefieres crear la base de datos manualmente, sigue estos pasos:

```sql
-- Crear la base de datos
CREATE DATABASE dashboard_db;

-- Usar la base de datos
USE dashboard_db;

-- Ejecutar el contenido de database.sql
```

### 3. Configurar el Dashboard

1. **Copiar los archivos**:
   ```bash
   # Copiar la carpeta dashboard a tu servidor web
   cp -r dashboard /var/www/html/
   # o en Windows
   xcopy dashboard C:\inetpub\wwwroot\dashboard /E /I
   ```

2. **Configurar la conexión a la base de datos**:
   
   Editar el archivo `config/database.php`:
   ```php
   private static $host = 'localhost';
   private static $instance_name = 'SQLEXPRESS'; // Cambiar según tu instancia
   private static $database = 'dashboard_db';
   private static $username = 'sa';            // Tu usuario de SQL Server
   private static $password = '';              // Tu contraseña
   ```

3. **Configurar la URL del sistema**:
   
   Editar el archivo `config/config.php`:
   ```php
   define('APP_URL', 'http://localhost/dashboard');
   ```

4. **Configurar permisos** (Linux):
   ```bash
   # Dar permisos de escritura donde sea necesario
   sudo chown -R www-data:www-data /var/www/html/dashboard
   chmod -R 755 /var/www/html/dashboard
   ```

## 🌐 Acceso al Sistema

1. Abre tu navegador web
2. Navega a: `http://localhost/dashboard/`
3. El sistema te redireccionará automáticamente a la página de login

### Usuario Administrador por Defecto
- **Email**: `admin@dashboard.com`
- **Contraseña**: `admin123`

**IMPORTANTE**: Cambia esta contraseña inmediatamente después del primer login.

## 📁 Estructura del Proyecto

```
dashboard/
├── assets/
│   ├── css/
│   │   └── style.css           # Estilos personalizados
│   ├── js/
│   │   └── main.js             # JavaScript principal
│   └── images/                 # Imágenes del sistema
├── classes/
│   ├── Auth.php                # Sistema de autenticación
│   ├── User.php                # Modelo de usuarios
│   └── Dashboard.php           # Lógica del dashboard
├── config/
│   ├── config.php              # Configuración general
│   └── database.php            # Configuración de BD
├── includes/
│   ├── header.php              # Encabezado HTML
│   └── footer.php              # Pie de página HTML
├── modules/
│   ├── home/
│   │   ├── login.php           # Página de login
│   │   └── logout.php          # Cierre de sesión
│   ├── users/
│   │   ├── index.php           # Lista de usuarios
│   │   ├── create.php          # Crear usuario
│   │   ├── edit.php            # Editar usuario
│   │   ├── view.php            # Ver usuario
│   │   └── delete.php          # Eliminar usuario
│   └── reports/
│       └── index.php           # Reportes y estadísticas
├── database.sql                # Script SQL
├── index.php                   # Página principal del dashboard
└── README.md                   # Este archivo
```

## 🔐 Seguridad

### Recomendaciones de Seguridad

1. **Cambiar credenciales por defecto**
   - Cambia la contraseña del administrador
   - Crea un nuevo usuario administrador y elimina el defecto

2. **Configurar SSL/HTTPS**
   - Instala un certificado SSL
   - Configura tu servidor para usar HTTPS
   - Actualiza `APP_URL` para usar `https://`

3. **Configurar防火墙**
   - Restringe acceso a la base de datos
   - Usa IPs permitidas en la configuración de SQL Server

4. **Validación de entradas**
   - El sistema ya incluye validaciones
   - Sanitiza todas las entradas de usuario

5. **Backups regulares**
   - Realiza backups diarios de la base de datos
   - Guarda los backups en ubicaciones seguras

### Configuración Avanzada de Seguridad

En `config/config.php`, ajusta estos parámetros:

```php
// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Activar en producción con HTTPS

// Configuración de errores (desactivar en producción)
error_reporting(0);
ini_set('display_errors', 0);
```

## 🎨 Personalización

### Cambiar el Diseño

1. **Colores y estilos**: Edita `assets/css/style.css`
2. **Logo y branding**: Modifica `includes/header.php`
3. **Layout**: Ajusta las clases de Bootstrap en los archivos PHP

### Agregar Nuevos Módulos

1. Crea la carpeta del módulo en `modules/`
2. Implementa la lógica en las clases correspondientes
3. Agrega el link en el sidebar (`includes/header.php`)

### Configurar SQL Server

Si usas una configuración diferente:

```php
// Para conectar a una instancia remota
private static $host = '192.168.1.100';
private static $instance_name = 'MSSQLSERVER';

// Para usar autenticación de Windows
// Modificar Database::getConnection() para usar autenticación Windows
```

## 🐛 Solución de Problemas

### Error de conexión a SQL Server
**Problema**: No se puede conectar a la base de datos

**Soluciones**:
1. Verifica que los drivers de SQL Server estén instalados
2. Confirma que SQL Server esté corriendo
3. Verifica las credenciales en `config/database.php`
4. Prueba la conexión con sqlcmd

### Error de sesión
**Problema**: La sesión no funciona correctamente

**Soluciones**:
1. Verifica que la carpeta de sesiones tenga permisos de escritura
2. Configura correctamente `session.save_path` en php.ini
3. Revisa la configuración de cookies en `config/config.php`

### Gráficos no se muestran
**Problema**: Los gráficos de Chart.js no aparecen

**Soluciones**:
1. Verifica la conexión a internet (para cargar Chart.js desde CDN)
2. Revisa la consola del navegador para errores de JavaScript
3. Confirma que los datos se estén pasando correctamente a las vistas

### Errores de permisos
**Problema**: Errores de escritura en archivos

**Soluciones**:
```bash
# Linux
sudo chown -R www-data:www-data /var/www/html/dashboard
chmod -R 755 /var/www/html/dashboard
chmod -R 775 /var/www/html/dashboard/assets
```

## 📊 Scripts de Base de Datos

El archivo `database.sql` incluye:

- **Tablas**: users, sessions, activity_logs, settings
- **Vistas**: v_user_statistics, v_recent_active_users
- **Procedimientos almacenados**: sp_log_activity, sp_clean_expired_sessions
- **Índices**: Para mejor rendimiento
- **Usuario administrador por defecto**

## 🔄 Actualización

Para actualizar el sistema:

1. Hacer backup de la base de datos
2. Reemplazar los archivos PHP
3. Ejecutar scripts de migración (si existen)
4. Probar el sistema en ambiente de desarrollo primero

## 📝 Configuración de Producción

Antes de poner el sistema en producción:

1. **Desactivar modo de desarrollo**:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Configurar HTTPS**:
   - Instalar certificado SSL
   - Actualizar `APP_URL` a `https://...`
   - Activar `session.cookie_secure = 1`

3. **Configurar backups**:
   - Script de backup automático
   - Retención de backups

4. **Monitorización**:
   - Configurar logs de errores
   - Monitorear uso de recursos

## 📞 Soporte

Para reportar bugs o solicitar características:

1. Revisa la documentación
2. Verifica los logs de error
3. Proporciona detalles del entorno (PHP version, SQL Server version)

## 📄 Licencia

Este proyecto es de código abierto y está disponible para uso educativo y comercial.

## 👨‍💻 Desarrollo

### Estándares de Código
- PSR-4 para autoload de clases
- PSR-12 para estilo de código
- Comentarios PHPDoc
- Nomenclatura clara y descriptiva

### Contribuyendo
Para contribuir al proyecto:

1. Fork el proyecto
2. Crea una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

## 🎯 Próximas Características Planificadas

- [ ] Sistema de roles y permisos avanzados
- [ ] Módulo de reportes con exportación a PDF/Excel
- [ ] Sistema de notificaciones
- [ ] API REST
- [ ] Módulo de configuración del sistema
- [ ] Sistema de archivos y documentación
- [ ] Integración con servicios externos

---

**Desarrollado con ❤️ usando PHP puro, Bootstrap 5 y SQL Server**