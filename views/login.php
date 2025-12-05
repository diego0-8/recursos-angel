<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Recursos Humanos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="icon-circle">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h1>Recursos Humanos</h1>
                <p>Ingresa tus credenciales para continuar</p>
            </div>
            
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?action=login">
                <div class="form-floating position-relative">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" class="form-control" id="usuario" name="usuario" 
                           placeholder="Usuario" required autocomplete="username">
                    <label for="usuario">Usuario</label>
                </div>
                
                <div class="form-floating position-relative">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Contraseña" required autocomplete="current-password">
                    <label for="password">Contraseña</label>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Iniciar Sesión
                </button>
            </form>
            
            <p class="footer-text">
                &copy; <?php echo date('Y'); ?> Sistema de Recursos Humanos
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
