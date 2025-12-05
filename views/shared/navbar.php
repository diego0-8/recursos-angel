<?php
/**
 * Navbar compartido para todos los usuarios
 */
$usuario_actual = $usuario_actual ?? null;
$rol = $usuario_actual['rol'] ?? '';
$nombre = $usuario_actual['nombre_completo'] ?? 'Usuario';
$iniciales = '';

// Obtener iniciales del nombre
$palabras = explode(' ', $nombre);
foreach ($palabras as $palabra) {
    if (!empty($palabra)) {
        $iniciales .= strtoupper($palabra[0]);
        if (strlen($iniciales) >= 2) break;
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-people-fill"></i>
            Recursos Humanos
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <?php if ($rol === 'administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'admin_dashboard' ? 'active' : ''; ?>" 
                           href="index.php?action=admin_dashboard">
                            <i class="bi bi-house-door me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'admin_empleados' ? 'active' : ''; ?>" 
                           href="index.php?action=admin_empleados">
                            <i class="bi bi-person-badge me-1"></i> Empleados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'admin_contratos' ? 'active' : ''; ?>" 
                           href="index.php?action=admin_contratos">
                            <i class="bi bi-file-earmark-text me-1"></i> Contratos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'admin_documentos' ? 'active' : ''; ?>" 
                           href="index.php?action=admin_documentos">
                            <i class="bi bi-folder me-1"></i> Documentos
                        </a>
                    </li>
                <?php elseif ($rol === 'contratante'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'contratante_dashboard' ? 'active' : ''; ?>" 
                           href="index.php?action=contratante_dashboard">
                            <i class="bi bi-house-door me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'contratante_contratos' ? 'active' : ''; ?>" 
                           href="index.php?action=contratante_contratos">
                            <i class="bi bi-file-earmark-text me-1"></i> Contratos
                        </a>
                    </li>
                <?php elseif ($rol === 'empleado'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'empleado_dashboard' ? 'active' : ''; ?>" 
                           href="index.php?action=empleado_dashboard">
                            <i class="bi bi-house-door me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($action ?? '') === 'mis_documentos' ? 'active' : ''; ?>" 
                           href="index.php?action=mis_documentos">
                            <i class="bi bi-folder me-1"></i> Mis Documentos
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" 
                       id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar"><?php echo htmlspecialchars($iniciales); ?></div>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($nombre); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="index.php?action=perfil">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?action=logout">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
