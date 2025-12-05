<?php
/**
 * Configuración del Sistema IPS CRM
 * Archivo de configuración principal
 */

// Configuración de errores ANTES de cualquier output
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla en producción
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Crear directorio de logs si no existe
if (!is_dir(__DIR__ . '/logs')) {
    @mkdir(__DIR__ . '/logs', 0755, true);
}

// Cargar configuración de optimización si existe
if (file_exists(__DIR__ . '/config_optimizacion.php')) {
    require_once __DIR__ . '/config_optimizacion.php';
}

// Configuración de seguridad de sesión (ANTES de session_start)
// Solo configurar si la sesión no ha sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configurar opciones de sesión antes de iniciarla
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
    
    // Configuración de sesión única para este proyecto
    session_name('Recursos_SID');
    
    // Iniciar sesión solo si no hay headers enviados
    if (!headers_sent()) {
        session_start();
    } else {
        // Si ya se enviaron headers, intentar continuar la sesión existente
        if (isset($_COOKIE[session_name()])) {
            session_id($_COOKIE[session_name()]);
        }
    }
}

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de la aplicación
define('APP_NAME', 'IPS CRM');
define('APP_VERSION', '1.0.0');

// Detectar URL base automáticamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = rtrim($scriptPath, '/');
define('APP_URL', $protocol . '://' . $host . $baseUrl);

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'recursos');
define('DB_CHARSET', 'utf8mb4');

// Configuración de seguridad
define('PASSWORD_MIN_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5);

// Configuración de archivos
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB (aumentado para documentos Word)
define('ALLOWED_FILE_TYPES', ['docx']); // Solo documentos Word para contratos

// Configuración de roles
define('ROLES', [
    'administrador' => 'Administrador',
    'empleado' => 'Empleado'
]);

// Configuración de estados
define('ESTADOS', [
    'activo' => 'Activo',
    'inactivo' => 'Inactivo'
]);

/**
 * Función para obtener la conexión a la base de datos
 * @return PDO
 */
function getDBConnection() {
    static $connection = null;
    
    if ($connection === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $connection = new PDO($dsn, DB_USER, DB_PASS);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Asegurar que la conexión use UTF-8
            $connection->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
            $connection->exec("SET CHARACTER SET utf8mb4");
        } catch(PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
        }
    }
    
    return $connection;
}
?>
