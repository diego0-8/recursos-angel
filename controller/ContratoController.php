<?php
/**
 * Controlador de Contratos
 * Maneja CRUD de contratos
 */

class ContratoController {
    
    private $db;
    private $porPagina = 5;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Obtener todas las empresas con conteo de contratos
     */
    public function obtenerEmpresas() {
        try {
            $sql = "SELECT e.*, 
                    (SELECT COUNT(*) FROM contratos c WHERE c.empresa_id = e.id) as total_contratos
                    FROM empresas e 
                    WHERE e.estado = 'activo'
                    ORDER BY e.nombre";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empresas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener empresa por ID
     */
    public function obtenerEmpresa($id) {
        try {
            $sql = "SELECT * FROM empresas WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empresa: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener contratos de una empresa paginados
     */
    public function obtenerContratosPorEmpresa($empresaId, $pagina = 1) {
        try {
            $offset = ($pagina - 1) * $this->porPagina;
            
            // Contar total
            $sqlCount = "SELECT COUNT(*) FROM contratos WHERE empresa_id = :empresa_id";
            $stmtCount = $this->db->prepare($sqlCount);
            $stmtCount->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmtCount->execute();
            $total = $stmtCount->fetchColumn();
            
            // Obtener contratos con joins
            $sql = "SELECT c.*, 
                    e.nombre as empleado_nombre,
                    ct.nombre as contratante_nombre
                    FROM contratos c
                    LEFT JOIN usuarios e ON c.empleado_cedula = e.cedula
                    LEFT JOIN usuarios ct ON c.contratante_cedula = ct.cedula
                    WHERE c.empresa_id = :empresa_id
                    ORDER BY c.fecha_firma DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $this->porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'contratos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total' => $total,
                'total_paginas' => ceil($total / $this->porPagina),
                'pagina_actual' => $pagina
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener contratos: " . $e->getMessage());
            return [
                'contratos' => [],
                'total' => 0,
                'total_paginas' => 0,
                'pagina_actual' => 1
            ];
        }
    }
    
    /**
     * Obtener empleados para select
     */
    public function obtenerEmpleados() {
        try {
            $sql = "SELECT cedula, nombre FROM usuarios 
                    WHERE rol IN ('empleado', 'aspirante') AND estado = 'activo'
                    ORDER BY nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empleados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener contratantes para select
     */
    public function obtenerContratantes() {
        try {
            $sql = "SELECT cedula, nombre FROM usuarios 
                    WHERE rol IN ('contratante', 'administrador') AND estado = 'activo'
                    ORDER BY nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener contratantes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Guardar contrato (crear o actualizar)
     */
    public function guardar($datos) {
        // Validar que datos sea un array
        if (!is_array($datos)) {
            return ['success' => false, 'error' => 'Datos inválidos'];
        }
        
        $modo = $datos['modo'] ?? 'crear';
        
        if ($modo === 'crear') {
            return $this->crear($datos);
        } else {
            return $this->actualizar($datos);
        }
    }
    
    /**
     * Crear nuevo contrato
     */
    private function crear($datos) {
        try {
            // Validar campos requeridos
            if (empty($datos['empresa_id']) || !is_numeric($datos['empresa_id'])) {
                return ['success' => false, 'error' => 'Empresa inválida'];
            }
            
            if (empty($datos['contratante_cedula']) || !is_numeric($datos['contratante_cedula'])) {
                return ['success' => false, 'error' => 'Contratante inválido'];
            }
            
            // Validar fechas
            if (!empty($datos['fecha_inicio']) && !$this->validarFecha($datos['fecha_inicio'])) {
                return ['success' => false, 'error' => 'Fecha de inicio inválida'];
            }
            
            if (!empty($datos['fecha_fin']) && !$this->validarFecha($datos['fecha_fin'])) {
                return ['success' => false, 'error' => 'Fecha de fin inválida'];
            }
            
            if (!empty($datos['fecha_firma']) && !$this->validarFecha($datos['fecha_firma'])) {
                return ['success' => false, 'error' => 'Fecha de firma inválida'];
            }
            
            // Validar salario si se proporciona
            if (!empty($datos['salario']) && (!is_numeric($datos['salario']) || $datos['salario'] < 0)) {
                return ['success' => false, 'error' => 'Salario inválido'];
            }
            
            $sql = "INSERT INTO contratos (empresa_id, empleado_cedula, contratante_cedula, 
                    tipo_contrato, cargo, fecha_inicio, fecha_fin, fecha_firma, salario, estado, descripcion)
                    VALUES (:empresa_id, :empleado_cedula, :contratante_cedula, 
                    :tipo_contrato, :cargo, :fecha_inicio, :fecha_fin, :fecha_firma, :salario, :estado, :descripcion)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':empresa_id', $datos['empresa_id'], PDO::PARAM_INT);
            $stmt->bindValue(':empleado_cedula', !empty($datos['empleado_cedula']) ? $datos['empleado_cedula'] : null, PDO::PARAM_INT);
            $stmt->bindParam(':contratante_cedula', $datos['contratante_cedula'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_contrato', !empty($datos['tipo_contrato']) ? trim($datos['tipo_contrato']) : null);
            $stmt->bindValue(':cargo', !empty($datos['cargo']) ? trim($datos['cargo']) : null);
            $stmt->bindValue(':fecha_inicio', !empty($datos['fecha_inicio']) ? $datos['fecha_inicio'] : null);
            $stmt->bindValue(':fecha_fin', !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null);
            $stmt->bindValue(':fecha_firma', !empty($datos['fecha_firma']) ? $datos['fecha_firma'] : null);
            $stmt->bindValue(':salario', !empty($datos['salario']) ? $datos['salario'] : null);
            $stmt->bindValue(':estado', $datos['estado'] ?? 'activo');
            $stmt->bindValue(':descripcion', !empty($datos['descripcion']) ? trim($datos['descripcion']) : null);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'created'];
            }
            
            return ['success' => false, 'error' => 'Error al crear el contrato'];
        } catch (PDOException $e) {
            error_log("Error al crear contrato: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al crear el contrato'];
        }
    }
    
    /**
     * Actualizar contrato existente
     */
    private function actualizar($datos) {
        try {
            // Validar ID del contrato
            if (empty($datos['contrato_id']) || !is_numeric($datos['contrato_id'])) {
                return ['success' => false, 'error' => 'ID de contrato inválido'];
            }
            
            // Validar campos requeridos
            if (empty($datos['contratante_cedula']) || !is_numeric($datos['contratante_cedula'])) {
                return ['success' => false, 'error' => 'Contratante inválido'];
            }
            
            // Validar fechas
            if (!empty($datos['fecha_inicio']) && !$this->validarFecha($datos['fecha_inicio'])) {
                return ['success' => false, 'error' => 'Fecha de inicio inválida'];
            }
            
            if (!empty($datos['fecha_fin']) && !$this->validarFecha($datos['fecha_fin'])) {
                return ['success' => false, 'error' => 'Fecha de fin inválida'];
            }
            
            if (!empty($datos['fecha_firma']) && !$this->validarFecha($datos['fecha_firma'])) {
                return ['success' => false, 'error' => 'Fecha de firma inválida'];
            }
            
            // Validar salario si se proporciona
            if (!empty($datos['salario']) && (!is_numeric($datos['salario']) || $datos['salario'] < 0)) {
                return ['success' => false, 'error' => 'Salario inválido'];
            }
            
            $sql = "UPDATE contratos SET 
                    empleado_cedula = :empleado_cedula,
                    contratante_cedula = :contratante_cedula,
                    tipo_contrato = :tipo_contrato,
                    cargo = :cargo,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    fecha_firma = :fecha_firma,
                    salario = :salario,
                    estado = :estado,
                    descripcion = :descripcion
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $datos['contrato_id'], PDO::PARAM_INT);
            $stmt->bindValue(':empleado_cedula', !empty($datos['empleado_cedula']) ? $datos['empleado_cedula'] : null, PDO::PARAM_INT);
            $stmt->bindParam(':contratante_cedula', $datos['contratante_cedula'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_contrato', !empty($datos['tipo_contrato']) ? trim($datos['tipo_contrato']) : null);
            $stmt->bindValue(':cargo', !empty($datos['cargo']) ? trim($datos['cargo']) : null);
            $stmt->bindValue(':fecha_inicio', !empty($datos['fecha_inicio']) ? $datos['fecha_inicio'] : null);
            $stmt->bindValue(':fecha_fin', !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null);
            $stmt->bindValue(':fecha_firma', !empty($datos['fecha_firma']) ? $datos['fecha_firma'] : null);
            $stmt->bindValue(':salario', !empty($datos['salario']) ? $datos['salario'] : null);
            $stmt->bindValue(':estado', $datos['estado'] ?? 'activo');
            $stmt->bindValue(':descripcion', !empty($datos['descripcion']) ? trim($datos['descripcion']) : null);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'updated'];
            }
            
            return ['success' => false, 'error' => 'Error al actualizar el contrato'];
        } catch (PDOException $e) {
            error_log("Error al actualizar contrato: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al actualizar el contrato'];
        }
    }
    
    /**
     * Eliminar contrato
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM contratos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'deleted'];
            }
            
            return ['success' => false, 'error' => 'No se encontró el contrato'];
        } catch (PDOException $e) {
            error_log("Error al eliminar contrato: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error al eliminar el contrato'];
        }
    }
    
    /**
     * Validar formato de fecha
     */
    private function validarFecha($fecha) {
        if (empty($fecha)) {
            return true; // Fechas opcionales
        }
        
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}
?>

