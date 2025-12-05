<?php
/**
 * Controlador de Autenticación
 * Maneja login, logout y verificación de roles
 */

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Mostrar formulario de login y procesar autenticación
     */
    public function login() {
        // Si ya está logueado, redirigir al dashboard correspondiente
        if ($this->estaAutenticado()) {
            $this->redirigirSegunRol();
            return;
        }
        
        $error = '';
        
        // Procesar formulario de login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($usuario) || empty($password)) {
                $error = 'Por favor, complete todos los campos.';
            } else {
                // Intentar autenticar
                $resultado = $this->usuarioModel->autenticar($usuario, $password);
                
                if ($resultado) {
                    // Guardar datos en sesión
                    $_SESSION['cedula'] = $resultado['cedula'];
                    $_SESSION['usuario'] = $resultado['usuario'];
                    $_SESSION['nombre_completo'] = $resultado['nombre_completo'];
                    $_SESSION['rol'] = $resultado['rol'];
                    $_SESSION['autenticado'] = true;
                    
                    // Redirigir según rol
                    $this->redirigirSegunRol();
                    return;
                } else {
                    $error = 'Usuario o contraseña incorrectos.';
                }
            }
        }
        
        // Mostrar vista de login
        include __DIR__ . '/../views/login.php';
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Redirigir al login
        header('Location: index.php?action=login');
        exit();
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function estaAutenticado() {
        return isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true;
    }
    
    /**
     * Requerir que el usuario tenga un rol específico
     */
    public function requerirRol($rol) {
        if (!$this->estaAutenticado()) {
            header('Location: index.php?action=login');
            exit();
        }
        
        if ($_SESSION['rol'] !== $rol) {
            // Redirigir al dashboard correspondiente si no tiene el rol requerido
            $this->redirigirSegunRol();
            exit();
        }
    }
    
    /**
     * Requerir autenticación (cualquier rol)
     */
    public function requerirAutenticacion() {
        if (!$this->estaAutenticado()) {
            header('Location: index.php?action=login');
            exit();
        }
    }
    
    /**
     * Obtener datos del usuario actual
     */
    public function obtenerUsuarioActual() {
        if (!$this->estaAutenticado()) {
            return null;
        }
        
        return [
            'cedula' => $_SESSION['cedula'],
            'usuario' => $_SESSION['usuario'],
            'nombre_completo' => $_SESSION['nombre_completo'],
            'rol' => $_SESSION['rol']
        ];
    }
    
    /**
     * Redirigir al dashboard según el rol del usuario
     */
    private function redirigirSegunRol() {
        $rol = $_SESSION['rol'] ?? '';
        
        switch ($rol) {
            case 'administrador':
                header('Location: index.php?action=admin_dashboard');
                break;
            case 'contratante':
                header('Location: index.php?action=contratante_dashboard');
                break;
            case 'aspirante':
                header('Location: index.php?action=aspirante_dashboard');
                break;
            case 'empleado':
                header('Location: index.php?action=empleado_dashboard');
                break;
            default:
                header('Location: index.php?action=login');
                break;
        }
        exit();
    }
}
?>
