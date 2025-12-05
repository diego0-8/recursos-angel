<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llenar Contrato - Contratante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <style>
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .form-section-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .campos-detectados {
            background: #e8f4fd;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            border-radius: 0 10px 10px 0;
            margin-bottom: 1.5rem;
        }
        .campo-badge {
            display: inline-block;
            background: #0d6efd;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin: 0.25rem;
        }
        .no-campos {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 0 10px 10px 0;
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
            <div class="page-header">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item">
                            <a href="index.php?action=contratante_contratos">Contratos</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa_id; ?>">
                                <?php echo htmlspecialchars($contrato['empresa_nombre']); ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Llenar Datos</li>
                    </ol>
                </nav>
                <h1>Completar Datos del Contrato</h1>
                <p>Plantilla de contrato - <strong><?php echo htmlspecialchars($contrato['empresa_nombre']); ?></strong></p>
            </div>
            
            <!-- Alertas -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>Datos guardados exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Formulario -->
                <div class="col-lg-8">
                    <?php if (!empty($campos_documento)): ?>
                        <!-- Campos detectados del documento -->
                        <div class="campos-detectados">
                            <h6 class="mb-2"><i class="bi bi-file-earmark-check me-2"></i>Campos detectados en el documento:</h6>
                            <div>
                                <?php foreach ($campos_documento as $campo): ?>
                                    <span class="campo-badge">{{<?php echo $campo; ?>}}</span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <form method="POST" action="index.php?action=contratante_guardar_campos_contrato">
                            <input type="hidden" name="contrato_id" value="<?php echo $contrato['id']; ?>">
                            <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">
                            <?php if (isset($_GET['aspirante_id']) && !empty($_GET['aspirante_id'])): ?>
                            <input type="hidden" name="aspirante_id" value="<?php echo (int)$_GET['aspirante_id']; ?>">
                            <?php endif; ?>
                            
                            <!-- Campos del Documento -->
                            <div class="form-section">
                                <h5 class="form-section-title">
                                    <i class="bi bi-input-cursor-text me-2"></i>Campos del Contrato
                                </h5>
                                <p class="text-muted small mb-3">
                                    Complete los siguientes campos que serán insertados en el documento del contrato.
                                </p>
                                <div class="row">
                                    <?php echo $formulario_html; ?>
                                </div>
                            </div>
                            
                            <!-- Botones -->
                            <div class="d-flex gap-3 justify-content-between">
                                <a href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa_id; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Volver
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="accion" value="guardar" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Guardar Datos
                                    </button>
                                    <button type="submit" name="accion" value="generar" class="btn btn-success">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Guardar y Generar Documento
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                    <?php else: ?>
                        <!-- No se detectaron campos -->
                        <div class="no-campos">
                            <h6><i class="bi bi-exclamation-triangle me-2"></i>No se detectaron campos en el documento</h6>
                            <p class="mb-0">
                                El documento no contiene campos marcados con el formato <code>{{CAMPO}}</code>.<br>
                                Para que el sistema detecte los campos a llenar, el documento debe tener marcadores como:
                                <code>{{NOMBRE}}</code>, <code>{{CEDULA}}</code>, <code>{{TELEFONO}}</code>, etc.
                            </p>
                        </div>
                        
                        <div class="form-section mt-4">
                            <h5 class="form-section-title">
                                <i class="bi bi-info-circle me-2"></i>¿Cómo preparar el documento?
                            </h5>
                            <ol class="mb-0">
                                <li class="mb-2">Abre tu documento de contrato en Word</li>
                                <li class="mb-2">Reemplaza los espacios en blanco con marcadores, por ejemplo:
                                    <ul class="mt-1">
                                        <li><code>_________________</code> → <code>{{NOMBRE}}</code></li>
                                        <li><code>C.C. ___________</code> → <code>C.C. {{CEDULA}}</code></li>
                                        <li><code>Tel: ___________</code> → <code>Tel: {{TELEFONO}}</code></li>
                                    </ul>
                                </li>
                                <li class="mb-2">Guarda el documento y vuelve a subirlo</li>
                            </ol>
                            
                            <div class="mt-3">
                                <h6>Campos disponibles:</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">
                                            <code>{{NOMBRE}}</code> - Nombre completo<br>
                                            <code>{{CEDULA}}</code> - Cédula<br>
                                            <code>{{CIUDAD}}</code> - Ciudad<br>
                                            <code>{{DIRECCION}}</code> - Dirección
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">
                                            <code>{{BARRIO}}</code> - Barrio<br>
                                            <code>{{TELEFONO}}</code> - Teléfono<br>
                                            <code>{{CELULAR}}</code> - Celular<br>
                                            <code>{{CORREO}}</code> - Email
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">
                                            <code>{{CARGO}}</code> - Cargo<br>
                                            <code>{{SALARIO}}</code> - Salario<br>
                                            <code>{{FECHA}}</code> - Fecha<br>
                                            <code>{{EPS}}</code> - EPS
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3 mt-4">
                            <a href="index.php?action=contratante_contratos_empresa&empresa_id=<?php echo $empresa_id; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Volver
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Resumen del Contrato -->
                <div class="col-lg-4">
                    <div class="card-custom sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <i class="bi bi-file-earmark-text me-2"></i>Información del Contrato
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong class="text-muted small">EMPRESA</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($contrato['empresa_nombre']); ?></p>
                            </div>
                            <?php if (!empty($contrato['empleado_nombre'])): ?>
                            <div class="mb-3">
                                <strong class="text-muted small">EMPLEADO</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($contrato['empleado_nombre']); ?></p>
                                <small class="text-muted">CC: <?php echo $contrato['empleado_cedula']; ?></small>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($contrato['tipo_contrato'])): ?>
                            <div class="mb-3">
                                <strong class="text-muted small">TIPO DE CONTRATO</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($contrato['tipo_contrato']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($contrato['cargo'])): ?>
                            <div class="mb-3">
                                <strong class="text-muted small">CARGO</strong>
                                <p class="mb-0"><?php echo htmlspecialchars($contrato['cargo']); ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($contrato['fecha_firma'])): ?>
                            <div class="mb-3">
                                <strong class="text-muted small">FECHA DE FIRMA</strong>
                                <p class="mb-0"><?php echo date('d/m/Y', strtotime($contrato['fecha_firma'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($contrato['archivo_contrato'])): ?>
                            <hr>
                            <a href="<?php echo htmlspecialchars($contrato['archivo_contrato']); ?>" 
                               class="btn btn-outline-primary w-100 mb-2" download>
                                <i class="bi bi-download me-2"></i>Descargar Original
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($contrato['archivo_generado'])): ?>
                            <a href="<?php echo htmlspecialchars($contrato['archivo_generado']); ?>" 
                               class="btn btn-success w-100" download>
                                <i class="bi bi-file-earmark-check me-2"></i>Descargar Completado
                            </a>
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
