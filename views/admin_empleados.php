<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleados - Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <?php 
    $action = 'admin_empleados';
    include __DIR__ . '/shared/navbar.php'; 
    ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1>Gestión de Empleados</h1>
                    <p>Administra los usuarios del sistema</p>
                </div>
                <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalEmpleado" onclick="limpiarFormulario()">
                    <i class="bi bi-person-plus me-2"></i>Nuevo Empleado
                </button>
            </div>
            
            <!-- Alertas -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php 
                    $mensajes = [
                        'created' => 'Empleado creado exitosamente.',
                        'updated' => 'Empleado actualizado exitosamente.',
                        'deleted' => 'Empleado eliminado exitosamente.',
                        'enabled' => 'Empleado habilitado exitosamente.',
                        'disabled' => 'Empleado deshabilitado exitosamente.'
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
            
            <!-- Tabla de Empleados -->
            <div class="card-custom">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i>Lista de Empleados</span>
                    <span class="badge bg-primary"><?php echo $total_empleados ?? 0; ?> registros</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $emp): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($emp['cedula']); ?></td>
                                            <td><?php echo htmlspecialchars($emp['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($emp['usuario']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $emp['rol'] === 'administrador' ? 'primary' : 'secondary'; ?>">
                                                    <?php echo ucfirst($emp['rol']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge-status badge-<?php echo $emp['estado']; ?>">
                                                    <?php echo ucfirst($emp['estado']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($emp['created_at'])); ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-action btn-outline-primary" 
                                                        title="Editar" onclick="editarEmpleado(<?php echo htmlspecialchars(json_encode($emp)); ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($emp['estado'] === 'activo'): ?>
                                                    <a href="index.php?action=toggle_empleado&cedula=<?php echo $emp['cedula']; ?>&estado=inactivo&page=<?php echo $pagina_actual; ?>" 
                                                       class="btn btn-action btn-outline-warning" title="Deshabilitar"
                                                       onclick="return confirm('¿Está seguro de deshabilitar este empleado?')">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="index.php?action=toggle_empleado&cedula=<?php echo $emp['cedula']; ?>&estado=activo&page=<?php echo $pagina_actual; ?>" 
                                                       class="btn btn-action btn-outline-success" title="Habilitar"
                                                       onclick="return confirm('¿Está seguro de habilitar este empleado?')">
                                                        <i class="bi bi-toggle-off"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="index.php?action=eliminar_empleado&cedula=<?php echo $emp['cedula']; ?>&page=<?php echo $pagina_actual; ?>" 
                                                   class="btn btn-action btn-outline-danger" title="Eliminar"
                                                   onclick="return confirm('¿Está seguro de eliminar este empleado? Esta acción no se puede deshacer.')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No hay empleados registrados
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <div class="card-footer bg-white">
                    <nav aria-label="Paginación de empleados">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?action=admin_empleados&page=<?php echo $pagina_actual - 1; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo $i === $pagina_actual ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?action=admin_empleados&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?action=admin_empleados&page=<?php echo $pagina_actual + 1; ?>">
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
    
    <!-- Modal Crear/Editar Empleado -->
    <div class="modal fade" id="modalEmpleado" tabindex="-1" aria-labelledby="modalEmpleadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEmpleadoLabel">
                        <i class="bi bi-person-plus me-2"></i>
                        <span id="modalTitulo">Nuevo Empleado</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEmpleado" method="POST" action="index.php?action=guardar_empleado">
                    <div class="modal-body">
                        <input type="hidden" name="cedula_original" id="cedula_original">
                        <input type="hidden" name="modo" id="modo" value="crear">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="cedula" name="cedula" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="usuario" class="form-label">Usuario <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="contrasena" class="form-label">
                                    Contraseña <span class="text-danger" id="passRequired">*</span>
                                    <small class="text-muted" id="passHint" style="display:none;">(dejar vacío para mantener actual)</small>
                                </label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirmar_contrasena" class="form-label">
                                    Confirmar Contraseña <span class="text-danger" id="passConfirmRequired">*</span>
                                </label>
                                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena">
                                <div class="invalid-feedback" id="passError">Las contraseñas no coinciden</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="rol" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="contratante">Contratante</option>
                                    <option value="aspirante">Aspirante</option>
                                    <option value="empleado">Empleado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function limpiarFormulario() {
            document.getElementById('formEmpleado').reset();
            document.getElementById('cedula_original').value = '';
            document.getElementById('modo').value = 'crear';
            document.getElementById('modalTitulo').textContent = 'Nuevo Empleado';
            document.getElementById('cedula').removeAttribute('readonly');
            document.getElementById('contrasena').setAttribute('required', 'required');
            document.getElementById('confirmar_contrasena').setAttribute('required', 'required');
            document.getElementById('passRequired').style.display = 'inline';
            document.getElementById('passConfirmRequired').style.display = 'inline';
            document.getElementById('passHint').style.display = 'none';
            document.getElementById('contrasena').classList.remove('is-invalid');
            document.getElementById('confirmar_contrasena').classList.remove('is-invalid');
        }
        
        function editarEmpleado(emp) {
            document.getElementById('cedula_original').value = emp.cedula;
            document.getElementById('cedula').value = emp.cedula;
            document.getElementById('cedula').setAttribute('readonly', 'readonly');
            document.getElementById('nombre').value = emp.nombre;
            document.getElementById('usuario').value = emp.usuario;
            document.getElementById('contrasena').value = '';
            document.getElementById('confirmar_contrasena').value = '';
            document.getElementById('contrasena').removeAttribute('required');
            document.getElementById('confirmar_contrasena').removeAttribute('required');
            document.getElementById('rol').value = emp.rol;
            document.getElementById('estado').value = emp.estado;
            document.getElementById('modo').value = 'editar';
            document.getElementById('modalTitulo').textContent = 'Editar Empleado';
            document.getElementById('passRequired').style.display = 'none';
            document.getElementById('passConfirmRequired').style.display = 'none';
            document.getElementById('passHint').style.display = 'inline';
            document.getElementById('contrasena').classList.remove('is-invalid');
            document.getElementById('confirmar_contrasena').classList.remove('is-invalid');
            
            var modal = new bootstrap.Modal(document.getElementById('modalEmpleado'));
            modal.show();
        }
        
        // Validar contraseñas antes de enviar
        document.getElementById('formEmpleado').addEventListener('submit', function(e) {
            var contrasena = document.getElementById('contrasena').value;
            var confirmar = document.getElementById('confirmar_contrasena').value;
            var modo = document.getElementById('modo').value;
            
            // Si es modo crear, la contraseña es obligatoria
            if (modo === 'crear' && !contrasena) {
                e.preventDefault();
                document.getElementById('contrasena').classList.add('is-invalid');
                return false;
            }
            
            // Si hay contraseña, debe coincidir con la confirmación
            if (contrasena && contrasena !== confirmar) {
                e.preventDefault();
                document.getElementById('confirmar_contrasena').classList.add('is-invalid');
                return false;
            }
            
            return true;
        });
        
        // Quitar error al escribir
        document.getElementById('confirmar_contrasena').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
        document.getElementById('contrasena').addEventListener('input', function() {
            this.classList.remove('is-invalid');
            document.getElementById('confirmar_contrasena').classList.remove('is-invalid');
        });
    </script>
</body>
</html>

