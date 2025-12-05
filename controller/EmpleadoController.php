<?php
/**
 * Controlador de Empleados
 * Maneja CRUD de empleados
 */

require_once __DIR__ . '/../models/Usuario.php';

class EmpleadoController {
    
    private $usuarioModel;
    private $porPagina = 5;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Obtener empleados paginados
     */
    public function obtenerEmpleadosPaginados($pagina = 1) {
        try {
            $db = getDBConnection();
            $offset = ($pagina - 1) * $this->porPagina;
            
            // Contar total
            $sqlCount = "SELECT COUNT(*) FROM usuarios";
            $stmtCount = $db->query($sqlCount);
            $total = $stmtCount->fetchColumn();
            
            // Obtener empleados paginados
            $sql = "SELECT cedula, nombre, usuario, rol, estado, created_at 
                    FROM usuarios 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', $this->porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'empleados' => $empleados,
                'total' => $total,
                'total_paginas' => ceil($total / $this->porPagina),
                'pagina_actual' => $pagina
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener empleados: " . $e->getMessage());
            return [
                'empleados' => [],
                'total' => 0,
                'total_paginas' => 0,
                'pagina_actual' => 1
            ];
        }
    }
    
    /**
     * Guardar empleado (crear o actualizar)
     */
    public function guardar($datos) {
        $modo = $datos['modo'] ?? 'crear';
        
        if ($modo === 'crear') {
            return $this->crear($datos);
        } else {
            return $this->actualizar($datos);
        }
    }
    
    /**
     * Crear nuevo empleado
     */
    private function crear($datos) {
        // Validar campos requeridos
        if (empty($datos['cedula']) || empty($datos['nombre']) || 
            empty($datos['usuario']) || empty($datos['contrasena']) || 
            empty($datos['rol'])) {
            return ['success' => false, 'error' => 'Todos los campos son obligatorios'];
        }
        
        // Validar cédula
        if (!is_numeric($datos['cedula']) || strlen($datos['cedula']) < 5 || strlen($datos['cedula']) > 15) {
            return ['success' => false, 'error' => 'Cédula inválida'];
        }
        
        // Validar nombre
        $nombre = trim($datos['nombre']);
        if (strlen($nombre) < 3 || strlen($nombre) > 100) {
            return ['success' => false, 'error' => 'El nombre debe tener entre 3 y 100 caracteres'];
        }
        
        // Validar usuario
        $usuario = trim($datos['usuario']);
        if (strlen($usuario) < 3 || strlen($usuario) > 50) {
            return ['success' => false, 'error' => 'El usuario debe tener entre 3 y 50 caracteres'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
            return ['success' => false, 'error' => 'El usuario solo puede contener letras, números y guiones bajos'];
        }
        
        // Validar contraseña
        if (strlen($datos['contrasena']) < 6) {
            return ['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        // Validar rol
        $rolesPermitidos = ['administrador', 'contratante', 'aspirante', 'empleado'];
        if (!in_array($datos['rol'], $rolesPermitidos)) {
            return ['success' => false, 'error' => 'Rol inválido'];
        }
        
        // Sanitizar datos
        $datos['cedula'] = (int)$datos['cedula'];
        $datos['nombre'] = $nombre;
        $datos['usuario'] = $usuario;
        
        // Verificar si ya existe el usuario
        if ($this->usuarioModel->existeUsuario($datos['usuario'])) {
            return ['success' => false, 'error' => 'El nombre de usuario ya existe'];
        }
        
        // Verificar si ya existe la cédula
        if ($this->usuarioModel->obtenerPorCedula($datos['cedula'])) {
            return ['success' => false, 'error' => 'La cédula ya está registrada'];
        }
        
        $resultado = $this->usuarioModel->crear($datos);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'created'];
        }
        
        return ['success' => false, 'error' => 'Error al crear el empleado'];
    }
    
    /**
     * Actualizar empleado existente
     */
    private function actualizar($datos) {
        $cedula = $datos['cedula_original'] ?? $datos['cedula'];
        
        // Validar cédula
        if (empty($cedula) || !is_numeric($cedula)) {
            return ['success' => false, 'error' => 'Cédula inválida'];
        }
        
        // Validar campos requeridos
        if (empty($datos['nombre']) || empty($datos['usuario']) || empty($datos['rol'])) {
            return ['success' => false, 'error' => 'Todos los campos son obligatorios'];
        }
        
        // Validar nombre
        $nombre = trim($datos['nombre']);
        if (strlen($nombre) < 3 || strlen($nombre) > 100) {
            return ['success' => false, 'error' => 'El nombre debe tener entre 3 y 100 caracteres'];
        }
        
        // Validar usuario
        $usuario = trim($datos['usuario']);
        if (strlen($usuario) < 3 || strlen($usuario) > 50) {
            return ['success' => false, 'error' => 'El usuario debe tener entre 3 y 50 caracteres'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
            return ['success' => false, 'error' => 'El usuario solo puede contener letras, números y guiones bajos'];
        }
        
        // Validar contraseña si se proporciona
        if (!empty($datos['contrasena']) && strlen($datos['contrasena']) < 6) {
            return ['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        // Validar rol
        $rolesPermitidos = ['administrador', 'contratante', 'aspirante', 'empleado'];
        if (!in_array($datos['rol'], $rolesPermitidos)) {
            return ['success' => false, 'error' => 'Rol inválido'];
        }
        
        // Sanitizar datos
        $datos['nombre'] = $nombre;
        $datos['usuario'] = $usuario;
        
        // Verificar si el usuario ya existe (excluyendo el actual)
        if ($this->usuarioModel->existeUsuario($datos['usuario'], $cedula)) {
            return ['success' => false, 'error' => 'El nombre de usuario ya existe'];
        }
        
        $datosActualizar = [
            'nombre' => $datos['nombre'],
            'rol' => $datos['rol'],
            'estado' => $datos['estado'] ?? 'activo'
        ];
        
        // Solo actualizar contraseña si se proporcionó
        if (!empty($datos['contrasena'])) {
            $datosActualizar['contrasena'] = $datos['contrasena'];
        }
        
        $resultado = $this->usuarioModel->actualizar($cedula, $datosActualizar);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'updated'];
        }
        
        return ['success' => false, 'error' => 'Error al actualizar el empleado'];
    }
    
    /**
     * Cambiar estado de empleado
     */
    public function toggleEstado($cedula, $nuevoEstado) {
        $resultado = $this->usuarioModel->actualizar($cedula, ['estado' => $nuevoEstado]);
        
        if ($resultado) {
            return ['success' => true, 'message' => $nuevoEstado === 'activo' ? 'enabled' : 'disabled'];
        }
        
        return ['success' => false, 'error' => 'Error al cambiar el estado'];
    }
    
    /**
     * Eliminar empleado
     */
    public function eliminar($cedula) {
        try {
            $db = getDBConnection();
            $sql = "DELETE FROM usuarios WHERE cedula = :cedula";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_INT);
            $resultado = $stmt->execute();
            
            if ($resultado && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'deleted'];
            }
            
            return ['success' => false, 'error' => 'No se encontró el empleado'];
        } catch (PDOException $e) {
            error_log("Error al eliminar empleado: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al eliminar el empleado'];
        }
    }
}
?>

