<?php

require_once __DIR__ . '/config/config.php';

$pageTitle = 'Dashboard';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Obtener estadísticas
$stats = Dashboard::getStatistics();
$recentUsers = Dashboard::getRecentUsers(2);
$usersByRole = Dashboard::getUsersByRole();
$usersByMonth = Dashboard::getUsersByMonth();

// Preparar datos para gráficos
$chartLabels = [];
$chartData = [];
foreach (array_slice(array_reverse($usersByMonth), 0, 6) as $data) {
    $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $chartLabels[] = $months[$data['month'] - 1];
    $chartData[] = $data['count'];
}

// JavaScript para gráficos
$customJS = <<<EOT
// Gráfico de usuarios por mes
const ctxUsers = document.getElementById('usersChart').getContext('2d');
new Chart(ctxUsers, {
    type: 'line',
    data: {
        labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
        datasets: [{
            label: 'Nuevos Usuarios',
            data: ['12', '19', '3', '5', '2', '3'],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Gráfico de usuarios por rol
const ctxRoles = document.getElementById('rolesChart').getContext('2d');
new Chart(ctxRoles, {
    type: 'doughnut',
    data: {
        labels: ['Administradores', 'Usuarios'],
        datasets: [{
            data: [1, 2],
            backgroundColor: ['#4e73df', '#1cc88a'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
EOT;

require_once INCLUDES_PATH . '/header.php';
?>

<div class="row fade-in">
    <!-- Cards de Estadísticas -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="stat-title">Total Usuarios</div>
                        <div class="stat-value"><?php echo number_format($stats['users']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="stat-title">Administradores</div>
                        <div class="stat-value"><?php echo number_format($stats['admins']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-shield stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="stat-title">Activos Este Mes</div>
                        <div class="stat-value"><?php echo number_format($stats['active_users']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="stat-title">Nuevos Usuarios</div>
                        <div class="stat-value"><?php echo number_format($stats['new_users']); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row fade-in">
    <!-- Gráfico de Usuarios por Mes -->
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Usuarios Registrados por Mes</h6>
            </div>
            <div class="card-body">
                <div style="height: 320px;">
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de Usuarios por Rol -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Usuarios por Rol</h6>
            </div>
            <div class="card-body">
                <div style="height: 320px;">
                    <canvas id="rolesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row fade-in">
    <!-- Últimos Usuarios Registrados -->
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Últimos Usuarios Registrados</h6>
                <a href="<?php echo APP_URL ?>/modules/users/index.php" class="btn btn-sm btn-primary">
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-custom table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentUsers)): ?>
                                <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo ucfirst($user['role']); ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo APP_URL ?>/modules/users/view.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/modules/users/edit.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        No hay usuarios registrados
                                    </td>
                                </tr>
                            <?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once INCLUDES_PATH . '/footer.php';
?>