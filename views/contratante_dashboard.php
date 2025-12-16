<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Contratante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php 
    $action = 'contratante_dashboard';
    include __DIR__ . '/shared/navbar.php'; 
    ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <h1>Panel de Contratante</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($usuario_actual['nombre_completo'] ?? 'Contratante'); ?></p>
            </div>
            
            <!-- Estadísticas -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-primary">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['total_contratos'] ?? 0; ?></div>
                        <div class="stat-label">Contratos Creados</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['contratos_activos'] ?? 0; ?></div>
                        <div class="stat-label">Contratos Activos</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['contratos_pendientes'] ?? 0; ?></div>
                        <div class="stat-label">Pendientes de Datos</div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="icon-box bg-info">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $estadisticas['contratos_mes'] ?? 0; ?></div>
                        <div class="stat-label">Este Mes</div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header">
                            <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="index.php?action=contratante_contratos" class="btn btn-primary-custom">
                                    <i class="bi bi-file-earmark-plus me-2"></i>Gestionar Contratos
                                </a>
                                <a href="index.php?action=contratante_aspirante" class="btn btn-success">
                                    <i class="bi bi-person-plus me-2"></i>Aspirante
                                </a>
                                <a href="index.php?action=contratante_empleados" class="btn btn-info text-white">
                                    <i class="bi bi-people me-2"></i>Empleados
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header">
                            <i class="bi bi-clock-history me-2"></i>Contratos Recientes
                        </div>
                        <div class="card-body">
                            <?php if (!empty($contratos_recientes)): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($contratos_recientes as $contrato): ?>
                                        <li class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                            <div class="icon-box bg-primary me-3" style="width:40px;height:40px;font-size:0.9rem;">
                                                <i class="bi bi-file-earmark"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong><?php echo htmlspecialchars($contrato['empleado_nombre']); ?></strong>
                                                <small class="text-muted d-block"><?php echo date('d/m/Y', strtotime($contrato['fecha_firma'])); ?></small>
                                            </div>
                                            <span class="badge bg-<?php echo $contrato['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($contrato['estado']); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted mb-0">No hay contratos recientes</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

