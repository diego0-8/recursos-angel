<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php 
    $action = 'admin_dashboard';
    include __DIR__ . '/shared/navbar.php'; 
    ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <h1>Inicio</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($usuario_actual['nombre_completo'] ?? 'Administrador'); ?></p>
            </div>
            
            <!-- Estadísticas -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['total_empleados'] ?? 0; ?></div>
                        <div class="stat-label">Total Empleados</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-success">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['contratos_activos'] ?? 0; ?></div>
                        <div class="stat-label">Contratos Activos</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-warning">
                            <i class="bi bi-folder"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['documentos_pendientes'] ?? 0; ?></div>
                        <div class="stat-label">Documentos Pendientes</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-info">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['nuevos_mes'] ?? 0; ?></div>
                        <div class="stat-label">Nuevos este Mes</div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-lightning me-2"></i>Acciones Rápidas</span>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="index.php?action=nuevo_empleado" class="btn btn-primary-custom">
                                    <i class="bi bi-person-plus me-2"></i>Registrar Nuevo Empleado
                                </a>
                                <a href="index.php?action=nuevo_contrato" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-plus me-2"></i>Crear Contrato
                                </a>
                                <a href="index.php?action=documentos" class="btn btn-outline-secondary">
                                    <i class="bi bi-folder-plus me-2"></i>Gestionar Documentos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header">
                            <i class="bi bi-clock-history me-2"></i>Actividad Reciente
                        </div>
                        <div class="card-body">
                            <?php if (!empty($actividad_reciente)): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($actividad_reciente as $actividad): ?>
                                        <li class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary me-3" style="width:40px;height:40px;font-size:0.9rem;">
                                                <i class="bi bi-activity"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($actividad['descripcion']); ?></strong>
                                                <small class="text-muted d-block"><?php echo $actividad['fecha']; ?></small>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted mb-0">No hay actividad reciente</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Empleados Recientes -->
            <div class="row">
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-people me-2"></i>Empleados Recientes</span>
                            <a href="index.php?action=empleados" class="btn btn-sm btn-primary-custom">Ver todos</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Cédula</th>
                                            <th>Fecha Ingreso</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($empleados_recientes)): ?>
                                            <?php foreach ($empleados_recientes as $emp): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($emp['nombre_completo']); ?></td>
                                                    <td><?php echo htmlspecialchars($emp['cedula']); ?></td>
                                                    <td><?php echo $emp['created_at']; ?></td>
                                                    <td>
                                                        <span class="badge-status badge-<?php echo $emp['estado']; ?>">
                                                            <?php echo ucfirst($emp['estado']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="index.php?action=ver_empleado&id=<?php echo $emp['id']; ?>" 
                                                           class="btn btn-action btn-outline-primary" title="Ver">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="index.php?action=editar_empleado&id=<?php echo $emp['id']; ?>" 
                                                           class="btn btn-action btn-outline-warning" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No hay empleados registrados
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

