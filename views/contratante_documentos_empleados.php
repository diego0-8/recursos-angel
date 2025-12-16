<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos para Empleados - <?php echo htmlspecialchars($empresa['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <style>
        .documento-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .documento-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }
        .documento-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
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
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item">
                                <a href="index.php?action=contratante_empleados">Empleados</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="index.php?action=contratante_empleados&empresa_id=<?php echo $empresa['id']; ?>"><?php echo htmlspecialchars($empresa['nombre']); ?></a>
                            </li>
                            <li class="breadcrumb-item active">Documentos</li>
                        </ol>
                    </nav>
                    <h1>Documentos para Empleados - <?php echo htmlspecialchars($empresa['nombre']); ?></h1>
                    <p>Plantillas de documentos disponibles para llenar con empleados</p>
                </div>
                <div>
                    <a href="index.php?action=contratante_empleados&empresa_id=<?php echo $empresa['id']; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Empleados
                    </a>
                </div>
            </div>
            
            <!-- Alertas -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php 
                    $mensajes = [
                        'created' => 'Documento generado exitosamente.',
                        'updated' => 'Documento actualizado exitosamente.'
                    ];
                    echo $mensajes[$_GET['success']] ?? 'Operación realizada exitosamente.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Lista de Documentos -->
            <div class="row g-4">
                <?php if (!empty($documentos)): ?>
                    <?php foreach ($documentos as $documento): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card-custom documento-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="documento-icon me-3">
                                            <i class="bi bi-file-earmark-word"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars(basename($documento['archivo_contrato'] ?? 'Sin nombre')); ?></h6>
                                            <small class="text-muted">
                                                Subido: <?php echo isset($documento['created_at']) ? date('d/m/Y H:i', strtotime($documento['created_at'])) : 'N/A'; ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($documento['datos_completos'])): ?>
                                        <span class="badge bg-success mb-3">
                                            <i class="bi bi-check-circle me-1"></i>Completado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark mb-3">
                                            <i class="bi bi-clock me-1"></i>Pendiente de llenar
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white border-0 pt-0">
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($documento['id'])): ?>
                                        <a href="index.php?action=contratante_llenar_contrato&id=<?php echo $documento['id']; ?>&empresa_id=<?php echo $empresa['id']; ?>&tipo=empleado" 
                                           class="btn btn-sm btn-primary flex-grow-1">
                                            <i class="bi bi-pencil-square me-1"></i>Llenar Campos
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!empty($documento['archivo_contrato'])): ?>
                                        <a href="<?php echo htmlspecialchars($documento['archivo_contrato']); ?>" 
                                           class="btn btn-sm btn-outline-secondary" download title="Descargar plantilla">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($documento['archivo_generado']) && file_exists($documento['archivo_generado'])): ?>
                                    <a href="<?php echo htmlspecialchars($documento['archivo_generado']); ?>" 
                                       class="btn btn-sm btn-success w-100 mt-2" download>
                                        <i class="bi bi-file-earmark-check me-1"></i>Descargar Completado
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card-custom text-center py-5">
                            <i class="bi bi-file-earmark-plus fs-1 text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No hay documentos disponibles</h5>
                            <p class="text-muted">No hay plantillas de documentos para empleados en esta empresa.</p>
                            <a href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>&tipo=empleados" class="btn btn-primary-custom">
                                <i class="bi bi-upload me-2"></i>Subir Plantilla para Empleados
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



