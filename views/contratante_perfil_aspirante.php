<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Aspirante - <?php echo htmlspecialchars($perfil['aspirante']['nombre'] ?? ''); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Perfil del Aspirante</h1>
                        <p class="text-muted">Información completa y documentos del aspirante</p>
                    </div>
                    <a href="index.php?action=contratante_aspirante" class="btn btn-outline-secondary">
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
                    <i class="bi bi-exclamation-triangle me-2"></i>No se encontró información del aspirante.
                </div>
            <?php else: 
                $aspirante = $perfil['aspirante'];
            ?>
                <!-- Información del Aspirante -->
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
                                        <?php echo htmlspecialchars($aspirante['nombre']); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Cédula:</strong><br>
                                        <?php echo htmlspecialchars($aspirante['cedula']); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Teléfono:</strong><br>
                                        <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($aspirante['telefono'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Teléfono 2:</strong><br>
                                        <i class="bi bi-telephone me-1"></i><?php echo !empty($aspirante['telefono2']) ? htmlspecialchars($aspirante['telefono2']) : '<span class="text-muted">N/A</span>'; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Correo Electrónico:</strong><br>
                                        <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($aspirante['correo'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <strong>Dirección:</strong><br>
                                        <?php echo htmlspecialchars($aspirante['direccion'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <strong>Estado:</strong><br>
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
                                        <span class="badge bg-<?php echo $clase; ?>"><?php echo $label; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documentos Completados -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card-custom">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-check me-2"></i>Documentos Completados
                                </div>
                                <span class="badge bg-primary">
                                    <?php echo count($perfil['documentos_completados']); ?> documento(s)
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($perfil['documentos_completados'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Documento</th>
                                                    <th>Fecha de Proceso</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($perfil['documentos_completados'] as $doc): ?>
                                                    <?php 
                                                    // Solo mostrar documentos que tienen archivo generado
                                                    if (empty($doc['archivo_generado']) || !file_exists($doc['archivo_generado'])) {
                                                        continue;
                                                    }
                                                    
                                                    // Obtener nombre del documento original para mostrar
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
                                                            <?php echo date('d/m/Y H:i', strtotime($doc['fecha_proceso'])); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Completado
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="index.php?action=contratante_descargar_documento&contrato_id=<?php echo $doc['id']; ?>&aspirante_id=<?php echo $aspirante['id']; ?>&formato=docx" 
                                                               class="btn btn-sm btn-secondary" target="_blank" title="Descargar documento con campos llenos">
                                                                <i class="bi bi-download me-1"></i>DOCX
                                                            </a>
                                                            <a href="index.php?action=contratante_descargar_documento&contrato_id=<?php echo $doc['id']; ?>&aspirante_id=<?php echo $aspirante['id']; ?>&formato=pdf" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Nota: La conversión a PDF requiere LibreOffice instalado. Si no está disponible, se descargará el archivo DOCX. ¿Continuar?');"
                                                               title="Descargar documento en PDF (requiere LibreOffice)">
                                                                <i class="bi bi-file-pdf me-1"></i>PDF
                                                            </a>
                                                            <small class="d-block text-muted mt-1">
                                                                <i class="bi bi-info-circle me-1"></i>PDF requiere LibreOffice
                                                            </small>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>No hay documentos completados aún.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documentos Subidos -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card-custom">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-cloud-upload me-2"></i>Documentos Subidos
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSubirDocumento">
                                    <i class="bi bi-plus-circle me-1"></i>Subir Documento
                                </button>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($perfil['documentos_subidos'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nombre del Archivo</th>
                                                    <th>Tipo</th>
                                                    <th>Subido por</th>
                                                    <th>Fecha</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($perfil['documentos_subidos'] as $doc): ?>
                                                    <tr>
                                                        <td>
                                                            <i class="bi bi-file-earmark me-2"></i>
                                                            <?php echo htmlspecialchars($doc['nombre_archivo']); ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info"><?php echo htmlspecialchars($doc['tipo_documento']); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($doc['uploaded_by_nombre'] ?? 'N/A'); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo date('d/m/Y H:i', strtotime($doc['created_at'])); ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="<?php echo htmlspecialchars($doc['ruta_archivo']); ?>" 
                                                                   class="btn btn-sm btn-primary" target="_blank" download>
                                                                    <i class="bi bi-download me-1"></i>Descargar
                                                                </a>
                                                                <?php 
                                                                // Verificar si el archivo es PDF
                                                                $extension = strtolower(pathinfo($doc['ruta_archivo'], PATHINFO_EXTENSION));
                                                                if ($extension === 'pdf'): 
                                                                ?>
                                                                    <a href="index.php?action=contratante_ver_documento&documento_id=<?php echo $doc['id']; ?>&aspirante_id=<?php echo $aspirante['id']; ?>&empresa_id=<?php echo $empresa_id; ?>" 
                                                                       class="btn btn-sm btn-danger" target="_blank">
                                                                        <i class="bi bi-file-pdf me-1"></i>Ver PDF
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>No hay documentos subidos aún.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal para Subir Documento -->
                <div class="modal fade" id="modalSubirDocumento" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-cloud-upload me-2"></i>Subir Documento
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="index.php?action=contratante_subir_documento_aspirante" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="aspirante_id" value="<?php echo $aspirante['id']; ?>">
                                    <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">
                                    
                                    <div class="mb-3">
                                        <label for="archivo" class="form-label">Archivo <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="archivo" name="archivo" required 
                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                                        <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (Máx. 10MB)</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                        <select class="form-select" id="tipo_documento" name="tipo_documento">
                                            <option value="general">General</option>
                                            <option value="cedula">Cédula</option>
                                            <option value="diploma">Diploma</option>
                                            <option value="certificado">Certificado</option>
                                            <option value="referencia">Referencia Laboral</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload me-1"></i>Subir Documento
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

