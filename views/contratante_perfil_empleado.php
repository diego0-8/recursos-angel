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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-person-circle me-2"></i>Información Personal
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if (!empty($empleado['fecha_nacimiento']) || !empty($empleado['barrio']) || !empty($empleado['eps'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalMasInfoEmpleado">
                                            <i class="bi bi-info-circle me-1"></i>Más información
                                        </button>
                                    <?php endif; ?>
                                    <?php if (!empty($empleado['aspirante_id'])): ?>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditarDatos">
                                            <i class="bi bi-pencil me-1"></i>Editar Datos
                                        </button>
                                    <?php endif; ?>
                                </div>
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
                                    
                                    <?php if (!empty($empleado['aspirante_id'])): ?>
                                        <!-- Datos personales del aspirante -->
                                        <div class="col-12">
                                            <hr>
                                            <h6 class="text-muted mb-3">
                                                <i class="bi bi-info-circle me-2"></i>Datos Personales (Inscripción como Aspirante)
                                            </h6>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Teléfono:</strong><br>
                                            <i class="bi bi-telephone me-1"></i>
                                            <?php echo !empty($empleado['telefono']) ? htmlspecialchars($empleado['telefono']) : '<span class="text-muted">N/A</span>'; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Teléfono 2:</strong><br>
                                            <i class="bi bi-phone me-1"></i>
                                            <?php echo !empty($empleado['telefono2']) ? htmlspecialchars($empleado['telefono2']) : '<span class="text-muted">N/A</span>'; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Correo Electrónico:</strong><br>
                                            <i class="bi bi-envelope me-1"></i>
                                            <?php if (!empty($empleado['correo'])): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($empleado['correo']); ?>">
                                                    <?php echo htmlspecialchars($empleado['correo']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Fecha de Inscripción:</strong><br>
                                            <i class="bi bi-calendar-plus me-1"></i>
                                            <?php if (!empty($empleado['fecha_inscripcion'])): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($empleado['fecha_inscripcion'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Fecha de Contratación:</strong><br>
                                            <i class="bi bi-calendar-check me-1"></i>
                                            <?php if (!empty($empleado['fecha_contratacion'])): ?>
                                                <span class="text-success fw-bold">
                                                    <?php echo date('d/m/Y H:i', strtotime($empleado['fecha_contratacion'])); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <strong>Dirección:</strong><br>
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <?php
                                            $direccionBase = !empty($empleado['direccion']) ? $empleado['direccion'] : '';
                                            $extraDireccion = [];
                                            if (!empty($empleado['barrio'])) {
                                                $extraDireccion[] = 'Barrio: ' . $empleado['barrio'];
                                            }
                                            if (!empty($empleado['localidad'])) {
                                                $extraDireccion[] = 'Localidad: ' . $empleado['localidad'];
                                            }
                                            $textoDireccion = trim($direccionBase);
                                            if (!empty($extraDireccion)) {
                                                $textoDireccion .= ($textoDireccion ? ' - ' : '') . implode(' | ', $extraDireccion);
                                            }
                                            echo $textoDireccion ? htmlspecialchars($textoDireccion) : '<span class="text-muted">N/A</span>';
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Si no fue aspirante, mostrar mensaje -->
                                        <div class="col-12">
                                            <hr>
                                            <div class="alert alert-info mb-0">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Este empleado no fue registrado como aspirante previamente.
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documentos Generados del Empleado -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card-custom">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-check me-2"></i>Documentos Generados
                                </div>
                                <span class="badge bg-primary">
                                    <?php echo count($perfil['documentos_generados'] ?? []); ?> documento(s)
                                </span>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($perfil['documentos_generados'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Documento</th>
                                                    <th>Fecha de Firma</th>
                                                    <th>Empresa</th>
                                                    <th>Origen</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($perfil['documentos_generados'] as $doc): ?>
                                                    <?php 
                                                    $nombreOriginal = basename($doc['archivo_contrato'] ?? 'documento');
                                                    $nombreGenerado = basename($doc['archivo_generado']);
                                                    $origen = $doc['origen'] ?? 'empleado';
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
                                                            <?php if ($origen === 'aspirante'): ?>
                                                                <span class="badge bg-warning text-dark" title="Documento llenado cuando era aspirante">
                                                                    <i class="bi bi-person me-1"></i>Aspirante
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success" title="Documento llenado como empleado">
                                                                    <i class="bi bi-briefcase me-1"></i>Empleado
                                                                </span>
                                                            <?php endif; ?>
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
                                        <i class="bi bi-info-circle me-2"></i>No hay documentos generados para este empleado.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documentos Subidos del Empleado (si fue aspirante antes) -->
                <?php if (!empty($perfil['documentos_subidos'])): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card-custom">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-cloud-upload me-2"></i>Documentos Subidos
                                </div>
                                <span class="badge bg-info">
                                    <?php echo count($perfil['documentos_subidos']); ?> documento(s)
                                </span>
                            </div>
                            <div class="card-body">
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
                                                            $extension = strtolower(pathinfo($doc['ruta_archivo'], PATHINFO_EXTENSION));
                                                            if ($extension === 'pdf'): 
                                                            ?>
                                                                <a href="index.php?action=contratante_ver_documento&documento_id=<?php echo $doc['id']; ?>&aspirante_id=<?php echo $perfil['empleado']['aspirante_id']; ?>&empresa_id=<?php echo $empresa_id; ?>" 
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
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para Editar Datos Personales -->
    <?php if (!empty($empleado['aspirante_id'])): ?>
    <div class="modal fade" id="modalEditarDatos" tabindex="-1" aria-labelledby="modalEditarDatosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarDatosLabel">
                        <i class="bi bi-pencil me-2"></i>Editar Datos Personales
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="index.php?action=contratante_actualizar_datos_personales">
                    <input type="hidden" name="aspirante_id" value="<?php echo $empleado['aspirante_id']; ?>">
                    <input type="hidden" name="empleado_cedula" value="<?php echo $empleado['cedula']; ?>">
                    <input type="hidden" name="empresa_id" value="<?php echo $empresa_id; ?>">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($empleado['telefono'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono2" class="form-label">Teléfono 2 (Opcional)</label>
                                <input type="tel" class="form-control" id="telefono2" name="telefono2" 
                                       value="<?php echo htmlspecialchars($empleado['telefono2'] ?? ''); ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?php echo htmlspecialchars($empleado['correo'] ?? ''); ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($empleado['direccion'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Más información del Empleado (datos capturados al contratar) -->
    <?php if (!empty($empleado['fecha_nacimiento']) || !empty($empleado['barrio']) || !empty($empleado['eps'])): ?>
    <div class="modal fade" id="modalMasInfoEmpleado" tabindex="-1" aria-labelledby="modalMasInfoEmpleadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMasInfoEmpleadoLabel">
                        <i class="bi bi-info-circle me-2"></i>Información adicional del empleado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong>Fecha de nacimiento:</strong><br>
                            <?php echo !empty($empleado['fecha_nacimiento']) ? date('d/m/Y', strtotime($empleado['fecha_nacimiento'])) : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Barrio:</strong><br>
                            <?php echo !empty($empleado['barrio']) ? htmlspecialchars($empleado['barrio']) : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Localidad:</strong><br>
                            <?php echo !empty($empleado['localidad']) ? htmlspecialchars($empleado['localidad']) : '<span class="text-muted">N/A</span>'; ?>
                        </div>

                        <div class="col-md-4">
                            <strong>Salario:</strong><br>
                            <?php echo isset($empleado['salario']) ? '$ ' . number_format($empleado['salario'], 0, ',', '.') : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Subsidio de transporte:</strong><br>
                            <?php echo isset($empleado['subsidio_transporte']) ? '$ ' . number_format($empleado['subsidio_transporte'], 0, ',', '.') : '<span class="text-muted">N/A</span>'; ?>
                        </div>

                        <div class="col-md-4">
                            <strong>EPS:</strong><br>
                            <?php echo !empty($empleado['eps']) ? htmlspecialchars($empleado['eps']) : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Fondo de pensión:</strong><br>
                            <?php echo !empty($empleado['fondo_pension']) ? htmlspecialchars($empleado['fondo_pension']) : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Fondo de cesantías:</strong><br>
                            <?php echo !empty($empleado['fondo_cesantias']) ? htmlspecialchars($empleado['fondo_cesantias']) : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Caja de compensación:</strong><br>
                            <?php echo !empty($empleado['caja_compensacion']) ? htmlspecialchars($empleado['caja_compensacion']) : '<span class="text-muted">N/A</span>'; ?>
                        </div>

                        <div class="col-md-4">
                            <strong>Género:</strong><br>
                            <?php
                            $generos = [
                                'femenino' => 'Femenino',
                                'masculino' => 'Masculino',
                                'no_binario' => 'No binario',
                                'otro' => 'Otro'
                            ];
                            echo !empty($empleado['genero']) ? htmlspecialchars($generos[$empleado['genero']] ?? $empleado['genero']) : '<span class="text-muted">N/A</span>';
                            ?>
                        </div>
                        <div class="col-md-4">
                            <strong>RH:</strong><br>
                            <?php echo !empty($empleado['rh']) ? htmlspecialchars($empleado['rh']) : '<span class="text-muted">N/A</span>'; ?>
                        </div>

                        <div class="col-md-4">
                            <strong>Nivel de escolaridad:</strong><br>
                            <?php echo !empty($empleado['nivel_escolaridad']) ? htmlspecialchars($empleado['nivel_escolaridad']) : '<span class="text-muted">N/A</span>'; ?>
                            <?php if (!empty($empleado['nivel_escolaridad_estado'])): ?>
                                <br>
                                <small class="text-muted">
                                    Estado: <?php echo $empleado['nivel_escolaridad_estado'] === 'certificado' ? 'Certificado' : 'En progreso'; ?>
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <strong>Estado civil:</strong><br>
                            <?php
                            $estadosCivil = [
                                'soltero' => 'Soltero(a)',
                                'casado' => 'Casado(a)',
                                'union_libre' => 'Unión libre',
                                'viudo' => 'Viudo(a)'
                            ];
                            echo !empty($empleado['estado_civil']) ? htmlspecialchars($estadosCivil[$empleado['estado_civil']] ?? $empleado['estado_civil']) : '<span class="text-muted">N/A</span>';
                            ?>
                        </div>

                        <div class="col-md-4">
                            <strong>Computador:</strong><br>
                            <?php echo isset($empleado['computador']) ? ($empleado['computador'] ? 'Sí' : 'No') : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Internet:</strong><br>
                            <?php echo isset($empleado['internet']) ? ($empleado['internet'] ? 'Sí' : 'No') : '<span class="text-muted">N/A</span>'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Hijos:</strong><br>
                            <?php
                            if (isset($empleado['tiene_hijos'])) {
                                echo $empleado['tiene_hijos'] ? 'Sí' : 'No';
                                if ($empleado['tiene_hijos'] && isset($empleado['numero_hijos'])) {
                                    echo ' (' . (int)$empleado['numero_hijos'] . ' hijo(s))';
                                }
                            } else {
                                echo '<span class="text-muted">N/A</span>';
                            }
                            ?>
                        </div>

                        <div class="col-md-4">
                            <strong>Contacto de emergencia:</strong><br>
                            <?php echo !empty($empleado['contacto_emergencia_nombre']) ? htmlspecialchars($empleado['contacto_emergencia_nombre']) : '<span class="text-muted">N/A</span>'; ?>
                            <?php if (!empty($empleado['contacto_emergencia_parentesco'])): ?>
                                <br>
                                <small class="text-muted">Parentesco: <?php echo htmlspecialchars($empleado['contacto_emergencia_parentesco']); ?></small>
                            <?php endif; ?>
                            <?php if (!empty($empleado['contacto_emergencia_telefono'])): ?>
                                <br>
                                <small class="text-muted">Tel: <?php echo htmlspecialchars($empleado['contacto_emergencia_telefono']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <strong>Exámenes médicos:</strong><br>
                            <?php
                            if (isset($empleado['examenes_medicos'])) {
                                echo $empleado['examenes_medicos'] ? 'Sí' : 'No';
                                if ($empleado['examenes_medicos'] && !empty($empleado['examenes_fecha'])) {
                                    echo '<br><small class="text-muted">Fecha: ' . date('d/m/Y', strtotime($empleado['examenes_fecha'])) . '</small>';
                                }
                            } else {
                                echo '<span class="text-muted">N/A</span>';
                            }
                            ?>
                            <?php if (!empty($empleado['examenes_resultados_pdf'])): ?>
                                <br>
                                <a href="<?php echo htmlspecialchars($empleado['examenes_resultados_pdf']); ?>" target="_blank" class="btn btn-sm btn-outline-danger mt-1">
                                    <i class="bi bi-file-pdf me-1"></i>Ver resultados PDF
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <strong>Observaciones:</strong><br>
                            <?php echo !empty($empleado['observaciones']) ? nl2br(htmlspecialchars($empleado['observaciones'])) : '<span class="text-muted">Sin observaciones</span>'; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

