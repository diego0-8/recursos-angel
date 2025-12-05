<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos <?php echo htmlspecialchars($empresa['nombre']); ?> - Contratante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <style>
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8f9fa;
        }
        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--primary-color);
            background: rgba(30, 60, 114, 0.05);
        }
        .upload-zone i {
            font-size: 3rem;
            color: #6c757d;
        }
        .upload-zone.dragover i {
            color: var(--primary-color);
        }
        .file-preview {
            background: #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .plantilla-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .plantilla-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }
        .plantilla-icon {
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
    $action = 'contratante_contratos';
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
                                <a href="index.php?action=contratante_contratos">Contratos</a>
                            </li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($empresa['nombre']); ?></li>
                        </ol>
                    </nav>
                    <h1>Plantillas de Contratos - <?php echo htmlspecialchars($empresa['nombre']); ?></h1>
                    <p>Sube y gestiona las plantillas de contratos</p>
                </div>
                <div>
                    <a href="index.php?action=contratante_contratos" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                    <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalSubirPlantilla">
                        <i class="bi bi-upload me-2"></i>Subir Plantilla
                    </button>
                </div>
            </div>
            
            <!-- Alertas -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php 
                    $mensajes = [
                        'created' => 'Plantilla subida exitosamente.',
                        'updated' => 'Plantilla actualizada exitosamente.',
                        'deleted' => 'Plantilla eliminada exitosamente.'
                    ];
                    echo $mensajes[$_GET['success']] ?? 'Operación exitosa.';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Lista de Plantillas -->
            <div class="row g-4">
                <?php if (!empty($contratos)): ?>
                    <?php foreach ($contratos as $contrato): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card-custom plantilla-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="plantilla-icon me-3">
                                            <i class="bi bi-file-earmark-word"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo basename($contrato['archivo_contrato']); ?></h6>
                                            <small class="text-muted">
                                                Subido: <?php echo date('d/m/Y H:i', strtotime($contrato['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <?php if ($contrato['datos_completos']): ?>
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
                                        <a href="index.php?action=contratante_llenar_contrato&id=<?php echo $contrato['id']; ?>&empresa_id=<?php echo $empresa['id']; ?>" 
                                           class="btn btn-sm btn-primary flex-grow-1">
                                            <i class="bi bi-pencil-square me-1"></i>Llenar Campos
                                        </a>
                                        <a href="<?php echo htmlspecialchars($contrato['archivo_contrato']); ?>" 
                                           class="btn btn-sm btn-outline-secondary" download title="Descargar">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmarEliminar(<?php echo $contrato['id']; ?>)" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <?php if (!empty($contrato['archivo_generado'])): ?>
                                    <a href="<?php echo htmlspecialchars($contrato['archivo_generado']); ?>" 
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
                            <h5 class="text-muted">No hay plantillas de contratos</h5>
                            <p class="text-muted">Sube tu primera plantilla de contrato en formato .docx</p>
                            <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalSubirPlantilla">
                                <i class="bi bi-upload me-2"></i>Subir Plantilla
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Paginación -->
            <?php if (($total_paginas ?? 0) > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>&page=<?php echo $pagina_actual - 1; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>&page=<?php echo $pagina_actual + 1; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Subir Plantilla -->
    <div class="modal fade" id="modalSubirPlantilla" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2"></i>Subir Plantilla de Contrato
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formSubirPlantilla" method="POST" action="index.php?action=contratante_subir_plantilla" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="empresa_id" value="<?php echo $empresa['id']; ?>">
                        
                        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('archivo_contrato').click()">
                            <i class="bi bi-cloud-arrow-up mb-3 d-block"></i>
                            <h5 class="mb-2">Arrastra tu plantilla aquí</h5>
                            <p class="text-muted mb-2">o haz clic para seleccionar</p>
                            <small class="text-muted">Solo archivos .docx (máximo 10MB)</small>
                        </div>
                        <input type="file" class="d-none" id="archivo_contrato" name="archivo_contrato" accept=".docx" required>
                        <div id="filePreview" class="file-preview d-none">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="bi bi-file-earmark-word text-primary me-2 fs-4"></i>
                                    <span id="fileName" class="fw-medium"></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Tip:</strong> Usa marcadores como <code>{{NOMBRE}}</code>, <code>{{CEDULA}}</code>, <code>{{TELEFONO}}</code> en tu documento para que el sistema genere el formulario automáticamente.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Subir Plantilla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('archivo_contrato');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length && files[0].name.endsWith('.docx')) {
                fileInput.files = files;
                showFilePreview(files[0]);
            } else {
                alert('Solo se permiten archivos .docx');
            }
        });
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                showFilePreview(e.target.files[0]);
            }
        });
        
        function showFilePreview(file) {
            fileName.textContent = file.name;
            uploadZone.classList.add('d-none');
            filePreview.classList.remove('d-none');
        }
        
        function removeFile() {
            fileInput.value = '';
            uploadZone.classList.remove('d-none');
            filePreview.classList.add('d-none');
        }
        
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar esta plantilla? Esta acción no se puede deshacer.')) {
                window.location.href = 'index.php?action=contratante_eliminar_contrato&id=' + id + '&empresa_id=<?php echo $empresa['id']; ?>';
            }
        }
    </script>
</body>
</html>
