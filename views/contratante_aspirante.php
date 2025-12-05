<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Aspirante - Contratante</title>
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
    </style>
</head>
<body>
    <?php 
    $action = 'contratante_aspirante';
    include __DIR__ . '/shared/navbar.php'; 
    ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <h1>Gestión de Aspirantes</h1>
                <p>Administra los aspirantes y sus procesos de contratación</p>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>Acción realizada exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Vincular Nuevo Aspirante -->
            <div class="mb-4">
                <h3 class="mb-4">Vincular Nuevo Aspirante</h3>
            </div>
            
            <!-- Selección de Empresas -->
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
                        <a href="index.php?action=contratante_aspirante_empresa&empresa_id=<?php echo $empresa['id']; ?>" 
                           class="text-decoration-none">
                            <div class="card-custom empresa-card <?php echo strtolower($empresa['codigo']); ?> text-center p-4">
                                <div class="icon-empresa">
                                    <i class="bi <?php echo $icono; ?>"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($empresa['nombre']); ?></h4>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($empresa['descripcion'] ?? ''); ?></p>
                                <div class="mt-3">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-person-plus me-1"></i>Vincular Aspirante
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Separador -->
            <div class="my-5">
                <hr>
            </div>
            
            <!-- Lista de Aspirantes Pendientes -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Aspirantes Pendientes</h3>
                </div>
                
                <!-- Buscador -->
                <div class="card-custom mb-3">
                    <div class="card-body">
                        <form method="GET" action="index.php" class="row g-3">
                            <input type="hidden" name="action" value="contratante_aspirante">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           name="buscar" 
                                           id="buscar"
                                           placeholder="Buscar por nombre o cédula..." 
                                           value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="bi bi-search me-1"></i>Buscar
                                    </button>
                                    <?php if (!empty($_GET['buscar'])): ?>
                                        <a href="index.php?action=contratante_aspirante" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Limpiar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($aspirantes_pendientes)): ?>
                <div class="card-custom mb-4">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>Lista de Aspirantes en Proceso
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cédula</th>
                                        <th>Empresa</th>
                                        <th>Contacto</th>
                                        <th>Documentos</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($aspirantes_pendientes as $aspirante): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($aspirante['nombre']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($aspirante['cedula']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($aspirante['empresa_nombre']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($aspirante['telefono']); ?>
                                                </small>
                                                <?php if (!empty($aspirante['telefono2'])): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($aspirante['telefono2']); ?>
                                                    </small>
                                                <?php endif; ?>
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($aspirante['correo']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo $aspirante['documentos_completados']; ?>/<?php echo $aspirante['total_documentos']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $estadoClass = [
                                                    'activo' => 'success',
                                                    'en_proceso' => 'warning',
                                                    'contratado' => 'primary',
                                                    'rechazado' => 'danger'
                                                ];
                                                $estadoLabel = [
                                                    'activo' => 'Activo',
                                                    'en_proceso' => 'En Proceso',
                                                    'contratado' => 'Contratado',
                                                    'rechazado' => 'Rechazado'
                                                ];
                                                $clase = $estadoClass[$aspirante['estado']] ?? 'secondary';
                                                $label = $estadoLabel[$aspirante['estado']] ?? ucfirst($aspirante['estado']);
                                                ?>
                                                <span class="badge bg-<?php echo $clase; ?>">
                                                    <?php echo $label; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?action=contratante_aspirante_empresa&empresa_id=<?php echo $aspirante['empresa_id']; ?>&aspirante_id=<?php echo $aspirante['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Seguir con el proceso">
                                                        <i class="bi bi-arrow-right-circle"></i> Seguir
                                                    </a>
                                                    <a href="index.php?action=contratante_perfil_aspirante&aspirante_id=<?php echo $aspirante['id']; ?>&empresa_id=<?php echo $aspirante['empresa_id']; ?>" 
                                                       class="btn btn-sm btn-info" title="Ver perfil completo">
                                                        <i class="bi bi-person-badge"></i> Perfil
                                                    </a>
                                                    <a href="index.php?action=contratante_aspirante_accion&aspirante_id=<?php echo $aspirante['id']; ?>&accion=contratado" 
                                                       class="btn btn-sm btn-success" title="Marcar como contratado"
                                                       onclick="return confirm('¿Está seguro de marcar este aspirante como contratado?');">
                                                        <i class="bi bi-check-circle"></i> Contratado
                                                    </a>
                                                    <a href="index.php?action=contratante_aspirante_accion&aspirante_id=<?php echo $aspirante['id']; ?>&accion=rechazado" 
                                                       class="btn btn-sm btn-danger" title="Desvincular aspirante"
                                                       onclick="return confirm('¿Está seguro de desvincular este aspirante?');">
                                                        <i class="bi bi-x-circle"></i> Desvincular
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
                    <i class="bi bi-info-circle me-2"></i>
                    <?php if (!empty($_GET['buscar'])): ?>
                        No se encontraron aspirantes que coincidan con "<?php echo htmlspecialchars($_GET['buscar']); ?>".
                        <a href="index.php?action=contratante_aspirante" class="alert-link">Ver todos los aspirantes</a>
                    <?php else: ?>
                        No hay aspirantes pendientes en este momento.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

