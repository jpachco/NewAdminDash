<?php
/**
 * Script de Instalación Automática del Dashboard
 * Este script guía el proceso de configuración inicial
 */

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];
$success = [];

// Paso 1: Verificación de requisitos
if ($step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar requisitos del servidor
    $requirements = [
        'PHP 7.4+' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO_SQLSRV Extension' => extension_loaded('pdo_sqlsrv'),
        'MBString Extension' => extension_loaded('mbstring'),
        'JSON Extension' => extension_loaded('json'),
        'Session Extension' => extension_loaded('session'),
        'Configuración de escritura' => is_writable(__DIR__ . '/config'),
    ];
    
    if (in_array(false, $requirements)) {
        $step = 1;
        $errors[] = 'Por favor corrige los requisitos antes de continuar';
    } else {
        $step = 2;
    }
}

// Paso 2: Configuración de base de datos
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbConfig = [
        'host' => trim($_POST['db_host']),
        'instance' => trim($_POST['db_instance']),
        'database' => trim($_POST['db_database']),
        'username' => trim($_POST['db_username']),
        'password' => trim($_POST['db_password']),
        'app_url' => trim($_POST['app_url'])
    ];
    
    // Validaciones básicas
    if (empty($dbConfig['host']) || empty($dbConfig['database']) || empty($dbConfig['username'])) {
        $errors[] = 'Por favor completa todos los campos requeridos';
    } else {
        // Probar conexión
        try {
            $dsn = "sqlsrv:Server={$dbConfig['host']}\\{$dbConfig['instance']};Database={$dbConfig['database']}";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Ejecutar script SQL
            $sqlScript = file_get_contents(__DIR__ . '/database.sql');
            if ($sqlScript) {
                // Dividir script en sentencias individuales
                $statements = preg_split('/\bGO\b/', $sqlScript);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        try {
                            $pdo->exec($statement);
                        } catch (PDOException $e) {
                            // Ignorar errores de objetos ya existentes
                            if (strpos($e->getMessage(), 'There is already an object') === false) {
                                throw $e;
                            }
                        }
                    }
                }
                
                // Actualizar archivo de configuración
                $configFile = __DIR__ . '/config/database.php';
                $configContent = file_get_contents($configFile);
                
                $configContent = str_replace('localhost', $dbConfig['host'], $configContent);
                $configContent = str_replace('SQLEXPRESS', $dbConfig['instance'], $configContent);
                $configContent = str_replace('dashboard_db', $dbConfig['database'], $configContent);
                $configContent = str_replace("'sa'", "'" . $dbConfig['username'] . "'", $configContent);
                $configContent = str_replace("''", "'" . $dbConfig['password'] . "'", $configContent);
                
                file_put_contents($configFile, $configContent);
                
                $success[] = 'Base de datos configurada exitosamente';
                $success[] = 'Tablas creadas exitosamente';
                $step = 3;
            }
        } catch (PDOException $e) {
            $errors[] = 'Error de conexión a la base de datos: ' . $e->getMessage();
            $step = 2;
        }
    }
}

// Paso 3: Crear usuario administrador
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminData = [
        'name' => trim($_POST['admin_name']),
        'email' => trim($_POST['admin_email']),
        'password' => $_POST['admin_password'],
        'confirm_password' => $_POST['admin_confirm_password']
    ];
    
    // Validaciones
    if (empty($adminData['name']) || empty($adminData['email']) || empty($adminData['password'])) {
        $errors[] = 'Por favor completa todos los campos';
    } elseif (strlen($adminData['password']) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($adminData['password'] !== $adminData['confirm_password']) {
        $errors[] = 'Las contraseñas no coinciden';
    } else {
        try {
            $hashedPassword = password_hash($adminData['password'], PASSWORD_DEFAULT);
            
            $pdo = new PDO(
                "sqlsrv:Server={$dbConfig['host']}\\{$dbConfig['instance']};Database={$dbConfig['database']}",
                $dbConfig['username'],
                $dbConfig['password']
            );
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) 
                                  VALUES (?, ?, ?, 'admin', 1, GETDATE())");
            $stmt->execute([
                $adminData['name'],
                $adminData['email'],
                $hashedPassword
            ]);
            
            $success[] = 'Usuario administrador creado exitosamente';
            $success[] = 'Instalación completada';
            $step = 4;
            
            // Eliminar archivo de instalación
            @unlink(__FILE__);
        } catch (PDOException $e) {
            $errors[] = 'Error al crear usuario: ' . $e->getMessage();
            $step = 3;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Dashboard PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-card {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .install-header {
            background: linear-gradient(180deg, #f8f9fc 0%, #eaecf4 100%);
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .install-body {
            padding: 30px;
        }
        .progress-step {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e3e6f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #5a5c69;
        }
        .step.active .step-number {
            background: #4e73df;
            color: white;
        }
        .step.completed .step-number {
            background: #1cc88a;
            color: white;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="install-header">
            <h2><i class="fas fa-tachometer-alt"></i> Dashboard PHP</h2>
            <p class="text-muted">Asistente de Instalación</p>
        </div>
        
        <div class="install-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-1"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php foreach ($success as $s): ?>
                        <p class="mb-1"><i class="fas fa-check-circle"></i> <?php echo $s; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="progress-step">
                <div class="step <?php echo $step >= 1 ? 'completed' : ''; ?>">
                    <div class="step-number">1</div>
                    <small>Requisitos</small>
                </div>
                <div class="step <?php echo $step >= 2 ? ($step === 2 ? 'active' : 'completed') : ''; ?>">
                    <div class="step-number">2</div>
                    <small>Base de Datos</small>
                </div>
                <div class="step <?php echo $step >= 3 ? ($step === 3 ? 'active' : 'completed') : ''; ?>">
                    <div class="step-number">3</div>
                    <small>Usuario Admin</small>
                </div>
                <div class="step <?php echo $step === 4 ? 'active' : ''; ?>">
                    <div class="step-number">4</div>
                    <small>Finalizado</small>
                </div>
            </div>
            
            <?php if ($step === 1): ?>
                <h5>Paso 1: Verificación de Requisitos</h5>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PHP Version <?php echo PHP_VERSION; ?>
                        <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
                            <span class="badge bg-success">OK</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Fallo</span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PDO Extension
                        <?php if (extension_loaded('pdo')): ?>
                            <span class="badge bg-success">OK</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Fallo</span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PDO_SQLSRV Extension
                        <?php if (extension_loaded('pdo_sqlsrv')): ?>
                            <span class="badge bg-success">OK</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Fallo</span>
                        <?php endif; ?>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Configuración de escritura
                        <?php if (is_writable(__DIR__ . '/config')): ?>
                            <span class="badge bg-success">OK</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Fallo</span>
                        <?php endif; ?>
                    </li>
                </ul>
                <form method="POST">
                    <button type="submit" class="btn btn-primary w-100">
                        Continuar <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            
            <?php elseif ($step === 2): ?>
                <h5>Paso 2: Configuración de Base de Datos</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Host de SQL Server *</label>
                        <input type="text" class="form-control" name="db_host" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instancia *</label>
                        <input type="text" class="form-control" name="db_instance" value="SQLEXPRESS" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de Base de Datos *</label>
                        <input type="text" class="form-control" name="db_database" value="dashboard_db" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario *</label>
                        <input type="text" class="form-control" name="db_username" value="sa" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="db_password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL de la Aplicación *</label>
                        <input type="url" class="form-control" name="app_url" value="http://localhost/dashboard" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Probar Conexión <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            
            <?php elseif ($step === 3): ?>
                <h5>Paso 3: Crear Usuario Administrador</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" name="admin_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="admin_email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="admin_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar Contraseña *</label>
                        <input type="password" class="form-control" name="admin_confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        Crear Administrador <i class="fas fa-check"></i>
                    </button>
                </form>
            
            <?php elseif ($step === 4): ?>
                <div class="text-center">
                    <div style="font-size: 5rem; color: #1cc88a;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5 class="mt-3">¡Instalación Completada!</h5>
                    <p class="text-muted">Tu dashboard está listo para usar.</p>
                    <a href="modules/home/login.php" class="btn btn-primary">
                        Ir al Login <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>