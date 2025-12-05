<?php
/**
 * Modelo de Usuario
 * Maneja operaciones de base de datos para usuarios
 */

class Usuario {
    
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Autenticar usuario con credenciales
     */
    public function autenticar($usuario, $password) {
        try {
            // Validar entrada
            if (empty($usuario) || empty($password)) {
                return false;
            }
            
            // Sanitizar usuario
            $usuario = trim($usuario);
            
            $sql = "SELECT cedula, nombre, usuario, contrasena, rol, estado 
                    FROM usuarios 
                    WHERE usuario = :usuario AND estado = 'activo'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && password_verify($password, $resultado['contrasena'])) {
                // No devolver la contraseña
                unset($resultado['contrasena']);
                // Agregar nombre_completo para compatibilidad
                $resultado['nombre_completo'] = $resultado['nombre'];
                return $resultado;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener usuario por cédula
     */
    public function obtenerPorCedula($cedula) {
        try {
            $sql = "SELECT cedula, nombre, usuario, rol, estado, created_at 
                    FROM usuarios 
                    WHERE cedula = :cedula";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public function crear($datos) {
        try {
            // Validar datos requeridos
            if (empty($datos['cedula']) || empty($datos['nombre']) || 
                empty($datos['usuario']) || empty($datos['contrasena'])) {
                return false;
            }
            
            // Sanitizar datos
            $cedula = (int)$datos['cedula'];
            $nombre = trim($datos['nombre']);
            $usuario = trim($datos['usuario']);
            $contrasena = $datos['contrasena'];
            $rol = $datos['rol'] ?? 'empleado';
            $estado = $datos['estado'] ?? 'activo';
            
            // Validar que el rol sea válido
            $rolesPermitidos = ['administrador', 'contratante', 'aspirante', 'empleado'];
            if (!in_array($rol, $rolesPermitidos)) {
                $rol = 'empleado';
            }
            
            // Validar que el estado sea válido
            $estadosPermitidos = ['activo', 'inactivo'];
            if (!in_array($estado, $estadosPermitidos)) {
                $estado = 'activo';
            }
            
            $sql = "INSERT INTO usuarios (cedula, nombre, usuario, contrasena, rol, estado) 
                    VALUES (:cedula, :nombre, :usuario, :contrasena, :rol, :estado)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindValue(':contrasena', password_hash($contrasena, PASSWORD_DEFAULT));
            $stmt->bindValue(':rol', $rol);
            $stmt->bindValue(':estado', $estado);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar($cedula, $datos) {
        try {
            $campos = [];
            $params = [':cedula' => $cedula];
            
            if (isset($datos['nombre'])) {
                $campos[] = "nombre = :nombre";
                $params[':nombre'] = $datos['nombre'];
            }
            
            if (isset($datos['rol'])) {
                $campos[] = "rol = :rol";
                $params[':rol'] = $datos['rol'];
            }
            
            if (isset($datos['estado'])) {
                $campos[] = "estado = :estado";
                $params[':estado'] = $datos['estado'];
            }
            
            if (isset($datos['contrasena']) && !empty($datos['contrasena'])) {
                $campos[] = "contrasena = :contrasena";
                $params[':contrasena'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            }
            
            if (empty($campos)) {
                return false;
            }
            
            $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE cedula = :cedula";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos($filtros = []) {
        try {
            $sql = "SELECT cedula, nombre, usuario, rol, estado, created_at 
                    FROM usuarios WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['rol'])) {
                $sql .= " AND rol = :rol";
                $params[':rol'] = $filtros['rol'];
            }
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar si existe un usuario con ese nombre de usuario
     */
    public function existeUsuario($usuario, $excluirCedula = null) {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario";
            $params = [':usuario' => $usuario];
            
            if ($excluirCedula) {
                $sql .= " AND cedula != :cedula";
                $params[':cedula'] = $excluirCedula;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar usuario: " . $e->getMessage());
            return false;
        }
    }
}
?>
