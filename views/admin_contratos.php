<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos - Administrador</title>
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
        .empresa-card .badge-count {
            font-size: 1.5rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <?php 
    $action = 'admin_contratos';
    include __DIR__ . '/shared/navbar.php'; 
    ?>
    
    <div class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <h1>Gestión de Contratos</h1>
                <p>Selecciona una empresa para ver sus contratos</p>
            </div>
            
            <!-- Selección de Empresas -->
            <div class="row g-4">
                <?php foreach ($empresas as $empresa): ?>
                    <?php 
                    $claseEmpresa = strtolower(str_replace(' ', '', $empresa['codigo']));
                    $iconos = [
                        'ONIX' => 'bi-headset',
                        'NEXDATA' => 'bi-database',
                        'TYS' => 'bi-building'
                    ];
                    $icono = $iconos[$empresa['codigo']] ?? 'bi-building';
                    ?>
                    <div class="col-md-4">
                        <a href="index.php?action=ver_contratos_empresa&empresa_id=<?php echo $empresa['id']; ?>" 
                           class="text-decoration-none">
                            <div class="card-custom empresa-card <?php echo strtolower($empresa['codigo']); ?> text-center p-4">
                                <div class="icon-empresa">
                                    <i class="bi <?php echo $icono; ?>"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($empresa['nombre']); ?></h4>
                                <p class="text-muted mb-3"><?php echo htmlspecialchars($empresa['descripcion'] ?? ''); ?></p>
                                <div class="badge-count text-primary">
                                    <?php echo $empresa['total_contratos'] ?? 0; ?>
                                </div>
                                <small class="text-muted d-block">Contratos registrados</small>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

