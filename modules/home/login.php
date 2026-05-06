<?php
require_once __DIR__ . '/../../config/config.php';

// ─────────────────────────────────────────
// VERIFICAR SI YA ESTÁ LOGUEADO
// (siempre primero, antes de cualquier proceso)
// ─────────────────────────────────────────
if (Auth::check()) {
    header('Location: ' . APP_URL . '/index.php');
    exit();
}

$pageTitle = 'Iniciar Sesión';
$error     = '';
$success   = '';
$emailValue = ''; // Para recordar el email si falla el login

// Mensaje de sesión expirada
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $error = 'Tu sesión ha expirado por inactividad. Inicia sesión nuevamente.';
}

// Mensaje de cierre de sesión exitoso
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $success = 'Has cerrado sesión correctamente.';
}

// ─────────────────────────────────────────
// PROCESAR FORMULARIO DE LOGIN
// ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $emailValue = htmlspecialchars($email); // Recordar email al recargar

    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        // Auth::login() retorna ['success' => bool, 'message' => string]
        $result = Auth::login($email, $password);

        if ($result['success']) {
            header('Location: ' . APP_URL . '/index.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

require_once INCLUDES_PATH . '/header.php';
?>

<div class="login-page">
    <div class="card login-card fade-in">

        <div class="login-header">
            <h2><?php echo APP_NAME; ?></h2>
            <p>Inicia sesión para continuar</p>
        </div>

        <div class="login-body">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">

                <!-- CSRF Token (protección contra ataques cross-site) -->
                <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           required
                           autocomplete="email"
                           placeholder="tu@email.com"
                           value="<?php echo $emailValue; ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           required
                           autocomplete="current-password"
                           placeholder="••••••••">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-custom btn-primary w-100" id="btnLogin">
                        <i class="fas fa-sign-in-alt"></i>
                        Iniciar Sesión
                    </button>
                </div>

            </form>
        </div>

        <div class="login-footer">
            <p class="text-muted">
                ¿Olvidaste tu contraseña?
                <a href="mailto:soporte@admindash.com" style="color: var(--primary-color);">Contacta al administrador</a>
            </p>
        </div>

    </div>
</div>

<script>
// Deshabilitar botón al enviar para evitar doble submit
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
