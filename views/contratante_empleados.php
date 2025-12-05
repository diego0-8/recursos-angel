<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - Contratante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <style>
        .empresa-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .empresa-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(30, 60, 114, 0.2);
        }
        .empresa-card .icon-empresa {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        .empresa-card.onix .icon-empresa {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        .empresa-card.nexdata .icon-empresa {
            background: linear-gradient(135deg, #11998e, #38ef7d);
        }
        .empresa-card.tys .icon-empresa {
            background: linear-gradient(135deg, #ee0979, #ff6a00);
        }
        .empresa-card h4 {
            color: var(--dark-color);
            font-weight: 600;
        }
        .empleados-section {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <?php 
    $action = 'contratante_empleados';
    include __DIR__ . '/shared/navbar.php'; 
    ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Gestión de Empleados</h1>
                        <p>Administra los empleados de cada empresa</p>
                    </div>
                    <a href="index.php?action=contratante_dashboard" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>Operación realizada exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Selección de Empresas -->
            <?php if (empty($empresa_seleccionada)): ?>
                <div class="mb-4">
                    <h3 class="mb-4">Seleccionar Empresa</h3>
                </div>
                
                <div class="row g-4">
                    <?php foreach ($empresas as $empresa): ?>
                        <?php 
                        $iconos = [
                            'ONIX' => 'bi-headset',
                            'NEXDATA' => 'bi-database',
                            'TYS' => 'bi-building'
                        ];
                        $icono = $iconos[$empresa['codigo']] ?? 'bi-building';
                        ?>
                        <div class="col-md-4">
                            <a href="index.php?action=contratante_empleados&empresa_id=<?php echo $empresa['id']; ?>" 
                               class="text-decoration-none">
                                <div class="card-custom empresa-card <?php echo strtolower($empresa['codigo']); ?> text-center p-4">
                                    <div class="icon-empresa">
                                        <i class="bi <?php echo $icono; ?>"></i>
                                    </div>
                                    <h4><?php echo htmlspecialchars($empresa['nombre']); ?></h4>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($empresa['descripcion'] ?? ''); ?></p>
                                    <div class="mt-3">
                                        <span class="badge bg-primary">
                                            <i class="bi bi-people me-1"></i>Ver Empleados
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Lista de Empleados de la Empresa -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="mb-0">Empleados de <?php echo htmlspecialchars($empresa_seleccionada['nombre']); ?></h3>
                            <p class="text-muted mb-0">Total: <?php echo count($empleados); ?> empleado(s)</p>
                        </div>
                        <a href="index.php?action=contratante_empleados" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver a Empresas
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($empleados)): ?>
                    <div class="card-custom mb-4">
                        <div class="card-header">
                            <i class="bi bi-people me-2"></i>Lista de Empleados
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Cédula</th>
                                            <th>Estado</th>
                                            <th>Documentos</th>
                                            <th>Último Contrato</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($empleados as $empleado): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($empleado['nombre']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($empleado['cedula']); ?></td>
                                                <td>
                                                    <?php
                                                    $estadoClass = $empleado['estado'] === 'activo' ? 'success' : 'secondary';
                                                    $estadoLabel = $empleado['estado'] === 'activo' ? 'Activo' : 'Inactivo';
                                                    ?>
                                                    <span class="badge bg-<?php echo $estadoClass; ?>">
                                                        <?php echo $estadoLabel; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $empleado['total_documentos'] ?? 0; ?> documento(s)
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($empleado['ultimo_contrato_fecha'])): ?>
                                                        <?php echo date('d/m/Y', strtotime($empleado['ultimo_contrato_fecha'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="index.php?action=contratante_perfil_empleado&empleado_cedula=<?php echo $empleado['cedula']; ?>&empresa_id=<?php echo $empresa_seleccionada['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="Ver perfil completo">
                                                            <i class="bi bi-person-badge"></i> Perfil
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No hay empleados registrados para esta empresa.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

