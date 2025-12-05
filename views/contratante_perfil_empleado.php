<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Empleado - <?php echo htmlspecialchars($perfil['empleado']['nombre'] ?? ''); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
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
                        <h1>Perfil del Empleado</h1>
                        <p class="text-muted">Información completa y documentos del empleado</p>
                    </div>
                    <a href="index.php?action=contratante_empleados&empresa_id=<?php echo $empresa['id']; ?>" class="btn btn-outline-secondary">
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
            
            <?php if (empty($perfil)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>No se encontró información del empleado.
                </div>
            <?php else: 
                $empleado = $perfil['empleado'];
            ?>
                <!-- Información del Empleado -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card-custom">
                            <div class="card-header">
                                <i class="bi bi-person-circle me-2"></i>Información Personal
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <strong>Nombre Completo:</strong><br>
                                        <?php echo htmlspecialchars($empleado['nombre']); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Cédula:</strong><br>
                                        <?php echo htmlspecialchars($empleado['cedula']); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Empresa:</strong><br>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($empleado['empresa_nombre']); ?></span>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Estado:</strong><br>
                                        <?php
                                        $estadoClass = $empleado['estado'] === 'activo' ? 'success' : 'secondary';
                                        $estadoLabel = $empleado['estado'] === 'activo' ? 'Activo' : 'Inactivo';
                                        ?>
                                        <span class="badge bg-<?php echo $estadoClass; ?>"><?php echo $estadoLabel; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documentos del Empleado -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card-custom">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-check me-2"></i>Documentos del Empleado
                                </div>
                                <span class="badge bg-primary">
                                    <?php echo count($perfil['documentos']); ?> documento(s)
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($perfil['documentos'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Documento</th>
                                                    <th>Fecha de Firma</th>
                                                    <th>Empresa</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($perfil['documentos'] as $doc): ?>
                                                    <?php 
                                                    $nombreOriginal = basename($doc['archivo_contrato'] ?? 'documento');
                                                    $nombreGenerado = basename($doc['archivo_generado']);
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <i class="bi bi-file-earmark-check me-2 text-success"></i>
                                                            <strong><?php echo htmlspecialchars($nombreOriginal); ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="bi bi-check-circle me-1"></i>Generado: <?php echo htmlspecialchars($nombreGenerado); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($doc['fecha_firma'])): ?>
                                                                <?php echo date('d/m/Y', strtotime($doc['fecha_firma'])); ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">
                                                                <?php echo htmlspecialchars($doc['empresa_nombre']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="index.php?action=contratante_descargar_documento&contrato_id=<?php echo $doc['id']; ?>&formato=docx" 
                                                               class="btn btn-sm btn-secondary" target="_blank" title="Descargar documento con campos llenos">
                                                                <i class="bi bi-download me-1"></i>DOCX
                                                            </a>
                                                            <a href="index.php?action=contratante_descargar_documento&contrato_id=<?php echo $doc['id']; ?>&formato=pdf" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Nota: La conversión a PDF requiere LibreOffice instalado. Si no está disponible, se descargará el archivo DOCX. ¿Continuar?');"
                                                               title="Descargar documento en PDF (requiere LibreOffice)">
                                                                <i class="bi bi-file-pdf me-1"></i>PDF
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>No hay documentos disponibles para este empleado.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

