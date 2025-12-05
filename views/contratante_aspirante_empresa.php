<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Aspirante - <?php echo htmlspecialchars($empresa['nombre'] ?? 'Empresa'); ?></title>
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
                <h1>Crear Aspirante - <?php echo htmlspecialchars($empresa['nombre'] ?? 'Empresa'); ?></h1>
                <p>Completa los datos del aspirante para iniciar el proceso de contratación</p>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>Aspirante creado exitosamente. Ahora puedes seleccionar los documentos.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (empty($aspirante_id)): ?>
                <!-- Formulario para crear aspirante -->
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card-custom">
                            <div class="card-header">
                                <i class="bi bi-person-plus me-2"></i>Datos del Aspirante
                            </div>
                            <div class="card-body">
                                <form method="POST" action="index.php?action=contratante_guardar_aspirante">
                                    <input type="hidden" name="empresa_id" value="<?php echo $empresa['id']; ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="cedula" name="cedula" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="telefono2" class="form-label">Teléfono 2 (Opcional)</label>
                                            <input type="tel" class="form-control" id="telefono2" name="telefono2">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="correo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="correo" name="correo" required>
                                        </div>
                                        
                                        <div class="col-md-12 mb-3">
                                            <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="direccion" name="direccion" rows="2" required></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="index.php?action=contratante_aspirante" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary-custom">
                                            <i class="bi bi-save me-2"></i>Guardar Aspirante
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Mostrar documentos disponibles para el aspirante -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Aspirante:</strong> <?php echo htmlspecialchars($aspirante_nombre ?? ''); ?> - 
                            CC: <?php echo htmlspecialchars($aspirante_cedula ?? ''); ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($contratos)): ?>
                    <div class="row g-4">
                        <?php 
                        foreach ($contratos as $contrato): 
                            // Los datos ya vienen preparados desde index.php
                            $campos_documento = $contrato['campos_documento'] ?? [];
                            $formulario_html = $contrato['formulario_html'] ?? '';
                            
                            $formId = 'form_contrato_' . $contrato['id'];
                            $collapseId = 'collapse_' . $contrato['id'];
                            // Si hay un contrato_id en la URL y coincide, expandir automáticamente
                            $expandir = (isset($_GET['contrato_id']) && $_GET['contrato_id'] == $contrato['id']);
                        ?>
                            <div class="col-12">
                                <div class="card-custom">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-word me-2"></i>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars(basename($contrato['archivo_contrato'])); ?></h6>
                                                <small class="text-muted">
                                                    Subido: <?php echo date('d/m/Y H:i', strtotime($contrato['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#<?php echo $collapseId; ?>" 
                                                aria-expanded="<?php echo $expandir ? 'true' : 'false'; ?>">
                                            <i class="bi bi-pencil-square me-1"></i>Llenar Documento
                                        </button>
                                    </div>
                                    
                                    <div class="collapse <?php echo $expandir ? 'show' : ''; ?>" id="<?php echo $collapseId; ?>">
                                        <div class="card-body">
                                            <?php if (!empty($campos_documento)): ?>
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Campos detectados: 
                                                        <?php foreach ($campos_documento as $campo): ?>
                                                            <span class="badge bg-secondary me-1">{{<?php echo htmlspecialchars($campo); ?>}}</span>
                                                        <?php endforeach; ?>
                                                    </small>
                                                </div>
                                                
                                                <form method="POST" action="index.php?action=contratante_guardar_campos_contrato" 
                                                      id="<?php echo $formId; ?>" class="form-llenar-contrato">
                                                    <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">
                                                    <input type="hidden" name="empresa_id" value="<?php echo $empresa['id']; ?>">
                                                    <input type="hidden" name="aspirante_id" value="<?php echo $aspirante_id; ?>">
                                                    
                                                    <div class="row">
                                                        <?php echo $formulario_html; ?>
                                                    </div>
                                                    
                                                    <div class="d-flex gap-2 justify-content-end mt-3">
                                                        <button type="submit" name="accion" value="guardar" class="btn btn-primary">
                                                            <i class="bi bi-save me-1"></i>Guardar y Generar Documento
                                                        </button>
                                                        <small class="text-muted align-self-center ms-2">
                                                            <i class="bi bi-info-circle me-1"></i>Se guardará y generará el documento con los campos llenos
                                                        </small>
                                                        <?php 
                                                        // Mostrar botón de descargar PDF si el documento ya está generado
                                                        $archivoGenerado = $contrato['archivo_generado'] ?? '';
                                                        if (!empty($archivoGenerado) && file_exists($archivoGenerado)): 
                                                        ?>
                                                            <a href="index.php?action=contratante_descargar_documento&contrato_id=<?php echo $contrato['id']; ?>&aspirante_id=<?php echo $aspirante_id; ?>&formato=pdf" 
                                                               class="btn btn-danger" target="_blank">
                                                                <i class="bi bi-file-pdf me-1"></i>Descargar PDF
                                                            </a>
                                                            <a href="index.php?action=contratante_descargar_documento&contrato_id=<?php echo $contrato['id']; ?>&aspirante_id=<?php echo $aspirante_id; ?>&formato=docx" 
                                                               class="btn btn-secondary" target="_blank">
                                                                <i class="bi bi-file-word me-1"></i>Descargar DOCX
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <div class="alert alert-warning">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                    No se detectaron campos en este documento. 
                                                    El documento debe contener marcadores como <code>{{NOMBRE}}</code>, <code>{{CEDULA}}</code>, etc.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No hay documentos disponibles para esta empresa. 
                        <a href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>">
                            Subir un documento primero
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Botón para ver perfil del aspirante -->
                <div class="mt-4 text-center">
                    <a href="index.php?action=contratante_perfil_aspirante&aspirante_id=<?php echo $aspirante_id; ?>&empresa_id=<?php echo $empresa['id']; ?>" 
                       class="btn btn-info btn-lg">
                        <i class="bi bi-person-badge me-2"></i>Ver Perfil del Aspirante
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .form-llenar-contrato {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .collapse.show {
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    <script>
        // Guardar texto original de los botones
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.form-llenar-contrato');
            
            forms.forEach(function(form) {
                const buttons = form.querySelectorAll('button[type="submit"]');
                buttons.forEach(btn => {
                    btn.dataset.originalText = btn.innerHTML;
                });
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const clickedButton = form.querySelector('button[type="submit"]:focus') || 
                                         form.querySelector('button[type="submit"][name="accion"][value="generar"]') ||
                                         form.querySelector('button[type="submit"][name="accion"][value="guardar"]');
                    const accion = clickedButton ? clickedButton.value : 'guardar';
                    formData.set('accion', accion);
                    
                    // Deshabilitar botones durante el envío
                    buttons.forEach(btn => {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Procesando...';
                    });
                    
                    // Enviar formulario normalmente (recargará la página con mensaje)
                    form.submit();
                });
            });
        });
    </script>
</body>
</html>

