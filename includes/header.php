<?php
require_once __DIR__ . '/../config/config.php';

// Verificar autenticación si no es página de login
if (!strpos($_SERVER['REQUEST_URI'], 'login.php')) {
    Auth::requireLogin();
}

$currentUser = Auth::user();

/**
 * Función robusta para detectar la página activa
 * Compara el final de la URL para evitar falsos positivos
 */
function isActive($targetPath, $isHome = false) {
    // 1. Obtenemos la URL limpia que el usuario ve en el navegador
    $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // 2. Si es Inicio, solo activamos si es EXACTAMENTE la raíz o index.php
    if ($isHome) {
        // Obtenemos la última parte de la ruta
        $end = basename($currentUri);
        // Es activo si termina en index.php o si la ruta es la raíz del proyecto
        return ($end == 'index.php' || $end == '' || $end == 'public') ? 'active' : '';
    }
    
    // 3. Para los demás módulos (reports, users, etc)
    // Buscamos si el nombre del módulo existe como una "carpeta" en la URL
    return (strpos($currentUri, '/' . $targetPath . '/') !== false) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&amp;display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/skills/design/hh-design-system-skill/assets/tokens.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/skills/design/hh-design-system-skill/assets/components.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/hh-integration.css">
    
    <!-- Custom CSS for this page -->
    <?php if (isset($customCSS)): ?>
    <style>
        <?php echo $customCSS; ?>
    </style>
    <?php endif; ?>
</head>
<body>

<?php if (!strpos($_SERVER['REQUEST_URI'], 'login.php')): ?>
    <div class="ds-app">
        <aside class="ds-sidebar" id="appSidebar">
            <div class="ds-brand">
                <a class="hh-brand-link" href="<?php echo APP_URL; ?>/index.php">
                    <span class="ds-brand-mark">HH</span>
                    <span class="ds-brand-name">HABER HOLDING</span>
                </a>
                <span class="ds-brand-sub">Dashboard Interno</span>
                <button class="hh-sidebar-toggle" id="sidebarToggleDesktop" type="button" aria-label="Ocultar sidebar">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

            <nav>
                <a class="ds-nav-link <?php echo isActive('index.php', true); ?>" href="<?php echo APP_URL; ?>/index.php">
                    <i class="fas fa-home me-2"></i> Inicio
                </a>
                <a class="ds-nav-link <?php echo isActive('reports'); ?>" href="<?php echo APP_URL; ?>/modules/reports/index.php">
                    <i class="fas fa-chart-bar me-2"></i> Reportes
                </a>
                
                <a class="ds-nav-link <?php echo isActive('sales'); ?>" href="<?php echo APP_URL; ?>/modules/sales/index.php">
                    <i class="fas fa-shopping-cart me-2"></i> Ventas
                </a>

                <div class="ds-nav-section">Inventario</div>
                    <a class="ds-nav-link <?php echo isActive('inv_almacen'); ?>" href="<?php echo APP_URL; ?>/modules/inventory/warehouses.php">
                        <i class="fas fa-database me-2"></i> Almacen
                    </a>
                    <a class="ds-nav-link <?php echo isActive('inv_tiendas'); ?>" href="<?php echo APP_URL; ?>/modules/inventory/index.php">
                        <i class="fas fa-users me-2"></i> Tienda
                    </a>

                <?php if (Auth::hasRole('admin')): ?>
                    <div class="ds-nav-section">Administración</div>
                    <a class="ds-nav-link <?php echo isActive('monitor'); ?>" href="<?php echo APP_URL; ?>/modules/monitor/index.php">
                        <i class="fas fa-database me-2"></i> Monitor
                    </a>
                    <a class="ds-nav-link <?php echo isActive('users'); ?>" href="<?php echo APP_URL; ?>/modules/users/index.php">
                        <i class="fas fa-users me-2"></i> Usuarios
                    </a>
                <?php endif; ?>
            </nav>

            <div class="hh-sidebar-user dropdown">
                <button class="hh-btn hh-btn--ghost hh-btn--sm dropdown-toggle w-100 justify-content-start" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-avatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></span>
                    <?php echo htmlspecialchars($currentUser['name']); ?>
                </button>
                <ul class="dropdown-menu hh-menu w-100" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item hh-menu-item" href="#"><i class="fas fa-user me-2"></i> Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item hh-menu-item danger" href="<?php echo APP_URL; ?>/modules/home/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Salir</a></li>
                </ul>
            </div>
        </aside>
        <button class="hh-sidebar-overlay" id="sidebarOverlay" type="button" aria-label="Cerrar menu"></button>

        <div class="ds-main">
            <button class="hh-sidebar-reopen" id="sidebarReopenDesktop" type="button" aria-label="Mostrar sidebar">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="hh-mobile-topbar">
                <button class="hh-mobile-menu-btn" id="sidebarToggleMobile" type="button" aria-label="Abrir menu">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="hh-mobile-title"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></span>
            </div>
            <div class="container-fluid hh-page-shell">
                <header class="ds-page-header">
                    <h3 class="fw-bold"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-decoration-none">Home</a></li>
                            <?php if (isset($pageTitle) && $pageTitle !== 'Dashboard'): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $pageTitle; ?></li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </header>
                
                <main class="main-content">
<?php endif; ?>