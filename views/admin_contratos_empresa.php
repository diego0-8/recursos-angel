<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos <?php echo htmlspecialchars($empresa['nombre']); ?> - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php 
    $action = 'admin_contratos';
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
                                <a href="index.php?action=admin_contratos">Contratos</a>
                            </li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($empresa['nombre']); ?></li>
                        </ol>
                    </nav>
                    <h1>Contratos - <?php echo htmlspecialchars($empresa['nombre']); ?></h1>
                    <p>Lista de contratos de la empresa</p>
                </div>
                <div>
                    <a href="index.php?action=admin_contratos" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                    <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalContrato" onclick="limpiarFormulario()">
                        <i class="bi bi-file-earmark-plus me-2"></i>Nuevo Contrato
                    </button>
                </div>
            </div>
            
            <!-- Alertas -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php 
                    $mensajes = [
                        'created' => 'Contrato creado exitosamente.',
                        'updated' => 'Contrato actualizado exitosamente.',
                        'deleted' => 'Contrato eliminado exitosamente.'
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
            
            <!-- Tabla de Contratos -->
            <div class="card-custom">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark-text me-2"></i>Lista de Contratos</span>
                    <span class="badge bg-primary"><?php echo $total_contratos ?? 0; ?> registros</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Empleado</th>
                                    <th>Cargo</th>
                                    <th>Contratante</th>
                                    <th>Tipo</th>
                                    <th>Fecha Firma</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($contratos)): ?>
                                    <?php foreach ($contratos as $contrato): ?>
                                        <tr>
                                            <td>#<?php echo $contrato['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($contrato['empleado_nombre']); ?></strong>
                                                <small class="text-muted d-block">CC: <?php echo $contrato['empleado_cedula']; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($contrato['cargo'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($contrato['contratante_nombre']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($contrato['tipo_contrato']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($contrato['fecha_firma'])); ?></td>
                                            <td>
                                                <?php 
                                                $badgeClass = [
                                                    'activo' => 'bg-success',
                                                    'finalizado' => 'bg-secondary',
                                                    'cancelado' => 'bg-danger'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $badgeClass[$contrato['estado']] ?? 'bg-secondary'; ?>">
                                                    <?php echo ucfirst($contrato['estado']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-action btn-outline-info" 
                                                        title="Ver detalles" onclick="verContrato(<?php echo htmlspecialchars(json_encode($contrato)); ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-action btn-outline-primary" 
                                                        title="Editar" onclick="editarContrato(<?php echo htmlspecialchars(json_encode($contrato)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="index.php?action=eliminar_contrato&id=<?php echo $contrato['id']; ?>&empresa_id=<?php echo $empresa['id']; ?>" 
                                                   class="btn btn-action btn-outline-danger" title="Eliminar"
                                                   onclick="return confirm('¿Está seguro de eliminar este contrato?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No hay contratos registrados para esta empresa
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Paginación -->
                <?php if (($total_paginas ?? 0) > 1): ?>
                <div class="card-footer bg-white">
                    <nav aria-label="Paginación">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?action=ver_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>&page=<?php echo $pagina_actual - 1; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?action=ver_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?action=ver_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>&page=<?php echo $pagina_actual + 1; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal Crear/Editar Contrato -->
    <div class="modal fade" id="modalContrato" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-plus me-2"></i>
                        <span id="modalTitulo">Nuevo Contrato</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formContrato" method="POST" action="index.php?action=guardar_contrato">
                    <div class="modal-body">
                        <input type="hidden" name="contrato_id" id="contrato_id">
                        <input type="hidden" name="empresa_id" value="<?php echo $empresa['id']; ?>">
                        <input type="hidden" name="modo" id="modo" value="crear">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="empleado_cedula" class="form-label">Empleado <span class="text-danger">*</span></label>
                                <select class="form-select" id="empleado_cedula" name="empleado_cedula" required>
                                    <option value="">Seleccionar empleado...</option>
                                    <?php foreach ($empleados as $emp): ?>
                                        <option value="<?php echo $emp['cedula']; ?>">
                                            <?php echo htmlspecialchars($emp['nombre'] . ' - CC: ' . $emp['cedula']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="contratante_cedula" class="form-label">Contratante <span class="text-danger">*</span></label>
                                <select class="form-select" id="contratante_cedula" name="contratante_cedula" required>
                                    <option value="">Seleccionar contratante...</option>
                                    <?php foreach ($contratantes as $cont): ?>
                                        <option value="<?php echo $cont['cedula']; ?>">
                                            <?php echo htmlspecialchars($cont['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="tipo_contrato" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_contrato" name="tipo_contrato" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="Indefinido">Indefinido</option>
                                    <option value="Fijo">Término Fijo</option>
                                    <option value="Obra o Labor">Obra o Labor</option>
                                    <option value="Prestación de Servicios">Prestación de Servicios</option>
                                    <option value="Aprendizaje">Aprendizaje</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cargo" name="cargo" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="fecha_firma" class="form-label">Fecha Firma <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_firma" name="fecha_firma" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="salario" class="form-label">Salario</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="salario" name="salario" step="0.01">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="estado_contrato" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="estado_contrato" name="estado" required>
                                    <option value="activo">Activo</option>
                                    <option value="finalizado">Finalizado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Ver Contrato -->
    <div class="modal fade" id="modalVerContrato" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2"></i>Detalles del Contrato
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContrato">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function limpiarFormulario() {
            document.getElementById('formContrato').reset();
            document.getElementById('contrato_id').value = '';
            document.getElementById('modo').value = 'crear';
            document.getElementById('modalTitulo').textContent = 'Nuevo Contrato';
        }
        
        function editarContrato(contrato) {
            document.getElementById('contrato_id').value = contrato.id;
            document.getElementById('empleado_cedula').value = contrato.empleado_cedula;
            document.getElementById('contratante_cedula').value = contrato.contratante_cedula;
            document.getElementById('tipo_contrato').value = contrato.tipo_contrato;
            document.getElementById('cargo').value = contrato.cargo || '';
            document.getElementById('fecha_inicio').value = contrato.fecha_inicio;
            document.getElementById('fecha_fin').value = contrato.fecha_fin || '';
            document.getElementById('fecha_firma').value = contrato.fecha_firma;
            document.getElementById('salario').value = contrato.salario || '';
            document.getElementById('estado_contrato').value = contrato.estado;
            document.getElementById('descripcion').value = contrato.descripcion || '';
            document.getElementById('modo').value = 'editar';
            document.getElementById('modalTitulo').textContent = 'Editar Contrato';
            
            var modal = new bootstrap.Modal(document.getElementById('modalContrato'));
            modal.show();
        }
        
        function verContrato(contrato) {
            var html = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted">Empleado:</strong>
                        <p class="mb-0">${contrato.empleado_nombre}<br><small class="text-muted">CC: ${contrato.empleado_cedula}</small></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted">Contratante:</strong>
                        <p class="mb-0">${contrato.contratante_nombre}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted">Cargo:</strong>
                        <p class="mb-0">${contrato.cargo || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted">Tipo de Contrato:</strong>
                        <p class="mb-0">${contrato.tipo_contrato}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong class="text-muted">Fecha Inicio:</strong>
                        <p class="mb-0">${contrato.fecha_inicio}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong class="text-muted">Fecha Fin:</strong>
                        <p class="mb-0">${contrato.fecha_fin || 'Indefinido'}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong class="text-muted">Fecha Firma:</strong>
                        <p class="mb-0">${contrato.fecha_firma}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted">Salario:</strong>
                        <p class="mb-0">$${contrato.salario ? parseFloat(contrato.salario).toLocaleString() : 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong class="text-muted">Estado:</strong>
                        <p class="mb-0"><span class="badge bg-${contrato.estado === 'activo' ? 'success' : contrato.estado === 'finalizado' ? 'secondary' : 'danger'}">${contrato.estado.charAt(0).toUpperCase() + contrato.estado.slice(1)}</span></p>
                    </div>
                    <div class="col-12">
                        <strong class="text-muted">Descripción:</strong>
                        <p class="mb-0">${contrato.descripcion || 'Sin descripción'}</p>
                    </div>
                </div>
            `;
            document.getElementById('detallesContrato').innerHTML = html;
            
            var modal = new bootstrap.Modal(document.getElementById('modalVerContrato'));
            modal.show();
        }
    </script>
</body>
</html>

