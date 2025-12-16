<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - Contratante</title>
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
        .empleados-section {
            margin-top: 2rem;
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
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Gestión de Empleados</h1>
                        <p>Administra los empleados de cada empresa</p>
                    </div>
                    <a href="index.php?action=contratante_dashboard" class="btn btn-outline-secondary">
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
            
            <!-- Selección de Empresas -->
            <?php if (empty($empresa_seleccionada)): ?>
                <div class="mb-4">
                    <h3 class="mb-4">Seleccionar Empresa</h3>
                </div>
                
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
                            <a href="index.php?action=contratante_empleados&empresa_id=<?php echo $empresa['id']; ?>" 
                               class="text-decoration-none">
                                <div class="card-custom empresa-card <?php echo strtolower($empresa['codigo']); ?> text-center p-4">
                                    <div class="icon-empresa">
                                        <i class="bi <?php echo $icono; ?>"></i>
                                    </div>
                                    <h4><?php echo htmlspecialchars($empresa['nombre']); ?></h4>
                                    <p class="text-muted mb-3"><?php echo htmlspecialchars($empresa['descripcion'] ?? ''); ?></p>
                                    <div class="mt-3">
                                        <span class="badge bg-primary">
                                            <i class="bi bi-people me-1"></i>Ver Empleados
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Lista de Empleados de la Empresa -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="mb-0">Empleados de <?php echo htmlspecialchars($empresa_seleccionada['nombre']); ?></h3>
                            <p class="text-muted mb-0">Total: <?php echo $total_empleados ?? count($empleados); ?> empleado(s)</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="index.php?action=contratante_documentos_empleados&empresa_id=<?php echo $empresa_seleccionada['id']; ?>" 
                               class="btn btn-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Documentos
                            </a>
                            <a href="index.php?action=contratante_empleados" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Volver a Empresas
                            </a>
                        </div>
                    </div>
                    
                    <!-- Buscador -->
                    <div class="card-custom mb-3">
                        <div class="card-body">
                            <form method="GET" action="index.php" class="row g-3 align-items-end">
                                <input type="hidden" name="action" value="contratante_empleados">
                                <input type="hidden" name="empresa_id" value="<?php echo $empresa_seleccionada['id']; ?>">
                                <div class="col-md-8">
                                    <label for="buscar" class="form-label">Buscar por nombre o cédula</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="buscar" 
                                           name="buscar" 
                                           value="<?php echo htmlspecialchars($buscar ?? ''); ?>" 
                                           placeholder="Ingrese nombre o cédula del empleado">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search me-2"></i>Buscar
                                    </button>
                                </div>
                                <?php if (!empty($buscar)): ?>
                                <div class="col-12">
                                    <a href="index.php?action=contratante_empleados&empresa_id=<?php echo $empresa_seleccionada['id']; ?>" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Limpiar búsqueda
                                    </a>
                                    <small class="text-muted ms-2">
                                        Mostrando resultados para: <strong><?php echo htmlspecialchars($buscar); ?></strong>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($empleados)): ?>
                    <div class="card-custom mb-4">
                        <div class="card-header">
                            <i class="bi bi-people me-2"></i>Lista de Empleados
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Cédula</th>
                                            <th>Estado</th>
                                            <th>Documentos</th>
                                            <th>Último Contrato</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($empleados as $empleado): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($empleado['nombre']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($empleado['cedula']); ?></td>
                                                <td>
                                                    <?php
                                                    $estadoClass = $empleado['estado'] === 'activo' ? 'success' : 'secondary';
                                                    $estadoLabel = $empleado['estado'] === 'activo' ? 'Activo' : 'Inactivo';
                                                    ?>
                                                    <span class="badge bg-<?php echo $estadoClass; ?>">
                                                        <?php echo $estadoLabel; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $totalDocs = (int)($empleado['total_documentos'] ?? 0);
                                                    if ($totalDocs > 0): 
                                                    ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-file-earmark-check me-1"></i><?php echo $totalDocs; ?> documento(s)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-file-earmark me-1"></i>0 documentos
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($empleado['ultimo_contrato_fecha'])): ?>
                                                        <span class="text-primary">
                                                            <i class="bi bi-calendar-check me-1"></i><?php echo date('d/m/Y', strtotime($empleado['ultimo_contrato_fecha'])); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            <i class="bi bi-clock me-1"></i><?php echo date('H:i', strtotime($empleado['ultimo_contrato_fecha'])); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">
                                                            <i class="bi bi-dash-circle me-1"></i>Sin contratos firmados
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="index.php?action=contratante_perfil_empleado&empleado_cedula=<?php echo $empleado['cedula']; ?>&empresa_id=<?php echo $empresa_seleccionada['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="Ver perfil completo">
                                                            <i class="bi bi-person-badge"></i> Perfil
                                                        </a>
                                                        <a href="index.php?action=contratante_documentos_empleados&empresa_id=<?php echo $empresa_seleccionada['id']; ?>" 
                                                           class="btn btn-sm btn-primary" title="Ver documentos disponibles">
                                                            <i class="bi bi-file-earmark-text"></i> Documentos
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
                    
                    <!-- Paginación -->
                    <?php if (($total_paginas ?? 0) > 1): ?>
                    <nav aria-label="Paginación de empleados">
                        <ul class="pagination justify-content-center">
                            <?php
                            $paramsBase = [
                                'action' => 'contratante_empleados',
                                'empresa_id' => $empresa_seleccionada['id']
                            ];
                            if (!empty($buscar)) {
                                $paramsBase['buscar'] = $buscar;
                            }
                            
                            // Botón Anterior
                            if (($pagina_actual ?? 1) > 1):
                                $paramsAnterior = $paramsBase;
                                $paramsAnterior['page'] = $pagina_actual - 1;
                                $urlAnterior = 'index.php?' . http_build_query($paramsAnterior);
                            ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $urlAnterior; ?>">
                                        <i class="bi bi-chevron-left"></i> Anterior
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bi bi-chevron-left"></i> Anterior
                                    </span>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            // Mostrar números de página
                            $pagina_actual = $pagina_actual ?? 1;
                            $total_paginas = $total_paginas ?? 1;
                            $inicio = max(1, $pagina_actual - 2);
                            $fin = min($total_paginas, $pagina_actual + 2);
                            
                            if ($inicio > 1):
                                $paramsPrimera = $paramsBase;
                                $paramsPrimera['page'] = 1;
                                $urlPrimera = 'index.php?' . http_build_query($paramsPrimera);
                            ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $urlPrimera; ?>">1</a>
                                </li>
                                <?php if ($inicio > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                <?php
                                $paramsPagina = $paramsBase;
                                $paramsPagina['page'] = $i;
                                $urlPagina = 'index.php?' . http_build_query($paramsPagina);
                                ?>
                                <li class="page-item <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo $urlPagina; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php
                            if ($fin < $total_paginas):
                                $paramsUltima = $paramsBase;
                                $paramsUltima['page'] = $total_paginas;
                                $urlUltima = 'index.php?' . http_build_query($paramsUltima);
                            ?>
                                <?php if ($fin < $total_paginas - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $urlUltima; ?>">
                                        <?php echo $total_paginas; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Botón Siguiente -->
                            <?php if ($pagina_actual < $total_paginas): ?>
                                <?php
                                $paramsSiguiente = $paramsBase;
                                $paramsSiguiente['page'] = $pagina_actual + 1;
                                $urlSiguiente = 'index.php?' . http_build_query($paramsSiguiente);
                                ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo $urlSiguiente; ?>">
                                        Siguiente <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        Siguiente <i class="bi bi-chevron-right"></i>
                                    </span>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="text-center text-muted mt-2">
                            <small>
                                Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?> 
                                (Mostrando <?php echo count($empleados); ?> de <?php echo $total_empleados; ?> empleados)
                            </small>
                        </div>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <?php if (!empty($buscar)): ?>
                            No se encontraron empleados que coincidan con la búsqueda: <strong><?php echo htmlspecialchars($buscar); ?></strong>
                        <?php else: ?>
                            No hay empleados registrados para esta empresa.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

