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

        /* Scroll cómodo dentro del modal de contratación */
        .modal-dialog.modal-xl.modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
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
                                                    <button type="button"
                                                            class="btn btn-sm btn-success btn-abrir-modal-contratar"
                                                            title="Marcar como contratado"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalContratarAspirante"
                                                            data-aspirante-id="<?php echo $aspirante['id']; ?>"
                                                            data-aspirante-nombre="<?php echo htmlspecialchars($aspirante['nombre']); ?>"
                                                            data-aspirante-cedula="<?php echo htmlspecialchars($aspirante['cedula']); ?>">
                                                        <i class="bi bi-check-circle"></i> Contratado
                                                    </button>
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
    
    <!-- Modal para contratar aspirante (datos adicionales de empleado) -->
    <div class="modal fade" id="modalContratarAspirante" tabindex="-1" aria-labelledby="modalContratarAspiranteLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="index.php?action=contratante_aspirante_contratar" enctype="multipart/form-data" id="formContratarAspirante">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalContratarAspiranteLabel">Completar datos para contratar aspirante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="aspirante_id" id="aspirante_id_modal">
                        <div class="alert alert-info mb-4" id="resumenAspiranteModal" style="display:none;">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="textoResumenAspirante"></span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha de nacimiento<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="fecha_nacimiento" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Barrio<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="barrio" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Localidad<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="localidad" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Salario a pagar (COP)<span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" 
                                           class="form-control campo-cop" 
                                           name="salario" 
                                           inputmode="numeric" 
                                           autocomplete="off"
                                           placeholder="Ej: 1.300.000"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Subsidio de transporte (COP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" 
                                           class="form-control campo-cop" 
                                           name="subsidio_transporte" 
                                           inputmode="numeric" 
                                           autocomplete="off"
                                           placeholder="Ej: 162.000">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">EPS<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="eps" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fondo de pensión<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fondo_pension" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fondo de cesantías<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="fondo_cesantias" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Caja de compensación<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="caja_compensacion" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Género<span class="text-danger">*</span></label>
                                <select class="form-select" name="genero" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="femenino">Femenino</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="no_binario">No binario</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RH<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="rh" placeholder="Ej: O+, A-, etc." required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nivel de escolaridad<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nivel_escolaridad" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado de escolaridad<span class="text-danger">*</span></label>
                                <select class="form-select" name="nivel_escolaridad_estado" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="en_progreso">En progreso</option>
                                    <option value="certificado">Certificado</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Estado civil<span class="text-danger">*</span></label>
                                <select class="form-select" name="estado_civil" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="soltero">Soltero(a)</option>
                                    <option value="casado">Casado(a)</option>
                                    <option value="union_libre">Unión libre</option>
                                    <option value="viudo">Viudo(a)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">¿Cuenta con computador?<span class="text-danger">*</span></label>
                                <select class="form-select" name="computador" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="si">Sí</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">¿Cuenta con internet?<span class="text-danger">*</span></label>
                                <select class="form-select" name="internet" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="si">Sí</option>
                                    <option value="no">No</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">¿Tiene hijos?<span class="text-danger">*</span></label>
                                <select class="form-select" name="tiene_hijos" id="tiene_hijos_select" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="si">Sí</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="grupo_numero_hijos" style="display:none;">
                                <label class="form-label">Número de hijos<span class="text-danger">*</span></label>
                                <input type="number" min="1" class="form-control" name="numero_hijos" id="numero_hijos_input">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nombre y apellidos contacto de emergencia<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contacto_emergencia_nombre" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Parentesco contacto de emergencia<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contacto_emergencia_parentesco" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Número de contacto de emergencia<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="contacto_emergencia_telefono" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">¿Exámenes médicos ocupacionales?<span class="text-danger">*</span></label>
                                <select class="form-select" name="examenes_medicos" id="examenes_medicos_select" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="si">Sí</option>
                                    <option value="no">No</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="grupo_fecha_examenes" style="display:none;">
                                <label class="form-label">Fecha de exámenes médicos<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="examenes_fecha" id="examenes_fecha_input">
                            </div>
                            <div class="col-md-4" id="grupo_pdf_examenes" style="display:none;">
                                <label class="form-label">Resultados exámenes médicos (PDF)<span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="examenes_pdf" id="examenes_pdf_input" accept="application/pdf">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Observaciones (opcional)</label>
                                <textarea class="form-control" name="observaciones" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar datos y contratar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('modalContratarAspirante');
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) return;
                    var aspiranteId = button.getAttribute('data-aspirante-id');
                    var aspiranteNombre = button.getAttribute('data-aspirante-nombre');
                    var aspiranteCedula = button.getAttribute('data-aspirante-cedula');

                    document.getElementById('aspirante_id_modal').value = aspiranteId;

                    var textoResumen = document.getElementById('textoResumenAspirante');
                    var resumenWrapper = document.getElementById('resumenAspiranteModal');
                    if (textoResumen && resumenWrapper) {
                        textoResumen.textContent = 'Está contratando a ' + aspiranteNombre + ' (Cédula: ' + aspiranteCedula + '). Complete los siguientes datos obligatorios.';
                        resumenWrapper.style.display = 'block';
                    }
                });
            }

            var tieneHijosSelect = document.getElementById('tiene_hijos_select');
            var grupoNumeroHijos = document.getElementById('grupo_numero_hijos');
            var numeroHijosInput = document.getElementById('numero_hijos_input');
            if (tieneHijosSelect && grupoNumeroHijos && numeroHijosInput) {
                tieneHijosSelect.addEventListener('change', function () {
                    if (this.value === 'si') {
                        grupoNumeroHijos.style.display = 'block';
                        numeroHijosInput.setAttribute('required', 'required');
                    } else {
                        grupoNumeroHijos.style.display = 'none';
                        numeroHijosInput.removeAttribute('required');
                        numeroHijosInput.value = '';
                    }
                });
            }

            var examenesSelect = document.getElementById('examenes_medicos_select');
            var grupoFechaExamenes = document.getElementById('grupo_fecha_examenes');
            var grupoPdfExamenes = document.getElementById('grupo_pdf_examenes');
            var examenesFechaInput = document.getElementById('examenes_fecha_input');
            var examenesPdfInput = document.getElementById('examenes_pdf_input');
            if (examenesSelect && grupoFechaExamenes && grupoPdfExamenes) {
                examenesSelect.addEventListener('change', function () {
                    if (this.value === 'si') {
                        grupoFechaExamenes.style.display = 'block';
                        grupoPdfExamenes.style.display = 'block';
                        if (examenesFechaInput) examenesFechaInput.setAttribute('required', 'required');
                        if (examenesPdfInput) examenesPdfInput.setAttribute('required', 'required');
                    } else {
                        grupoFechaExamenes.style.display = 'none';
                        grupoPdfExamenes.style.display = 'none';
                        if (examenesFechaInput) {
                            examenesFechaInput.removeAttribute('required');
                            examenesFechaInput.value = '';
                        }
                        if (examenesPdfInput) {
                            examenesPdfInput.removeAttribute('required');
                            examenesPdfInput.value = '';
                        }
                    }
                });
            }

            // Formateo simple de campos en pesos colombianos (solo miles, sin decimales)
            function formatearCOP(valor) {
                valor = valor.replace(/[^\d]/g, '');
                if (!valor) return '';
                return valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            document.querySelectorAll('.campo-cop').forEach(function (input) {
                input.addEventListener('input', function () {
                    var cursorPos = this.selectionStart;
                    var largoAntes = this.value.length;
                    this.value = formatearCOP(this.value);
                    var largoDespues = this.value.length;
                    this.selectionEnd = cursorPos + (largoDespues - largoAntes);
                });
            });

            // Antes de enviar el formulario, limpiar formato COP a números puros
            var formContratar = document.getElementById('formContratarAspirante');
            if (formContratar) {
                formContratar.addEventListener('submit', function () {
                    document.querySelectorAll('.campo-cop').forEach(function (input) {
                        if (input.value) {
                            // remover puntos de miles, dejar solo dígitos
                            input.value = input.value.replace(/[^\d]/g, '');
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>

